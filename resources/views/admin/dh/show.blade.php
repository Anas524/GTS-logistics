@extends('layouts.gts_app')

@push('head')
<style>
    /* ---------- Custom scrollbar (Chrome/Edge/Safari) ---------- */
    .dh-scroll::-webkit-scrollbar {
        width: 10px;
    }

    .dh-scroll::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.06);
        border-radius: 999px;
    }

    .dh-scroll::-webkit-scrollbar-thumb {
        background: rgba(56, 189, 248, 0.55);
        /* sky-ish */
        border-radius: 999px;
        border: 2px solid rgba(2, 6, 23, 0.65);
        /* blend with dark bg */
    }

    .dh-scroll::-webkit-scrollbar-thumb:hover {
        background: rgba(56, 189, 248, 0.8);
    }

    /* ---------- Firefox scrollbar ---------- */
    .dh-scroll {
        scrollbar-width: thin;
        scrollbar-color: rgba(56, 189, 248, 0.65) rgba(255, 255, 255, 0.06);
    }

    #dh-selected-list {
        white-space: normal;
    }

    #dh-selected-list .file-name {
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .dh-file-item .truncate {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    #dh-viewer-side-list button {
        background: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
    }

    #dh-viewer-side-list button:focus {
        outline: none !important;
    }

    #dh-viewer-side-list .dh-open {
        background: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
    }

    #dh-viewer-side-list .dh-open:focus {
        outline: none !important;
    }
</style>
@endpush

