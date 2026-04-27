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

    .dh-drop-active {
        position: relative;
    }

    .dh-drop-active::after {
        content: "Drop files to upload";
        position: absolute;
        inset: 0;
        background: rgba(14, 165, 233, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: 600;
        color: #0284c7;
    }
    .dh-zoom-img {
        will-change: transform;
    }
</style>
@endpush

@section('content')
<section class="text-dec-none p-font py-8 px-4">
    @php
    $fileCount = $records->sum(function ($rec) {
    return $rec->attachments ? $rec->attachments->count() : 0;
    });
    $folderCount = isset($subfolders) ? $subfolders->count() : 0;

    $user = auth()->user();
    $isAdmin = $user && method_exists($user, 'isAdmin') && $user->isAdmin();
    $isConsultant = $user && method_exists($user, 'isConsultant') && $user->isConsultant();
    $canUpload = $isAdmin || $isConsultant;

    $fileItems = collect();

    $canManage = $isAdmin;

    foreach ($records as $rec) {
    foreach (($rec->attachments ?? collect()) as $att) {
    $fileItems->push([
    'record' => $rec,
    'attachment' => $att,
    'record_id' => $rec->id,
    'name' => $att->original_name ?: ('Attachment ' . $att->id),
    'mime' => $att->mime,
    'date' => $rec->doc_date ? $rec->doc_date->format('d M Y') : '—',
    'created' => optional($att->created_at)->format('d M Y'),
    'description' => $att->description,
    ]);
    }
    }
    @endphp

    <style>
        .dh-drive-shell {
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
            gap: 1.25rem;
        }

        .dh-glass {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 10px 35px rgba(15, 23, 42, 0.06);
        }

        .dh-drive-sidebar-link.active,
        .dh-drive-sidebar-link:hover {
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            color: #fff;
            border-color: transparent;
        }

        .dh-drive-file-card {
            transition: box-shadow 0.2s ease, border-color 0.2s ease;
            overflow: visible;
            position: relative;
            z-index: 1;
        }

        .dh-drive-file-card:hover {
            transform: none !important;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
            border-color: rgba(56, 189, 248, 0.35);
        }

        .dh-drive-file-card.menu-open {
            z-index: 120;
        }

        .dh-folder-title {
            white-space: normal;
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        .dh-drive-file-name {
            display: -webkit-box !important;
            -webkit-line-clamp: 2 !important;
            -webkit-box-orient: vertical !important;

            overflow: hidden !important;
            white-space: normal !important;
            word-break: break-word !important;
        }

        .dh-drive-file-name:hover {
            position: relative;
            z-index: 10;
        }

        .dh-drive-preview {
            height: 180px;
        }

        .dh-drive-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .dh-drive-preview-icon {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            background: linear-gradient(135deg, #f8fafc, #eef2ff);
            color: #475569;
            font-size: 2rem;
        }

        .dh-upload-queue {
            position: fixed;
            right: 24px;
            bottom: 24px;
            width: 360px;
            max-width: calc(100vw - 24px);
            z-index: 60;
        }

        .dh-view-toggle-btn {
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #64748b;
            transition: all 0.2s ease;
        }

        .dh-view-toggle-btn:hover {
            background: #f8fafc;
            color: #0f172a;
        }

        .dh-view-toggle-btn.is-active {
            background: #0f172a;
            color: #fff;
            border-color: #0f172a;
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.14);
        }

        .dh-file-menu-item {
            display: flex;
            width: 100%;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border: 0 !important;
            outline: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            border-radius: 12px;
            text-align: left;
            font-size: 12px;
            font-weight: 500;
            color: #334155;
            transition: background-color 0.18s ease, color 0.18s ease;
            text-decoration: none !important;
        }

        .dh-file-menu-item:link,
        .dh-file-menu-item:visited,
        .dh-file-menu-item:hover,
        .dh-file-menu-item:active {
            text-decoration: none !important;
        }

        .dh-file-menu-item * {
            text-decoration: none !important;
        }

        .dh-file-menu-item:hover {
            background: #f8fafc !important;
            color: #0f172a;
        }

        .dh-file-menu-item.danger {
            color: #e11d48;
        }

        .dh-file-menu-item.danger:hover {
            background: #fff1f2 !important;
            color: #be123c;
        }

        .dh-file-menu {
            position: fixed;
            top: 0;
            left: 0;
            min-width: 220px;
            max-width: min(260px, calc(100vw - 20px));
            max-height: min(360px, calc(100vh - 20px));
            overflow-y: auto;
            padding: 8px;
            border-radius: 18px;
            border: 1px solid #e2e8f0;
            background: #fff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14);
            scrollbar-width: thin;
            scrollbar-color: rgba(148, 163, 184, 0.7) transparent;
            z-index: 140;
            overscroll-behavior: contain;
        }

        .dh-file-menu::-webkit-scrollbar {
            width: 8px;
        }

        .dh-file-menu::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.7);
            border-radius: 999px;
        }

        .dh-file-menu-anchor {
            position: relative;
            z-index: 130;
        }

        #dh-list-wrap {
            isolation: isolate;
            overflow: visible !important;
        }

        #dh-list-wrap,
        #dh-list-wrap table,
        #dh-list-wrap thead,
        #dh-list-wrap tbody,
        #dh-list-wrap tr,
        #dh-list-wrap td {
            overflow: visible !important;
        }

        .dh-folder-row {
            position: relative;
            z-index: 1;
        }

        .dh-folder-row.menu-open-row {
            z-index: 220;
        }

        .dh-list-action-cell {
            position: relative;
            overflow: visible !important;
        }

        .dh-list-action-anchor {
            position: relative;
            display: inline-block;
            overflow: visible !important;
            z-index: 130;
        }

        .dh-list-action-anchor.menu-open-anchor {
            z-index: 240;
        }

        .dh-list-action-cell.menu-open-cell {
            z-index: 230;
        }

        #dh-list-wrap .dh-folder-menu {
            z-index: 260 !important;
        }

        .dh-list-action-cell {
            position: relative;
            overflow: visible !important;
        }

        #dh-list-wrap tbody tr {
            position: relative;
            z-index: 1;
        }

        #dh-list-wrap tbody tr.dh-list-row-menu-open {
            z-index: 220;
        }

        .dh-list-action-cell.dh-menu-open-cell {
            z-index: 230;
        }

        #dh-list-wrap .dh-file-menu {
            z-index: 260 !important;
        }

        #dh-folders-list-wrap {
            isolation: isolate;
            overflow: visible !important;
        }

        #dh-folders-list-wrap,
        #dh-folders-list-wrap table,
        #dh-folders-list-wrap thead,
        #dh-folders-list-wrap tbody,
        #dh-folders-list-wrap tr,
        #dh-folders-list-wrap td {
            overflow: visible !important;
        }

        #dh-folders-list-wrap tbody tr {
            position: relative;
            z-index: 1;
        }

        #dh-folders-list-wrap tbody tr.dh-list-row-menu-open {
            z-index: 220;
        }

        #dh-folders-list-wrap .dh-list-action-cell.dh-menu-open-cell {
            z-index: 230;
        }

        #dh-folders-list-wrap .dh-list-action-anchor.dh-menu-open-anchor {
            z-index: 240;
        }

        #dh-folders-list-wrap .dh-file-menu {
            z-index: 260 !important;
        }

        #dh-folders-box {
            position: relative;
            z-index: 1;
        }

        #dh-folders-box.dh-box-menu-open {
            z-index: 300;
        }

        #dh-files-box {
            position: relative;
            z-index: 1;
        }

        .dh-move-tree-node {
            border-radius: 16px;
            border: 1px solid transparent;
            transition: all 0.18s ease;
        }

        .dh-move-tree-node:hover {
            background: #f8fafc;
            border-color: #e2e8f0;
        }

        .dh-move-tree-node.is-selected {
            background: #e0f2fe;
            border-color: #38bdf8;
        }

        .dh-move-tree-node.is-disabled {
            opacity: 0.55;
        }

        .dh-move-tree-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 12px;
            border: 0;
            background: transparent;
            text-align: left;
            border-radius: 16px;
        }

        .dh-move-tree-btn:focus {
            outline: none;
        }

        .dh-move-tree-left {
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dh-move-tree-title {
            font-size: 12px;
            font-weight: 600;
            color: #0f172a;
            line-height: 1.3;
            word-break: break-word;
        }

        .dh-move-tree-meta {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
        }

        .dh-move-tree-children {
            margin-left: 18px;
            padding-left: 14px;
            border-left: 1px dashed #cbd5e1;
        }

        .dh-move-tree-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 999px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #64748b;
            flex-shrink: 0;
        }

        .dh-move-tree-toggle:hover {
            background: #f8fafc;
            color: #0f172a;
        }

        .dh-move-tree-spacer {
            width: 26px;
            height: 26px;
            flex-shrink: 0;
        }

        .dh-move-tree-check {
            width: 20px;
            height: 20px;
            border-radius: 999px;
            border: 1px solid #cbd5e1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: transparent;
            background: #fff;
        }

        .dh-move-tree-node.is-selected .dh-move-tree-check {
            border-color: #0ea5e9;
            background: #0ea5e9;
            color: #fff;
        }

        @media (max-width: 1024px) {
            .dh-drive-shell {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .dh-file-menu {
                min-width: 190px;
                max-width: calc(100vw - 16px);
                max-height: calc(100vh - 16px);
                border-radius: 16px;
            }
        }

        @media (max-width: 640px) {
            .dh-upload-queue {
                right: 12px;
                bottom: 12px;
                width: calc(100vw - 24px);
            }
        }
    </style>

    <div id="dh-show-root"
        class="max-w-7xl mx-auto font-plus space-y-5"
        data-is-admin="{{ $isAdmin ? 1 : 0 }}"
        data-can-upload="{{ $canUpload ? 1 : 0 }}"
        data-can-manage="{{ $canManage ? 1 : 0 }}">

        {{-- Breadcrumb --}}
        <div class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
            @if($folder->parent)
            <a href="{{ route('dh.show', $folder->parent) }}"
                class="inline-flex items-center gap-2 rounded-full px-1 py-1 font-semibold text-slate-700 no-underline hover:text-slate-900 leading-none transition">
                <i class="fa-solid fa-arrow-left text-[11px] leading-none translate-y-[1px]"></i>
                <span class="leading-none">Back to {{ $folder->parent->folder_name }}</span>
            </a>
            @else
            <a href="{{ route('admin.dashboard') }}"
                class="inline-flex items-center gap-2 rounded-full px-1 py-1 font-semibold text-slate-700 no-underline hover:text-slate-900 leading-none transition">
                <i class="fa-solid fa-arrow-left text-[11px] leading-none translate-y-[1px]"></i>
                <span class="leading-none">Back to dashboard</span>
            </a>
            @endif

            <span class="text-slate-300">/</span>

            <a href="{{ route('dh.index') }}"
                class="font-semibold text-slate-600 no-underline hover:text-slate-900 leading-none transition">
                Document Hub
            </a>

            @foreach($breadcrumbs as $crumb)
            <span class="text-slate-300">/</span>

            @if(!$loop->last)
            <a href="{{ route('dh.show', $crumb) }}"
                class="font-semibold text-slate-600 no-underline hover:text-slate-900 leading-none transition">
                {{ $crumb->folder_name }}
            </a>
            @else
            <span class="font-semibold text-slate-700 leading-none">
                {{ $crumb->folder_name }}
            </span>
            @endif
            @endforeach
        </div>

        <div class="dh-drive-shell">
            {{-- Sidebar --}}
            <aside class="dh-glass rounded-3xl p-4 md:p-5 h-fit">
                <div class="flex items-center gap-3 px-2 pb-4 border-b border-slate-200/80">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-100 text-sky-600">
                        <i class="fa-solid fa-folder-open text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-base font-semibold text-slate-900">{{ $folder->folder_name }}</h1>
                        <p class="text-[11px] text-slate-500">Folder files</p>
                    </div>
                </div>

                <div class="mt-4 space-y-2">
                    @if($isAdmin)
                    <button type="button"
                        id="dh-open-folder-modal"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                        <i class="fa-solid fa-folder-plus text-xs"></i>
                        <span>New Folder</span>
                    </button>
                    @endif

                    @if($canUpload)
                    <button type="button"
                        id="dh-open-upload-modal"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-sky-500 px-4 py-3 text-sm font-semibold text-white shadow-md hover:bg-sky-600">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>New Upload</span>
                    </button>
                    @endif

                    <div class="space-y-2 pt-2">
                        <div class="dh-drive-sidebar-link active flex items-center justify-between rounded-2xl border border-slate-200 px-3 py-3 text-[12px] font-semibold transition-all">
                            <span class="inline-flex items-center gap-2">
                                <i class="fa-regular fa-folder-open text-[12px]"></i>
                                This Folder
                            </span>
                            <span class="rounded-full bg-white/20 px-2 py-0.5 text-[10px]">
                                {{ $folderCount + $fileCount }}
                            </span>
                        </div>

                        <a href="{{ route('dh.index') }}"
                            class="dh-drive-sidebar-link flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-3 py-3 text-[12px] font-semibold text-slate-700 transition-all">
                            <span class="inline-flex items-center gap-2">
                                <i class="fa-solid fa-hard-drive text-[12px]"></i>
                                My Drive
                            </span>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] text-slate-600">Open</span>
                        </a>

                        @if($isAdmin)
                        <a href="{{ route('dh.trash.index') }}"
                            class="dh-drive-sidebar-link flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-3 py-3 text-[12px] font-semibold text-slate-700 transition-all">
                            <span class="inline-flex items-center gap-2">
                                <i class="fa-regular fa-trash-can text-[12px]"></i>
                                Trash
                            </span>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] text-slate-600">View</span>
                        </a>
                        @endif

                        @if($folder->parent)
                        <a href="{{ route('dh.show', $folder->parent) }}"
                            class="dh-drive-sidebar-link flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-3 py-3 text-[12px] font-semibold text-slate-700 transition-all">
                            <span class="inline-flex items-center gap-2">
                                <i class="fa-solid fa-arrow-left text-[12px]"></i>
                                Back to {{ $folder->parent->folder_name }}
                            </span>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] text-slate-600">Open</span>
                        </a>
                        @endif
                    </div>

                    <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <h3 class="text-[12px] font-semibold text-slate-800">Folder Info</h3>
                        <div class="mt-3 space-y-2 text-[11px] text-slate-500">
                            <p><span class="font-medium text-slate-700">Month:</span> {{ $folder->month_label ?: 'No month set' }}</p>
                            <p><span class="font-medium text-slate-700">Folders:</span> {{ $folderCount }}</p>
                            <p><span class="font-medium text-slate-700">Files:</span> {{ $fileCount }}</p>
                            <p><span class="font-medium text-slate-700">Notes:</span> {{ $folder->remarks ?: 'No remarks' }}</p>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Main canvas --}}
            <div id="dh-main-drop-area" class="space-y-4">
                <div class="dh-glass rounded-3xl px-4 py-4 md:px-6 md:py-5">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl md:text-2xl font-semibold text-slate-900">
                                My Drive <span class="text-slate-400 mx-1">›</span> {{ $folder->folder_name }}
                            </h2>
                            <p class="mt-1 text-xs text-slate-500">
                                Upload directly into this folder and browse files in a simpler Drive-style view.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <div class="inline-flex rounded-full bg-white p-1 gap-1">
                                <button type="button"
                                    id="dh-grid-view-btn"
                                    class="dh-view-toggle-btn is-active inline-flex h-12 w-12 items-center justify-center rounded-full"
                                    title="Grid view">
                                    <i class="fa-solid fa-table-cells-large text-[14px]"></i>
                                </button>

                                <button type="button"
                                    id="dh-list-view-btn"
                                    class="dh-view-toggle-btn inline-flex h-12 w-12 items-center justify-center rounded-full"
                                    title="List view">
                                    <i class="fa-solid fa-list-ul text-[14px]"></i>
                                </button>
                            </div>

                            @if($fileCount > 0)
                            <a href="{{ route('dh.folder.downloadAll', $folder) }}"
                                class="inline-flex h-12 items-center gap-2 rounded-full bg-slate-900 px-5 text-[12px] font-semibold text-white shadow-sm transition hover:bg-slate-800">
                                <i class="fa-solid fa-file-zipper text-[12px]"></i>
                                <span>Download all</span>
                            </a>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
                        <div class="relative w-full lg:max-w-md">
                            <input
                                id="dh-file-search"
                                type="text"
                                placeholder="Search folders and files in this folder..."
                                class="w-full rounded-2xl border border-slate-200 bg-white px-11 py-3 text-[12px] text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100">
                            <span class="absolute inset-y-0 left-4 flex items-center text-slate-400 text-xs">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                            @if($folderCount > 0)
                            <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2">
                                <i class="fa-solid fa-circle text-[8px] text-sky-500"></i>
                                {{ $folderCount }} folder{{ $folderCount === 1 ? '' : 's' }}
                            </span>
                            @endif

                            @if($fileCount > 0)
                            <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2">
                                <i class="fa-solid fa-circle text-[8px] text-emerald-500"></i>
                                {{ $fileCount }} file{{ $fileCount === 1 ? '' : 's' }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>

                @if($canUpload)
                <div id="dh-folder-upload-modal"
                    class="fixed top-0 left-0 w-screen h-screen z-[9999] hidden items-center justify-center bg-slate-900/70 backdrop-blur-sm px-4 py-4 !mt-0"
                    style="margin-top:0 !important;">
                    <div class="w-full max-w-2xl max-h-[calc(100vh-2rem)] overflow-y-auto rounded-3xl bg-white shadow-2xl border border-slate-200">
                        <div class="flex items-start justify-between px-6 py-5 border-b border-slate-200">
                            <div>
                                <h2 class="text-base font-semibold text-slate-900">Upload Files</h2>
                                <p class="mt-1 text-[12px] text-slate-500">Choose the date, add description, then select files to upload.</p>
                            </div>
                            <button type="button"
                                data-dh-folder-upload-close
                                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-700">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <form id="dh-folder-upload-form"
                            method="POST"
                            action="{{ route('dh.folder.quickUpload', $folder) }}"
                            enctype="multipart/form-data"
                            class="px-6 py-5 space-y-5">
                            @csrf

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-[12px] font-semibold text-slate-700">Date</label>
                                    <input type="date"
                                        name="doc_date"
                                        id="dh-folder-upload-date"
                                        value="{{ now()->format('Y-m-d') }}"
                                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100">
                                </div>

                                <div>
                                    <label class="mb-2 block text-[12px] font-semibold text-slate-700">Description</label>
                                    <input type="text"
                                        name="description"
                                        id="dh-folder-upload-description"
                                        placeholder="Enter file description"
                                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100">
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-[12px] font-semibold text-slate-700">Files</label>
                                <label for="dh-folder-upload-file"
                                    id="dh-folder-upload-dropzone"
                                    class="flex min-h-[140px] cursor-pointer flex-col items-center justify-center rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-6 py-8 text-center transition hover:border-sky-400 hover:bg-sky-50/60">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-sky-500 shadow-sm">
                                        <i class="fa-solid fa-cloud-arrow-up text-xl"></i>
                                    </div>
                                    <p class="mt-4 text-sm font-semibold text-slate-800">Click to choose files</p>
                                    <p class="mt-1 text-[12px] text-slate-500">PDF, images, videos, and Excel files are allowed</p>
                                </label>

                                <input id="dh-folder-upload-file"
                                    type="file"
                                    name="files[]"
                                    multiple
                                    accept="application/pdf,image/*,video/*,.xls,.xlsx,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                                    class="hidden">
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <h3 class="text-[12px] font-semibold text-slate-800">Selected files</h3>
                                    <span id="dh-folder-selected-count" class="text-[11px] text-slate-500">Selected: 0 files</span>
                                </div>

                                <div id="dh-folder-selected-empty" class="mt-3 text-[11px] text-slate-400">
                                    No files selected yet.
                                </div>

                                <div id="dh-folder-selected-list" class="mt-3 hidden max-h-48 overflow-auto space-y-2"></div>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-2">
                                <button type="button"
                                    data-dh-folder-upload-close
                                    class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    Cancel
                                </button>

                                <button type="submit"
                                    id="dh-folder-upload-submit"
                                    class="inline-flex items-center justify-center rounded-2xl bg-sky-500 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-sky-600">
                                    Upload Files
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="dh-folder-upload-queue"
                    class="dh-upload-queue hidden rounded-3xl border border-slate-200 bg-white p-4 shadow-2xl">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Uploading files</h3>
                            <p id="dh-folder-queue-sub" class="mt-1 text-[12px] text-slate-500">Preparing upload...</p>
                        </div>
                        <button type="button"
                            id="dh-folder-queue-close"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 hover:bg-slate-50">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>

                    <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100">
                        <div id="dh-folder-queue-bar" class="h-full w-0 rounded-full bg-sky-500 transition-all"></div>
                    </div>
                </div>
                @endif

                @if($subfolders->count())
                <div id="dh-folders-box" class="dh-glass rounded-3xl p-4 md:p-5">
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold text-slate-900">Folders</h3>
                        <p class="text-[11px] text-slate-500 mt-1">Folders inside {{ $folder->folder_name }}</p>
                    </div>

                    {{-- FOLDER GRID VIEW --}}
                    <div id="dh-folders-grid-wrap" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($subfolders as $sub)
                        <div
                            data-folder-card
                            data-folder-name="{{ strtolower($sub->folder_name) }}"
                            data-folder-title="{{ $sub->folder_name }}"
                            data-folder-url="{{ route('dh.show', $sub) }}"
                            data-folder-rename-url="{{ route('dh.folders.rename', $sub) }}"
                            data-folder-trash-url="{{ route('dh.folders.trash', $sub) }}"
                            data-folder-created="{{ optional($sub->created_at)->format('d M Y') }}"
                            data-folder-remarks="{{ e($sub->remarks ?? '') }}"
                            class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm hover:border-sky-300 hover:shadow-md transition cursor-pointer">

                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-600">
                                        <i class="fa-solid fa-folder text-lg"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-sm font-semibold text-slate-900 break-words whitespace-normal">{{ $sub->folder_name }}</h4>
                                        <p class="text-[11px] text-slate-500">{{ optional($sub->created_at)->format('d M Y') }}</p>
                                    </div>
                                </div>

                                @if($isAdmin)
                                <div class="dh-file-menu-anchor relative">
                                    <button type="button"
                                        class="dh-file-menu-btn inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-700"
                                        data-folder-menu-toggle>
                                        <i class="fa-solid fa-ellipsis-vertical text-[11px]"></i>
                                    </button>

                                    <div class="dh-file-menu hidden" data-folder-menu>
                                        <a href="{{ route('dh.show', $sub) }}" class="dh-file-menu-item">
                                            <i class="fa-regular fa-folder-open w-4 text-slate-400"></i>
                                            <span>Open</span>
                                        </a>

                                        <button type="button" class="dh-file-menu-item" data-folder-rename>
                                            <i class="fa-regular fa-pen-to-square w-4 text-slate-400"></i>
                                            <span>Rename</span>
                                        </button>

                                        <button type="button" class="dh-file-menu-item" data-folder-info>
                                            <i class="fa-solid fa-info w-4 text-slate-400"></i>
                                            <span>Folder information</span>
                                        </button>

                                        <div class="my-1 border-t border-slate-100"></div>

                                        <button type="button" class="dh-file-menu-item danger" data-folder-trash>
                                            <i class="fa-regular fa-trash-can w-4"></i>
                                            <span>Move to trash</span>
                                        </button>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- FOLDER LIST VIEW --}}
                    <div id="dh-folders-list-wrap" class="hidden overflow-visible rounded-2xl border border-slate-200 bg-white">
                        <table class="w-full table-fixed text-[12px]">
                            <thead class="bg-slate-50 text-slate-600">
                                <tr>
                                    <th class="w-[50%] px-4 py-3 text-left">Name</th>
                                    <th class="w-[18%] px-4 py-3 text-left">Date</th>
                                    <th class="w-[14%] px-4 py-3 text-left">Type</th>
                                    <th class="w-[18%] px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subfolders as $sub)
                                <tr class="border-t border-slate-100 hover:bg-slate-50/70 cursor-pointer"
                                    data-folder-row
                                    data-folder-name="{{ strtolower($sub->folder_name) }}"
                                    data-folder-title="{{ $sub->folder_name }}"
                                    data-folder-url="{{ route('dh.show', $sub) }}"
                                    data-folder-rename-url="{{ route('dh.folders.rename', $sub) }}"
                                    data-folder-trash-url="{{ route('dh.folders.trash', $sub) }}"
                                    data-folder-created="{{ optional($sub->created_at)->format('d M Y') }}"
                                    data-folder-remarks="{{ e($sub->remarks ?? '') }}">

                                    <td class="px-4 py-3 align-top whitespace-normal">
                                        <div class="flex items-start gap-2 min-w-0">
                                            <i class="fa-solid fa-folder text-sky-500 mt-0.5 shrink-0"></i>
                                            <div class="min-w-0 w-full">
                                                <div class="font-medium text-slate-900 break-words">
                                                    {{ $sub->folder_name }}
                                                </div>
                                                @if(!empty($sub->remarks))
                                                <p class="mt-2 text-[11px] text-slate-500 break-words">
                                                    {{ $sub->remarks }}
                                                </p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 text-slate-600 align-top whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] whitespace-nowrap">
                                            <i class="fa-regular fa-calendar"></i>
                                            <span>{{ optional($sub->created_at)->format('d M Y') }}</span>
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 text-slate-600 align-top">
                                        Folder
                                    </td>

                                    <td class="dh-list-action-cell px-4 py-3 text-right align-top">
                                        <div class="dh-list-action-anchor">
                                            @if($isAdmin)
                                            <button type="button"
                                                class="dh-file-menu-btn inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-700"
                                                data-folder-menu-toggle>
                                                <i class="fa-solid fa-ellipsis-vertical text-[11px]"></i>
                                            </button>

                                            <div class="dh-file-menu hidden" data-folder-menu>
                                                <a href="{{ route('dh.show', $sub) }}" class="dh-file-menu-item">
                                                    <i class="fa-regular fa-folder-open w-4 text-slate-400"></i>
                                                    <span>Open</span>
                                                </a>

                                                @if($isAdmin)
                                                <button type="button" class="dh-file-menu-item" data-folder-rename>
                                                    <i class="fa-regular fa-pen-to-square w-4 text-slate-400"></i>
                                                    <span>Rename</span>
                                                </button>
                                                @endif

                                                <button type="button" class="dh-file-menu-item" data-folder-info>
                                                    <i class="fa-solid fa-info w-4 text-slate-400"></i>
                                                    <span>Folder information</span>
                                                </button>

                                                @if($isAdmin)
                                                <div class="my-1 border-t border-slate-100"></div>

                                                <button type="button" class="dh-file-menu-item danger" data-folder-trash>
                                                    <i class="fa-regular fa-trash-can w-4"></i>
                                                    <span>Move to trash</span>
                                                </button>
                                                @endif
                                            </div>
                                            @else
                                            <a href="{{ route('dh.show', $sub) }}"
                                                class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-semibold text-slate-700 hover:bg-slate-50">
                                                <i class="fa-solid fa-folder-open text-[10px]"></i>
                                                <span>Open</span>
                                            </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                @if($fileItems->count())
                <div id="dh-files-box" class="dh-glass rounded-3xl p-4 md:p-5">
                    {{-- GRID VIEW --}}
                    <div id="dh-grid-wrap" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach($fileItems as $item)
                        @php
                        $rec = $item['record'];
                        $att = $item['attachment'];
                        $isImage = str_starts_with((string) ($item['mime'] ?? ''), 'image/');
                        $downloadUrl = route('dh.attachments.download', $att);
                        @endphp

                        <div class="dh-drive-file-card rounded-3xl border border-slate-200 bg-white p-3"
                            data-file-card
                            data-file-name="{{ strtolower($item['name']) }}"
                            data-record-id="{{ $item['record_id'] }}"
                            data-att-id="{{ $att->id }}"
                            data-rename-url="{{ route('dh.attachments.rename', $att) }}"
                            data-trash-url="{{ route('dh.attachments.trash', $att) }}"
                            data-move-url="{{ route('dh.attachments.move', $att) }}"
                            data-share-url="{{ route('dh.attachments.share', $att) }}"
                            data-download-url="{{ $downloadUrl }}"
                            data-inline-url="{{ route('dh.attachments.download', [$att, 'inline' => 1]) }}"
                            data-file-title="{{ $item['name'] }}"
                            data-file-type="{{ $item['mime'] ?: 'Unknown type' }}"
                            data-file-size="{{ $att->size ? number_format($att->size / 1024, 1) . ' KB' : '—' }}"
                            data-file-date="{{ $item['date'] }}"
                            data-file-created="{{ $item['created'] }}"
                            data-file-folder="{{ $folder->folder_name }}"
                            data-file-description="{{ e($att->description ?? '') }}"
                            data-description-url="{{ route('dh.attachments.description', $att) }}">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-regular {{ $isImage ? 'fa-image text-rose-400' : 'fa-file-lines text-slate-400' }}"></i>
                                        <h3 class="dh-drive-file-name text-sm font-semibold text-slate-900">
                                            {{ $item['name'] }}
                                        </h3>
                                    </div>

                                    <div class="mt-2 flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1">
                                            <i class="fa-regular fa-calendar"></i>
                                            <span>{{ $item['date'] }}</span>
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <div class="dh-file-menu-anchor relative">
                                        <button type="button"
                                            class="dh-file-menu-btn inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-700"
                                            data-file-menu-toggle>
                                            <i class="fa-solid fa-ellipsis-vertical text-[11px]"></i>
                                        </button>

                                        <div class="dh-file-menu dh-file-menu--grid hidden" data-file-menu>
                                            <button type="button" class="dh-file-menu-item" data-file-preview>
                                                <i class="fa-regular fa-eye w-4 text-slate-400"></i>
                                                Open
                                            </button>

                                            <a href="{{ $downloadUrl }}" class="dh-file-menu-item">
                                                <i class="fa-solid fa-download w-4 text-slate-400"></i>
                                                Download
                                            </a>

                                            @if($isAdmin)
                                            <button type="button" class="dh-file-menu-item" data-file-rename>
                                                <i class="fa-regular fa-pen-to-square w-4 text-slate-400"></i>
                                                Rename
                                            </button>

                                            <button type="button" class="dh-file-menu-item" data-file-edit-description
                                                data-description-url="{{ route('dh.attachments.description', $att) }}">
                                                <i class="fa-regular fa-note-sticky w-4 text-slate-400"></i>
                                                <span>Edit description</span>
                                            </button>
                                            @endif

                                            <button type="button" class="dh-file-menu-item" data-file-share>
                                                <i class="fa-regular fa-share-from-square w-4 text-slate-400"></i>
                                                Share link
                                            </button>

                                            @if($isAdmin)
                                            <button type="button" class="dh-file-menu-item" data-file-move>
                                                <i class="fa-regular fa-folder-open w-4 text-slate-400"></i>
                                                Move
                                            </button>
                                            @endif

                                            <button type="button" class="dh-file-menu-item" data-file-info>
                                                <i class="fa-solid fa-info w-4 text-slate-400"></i>
                                                File information
                                            </button>

                                            @if($isAdmin)
                                            <div class="my-1 border-t border-slate-100"></div>

                                            <button type="button" class="dh-file-menu-item danger" data-file-trash>
                                                <i class="fa-regular fa-trash-can w-4"></i>
                                                Move to trash
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="dh-drive-preview mt-3 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 cursor-pointer"
                                data-file-preview>
                                @if($isImage)
                                <img src="{{ $downloadUrl }}" alt="{{ $item['name'] }}">
                                @else
                                <div class="dh-drive-preview-icon">
                                    <i class="fa-regular fa-file-lines"></i>
                                </div>
                                @endif
                            </div>

                            <div class="mt-3 space-y-2">
                                <div class="rounded-2xl bg-slate-50 px-3 py-2 min-h-[52px]" data-file-description-holder>
                                    @if(!empty($att->description))
                                    <p class="text-[12px] text-slate-600 line-clamp-2" data-file-description-text>{{ $att->description }}</p>
                                    @else
                                    <p class="text-[12px] text-slate-400 italic" data-file-description-text>No description</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- LIST VIEW --}}
                    <div id="dh-list-wrap" class="hidden overflow-visible rounded-2xl border border-slate-200 bg-white">
                        <table class="w-full table-fixed text-[12px]">
                            <thead class="bg-slate-50 text-slate-600">
                                <tr>
                                    <th class="w-[46%] px-4 py-3 text-left">Name</th>
                                    <th class="w-[14%] px-4 py-3 text-left">Date</th>
                                    <th class="w-[14%] px-4 py-3 text-left">Type</th>
                                    <th class="w-[10%] px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fileItems as $item)
                                @php
                                $rec = $item['record'];
                                $att = $item['attachment'];
                                $downloadUrl = route('dh.attachments.download', $att);
                                @endphp

                                <tr class="border-t border-slate-100"
                                    data-file-row
                                    data-file-name="{{ strtolower($item['name']) }}"
                                    data-record-id="{{ $item['record_id'] }}"
                                    data-att-id="{{ $att->id }}"
                                    data-rename-url="{{ route('dh.attachments.rename', $att) }}"
                                    data-trash-url="{{ route('dh.attachments.trash', $att) }}"
                                    data-move-url="{{ route('dh.attachments.move', $att) }}"
                                    data-share-url="{{ route('dh.attachments.share', $att) }}"
                                    data-download-url="{{ $downloadUrl }}"
                                    data-inline-url="{{ route('dh.attachments.download', [$att, 'inline' => 1]) }}"
                                    data-file-title="{{ $item['name'] }}"
                                    data-file-type="{{ $item['mime'] ?: 'Unknown type' }}"
                                    data-file-size="{{ $att->size ? number_format($att->size / 1024, 1) . ' KB' : '—' }}"
                                    data-file-date="{{ $item['date'] }}"
                                    data-file-created="{{ $item['created'] }}"
                                    data-file-folder="{{ $folder->folder_name }}"
                                    data-file-description="{{ e($att->description ?? '') }}"
                                    data-description-url="{{ route('dh.attachments.description', $att) }}">

                                    <td class="px-4 py-3">
                                        <div class="flex items-start gap-2">
                                            <i class="fa-regular fa-file-lines text-slate-400 mt-0.5"></i>

                                            <div class="min-w-0">
                                                <div class="font-medium text-slate-900 break-words">
                                                    {{ $item['name'] }}
                                                </div>

                                                <div class="mt-2" data-file-description-holder>
                                                    @if(!empty($att->description))
                                                    <p class="text-[11px] text-slate-500 break-words" data-file-description-text>
                                                        {{ $att->description }}
                                                    </p>
                                                    @else
                                                    <p class="text-[11px] text-slate-400 italic" data-file-description-text>
                                                        No description
                                                    </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 text-slate-600">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[11px]">
                                            <i class="fa-regular fa-calendar"></i>
                                            <span>{{ $item['date'] }}</span>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">{{ $item['mime'] ?: 'Unknown' }}</td>

                                    <td class="dh-list-action-cell px-4 py-3 text-right">
                                        <div class="dh-list-action-anchor">
                                            <button type="button"
                                                class="dh-file-menu-btn inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-700"
                                                data-file-menu-toggle>
                                                <i class="fa-solid fa-ellipsis-vertical text-[11px]"></i>
                                            </button>

                                            <div class="dh-file-menu dh-file-menu--list hidden" data-file-menu>
                                                <button type="button" class="dh-file-menu-item" data-file-preview>
                                                    <i class="fa-regular fa-eye w-4 text-slate-400"></i> Open
                                                </button>
                                                <a href="{{ $downloadUrl }}" class="dh-file-menu-item">
                                                    <i class="fa-solid fa-download w-4 text-slate-400"></i> Download
                                                </a>
                                                @if($isAdmin)
                                                <button type="button" class="dh-file-menu-item" data-file-rename>
                                                    <i class="fa-regular fa-pen-to-square w-4 text-slate-400"></i> Rename
                                                </button>
                                                <button type="button"
                                                    class="dh-file-menu-item"
                                                    data-file-edit-description
                                                    data-description-url="{{ route('dh.attachments.description', $att) }}">
                                                    <i class="fa-regular fa-note-sticky w-4 text-slate-400"></i>
                                                    <span>Edit description</span>
                                                </button>
                                                @endif
                                                <button type="button" class="dh-file-menu-item" data-file-share>
                                                    <i class="fa-regular fa-share-from-square w-4 text-slate-400"></i> Share link
                                                </button>
                                                @if($isAdmin)
                                                <button type="button" class="dh-file-menu-item" data-file-move>
                                                    <i class="fa-regular fa-folder-open w-4 text-slate-400"></i> Move
                                                </button>
                                                @endif
                                                <button type="button" class="dh-file-menu-item" data-file-info>
                                                    <i class="fa-solid fa-info w-4 text-slate-400"></i> File information
                                                </button>
                                                @if($isAdmin)
                                                <div class="my-1 border-t border-slate-100"></div>
                                                <button type="button" class="dh-file-menu-item danger" data-file-trash>
                                                    <i class="fa-regular fa-trash-can w-4"></i> Move to trash
                                                </button>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @elseif(!$subfolders->count())
                <div class="dh-glass rounded-3xl p-6 md:p-8">
                    <div id="dh-folder-dropzone"
                        class="flex min-h-[520px] flex-col items-center justify-center rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center transition-all">
                        <div class="flex h-24 w-24 items-center justify-center rounded-[30px] bg-sky-50 text-sky-500">
                            <i class="fa-solid fa-cloud-arrow-up text-4xl"></i>
                        </div>

                        <h3 class="mt-6 text-3xl font-semibold text-slate-900">Drop files here</h3>
                        <p class="mt-3 text-lg text-slate-500">
                            or use the “New Upload” button from the sidebar.
                        </p>

                        @if($canUpload)
                        <button type="button"
                            id="dh-open-upload-modal-empty"
                            class="mt-6 inline-flex items-center gap-2 rounded-2xl bg-sky-500 px-6 py-3 text-sm font-semibold text-white shadow-md hover:bg-sky-600">
                            <i class="fa-solid fa-plus text-xs"></i>
                            <span>Browse files</span>
                        </button>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <form id="dh-file-rename-form" method="POST" class="hidden">
        @csrf
        @method('PATCH')
        <input type="hidden" name="original_name" id="dh-file-rename-name">
    </form>

    <form id="dh-file-trash-form" method="POST" class="hidden">
        @csrf
        @method('PATCH')
    </form>

    <form id="dh-file-move-form" method="POST" class="hidden">
        @csrf
        @method('PATCH')
        <input type="hidden" name="target_folder_id" id="dh-file-move-folder-id">
    </form>

    <div id="dh-file-rename-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm px-4">
        <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl border border-slate-200 px-6 py-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Rename File</h2>
                    <p class="mt-1 text-[11px] text-slate-500">Update the file name.</p>
                </div>
                <button type="button" data-dh-file-rename-close class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-400 hover:bg-slate-50">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <input type="text"
                id="dh-file-rename-input"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-[12px] focus:border-sky-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-100">

            <div class="mt-5 flex items-center justify-end gap-2">
                <button type="button" data-dh-file-rename-close class="rounded-full px-4 py-2 border border-slate-300 text-slate-600 bg-white hover:bg-slate-50">Cancel</button>
                <button type="button" id="dh-file-rename-confirm" class="rounded-full px-5 py-2 bg-slate-900 text-white font-semibold shadow-sm hover:bg-slate-800">Save</button>
            </div>
        </div>
    </div>

    <div id="dh-file-move-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm px-4">
        <div class="w-full max-w-2xl rounded-3xl bg-white shadow-2xl border border-slate-200 px-6 py-6">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Move File</h2>
                    <p class="mt-1 text-[11px] text-slate-500">
                        Search and choose the destination folder from the tree below.
                    </p>
                </div>

                <button type="button"
                    data-dh-file-move-close
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-400 hover:bg-slate-50">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div class="relative">
                    <input
                        type="text"
                        id="dh-file-move-search"
                        placeholder="Search folder..."
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 pl-11 pr-4 py-3 text-[12px] text-slate-700 focus:border-sky-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-100">
                    <span class="absolute inset-y-0 left-4 flex items-center text-slate-400 text-xs">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-semibold text-slate-700">Selected destination</p>
                            <p id="dh-file-move-selected-label" class="mt-1 text-[11px] text-slate-400 italic">
                                No folder selected
                            </p>
                        </div>

                        <button type="button"
                            id="dh-file-move-clear"
                            class="hidden rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-semibold text-slate-600 hover:bg-slate-50">
                            Clear
                        </button>
                    </div>
                </div>

                <div id="dh-file-move-tree-wrap"
                    class="max-h-[420px] overflow-auto rounded-2xl border border-slate-200 bg-white p-3">
                    @php
                    $folderTreeItems = collect($folderOptions ?? [])
                    ->map(function ($opt) use ($folder) {
                    return [
                    'id' => $opt->id,
                    'name' => $opt->folder_name,
                    'parent_id' => $opt->parent_id ?? null,
                    'is_current' => (int) $opt->id === (int) $folder->id,
                    ];
                    })
                    ->values();
                    @endphp

                    <div id="dh-file-move-tree"
                        data-folder-tree='@json($folderTreeItems)'>
                    </div>

                    <div id="dh-file-move-empty"
                        class="hidden py-10 text-center text-[12px] text-slate-400">
                        No matching folders found.
                    </div>
                </div>
            </div>

            <div class="mt-5 flex items-center justify-end gap-2">
                <button type="button"
                    data-dh-file-move-close
                    class="rounded-full px-4 py-2 border border-slate-300 text-slate-600 bg-white hover:bg-slate-50">
                    Cancel
                </button>

                <button type="button"
                    id="dh-file-move-confirm"
                    class="rounded-full px-5 py-2 bg-slate-900 text-white font-semibold shadow-sm hover:bg-slate-800">
                    Move
                </button>
            </div>
        </div>
    </div>

    <div id="dh-file-trash-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm px-4">
        <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl border border-slate-200 px-6 py-6">
            <div class="flex items-start justify-between mb-3">
                <h2 class="text-sm font-semibold text-slate-900">Move file to trash?</h2>
                <button type="button" data-dh-file-trash-close class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-400 hover:bg-slate-50">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <p id="dh-file-trash-text" class="text-[11px] text-slate-600 leading-relaxed"></p>

            <div class="mt-4 flex items-center justify-end gap-2">
                <button type="button" data-dh-file-trash-close class="rounded-full px-4 py-2 border border-slate-300 text-slate-600 bg-white hover:bg-slate-50">Cancel</button>
                <button type="button" id="dh-file-trash-confirm" class="rounded-full px-5 py-2 bg-rose-500 text-white font-semibold shadow-sm hover:bg-rose-600">Move to trash</button>
            </div>
        </div>
    </div>

    <div id="dh-file-info-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm px-4">
        <div class="w-full max-w-lg rounded-3xl bg-white shadow-2xl border border-slate-200 px-6 py-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">File Information</h2>
                    <p class="mt-1 text-[11px] text-slate-500">Details about this file.</p>
                </div>
                <button type="button" data-dh-file-info-close class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-400 hover:bg-slate-50">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Name</p>
                    <p id="dh-file-info-name" class="mt-2 text-sm font-semibold text-slate-900 break-words">—</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Type</p>
                    <p id="dh-file-info-type" class="mt-2 text-sm font-semibold text-slate-900">—</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Size</p>
                    <p id="dh-file-info-size" class="mt-2 text-sm font-semibold text-slate-900">—</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Modified</p>
                    <p id="dh-file-info-date" class="mt-2 text-sm font-semibold text-slate-900">—</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:col-span-2">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Description</p>
                    <p id="dh-file-info-description" class="mt-2 text-[12px] leading-6 text-slate-700 break-words">—</p>
                </div>
            </div>

            <div class="mt-5 flex items-center justify-end">
                <button type="button" data-dh-file-info-close class="rounded-full px-5 py-2 border border-slate-300 text-slate-700 bg-white hover:bg-slate-50">Close</button>
            </div>
        </div>
    </div>

    {{-- bottom upload queue --}}
    @if($canUpload)
    <div id="dh-folder-upload-queue"
        class="dh-upload-queue hidden">
        <div class="rounded-3xl border border-sky-200 bg-white shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Uploading files</h3>
                    <p id="dh-folder-queue-sub" class="text-[12px] text-slate-500">Preparing upload...</p>

                    <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-200">
                        <div id="dh-folder-queue-bar"
                            class="h-full w-0 rounded-full bg-sky-500 transition-all duration-200"></div>
                    </div>
                </div>

                <button type="button"
                    id="dh-folder-queue-close"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-400 hover:bg-slate-50 hover:text-slate-600">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="px-5 py-4">
                <div id="dh-folder-selected-count" class="text-[12px] font-medium text-slate-700">
                    Selected: 0 files
                </div>

                <div id="dh-folder-selected-list"
                    class="mt-3 max-h-40 overflow-auto rounded-2xl border border-slate-200 bg-slate-50 p-3 text-[11px] text-slate-700 hidden"></div>

                <div id="dh-folder-selected-empty" class="mt-3 text-[11px] text-slate-400">
                    No file selected yet.
                </div>
            </div>
        </div>
    </div>
    @endif
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

