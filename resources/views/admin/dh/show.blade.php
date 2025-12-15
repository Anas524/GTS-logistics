@extends('layouts.gts_app')

@section('content')
<section class="text-dec-none p-font py-10 px-4">
    @php
    // Count only records that have an actual file
    $fileCount = $records->filter(function ($rec) {
    return !empty($rec->file_path);
    })->count();

    $user = auth()->user();
    $isAdmin = $user && method_exists($user, 'isAdmin') && $user->isAdmin();
    $isConsultant = $user && method_exists($user, 'isConsultant') && $user->isConsultant();
    @endphp

    <div class="max-w-6xl mx-auto font-plus" id="dh-show-root" data-is-admin="{{ $isAdmin ? 1 : 0 }}">

        {{-- Back buttons ------------------------------------------------------ --}}
        <div class="mb-4 flex items-center gap-2">
            @if($folder->parent_id)
            {{-- When this is a subfolder --}}
            <a href="{{ route('dh.subfolders.index', $folder->parent_id) }}"
                class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-medium text-slate-700 shadow-sm hover:border-sky-300">
                ← Back to subfolders
            </a>
            <a href="{{ route('dh.index') }}"
                class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-medium text-slate-700 shadow-sm hover:border-sky-300">
                ← Back to Document Hub
            </a>
            @else
            {{-- Normal root folder --}}
            <a href="{{ route('dh.index') }}"
                class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-medium text-slate-700 shadow-sm hover:border-sky-300">
                ← Back to Document Hub
            </a>
            @endif
        </div>

        {{-- Main card -------------------------------------------------------- --}}
        <div class="rounded-3xl border border-slate-100 bg-white shadow-sm px-6 py-5 space-y-4">

            {{-- Header row --}}
            <div class="flex items-center justify-between mb-4">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <h1 class="text-xl md:text-2xl font-semibold text-slate-900">
                            Folder: {{ $folder->folder_name }}
                        </h1>

                        @if($fileCount > 0)
                        <div class="inline-flex items-center gap-2 rounded-full bg-slate-900/95 px-3 py-1">
                            <span
                                class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white text-[11px] font-semibold text-slate-900">
                                {{ $fileCount }}
                            </span>
                            <span class="text-[10px] font-medium text-slate-100">
                                file{{ $fileCount === 1 ? '' : 's' }}
                            </span>
                        </div>
                        @endif
                    </div>

                    <p class="text-[11px] md:text-xs text-slate-500">
                        {{ $folder->month_label ?: 'No month set' }}
                        @if($folder->remarks) · {{ $folder->remarks }} @endif
                    </p>
                </div>

                {{-- Download all button (if any files exist) --}}
                @if($fileCount > 0)
                <a href="{{ route('dh.folder.downloadAll', $folder) }}"
                    class="inline-flex items-center gap-2 rounded-full border border-slate-700 bg-slate-900 px-4 py-2 text-[11px] font-semibold text-slate-50 shadow-sm hover:bg-slate-800">
                    <i class="fa-solid fa-file-zipper text-[11px]"></i>
                    <span>Download all</span>
                </a>
                @endif
            </div>

            {{-- Add row form --}}
            @if($isAdmin)
            <form method="POST"
                action="{{ route('dh.records.store', $folder) }}"
                class="flex flex-wrap gap-3 items-end">
                @csrf
                <div>
                    <label class="block text-[11px] font-medium text-slate-700 mb-1">Date</label>
                    <input type="date" name="doc_date"
                        class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-[11px] focus:border-sky-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-sky-400">
                </div>
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-[11px] font-medium text-slate-700 mb-1">Description</label>
                    <input type="text" name="description"
                        class="w-full rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-[11px] focus:border-sky-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-sky-400">
                </div>
                <button type="submit"
                    class="rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-slate-800">
                    + Add row
                </button>
            </form>
            @else
            <p class="text-[11px] text-slate-500">
                You have read-only access to this folder. Rows can only be added or removed by an admin.
            </p>
            @endif

            {{-- Table --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50/60">
                <table class="w-full text-[11px]">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            {{-- wider + no-wrap for single-line date --}}
                            <th class="px-4 py-2 text-left w-[130px] whitespace-nowrap">Date</th>
                            <th class="px-4 py-2 text-left">Description</th>
                            <th class="px-4 py-2 text-left w-[220px]">File</th>
                            <th class="px-4 py-2 text-right w-[170px]">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @forelse($records as $rec)
                        <tr class="border-t border-slate-100"
                            data-has-file="{{ $rec->file_path ? '1' : '0' }}"
                            data-file-name="{{ $rec->original_name ?? '' }}">
                            {{-- Date --}}
                            <td class="px-4 py-2 align-top whitespace-nowrap">
                                {{ $rec->doc_date ? $rec->doc_date->format('d M Y') : '—' }}
                            </td>

                            {{-- Description --}}
                            <td class="px-4 py-2 align-top">
                                {{ $rec->description ?: '—' }}
                            </td>

                            {{-- File name --}}
                            <td class="px-4 py-2 align-top">
                                @if($rec->file_path)
                                <span class="text-slate-700">
                                    {{ $rec->original_name ?: 'Attachment' }}
                                </span>
                                @else
                                <span class="text-slate-400">No file</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-2 align-top text-right">
                                @if($isAdmin)
                                {{-- Upload --}}
                                <button
                                    type="button"
                                    class="dh-btn-icon dh-btn-upload"
                                    title="Upload / replace attachment"
                                    data-dh-upload
                                    data-id="{{ $rec->id }}"
                                    data-upload-url="{{ route('dh.records.upload', $rec) }}"
                                    data-existing-name="{{ $rec->original_name }}">
                                    <i class="fa-solid fa-upload"></i>
                                </button>

                                {{-- View --}}
                                @if($rec->file_path)
                                <button
                                    type="button"
                                    class="dh-btn-icon dh-btn-view ml-2"
                                    title="View attachment"
                                    data-dh-view
                                    data-filename="{{ $rec->original_name ?: 'Attachment' }}"
                                    data-inline-url="{{ route('dh.records.download', [$rec, 'inline' => 1]) }}"
                                    data-download-url="{{ route('dh.records.download', $rec) }}"
                                    data-record-name="{{ $rec->original_name ?: 'Attachment' }}">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                                @endif

                                {{-- Delete --}}
                                <button
                                    type="button"
                                    class="dh-btn-icon dh-btn-delete ml-2 text-rose-500 hover:text-rose-600"
                                    title="Delete this row"
                                    data-dh-delete
                                    data-record-name="{{ $rec->description ?: ($rec->original_name ?: 'this record') }}"
                                    data-delete-url="{{ route('dh.records.destroy', $rec) }}">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                                @else
                                {{-- Consultant: view only --}}
                                @if($rec->file_path)
                                <button
                                    type="button"
                                    class="dh-btn-icon dh-btn-view"
                                    title="View attachment"
                                    data-dh-view
                                    data-filename="{{ $rec->original_name ?: 'Attachment' }}"
                                    data-inline-url="{{ route('dh.records.download', [$rec, 'inline' => 1]) }}"
                                    data-download-url="{{ route('dh.records.download', $rec) }}"
                                    data-record-name="{{ $rec->original_name ?: 'Attachment' }}">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                                @endif
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-500">
                                @if($isAdmin)
                                    No records yet. Add one above.
                                @elseif($isConsultant)
                                    No records are available in this folder yet. Please contact an admin if you need a file added here.
                                @else
                                    No records yet.
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div> {{-- /main card --}}
    </div>
</section>

{{-- Upload Attachments modal --}}
@if($isAdmin)
<div id="dh-upload-modal"
    class="text-dec-none fixed inset-0 z-40 hidden items-center justify-center bg-slate-900/70 backdrop-blur-sm"
    aria-hidden="true">
    <div class="w-full max-w-2xl rounded-3xl bg-slate-950 text-slate-50 shadow-2xl border border-slate-800 px-8 py-6">
        <div class="flex items-center justify-between gap-4 mb-4">
            <h3 id="dh-upload-title" class="text-base font-semibold">
                Upload Attachments
            </h3>
            <button type="button"
                class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/10 text-slate-100 hover:bg-white/20"
                data-dh-upload-close>
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <p class="text-xs text-slate-400 mb-4">
            PDF and images only, max 25MB each.
        </p>

        <form id="dh-upload-form" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            {{-- Dropzone --}}
            <div id="dh-dropzone"
                class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-700 bg-slate-900/60 px-6 py-10 text-center">
                <p class="text-sm text-slate-200 mb-1">Drag &amp; drop files here</p>
                <span class="text-[11px] text-slate-500 mb-3">or</span>
                <label
                    class="inline-flex items-center rounded-full bg-sky-500 px-4 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-sky-600 cursor-pointer">
                    Browse files
                    <input id="dh-upload-file" type="file" name="file"
                        accept="application/pdf,image/*" class="hidden">
                </label>

                <p id="dh-selected-file-name" class="mt-3 text-[11px] text-slate-400">
                    No file selected yet.
                </p>
            </div>

            {{-- Existing file --}}
            <div id="dh-existing-wrap" class="hidden mt-3">
                <div class="text-[11px] text-slate-400 mb-1">Existing attachment</div>
                <div class="flex items-center justify-between rounded-xl bg-slate-900/70 border border-slate-700 px-3 py-2">
                    <span id="dh-existing-name" class="text-xs text-slate-100 truncate"></span>
                </div>
            </div>

            <div class="mt-5 flex items-center justify-end gap-2 text-xs">
                <button type="button"
                    class="rounded-full border border-slate-600 bg-slate-900 px-4 py-2 text-xs font-semibold text-slate-100 hover:bg-slate-800"
                    data-dh-upload-close>
                    Cancel
                </button>
                <button type="submit"
                    class="rounded-full bg-sky-500 px-5 py-1.5 font-semibold text-white shadow-sm hover:bg-sky-600">
                    Upload
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Attachments Viewer modal --}}
<div id="dh-viewer-modal"
    class="text-dec-none fixed inset-0 z-40 hidden items-center justify-center bg-slate-900/70 backdrop-blur-sm"
    aria-hidden="true">
    <div class="w-full max-w-5xl rounded-3xl bg-slate-950 text-slate-50 shadow-2xl border border-slate-800 px-6 py-5 flex flex-col">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-4">
            <h3 id="dh-viewer-title" class="text-sm font-semibold truncate max-w-[80%]">
                Attachments Viewer
            </h3>
            <button type="button"
                class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/10 text-slate-100 hover:bg-white/20"
                data-dh-view-close>
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        {{-- Preview (full width) --}}
        <div class="h-[480px] rounded-2xl border border-slate-700 bg-slate-900/70 overflow-hidden">
            <iframe id="dh-viewer-frame" class="w-full h-full" loading="lazy"></iframe>
        </div>

        {{-- Footer actions --}}
        <div class="mt-4 flex items-center justify-end">
            <a id="dh-download-current"
                href="#"
                target="_blank"
                rel="noreferrer"
                download
                class="inline-flex items-center gap-2 rounded-full border border-slate-700 px-4 py-1.5 text-xs font-semibold text-slate-50 hover:bg-slate-800">
                <i class="fa-solid fa-download text-[11px]"></i>
                <span>Download</span>
            </a>
        </div>
    </div>
