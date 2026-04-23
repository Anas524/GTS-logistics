<?php

namespace App\Http\Controllers;

use App\Models\DhFolder;
use App\Models\DhRecord;
use Illuminate\Http\Request;
use App\Models\DhRecordAttachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class DocumentHubController extends Controller
{
    public function __construct()
    {
        // both admin & consultant must be logged in
        $this->middleware('auth');
    }

    // List folders (root only)
    public function index()
    {
        $folders = DhFolder::with([
            'records.attachments' => function ($q) {
                $q->where('is_trashed', false);
            },
            'children' => function ($q) {
                $q->where('is_trashed', false)->orderBy('created_at', 'desc');
            },
            'children.records.attachments' => function ($q) {
                $q->where('is_trashed', false);
            },
        ])
            ->whereNull('parent_id')
            ->where('is_trashed', false)
            ->orderBy('created_at', 'desc')
            ->get();

        $existingNames = $folders->pluck('folder_name')
            ->map(function ($n) {
                return mb_strtolower(trim($n));
            })
            ->values()
            ->all();

        session([
            'dh_trash_back_url' => route('dh.index'),
            'dh_trash_back_label' => 'Back to Document Hub',
        ]);

        return view('admin.dh.index', compact('folders', 'existingNames'));
    }

    // Create folder or subfolder (index + show modals)
    public function storeFolder(Request $request)
    {
        $data = $request->validate([
            'folder_name' => ['required', 'string', 'max:150'],
            'month_label' => ['nullable', 'string', 'max:50'],
            'remarks'     => ['nullable', 'string'],
            'parent_id'   => ['nullable', 'exists:dh_folders,id'],
        ]);

        $folder = DhFolder::create($data);

        if (!empty($data['parent_id'])) {
            return redirect()
                ->route('dh.show', $data['parent_id'])
                ->with('status', 'Folder created.');
        }

        return redirect()
            ->route('dh.show', $folder)
            ->with('status', 'Folder created.');
    }

    public function show(DhFolder $folder)
    {
        if ($folder->is_trashed) {
            abort(404);
        }

        $folder->load([
            'children' => function ($q) {
                $q->where('is_trashed', false)->orderBy('created_at', 'desc');
            },
            'parent',
            'parent.parent',
            'parent.parent.parent',
            'parent.parent.parent.parent',
        ]);

        $subfolders = $folder->children;

        $records = $folder->records()
            ->with(['attachments' => function ($q) {
                $q->where('is_trashed', false);
            }])
            ->latest('doc_date')
            ->get();

        $folderOptions = DhFolder::query()
            ->where('is_trashed', false)
            ->where('id', '!=', $folder->id)
            ->orderBy('folder_name')
            ->get(['id', 'folder_name', 'parent_id']);

        $breadcrumbs = collect();
        $current = $folder;

        while ($current) {
            $breadcrumbs->prepend($current);
            $current = $current->parent;
        }

        session([
            'dh_trash_back_url' => route('dh.show', $folder),
            'dh_trash_back_label' => 'Back to Folder: ' . $folder->folder_name,
        ]);

        return view('admin.dh.show', compact(
            'folder',
            'subfolders',
            'records',
            'folderOptions',
            'breadcrumbs'
        ));
    }

    // Add a row (no file yet)
    public function storeRecord(DhFolder $folder, Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();
        if (!$user || (!$user->isAdmin() && !$user->isConsultant())) {
            abort(403);
        }

        $data = $request->validate([
            'doc_date'    => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
        ]);

        $data['folder_id'] = $folder->id;

        DhRecord::create($data);

        return back()->with('status', 'Record added.');
    }

    // Upload / replace file for a record
    public function uploadFile(DhRecord $record, Request $request)
    {
        $user = $request->user();
        if (!$user || (!$user->isAdmin() && !$user->isConsultant())) {
            abort(403);
        }

        $request->validate([
            'description' => ['nullable', 'string', 'max:1000'],
            'files'       => ['required', 'array', 'min:1'],
            'files.*'     => ['file', 'max:25600', 'mimes:pdf,jpg,jpeg,png,webp,xls,xlsx,csv'], // 25MB
        ]);

        foreach ($request->file('files') as $file) {
            $path = $file->store('dh-files', 'public');

            DhRecordAttachment::create([
                'record_id'     => $record->id,
                'file_path'     => $path,
                'original_name' => $file->getClientOriginalName(),
                'description'   => $request->input('description'),
                'mime'          => $file->getClientMimeType(),
                'size'          => $file->getSize(),
            ]);
        }

        return back()->with('status', 'Files uploaded.');
    }

    // Download / view attachment
    public function download(DhRecord $record, Request $request)
    {
        if (!$record->file_path) {
            abort(404);
        }

        $fullPath     = Storage::disk('public')->path($record->file_path);
        $downloadName = $record->original_name ?: 'document.pdf';

        // Inline for iframe
        if ($request->boolean('inline')) {
            return response()->file($fullPath, [
                'Content-Disposition' => 'inline; filename="' . $downloadName . '"',
            ]);
        }

        // Normal download
        return response()->download($fullPath, $downloadName);
    }

    public function subfolderIndex(DhFolder $folder)
    {
        if ($folder->is_trashed) {
            abort(404);
        }

        $subfolders = $folder->children()
            ->with([
                'records.attachments' => function ($q) {
                    $q->where('is_trashed', false);
                },
                'children',
            ])
            ->where('is_trashed', false)
            ->orderBy('created_at', 'desc')
            ->get();

        $existingSubNames = $subfolders->pluck('folder_name')
            ->map(function ($n) {
                return mb_strtolower(trim($n));
            })
            ->values()
            ->all();

        session([
            'dh_trash_back_url' => route('dh.subfolders.index', $folder),
            'dh_trash_back_label' => 'Back to Subfolders: ' . $folder->folder_name,
        ]);

        return view('admin.dh.subfolders', compact('folder', 'subfolders', 'existingSubNames'));
    }

    public function destroyRecord(DhRecord $record)
    {
        // delete file if exists
        if ($record->file_path) {
            Storage::disk('public')->delete($record->file_path);
        }

        $record->delete();

        return back()->with('status', 'Record deleted.');
    }

    public function destroy(DhFolder $folder)
    {
        // Remember parent BEFORE deleting
        $parentId = $folder->parent_id;

        // Recursively delete this folder, its subfolders, records and files
        $this->deleteFolderRecursive($folder);

        // If this was a subfolder → go back to that parent's subfolders page
        if ($parentId) {
            return redirect()
                ->route('dh.subfolders.index', $parentId)
                ->with('status', 'Subfolder deleted.');
        }

        // Root folder → go back to main Document Hub
        return redirect()
            ->route('dh.index')
            ->with('status', 'Folder deleted.');
    }

    protected function deleteFolderRecursive(DhFolder $folder)
    {
        // Load relations so we can traverse
        $folder->load(['records', 'children']);

        // Delete files for this folder's records
        foreach ($folder->records as $rec) {
            if ($rec->file_path) {
                Storage::disk('public')->delete($rec->file_path);
            }
            $rec->delete();
        }

        // Recurse into children
        foreach ($folder->children as $child) {
            $this->deleteFolderRecursive($child);
        }

        // Finally delete the folder itself
        $folder->delete();
    }

    public function downloadAll(DhFolder $folder)
    {
        $zip = new \ZipArchive();

        $safeName = \Illuminate\Support\Str::slug($folder->folder_name ?: 'folder');
        $zipName = $safeName . '-' . now()->format('Ymd-His') . '.zip';

        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipPath = $tempDir . '/' . $zipName;

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Could not create ZIP file.');
        }

        $addedAny = $this->addFolderToZip($zip, $folder, $safeName);

        $zip->close();

        if (!$addedAny) {
            if (file_exists($zipPath)) {
                @unlink($zipPath);
            }

            return back()->with('status', 'No files to download in this folder.');
        }

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }

    /**
     * Recursively add a folder and all nested subfolders/files into the zip.
     */
    protected function addFolderToZip(\ZipArchive $zip, \App\Models\DhFolder $folder, string $basePath = ''): bool
    {
        $addedAny = false;

        $folder->load([
            'records.attachments' => function ($q) {
                $q->where('is_trashed', false)->orderBy('id');
            },
            'children' => function ($q) {
                $q->where('is_trashed', false)->orderBy('created_at', 'desc');
            },
        ]);

        $currentFolderPath = trim($basePath, '/');

        if ($currentFolderPath !== '') {
            $zip->addEmptyDir($currentFolderPath);
        }

        foreach ($folder->records as $record) {
            foreach (($record->attachments ?? collect()) as $att) {
                if (!$att->file_path) {
                    continue;
                }

                $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($att->file_path);

                if (!file_exists($fullPath)) {
                    continue;
                }

                $originalName = $att->original_name ?: basename($fullPath);
                $nameInZip = $currentFolderPath !== ''
                    ? $currentFolderPath . '/' . $originalName
                    : $originalName;

                $nameInZip = $this->getUniqueZipPath($zip, $nameInZip);

                $zip->addFile($fullPath, $nameInZip);
                $addedAny = true;
            }
        }

        foreach ($folder->children as $childFolder) {
            $childSafeName = \Illuminate\Support\Str::slug($childFolder->folder_name ?: ('folder-' . $childFolder->id));
            $childPath = $currentFolderPath !== ''
                ? $currentFolderPath . '/' . $childSafeName
                : $childSafeName;

            if ($this->addFolderToZip($zip, $childFolder, $childPath)) {
                $addedAny = true;
            }
        }

        return $addedAny;
    }

    /**
     * Ensure duplicate file names inside the same zip path become unique.
     */
    protected function getUniqueZipPath(\ZipArchive $zip, string $path): string
    {
        $dir = pathinfo($path, PATHINFO_DIRNAME);
        $dir = $dir === '.' ? '' : $dir;

        $filename = pathinfo($path, PATHINFO_FILENAME);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $candidate = $path;
        $n = 1;

        while ($zip->locateName($candidate) !== false) {
            $suffix = ' (' . $n++ . ')';
            $newName = $filename . $suffix . ($extension ? '.' . $extension : '');
            $candidate = $dir !== '' ? $dir . '/' . $newName : $newName;
        }

        return $candidate;
    }

    public function recordAttachments(DhRecord $record)
    {
        $record->load(['attachments' => function ($q) {
            $q->where('is_trashed', false);
        }]);

        return response()->json([
            'items' => $record->attachments->map(fn($a) => [
                'id' => $a->id,
                'name' => $a->original_name ?: 'Attachment',
                'inline_url' => route('dh.attachments.download', [$a, 'inline' => 1]),
                'download_url' => route('dh.attachments.download', $a),
            ])->values()->all()
        ]);
    }

    public function downloadAttachment(DhRecordAttachment $att, Request $request)
    {
        $fullPath = Storage::disk('public')->path($att->file_path);
        if (!file_exists($fullPath)) abort(404);

        $downloadName = $att->original_name ?: 'document.pdf';

        if ($request->boolean('inline')) {
            return response()->file($fullPath, [
                'Content-Disposition' => 'inline; filename="' . $downloadName . '"',
            ]);
        }

        return response()->download($fullPath, $downloadName);
    }

    public function downloadRecordAll(DhRecord $record)
    {
        $record->load(['attachments' => function ($q) {
            $q->where('is_trashed', false);
        }]);

        if ($record->attachments->isEmpty()) {
            return back()->with('status', 'No attachments to download.');
        }

        $zip = new \ZipArchive();

        $safe = \Illuminate\Support\Str::slug($record->description ?: ('record-' . $record->id));
        $zipName = $safe . '-' . now()->format('Ymd-His') . '.zip';

        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipPath = $tempDir . '/' . $zipName;

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Could not create ZIP file.');
        }

        foreach ($record->attachments as $att) {
            if (!$att->file_path) continue;

            $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($att->file_path);
            if (!file_exists($fullPath)) continue;

            $nameInZip = $att->original_name ?: basename($fullPath);

            $base = pathinfo($nameInZip, PATHINFO_FILENAME);
            $ext  = pathinfo($nameInZip, PATHINFO_EXTENSION);
            $n = 1;

            while ($zip->locateName($nameInZip) !== false) {
                $suffix = ' (' . $n++ . ')';
                $nameInZip = $base . $suffix . ($ext ? '.' . $ext : '');
            }

            $zip->addFile($fullPath, $nameInZip);
        }

        $zip->close();

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }

    public function deleteAttachment(Request $request, DhRecordAttachment $attachment)
    {
        $user = $request->user();

        if (!$user || (!$user->isAdmin() && !$user->isConsultant())) {
            abort(403);
        }

        if ($attachment->file_path) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        return response()->json(['ok' => true]);
    }

    public function rename(Request $request, DhFolder $folder)
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            abort(403);
        }

        if ($folder->is_trashed) {
            return back()->with('status', 'Cannot rename a folder in trash.');
        }

        $data = $request->validate([
            'folder_name' => ['required', 'string', 'max:150'],
        ]);

        $folder->update([
            'folder_name' => $data['folder_name'],
        ]);

        return back()->with('status', 'Folder renamed.');
    }

    public function updateFolderDescription(Request $request, DhFolder $folder)
    {
        $user = $request->user();

        if (!$user || !$user->isAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'month_label' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $folder->update([
            'month_label' => $data['month_label'] ?: null,
            'remarks' => $data['remarks'] ?: null,
        ]);

        return back()->with('success', 'Folder details updated successfully.');
    }

    public function moveToTrash(Request $request, DhFolder $folder)
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            abort(403);
        }

        $this->markFolderTreeAsTrashed($folder, $user->id);

        return back()->with('status', 'Folder moved to trash.');
    }

    public function trashIndex()
    {
        $folders = DhFolder::with([
            'records.attachments',
            'children',
            'parent',
        ])
            ->where('is_trashed', true)
            ->orderByDesc('trashed_at')
            ->get();

        $attachments = DhRecordAttachment::with([
            'record.folder',
        ])
            ->where('is_trashed', true)
            ->orderByDesc('trashed_at')
            ->get();

        $backUrl = session('dh_trash_back_url', route('dh.index'));
        $backLabel = session('dh_trash_back_label', 'Back to My Drive');

        return view('admin.dh.trash', compact('folders', 'attachments', 'backUrl', 'backLabel'));
    }

    public function restore(Request $request, DhFolder $folder)
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            abort(403);
        }

        $this->restoreFolderTree($folder);

        return back()->with('status', 'Folder restored.');
    }

    public function quickUploadToFolder(DhFolder $folder, Request $request)
    {
        $user = $request->user();
        if (!$user || (!$user->isAdmin() && !$user->isConsultant())) {
            abort(403);
        }

        $data = $request->validate([
            'doc_date'     => ['nullable', 'date'],
            'description'  => ['nullable', 'string', 'max:1000'],
            'files'        => ['required', 'array', 'min:1'],
            'files.*'      => ['file', 'max:25600', 'mimes:pdf,jpg,jpeg,png,webp,mp4,mov,avi,webm,xls,xlsx,csv'],
        ]);

        foreach ($request->file('files', []) as $file) {
            $record = DhRecord::create([
                'folder_id'   => $folder->id,
                'doc_date'    => $data['doc_date'] ?? now()->toDateString(),
                'description' => null,
            ]);

            $path = $file->store('dh-files', 'public');

            DhRecordAttachment::create([
                'record_id'     => $record->id,
                'file_path'     => $path,
                'original_name' => $file->getClientOriginalName(),
                'description'   => $data['description'] ?? null,
                'mime'          => $file->getClientMimeType(),
                'size'          => $file->getSize(),
            ]);
        }

        return back()->with('status', 'Files uploaded to folder.');
    }

    protected function markFolderTreeAsTrashed(DhFolder $folder, ?int $userId = null): void
    {
        $folder->load('children');

        $folder->update([
            'is_trashed' => true,
            'trashed_at' => now(),
            'trashed_by' => $userId,
        ]);

        foreach ($folder->children as $child) {
            $this->markFolderTreeAsTrashed($child, $userId);
        }
    }

    protected function restoreFolderTree(DhFolder $folder): void
    {
        $folder->load('children');

        $folder->update([
            'is_trashed' => false,
            'trashed_at' => null,
            'trashed_by' => null,
        ]);

        foreach ($folder->children as $child) {
            $this->restoreFolderTree($child);
        }
    }

    public function renameAttachment(Request $request, DhRecordAttachment $attachment)
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            abort(403);
        }

        if ($attachment->is_trashed) {
            return back()->with('status', 'Cannot rename a file in trash.');
        }

        $data = $request->validate([
            'original_name' => ['required', 'string', 'max:255'],
        ]);

        $attachment->update([
            'original_name' => $data['original_name'],
        ]);

        return back()->with('status', 'File renamed.');
    }

    public function trashAttachment(Request $request, DhRecordAttachment $attachment)
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            abort(403);
        }

        $attachment->update([
            'is_trashed' => true,
            'trashed_at' => now(),
            'trashed_by' => $user->id,
        ]);

        return back()->with('status', 'File moved to trash.');
    }

    public function restoreAttachment(Request $request, DhRecordAttachment $attachment)
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            abort(403);
        }

        $attachment->update([
            'is_trashed' => false,
            'trashed_at' => null,
            'trashed_by' => null,
        ]);

        return back()->with('status', 'File restored.');
    }

    public function moveAttachment(Request $request, DhRecordAttachment $attachment)
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'target_folder_id' => ['required', 'exists:dh_folders,id'],
        ]);

        $targetFolder = DhFolder::findOrFail($data['target_folder_id']);

        $targetRecord = DhRecord::create([
            'folder_id' => $targetFolder->id,
            'doc_date' => now()->toDateString(),
            'description' => 'Moved file',
        ]);

        $attachment->update([
            'record_id' => $targetRecord->id,
        ]);

        return back()->with('status', 'File moved successfully.');
    }

    public function generateAttachmentShareLink(Request $request, DhRecordAttachment $attachment)
    {
        $user = $request->user();
        if (!$user || (!$user->isAdmin() && !$user->isConsultant())) {
            abort(403);
        }

        if (!$attachment->share_token) {
            $attachment->update([
                'share_token' => Str::random(40),
            ]);
            $attachment->refresh();
        }

        return response()->json([
            'ok' => true,
            'share_url' => route('dh.attachments.download', $attachment) . '?token=' . $attachment->share_token,
        ]);
    }

    public function updateAttachmentDescription(Request $request, DhRecordAttachment $attachment)
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $attachment->update([
            'description' => $data['description'] ?? null,
        ]);

        return response()->json([
            'ok' => true,
            'description' => $attachment->description,
        ]);
    }

    public function forceDeleteFolder(Request $request, DhFolder $folder)
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            abort(403);
        }

        if (!$folder->is_trashed) {
            return back()->with('status', 'Only trashed folders can be permanently deleted.');
        }

        $this->forceDeleteFolderTree($folder);

        return back()->with('status', 'Folder permanently deleted.');
    }

    public function forceDeleteAttachment(Request $request, DhRecordAttachment $attachment)
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            abort(403);
        }

        if (!$attachment->is_trashed) {
            return back()->with('status', 'Only trashed files can be permanently deleted.');
        }

        if ($attachment->file_path) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $record = $attachment->record;
        $attachment->delete();

        if ($record && $record->attachments()->count() === 0) {
            $record->delete();
        }

        return back()->with('status', 'File permanently deleted.');
    }

    protected function forceDeleteFolderTree(DhFolder $folder): void
    {
        $folder->load([
            'children',
            'records.attachments',
        ]);

        foreach ($folder->records as $record) {
            foreach ($record->attachments as $attachment) {
                if ($attachment->file_path) {
                    Storage::disk('public')->delete($attachment->file_path);
                }
                $attachment->delete();
            }

            $record->delete();
        }

        foreach ($folder->children as $child) {
            $this->forceDeleteFolderTree($child);
        }

        $folder->delete();
    }

    public function restoreSelectedTrash(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'folder_ids' => ['nullable', 'array'],
            'folder_ids.*' => ['integer', 'exists:dh_folders,id'],
            'attachment_ids' => ['nullable', 'array'],
            'attachment_ids.*' => ['integer', 'exists:dh_record_attachments,id'],
        ]);

        $folderIds = collect($data['folder_ids'] ?? [])->filter()->values();
        $attachmentIds = collect($data['attachment_ids'] ?? [])->filter()->values();

        if ($folderIds->isEmpty() && $attachmentIds->isEmpty()) {
            return back()->with('status', 'No trashed items selected.');
        }

        if ($folderIds->isNotEmpty()) {
            DhFolder::whereIn('id', $folderIds)
                ->where('is_trashed', true)
                ->get()
                ->each(function ($folder) {
                    $this->restoreFolderTree($folder);
                });
        }

        if ($attachmentIds->isNotEmpty()) {
            DhRecordAttachment::whereIn('id', $attachmentIds)
                ->where('is_trashed', true)
                ->update([
                    'is_trashed' => false,
                    'trashed_at' => null,
                    'trashed_by' => null,
                ]);
        }

        return back()->with('status', 'Selected trash items restored.');
    }

    public function forceDeleteSelectedTrash(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'folder_ids' => ['nullable', 'array'],
            'folder_ids.*' => ['integer', 'exists:dh_folders,id'],
            'attachment_ids' => ['nullable', 'array'],
            'attachment_ids.*' => ['integer', 'exists:dh_record_attachments,id'],
        ]);

        $folderIds = collect($data['folder_ids'] ?? [])->filter()->values();
        $attachmentIds = collect($data['attachment_ids'] ?? [])->filter()->values();

        if ($folderIds->isEmpty() && $attachmentIds->isEmpty()) {
            return back()->with('status', 'No trashed items selected.');
        }

        if ($folderIds->isNotEmpty()) {
            DhFolder::whereIn('id', $folderIds)
                ->where('is_trashed', true)
                ->get()
                ->each(function ($folder) {
                    $this->forceDeleteFolderTree($folder);
                });
        }

        if ($attachmentIds->isNotEmpty()) {
            DhRecordAttachment::whereIn('id', $attachmentIds)
                ->where('is_trashed', true)
                ->get()
                ->each(function ($attachment) {
                    if ($attachment->file_path) {
                        Storage::disk('public')->delete($attachment->file_path);
                    }

                    $record = $attachment->record;
                    $attachment->delete();

                    if ($record && $record->attachments()->count() === 0) {
                        $record->delete();
                    }
                });
        }

        return back()->with('status', 'Selected trash items permanently deleted.');
    }
}