{{-- File Viewer modal --}}
<div id="dh-viewer-modal"
    class="text-dec-none fixed inset-0 z-40 hidden items-center justify-center bg-slate-950/75 backdrop-blur-md px-4 py-6"
    aria-hidden="true">

    <div class="w-full max-w-6xl h-[88vh] rounded-3xl bg-white text-slate-900 shadow-2xl border border-slate-200 overflow-hidden flex flex-col">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 bg-white">
            <div class="min-w-0">
                <h3 id="dh-viewer-title" class="text-sm md:text-base font-semibold truncate">
                    File Preview
                </h3>
            </div>

            <button type="button"
                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition"
                data-dh-view-close>
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        {{-- Preview area --}}
        <div class="flex-1 min-h-0 p-4 md:p-5 bg-slate-100">
            <div id="dh-viewer-content"
                class="h-full min-h-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-5 py-4 border-t border-slate-200 bg-white flex items-center justify-end gap-2">
            <a id="dh-download-current"
                href="#"
                target="_blank"
                rel="noreferrer"
                class="inline-flex items-center gap-2 rounded-full bg-sky-500 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-600 transition">
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

{{-- Edit Description modal --}}
<div id="dh-description-modal" class="fixed inset-0 z-[170] hidden items-center justify-center bg-slate-950/55 px-4">
    <div class="w-full max-w-lg rounded-3xl bg-white shadow-2xl border border-slate-200 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Edit Description</h3>
                <p class="text-[11px] text-slate-500 mt-1">Update the selected file description.</p>
            </div>

            <button type="button"
                id="dh-description-close"
                class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-700">
                <i class="fa-solid fa-xmark text-[12px]"></i>
            </button>
        </div>

        <div class="px-5 py-5 space-y-3">
            <div>
                <label for="dh-description-input" class="block text-[11px] font-semibold text-slate-700 mb-2">
                    Description
                </label>
                <textarea
                    id="dh-description-input"
                    rows="5"
                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-[13px] text-slate-700 outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100"
                    placeholder="Enter file description (optional)"></textarea>
            </div>

            <p id="dh-description-file-name" class="text-[11px] text-slate-500 break-words"></p>
        </div>

        <div class="flex items-center justify-end gap-2 px-5 py-4 border-t border-slate-200 bg-slate-50">
            <button type="button"
                id="dh-description-cancel"
                class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-4 py-2 text-[11px] font-semibold text-slate-700 hover:bg-slate-50">
                Cancel
            </button>

            <button type="button"
                id="dh-description-save"
                class="inline-flex items-center justify-center rounded-full bg-sky-500 px-4 py-2 text-[11px] font-semibold text-white hover:bg-sky-600">
                Save
            </button>
        </div>
    </div>