</div>

{{-- Replace confirmation modal --}}
@if($isAdmin)
<div id="dh-replace-modal"
    class="fixed inset-0 z-40 hidden items-center justify-center bg-slate-900/70 backdrop-blur-sm"
    aria-hidden="true">
    <div class="w-full max-w-md rounded-2xl bg-slate-950 text-slate-50 shadow-2xl border border-slate-800 px-6 py-5">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold">
                Replace attachment?
            </h3>
            <button type="button"
                class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/5 text-slate-200 hover:bg-white/10"
                data-dh-replace-cancel>
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>

        {{-- Message --}}
        <p id="dh-replace-text" class="text-[11px] text-slate-300 leading-relaxed">
            This record already has an attachment. Uploading a new file will replace the existing file.
        </p>

        {{-- Actions --}}
        <div class="mt-5 flex items-center justify-end gap-2 text-[11px]">
            <button type="button"
                class="rounded-full border border-slate-600 bg-slate-900 px-4 py-2 text-xs font-semibold text-slate-100 hover:bg-slate-800"
                data-dh-replace-cancel>
                Keep existing
            </button>
            <button type="button"
                class="rounded-full bg-rose-500 px-5 py-1.5 font-semibold text-white shadow-sm hover:bg-rose-600"
                id="dh-replace-confirm">
                Replace file
            </button>
        </div>
    </div>
