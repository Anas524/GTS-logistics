<?php

namespace App\Http\Controllers;

use App\Models\DhFolder;
use App\Models\DhRecord;
use Illuminate\Http\Request;
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
        $folders = DhFolder::with(['records', 'children']) // eager load
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->get();

        // Normalise names once in PHP, not in Blade
        $existingNames = $folders->pluck('folder_name')
            ->map(function ($n) {
                return mb_strtolower(trim($n));
            })
            ->values()
            ->all();

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
            // Subfolder: go back to subfolder index of the parent
            return redirect()
                ->route('dh.subfolders.index', $data['parent_id'])
                ->with('status', 'Subfolder created.');
        }

        // Root folder: go to show page
        return redirect()
            ->route('dh.show', $folder)
            ->with('status', 'Folder created.');
    }

    public function show(DhFolder $folder)
    {
        $folder->load(['records']);

        $records = $folder->records()->latest('doc_date')->get();

        return view('admin.dh.show', compact('folder', 'records'));
    }

    // Add a row (no file yet)
    public function storeRecord(DhFolder $folder, Request $request)
    {
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
        $data = $request->validate([
            'file' => ['required', 'file', 'max:10240'], // 10 MB
        ]);

        // Delete old file if any
        if ($record->file_path) {
            Storage::disk('public')->delete($record->file_path);
        }

        $file = $request->file('file');
        $path = $file->store('dh-files', 'public');

        $record->update([
            'file_path'     => $path,
            'original_name' => $file->getClientOriginalName(),
        ]);

        return back()->with('status', 'File uploaded.');
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
        // Only children of this folder
        $subfolders = $folder->children()
            ->with(['records'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Normalise subfolder names once here (lowercased, trimmed)
        $existingSubNames = $subfolders->pluck('folder_name')
            ->map(function ($n) {
                return mb_strtolower(trim($n));
            })
            ->values()
            ->all(); // plain PHP array

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
        // Only records directly under this folder
        $records = $folder->records()
            ->whereNotNull('file_path')
            ->get();

        if ($records->isEmpty()) {
            return back()->with('status', 'No files to download in this folder.');
        }

        $zip = new ZipArchive();

        // Nice zip file name
        $safeName   = Str::slug($folder->folder_name ?: 'folder');
        $zipName    = $safeName . '-' . now()->format('Ymd-His') . '.zip';
        $tempDir    = storage_path('app/temp');
        $zipPath    = $tempDir . '/' . $zipName;

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Could not create ZIP file.');
        }

        foreach ($records as $rec) {
            if (!$rec->file_path) {
                continue;
            }

            $fullPath = Storage::disk('public')->path($rec->file_path);

            if (!file_exists($fullPath)) {
                continue;
            }

            $nameInZip = $rec->original_name ?: basename($fullPath);

            // Avoid duplicate names inside zip
            $base = pathinfo($nameInZip, PATHINFO_FILENAME);
            $ext  = pathinfo($nameInZip, PATHINFO_EXTENSION);
            $n    = 1;

            while ($zip->locateName($nameInZip) !== false) {
                $suffix   = ' (' . $n++ . ')';
                $nameInZip = $base . $suffix . ($ext ? '.' . $ext : '');
            }

            $zip->addFile($fullPath, $nameInZip);
        }

        $zip->close();

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }
}