</div>

@if($isAdmin)
<div id="dh-folder-create-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm px-4">
    <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl border border-slate-200 px-6 py-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Create Folder</h2>
                <p class="mt-1 text-[11px] text-slate-500">Add a new folder inside this folder.</p>
            </div>
            <button type="button"
                data-dh-folder-create-close
                class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-400 hover:bg-slate-50">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('dh.folders.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $folder->id }}">

            <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-2">Folder name</label>
                <input type="text"
                    name="folder_name"
                    required
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-[12px] focus:border-sky-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-100"
                    placeholder="Untitled folder">
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-2">Remarks (optional)</label>
                <textarea
                    name="remarks"
                    rows="3"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-[12px] focus:border-sky-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-100"
                    placeholder="Add notes"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2">
                <button type="button"
                    data-dh-folder-create-close
                    class="rounded-full px-4 py-2 border border-slate-300 text-slate-600 bg-white hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit"
                    class="rounded-full px-5 py-2 bg-slate-900 text-white font-semibold shadow-sm hover:bg-slate-800">
                    Create
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<form id="dh-folder-rename-form" method="POST" class="hidden">
    @csrf
    @method('PATCH')
    <input type="hidden" name="folder_name" id="dh-folder-rename-name">
</form>

<form id="dh-folder-trash-form" method="POST" class="hidden">
    @csrf
    @method('PATCH')