</div>

{{-- Delete record confirmation modal --}}
<div id="dh-delete-modal"
    class="fixed inset-0 z-40 hidden items-center justify-center bg-slate-900/70 backdrop-blur-sm"
    aria-hidden="true">
    <div class="w-full max-w-md rounded-2xl bg-slate-950 text-slate-50 shadow-2xl border border-slate-800 px-6 py-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold">
                Delete record?
            </h3>
            <button type="button"
                class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/5 text-slate-200 hover:bg-white/10"
                data-dh-delete-cancel>
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>

        <p id="dh-delete-text" class="text-[11px] text-slate-300 leading-relaxed">
            Are you sure you want to delete this record? This will also delete any attached file.
        </p>

        <div class="mt-5 flex items-center justify-end gap-2 text-[11px]">
            <button type="button"
                class="rounded-full border border-slate-600 bg-slate-900 px-4 py-2 text-xs font-semibold text-slate-100 hover:bg-slate-800"
                data-dh-delete-cancel>
                Cancel
            </button>
            <button type="button"
                class="rounded-full bg-rose-500 px-5 py-1.5 font-semibold text-white shadow-sm hover:bg-rose-600"
                id="dh-delete-confirm">
                Delete
            </button>
        </div>
    </div>
</div>

{{-- hidden delete form --}}
<form id="dh-delete-form" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>
@endif