@section('content')
<section class="text-dec-none p-font py-10 px-4">
    @php
    // Count only records that have an actual file
    $fileCount = $records->filter(fn($rec) => !empty($rec->file_path))->count();

    $user = auth()->user();
    $isAdmin = $user && method_exists($user, 'isAdmin') && $user->isAdmin();
    $isConsultant = $user && method_exists($user, 'isConsultant') && $user->isConsultant();

    $canUpload = $isAdmin || $isConsultant;
    @endphp

    <div class="max-w-6xl mx-auto font-plus" id="dh-show-root" data-is-admin="{{ $isAdmin ? 1 : 0 }}" data-can-upload="{{ $canUpload ? 1 : 0 }}">

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
            @if($canUpload)
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
                You have read-only access. Only admin/consultant can add rows and upload files.
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
                        @php $attCount = $rec->attachments->count(); @endphp

                        <tr class="border-t border-slate-100"
                            data-has-file="{{ $attCount ? '1' : '0' }}"
                            data-attachments-count="{{ $attCount }}">
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
                                @if($attCount)
                                <span class="text-slate-700">{{ $attCount }} file{{ $attCount==1?'':'s' }}</span>
                                @else
                                <span class="text-slate-400">No file</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-2 align-top text-right">
                                {{-- Upload (admin + consultant) --}}
                                @if($canUpload)
                                <button
                                    type="button"
                                    class="dh-btn-icon dh-btn-upload"
                                    title="Upload attachments"
                                    data-dh-upload
                                    data-id="{{ $rec->id }}"
                                    data-upload-url="{{ route('dh.records.upload', $rec) }}"
                                    data-existing-count="{{ $attCount }}">
                                    <i class="fa-solid fa-upload"></i>
                                </button>
                                @endif

                                {{-- View --}}
                                @if($attCount)
                                <button
                                    type="button"
                                    class="dh-btn-icon dh-btn-view ml-2"
                                    title="View attachments"
                                    data-dh-view
                                    data-record-id="{{ $rec->id }}">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                                @endif

                                {{-- Delete (admin only) --}}
                                @if($isAdmin)
                                <button
                                    type="button"
                                    class="dh-btn-icon dh-btn-delete ml-2 text-rose-500 hover:text-rose-600"
                                    title="Delete this row"
                                    data-dh-delete
                                    data-record-name="{{ $rec->description ?: ($rec->original_name ?: 'this record') }}"
                                    data-delete-url="{{ route('dh.records.destroy', $rec) }}">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-500">
                                @if($canUpload)
                                No records yet. Add one above.
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
@if($canUpload)
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
                    <input id="dh-upload-file" type="file" name="files[]" multiple
                        accept="application/pdf,image/*" class="hidden">
                </label>

                <div class="mt-3 space-y-2">
                    <div id="dh-selected-count" class="text-[11px] text-slate-300">
                        Selected: 0 files
                    </div>

                    <div id="dh-selected-list" class="dh-scroll max-h-32 overflow-auto rounded-xl border border-slate-800 bg-slate-900/50 p-2 text-[11px] text-slate-200 hidden">
                        <!-- file rows injected by JS -->
                    </div>

                    <div id="dh-selected-empty" class="text-[11px] text-slate-400">
                        No file selected yet.
                    </div>
                </div>
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

    <div class="w-full max-w-6xl h-[85vh] rounded-3xl bg-slate-950 text-slate-50 shadow-2xl border border-slate-800 overflow-hidden flex flex-col">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800">
            <div class="min-w-0">
                <h3 id="dh-viewer-title" class="text-sm font-semibold truncate">
                    Attachments Viewer
                </h3>
                <p id="dh-viewer-sub" class="text-[11px] text-slate-400 mt-0.5">
                    0 files
                </p>
            </div>

            <button type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-slate-100 hover:bg-white/20"
                data-dh-view-close>
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="flex-1 min-h-0 flex">

            {{-- LEFT SIDEBAR --}}
            <aside class="w-[320px] border-r border-slate-800 bg-slate-950/80 p-4 flex flex-col min-h-0">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-xs font-semibold text-slate-200">
                        Files
                    </div>
                    <span id="dh-viewer-count-badge"
                        class="text-[11px] px-2 py-0.5 rounded-full bg-white/10 text-slate-200">
                        0
                    </span>
                </div>

                <div id="dh-viewer-side-list"
                    class="dh-scroll flex-1 min-h-0 overflow-y-auto space-y-2 pr-1">
                    {{-- buttons injected by JS --}}
                </div>
            </aside>

            {{-- RIGHT PREVIEW --}}
            <main class="flex-1 min-h-0 p-4">
                <div class="h-full rounded-2xl border border-slate-700 bg-slate-900/70 overflow-hidden">
                    <iframe id="dh-viewer-frame" class="w-full h-full" loading="lazy"></iframe>
                </div>
            </main>

        </div>

        {{-- Footer buttons --}}
        <div class="px-5 py-4 border-t border-slate-800 flex items-center justify-end gap-2">
            <a id="dh-download-all"
                href="#"
                target="_blank"
                rel="noreferrer"
                class="inline-flex items-center gap-2 rounded-full border border-slate-700 px-4 py-2 text-xs font-semibold text-slate-50 hover:bg-slate-800">
                <i class="fa-solid fa-file-zipper text-[11px]"></i>
                <span>Download All</span>
            </a>

            <a id="dh-download-current"
                href="#"
                target="_blank"
                rel="noreferrer"
                class="inline-flex items-center gap-2 rounded-full bg-sky-500 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-600">
                <i class="fa-solid fa-download text-[11px]"></i>
                <span>Download</span>
            </a>
        </div>

    </div>
</div>