</form>

<div id="dh-folder-rename-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm px-4">
    <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl border border-slate-200 px-6 py-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Rename Folder</h2>
                <p class="mt-1 text-[11px] text-slate-500">Update the folder name.</p>
            </div>
            <button type="button" data-dh-folder-rename-close class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-400 hover:bg-slate-50">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <input type="text"
            id="dh-folder-rename-input"
            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-[12px] focus:border-sky-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-100">

        <div class="mt-5 flex items-center justify-end gap-2">
            <button type="button" data-dh-folder-rename-close class="rounded-full px-4 py-2 border border-slate-300 text-slate-600 bg-white hover:bg-slate-50">Cancel</button>
            <button type="button" id="dh-folder-rename-confirm" class="rounded-full px-5 py-2 bg-slate-900 text-white font-semibold shadow-sm hover:bg-slate-800">Save</button>
        </div>
    </div>
</div>

<div id="dh-folder-trash-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm px-4">
    <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl border border-slate-200 px-6 py-6">
        <div class="flex items-start justify-between mb-3">
            <h2 class="text-sm font-semibold text-slate-900">Move folder to trash?</h2>
            <button type="button" data-dh-folder-trash-close class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-400 hover:bg-slate-50">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <p id="dh-folder-trash-text" class="text-[11px] text-slate-600 leading-relaxed"></p>

        <div class="mt-4 flex items-center justify-end gap-2">
            <button type="button" data-dh-folder-trash-close class="rounded-full px-4 py-2 border border-slate-300 text-slate-600 bg-white hover:bg-slate-50">Cancel</button>
            <button type="button" id="dh-folder-trash-confirm" class="rounded-full px-5 py-2 bg-rose-500 text-white font-semibold shadow-sm hover:bg-rose-600">Move to trash</button>
        </div>
    </div>