@endsection

@push('scripts')
<script>
    $(function() {
        var rootEl = document.getElementById('dh-show-root');
        var isAdmin = rootEl && rootEl.dataset.isAdmin === '1';

        // ---------- Viewer modal (everyone) ----------
        var $viewerModal = $('#dh-viewer-modal');
        var $viewerTitle = $('#dh-viewer-title');
        var $viewerFrame = $('#dh-viewer-frame');
        var $downloadOne = $('#dh-download-current');

        function openViewerModal(btn) {
            var $btn = $(btn);

            var inlineUrl = $btn.data('inline-url'); // url with ?inline=1
            var downloadUrl = $btn.data('download-url'); // plain download url
            var label = $btn.data('record-name') || 'Attachment';

            // Fallbacks
            inlineUrl = inlineUrl || downloadUrl || '#';
            downloadUrl = downloadUrl || inlineUrl;

            $viewerTitle.text(label);
            $viewerFrame.attr('src', inlineUrl); // open in iframe
            $downloadOne.attr('href', downloadUrl); // real download

            $viewerModal.removeClass('hidden').addClass('flex');
        }

        function closeViewerModal() {
            $viewerModal.removeClass('flex').addClass('hidden');
            $viewerFrame.attr('src', '');
        }

        // Open viewer from row button
        $(document).on('click', '[data-dh-view]', function(e) {
            e.preventDefault();
            openViewerModal(this);
        });

        // Close viewer (X)
        $(document).on('click', '[data-dh-view-close]', function(e) {
            e.preventDefault();
            closeViewerModal();
        });

        // Backdrop click for viewer
        $viewerModal.on('click', function(e) {
            if (e.target === this) {
                closeViewerModal();
            }
        });

        // ---------- Admin-only: upload / delete / drag & drop ----------
        if (isAdmin) {
            // ---------- Upload modal ----------
            var $uploadModal = $('#dh-upload-modal');
            var $uploadForm = $('#dh-upload-form');
            var $uploadFile = $('#dh-upload-file');
            var $uploadTitle = $('#dh-upload-title');
            var $existingWrap = $('#dh-existing-wrap'); // block showing existing file
            var $existingName = $('#dh-existing-name'); // span with filename
            var $selectedLabel = $('#dh-selected-file-name');
            var $dropzone = $('#dh-dropzone');
            var droppedFile = null; // stores file if user uses drag & drop

            // ---------- Replace confirmation modal ----------
            var $replaceModal = $('#dh-replace-modal');
            var pendingUploadBtn = null; // store the clicked upload button

            // ---------- Delete modal ----------
            var $deleteModal = $('#dh-delete-modal');
            var $deleteText = $('#dh-delete-text');
            var $deleteForm = $('#dh-delete-form');
            var pendingDeleteUrl = null;


            // ---------- Upload helpers ----------
            function openUploadModal(btn) {
                var $btn = $(btn);
                var $row = $btn.closest('tr');

                // reset any previously dropped file
                droppedFile = null;

                // Take a nice label from the description cell (2nd td)
                var recordName = $.trim($row.find('td').eq(1).text()) || 'Record';

                var uploadUrl = $btn.data('upload-url');

                // has file? -> from row data
                var hasFile = String($row.data('has-file')) === '1';
                var existing = $row.data('file-name') ||
                    $btn.data('existing-name') ||
                    '';

                // Title + form action
                $uploadTitle.text('Upload Attachments – ' + recordName);
                $uploadForm.attr('action', uploadUrl);

                // Reset file input
                $uploadFile.val('');
                $selectedLabel.text('No file selected yet.');

                // Existing attachment block
                if (hasFile && existing) {
                    $existingWrap.removeClass('hidden');
                    $existingName.text(existing);
                } else {
                    $existingWrap.addClass('hidden');
                    $existingName.text('');
                }

                // Show modal
                $uploadModal.removeClass('hidden').addClass('flex');
            }

            function closeUploadModal() {
                // also reset here, for safety
                droppedFile = null;
                $uploadModal.removeClass('flex').addClass('hidden');
            }

            function openReplaceModal(existingName) {
                var msg = 'This record already has an attachment';
                if (existingName) {
                    msg += ' (“' + existingName + '”).';
                } else {
                    msg += '.';
                }
                msg += ' Uploading a new file will permanently replace it. Continue?';

                $('#dh-replace-text').text(msg);

                $replaceModal
                    .removeClass('hidden')
                    .addClass('flex')
                    .attr('aria-hidden', 'false');
            }

            function closeReplaceModal() {
                $replaceModal
                    .addClass('hidden')
                    .removeClass('flex')
                    .attr('aria-hidden', 'true');
            }

            // --- Upload button click ---
            $(document).on('click', '[data-dh-upload]', function(e) {
                e.preventDefault();

                var $btn = $(this);
                var $row = $btn.closest('tr');

                var hasFile = String($row.data('has-file')) === '1';
                var existing = $row.data('file-name') ||
                    $btn.data('existing-name') ||
                    '';

                if (hasFile && existing) {
                    // Ask before replacing
                    pendingUploadBtn = this;
                    openReplaceModal(existing);
                } else {
                    // No existing file -> open upload directly
                    openUploadModal(this);
                }
            });

            // Replace modal: cancel (both X and "Keep existing")
            $(document).on('click', '[data-dh-replace-cancel]', function(e) {
                e.preventDefault();
                pendingUploadBtn = null;
                closeReplaceModal();
            });

            // Replace modal: confirm
            $('#dh-replace-confirm').on('click', function(e) {
                e.preventDefault();
                if (!pendingUploadBtn) return;
                closeReplaceModal();
                openUploadModal(pendingUploadBtn);
                pendingUploadBtn = null;
            });

            // Upload modal close (X + footer cancel)
            $(document).on('click', '[data-dh-upload-close]', function(e) {
                e.preventDefault();
                closeUploadModal();
            });

            // Click on backdrop to close upload modal
            $uploadModal.on('click', function(e) {
                if (e.target === this) {
                    closeUploadModal();
                }
            });

            // Show selected file name
            $uploadFile.on('change', function() {
                // If user picked manually, ignore any previous dropped file
                droppedFile = null;

                var name = this.files && this.files[0] ? this.files[0].name : '';
                $selectedLabel.text(name || 'No file selected yet.');
            });

            // ----- Delete helpers -----
            function openDeleteModal(name, url) {
                pendingDeleteUrl = url || null;

                $deleteText.text(
                    'Are you sure you want to delete "' + name +
                    '"? This will also delete any attached file.'
                );

                $deleteModal
                    .removeClass('hidden')
                    .addClass('flex')
                    .attr('aria-hidden', 'false');
            }

            function closeDeleteModal() {
                pendingDeleteUrl = null;
                $deleteModal
                    .addClass('hidden')
                    .removeClass('flex')
                    .attr('aria-hidden', 'true');
            }

            // open delete modal from row button
            $(document).on('click', '[data-dh-delete]', function(e) {
                e.preventDefault();

                var $btn = $(this);
                var $row = $btn.closest('tr');

                var hasFile = String($row.data('has-file')) === '1';
                var name = $btn.data('record-name') || 'this record';
                var url = $btn.data('delete-url') || '';

                if (!url) {
                    return; // safety guard
                }

                if (!hasFile) {
                    // No attachment -> delete directly, no modal
                    $deleteForm.attr('action', url);
                    $deleteForm.trigger('submit');
                    return;
                }

                // Has attachment -> show confirmation modal
                openDeleteModal(name, url);
            });

            // delete modal: cancel
            $(document).on('click', '[data-dh-delete-cancel]', function(e) {
                e.preventDefault();
                closeDeleteModal();
            });

            // delete modal: confirm
            $('#dh-delete-confirm').on('click', function(e) {
                e.preventDefault();
                if (!pendingDeleteUrl) {
                    closeDeleteModal();
                    return;
                }

                $deleteForm.attr('action', pendingDeleteUrl);
                $deleteForm.trigger('submit');
            });

            // backdrop click for delete modal
            $deleteModal.on('click', function(e) {
                if (e.target === this) {
                    closeDeleteModal();
                }
            });

            // Prevent browser default (opening file in tab) for drag/drop anywhere
            $(document).on('dragover drop', function(e) {
                e.preventDefault();
            });

            // --- Drag & drop on the dropzone ---
            if ($dropzone.length) {
                $dropzone.on('dragenter dragover', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $dropzone.addClass('ring-1 ring-sky-500 bg-slate-900/80');
                });

                $dropzone.on('dragleave dragend', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $dropzone.removeClass('ring-1 ring-sky-500 bg-slate-900/80');
                });

                $dropzone.on('drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $dropzone.removeClass('ring-1 ring-sky-500 bg-slate-900/80');

                    var dt = e.originalEvent.dataTransfer;
                    if (!dt || !dt.files || !dt.files.length) {
                        return;
                    }

                    // We only support a single file (same as <input>)
                    droppedFile = dt.files[0];

                    // Show file name in label
                    $selectedLabel.text(droppedFile.name || 'File selected');
                });
            }

            // When submitting the upload form, if user used drag & drop, send via AJAX
            $uploadForm.on('submit', function(e) {
                if (!droppedFile) {
                    // Normal file input submit – let browser handle it
                    return true;
                }

                e.preventDefault();

                var action = $uploadForm.attr('action');
                var token = $uploadForm.find('input[name="_token"]').val();

                var formData = new FormData();
                formData.append('_token', token);
                formData.append('file', droppedFile);

                $.ajax({
                    url: action,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        // Close modal + reload page so the table updates
                        closeUploadModal();
                        window.location.reload();
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        alert('Failed to upload file. Please try again.');
                    }
                });
            });

            // ESC closes all modals
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeUploadModal();
                    closeViewerModal();
                    closeReplaceModal();
                    closeDeleteModal();
                }
            });

        } else {
            // ESC for consultants: only close viewer
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeViewerModal();
                }
            });
        }

        $('#dh-download-current').on('click', function(e) {
            e.preventDefault();
            var href = $(this).attr('href');
            if (!href || href === '#') return;
            window.open(href, '_blank');
        });

    });
</script>
@endpush