{{-- Replace confirmation modal --}}
@if($canUpload)
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
        var canUpload = rootEl && rootEl.dataset.canUpload === '1';

        // ---------- Viewer modal (everyone) ----------
        var $viewerModal = $('#dh-viewer-modal');
        var $viewerTitle = $('#dh-viewer-title');
        var $viewerFrame = $('#dh-viewer-frame');
        var $downloadOne = $('#dh-download-current');
        var $viewerSub = $('#dh-viewer-sub');
        var $viewerSideList = $('#dh-viewer-side-list');
        var $downloadAll = $('#dh-download-all');

        function updateRowFileCount(recordId, count) {
            const $viewBtn = $(`[data-dh-view][data-record-id="${recordId}"]`);
            const $row = $viewBtn.closest('tr');
            if (!$row.length) return;

            // update row dataset
            $row.attr('data-has-file', count > 0 ? '1' : '0');
            $row.attr('data-attachments-count', count);

            // update "File" column (3rd td)
            const $fileCell = $row.find('td').eq(2);
            if (count > 0) {
                $fileCell.html(`<span class="text-slate-700">${count} file${count === 1 ? '' : 's'}</span>`);
                $viewBtn.removeClass('hidden').prop('disabled', false);
            } else {
                $fileCell.html(`<span class="text-slate-400">No file</span>`);
                $viewBtn.addClass('hidden').prop('disabled', true);
            }
        }

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
            $viewerTitle.text('Attachments Viewer');
            $viewerSub.text('0 files');
            $('#dh-viewer-count-badge').text('0');
            $viewerSideList.empty();
            $downloadOne.attr('href', '#');
            $downloadAll.attr('href', '#');
        }

        function buildSidebar(items, activeIdx) {
            $viewerSideList.empty();

            items.forEach(function(att, idx) {
                // inject item row (includes delete button)
                const html = renderAttachmentItem(att, idx, items.length, idx === activeIdx);
                $viewerSideList.append(html);
            });
        }

        function openAttachment(items, idx) {
            if (!items || !items.length) return;

            var item = items[idx];
            $viewerModal.data('items', items).data('idx', idx);

            $viewerFrame.attr('src', item.inline_url || item.download_url || '#');
            $downloadOne.attr('href', item.download_url || item.inline_url || '#');

            $viewerTitle.text((item.name || 'Attachment') + ' (' + (idx + 1) + '/' + items.length + ')');

            buildSidebar(items, idx);
        }

        function escapeHtml(str) {
            return String(str ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function renderAttachmentItem(att, idx, total, isActive) {
            const name = att.name || ('Attachment ' + (idx + 1));

            return `
                <div class="dh-file-item flex items-center justify-between gap-3 p-3 rounded-xl border border-white/10
                            ${isActive ? 'bg-white/10' : 'bg-white/5 hover:bg-white/10'}"
                    data-att-id="${att.id}">

                <!-- Click anywhere on name area to preview -->
                <button type="button"
                        class="dh-open flex-1 text-left min-w-0 bg-transparent border-0 outline-none ring-0 focus:outline-none focus:ring-0 active:outline-none"
                        data-idx="${idx}">
                    <div class="truncate font-medium text-white">${escapeHtml((idx + 1) + '. ' + name)}</div>
                    <div class="text-xs text-white/50">Click to preview</div>
                </button>

                <!-- ONLY delete button -->
                ${canUpload ? `
                    <button type="button"
                            class="dh-del px-2 py-2 rounded-lg bg-red-500/10 hover:bg-red-500/20 shrink-0"
                            data-id="${att.id}"
                            title="Delete">
                        <i class="fa-regular fa-trash-can text-rose-300"></i>
                    </button>
                    ` : ``}
                </div>
            `;
        }

        function openViewerModalByRecordId(recordId) {
            $.get('/admin/document-hub/records/' + recordId + '/attachments')
                .done(function(res) {
                    var items = (res && res.items) ? res.items : [];
                    if (!items.length) {
                        // instantly fix table UI
                        updateRowFileCount(recordId, 0);

                        alert('No attachments found.');
                        return;
                    }

                    updateRowFileCount(recordId, items.length);

                    $viewerModal.data('recordId', recordId);

                    $viewerModal.data('items', items).data('idx', 0);

                    // show modal
                    $viewerModal.removeClass('hidden').addClass('flex');

                    // counts
                    $viewerSub.text(items.length + ' file' + (items.length === 1 ? '' : 's'));
                    $('#dh-viewer-count-badge').text(items.length);

                    // set download all zip url
                    $downloadAll.attr('href', `/admin/document-hub/records/${recordId}/download-all`);

                    // open first (this will build sidebar correctly)
                    openAttachment(items, 0);
                })
                .fail(function() {
                    alert('Failed to load attachments.');
                });
        }

        // Open viewer from row button (now uses record id)
        $(document).on('click', '[data-dh-view], .dh-btn-view', function(e) {
            e.preventDefault();

            var recordId = $(this).data('record-id');
            if (!recordId) return;

            openViewerModalByRecordId(recordId);
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

        // ---------- Upload logic (admin + consultant) ----------
        if (canUpload) {
            // ---------- Upload modal ----------
            var $uploadModal = $('#dh-upload-modal');
            var $uploadForm = $('#dh-upload-form');
            var $uploadFile = $('#dh-upload-file');
            var $uploadTitle = $('#dh-upload-title');
            var $existingWrap = $('#dh-existing-wrap'); // block showing existing file
            var $existingName = $('#dh-existing-name'); // span with filename
            var $selectedCount = $('#dh-selected-count');
            var $selectedList = $('#dh-selected-list');
            var $selectedEmpty = $('#dh-selected-empty');
            var $dropzone = $('#dh-dropzone');
            var droppedFiles = [];

            // ---------- Replace confirmation modal ----------
            var $replaceModal = $('#dh-replace-modal');
            var pendingUploadBtn = null; // store the clicked upload button

            // ---------- Upload helpers ----------
            function open_attach_upload_modal(btn) {
                var $btn = $(btn);
                var $row = $btn.closest('tr');

                // Reset dropped files
                droppedFiles = [];

                // Take a nice label from the description cell (2nd td)
                var recordName = $.trim($row.find('td').eq(1).text()) || 'Record';
                var uploadUrl = $btn.data('upload-url');

                // has file? -> from row data
                var hasFile = String($row.data('has-file')) === '1';
                // (optional) if you still show existing block, you may keep it, otherwise remove
                var existing = $row.data('file-name') || $btn.data('existing-name') || '';

                // Title + form action
                $uploadTitle.text('Upload Attachments – ' + recordName);
                $uploadForm.attr('action', uploadUrl);

                // Reset file input + UI list
                $uploadFile.val('');
                renderSelectedFiles([]);

                // Existing attachment block (optional, keep only if you still show it)
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

            function renderSelectedFiles(files) {
                const list = files || [];

                $selectedCount.text('Selected: ' + list.length + ' file' + (list.length === 1 ? '' : 's'));

                if (!list.length) {
                    $selectedList.addClass('hidden').empty();
                    $selectedEmpty.removeClass('hidden');
                    return;
                }

                $selectedEmpty.addClass('hidden');
                $selectedList.removeClass('hidden').empty();

                list.forEach(function(f, i) {
                    const sizeKb = f.size ? Math.round(f.size / 1024) : null;
                    const line = $('<div></div>')
                        .addClass('flex items-start justify-between gap-3 px-2 py-1 rounded-lg hover:bg-white/5')
                        .append(
                            $('<span></span>')
                            .addClass('min-w-0 flex-1 text-[11px] text-slate-200')
                            .css({
                                'white-space': 'normal',
                                'overflow-wrap': 'anywhere',
                                'word-break': 'break-word'
                            })
                            .text((i + 1) + '. ' + f.name)
                        )
                        .append(
                            $('<span></span>')
                            .addClass('shrink-0 text-[10px] text-slate-400 whitespace-nowrap')
                            .text(sizeKb ? (sizeKb + ' KB') : '')
                        );

                    $selectedList.append(line);
                });
            }

            function close_attach_upload_modal() {
                droppedFiles = [];

                // reset input + UI
                $uploadFile.val('');
                renderSelectedFiles([]);

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
                    open_attach_upload_modal(this);
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
                open_attach_upload_modal(pendingUploadBtn);
                pendingUploadBtn = null;
            });

            // Upload modal close (X + footer cancel)
            $(document).on('click', '[data-dh-upload-close]', function(e) {
                e.preventDefault();
                close_attach_upload_modal();
            });

            // Click on backdrop to close upload modal
            $uploadModal.on('click', function(e) {
                if (e.target === this) {
                    close_attach_upload_modal();
                }
            });

            // Show selected file name
            $uploadFile.on('change', function() {
                droppedFiles = []; // reset dropped
                const files = this.files ? Array.from(this.files) : [];
                renderSelectedFiles(files);
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
                    if (!dt || !dt.files || !dt.files.length) return;

                    // Store dropped files (multiple)
                    droppedFiles = Array.from(dt.files || []);

                    // IMPORTANT: clear the file input so browser doesn't also submit old selections
                    $uploadFile.val('');

                    // Update UI (count + list)
                    renderSelectedFiles(droppedFiles);
                });
            }

            // When submitting the upload form, if user used drag & drop, send via AJAX
            $uploadForm.on('submit', function(e) {

                // If user used drag & drop, we must send via AJAX
                if (droppedFiles && droppedFiles.length) {
                    e.preventDefault();

                    var action = $uploadForm.attr('action');
                    var token = $uploadForm.find('input[name="_token"]').val();

                    var formData = new FormData();
                    formData.append('_token', token);

                    // send as files[]
                    droppedFiles.forEach(function(f) {
                        formData.append('files[]', f);
                    });

                    $.ajax({
                        url: action,
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function() {
                            close_attach_upload_modal();
                            window.location.reload();
                        },
                        error: function(xhr) {
                            console.error(xhr);
                            alert('Failed to upload files. Please try again.');
                        }
                    });

                    return;
                }

                // If user selected from file input, let normal form submit happen
                // (browser will submit all files[] automatically)
                return true;
            });

            // ESC closes all modals
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    close_attach_upload_modal();
                    closeReplaceModal();
                    closeViewerModal();
                }
            });

        }

        // ---------- Delete logic (admin only) ----------
        if (isAdmin) {

            var $deleteModal = $('#dh-delete-modal');
            var $deleteText = $('#dh-delete-text');
            var $deleteForm = $('#dh-delete-form');
            var pendingDeleteUrl = null;

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
            // ESC for consultants: only close viewer
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeDeleteModal();
                    closeViewerModal();
                }
            });
        }

        // ---------- Consultant-only ESC (if not admin and not canUpload, optional) ----------
        if (!isAdmin && !canUpload) {
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') closeViewerModal();
            });
        }

        $('#dh-download-current').on('click', function(e) {
            e.preventDefault();
            var href = $(this).attr('href');
            if (!href || href === '#') return;
            window.open(href, '_blank');
        });

        $('#dh-download-all').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var href = $(this).attr('href');
            if (!href || href === '#') return;

            // open download in new tab (ensures browser starts download)
            window.open(href, '_blank');
        });

        $(document).off('click.dhDel').on('click.dhDel', '.dh-del', function() {
            const id = $(this).data('id');
            if (!id) return;

            if (!confirm('Delete this file?')) return;

            $.ajax({
                    url: `/admin/document-hub/attachments/${id}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                })
                .done(function(res) {
                    if (!res || !res.ok) return;

                    // remove from sidebar list
                    $(`.dh-file-item[data-att-id="${id}"]`).remove();

                    // update viewer items in memory
                    let items = $viewerModal.data('items') || [];
                    items = items.filter(x => String(x.id) !== String(id));
                    $viewerModal.data('items', items);

                    // update table row count (IMPORTANT)
                    const recordId = $viewerModal.data('recordId'); // you set this in openViewerModalByRecordId
                    if (recordId) updateRowFileCount(recordId, items.length);

                    // update viewer counts
                    $viewerSub.text(items.length + ' file' + (items.length === 1 ? '' : 's'));
                    $('#dh-viewer-count-badge').text(items.length);

                    // open next or close
                    if (items.length) openAttachment(items, 0);
                    else closeViewerModal();
                })
                .fail(function(xhr) {
                    alert('Delete failed. Please refresh and try again.');
                    console.error(xhr.responseText || xhr.statusText);
                });
        });

        $(document).off('click.dhOpen').on('click.dhOpen', '.dh-open', function() {
            const items = $viewerModal.data('items') || [];
            const idx = Number($(this).data('idx'));
            if (!items.length || isNaN(idx)) return;
            openAttachment(items, idx);
        });

    });
</script>
@endpush