</div>

<div id="dh-folder-info-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm px-4">
    <div class="w-full max-w-lg rounded-3xl bg-white shadow-2xl border border-slate-200 px-6 py-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Folder Information</h2>
                <p class="mt-1 text-[11px] text-slate-500">Details about this folder.</p>
            </div>
            <button type="button" data-dh-folder-info-close class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-400 hover:bg-slate-50">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[10px] uppercase tracking-wide text-slate-400">Name</p>
                <p id="dh-folder-info-name" class="mt-2 text-sm font-semibold text-slate-900 break-words">—</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[10px] uppercase tracking-wide text-slate-400">Created</p>
                <p id="dh-folder-info-date" class="mt-2 text-sm font-semibold text-slate-900">—</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:col-span-2">
                <p class="text-[10px] uppercase tracking-wide text-slate-400">Remarks</p>
                <p id="dh-folder-info-remarks" class="mt-2 text-[12px] leading-6 text-slate-700 break-words">—</p>
            </div>
        </div>

        <div class="mt-5 flex items-center justify-end">
            <button type="button" data-dh-folder-info-close class="rounded-full px-5 py-2 border border-slate-300 text-slate-700 bg-white hover:bg-slate-50">Close</button>
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
<script src="{{ asset('js/dh-show.js') }}"></script>
@endpush