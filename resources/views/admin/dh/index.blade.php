@extends('layouts.gts_app')

@section('content')
<section class="text-dec-none p-font py-8 px-4">
    @php
    $user = auth()->user();
    $isAdmin = $user && method_exists($user, 'isAdmin') && $user->isAdmin();
    $isConsultant = $user && method_exists($user, 'isConsultant') && $user->isConsultant();
    $folders = $folders ?? collect();

    $totalFolders = $folders->count();
    $totalSubfolders = $folders->sum(fn($folder) => $folder->children ? $folder->children->count() : 0);
    $totalFiles = $folders->sum(function ($folder) {
    $records = $folder->records ?? collect();
    return $records->sum(function ($record) {
    return $record->attachments ? $record->attachments->count() : 0;
    });
    });
    @endphp

    <style>
        .dh-drive-shell {
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
            gap: 1.25rem;
        }

        .dh-glass {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 10px 35px rgba(15, 23, 42, 0.06);
        }

        .dh-sidebar-link.active,
        .dh-sidebar-link:hover {
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            color: #fff;
            border-color: transparent;
        }

        .dh-folder-card.is-selected {
            border-color: #38bdf8 !important;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.12);
            background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 100%);
        }

        .dh-folder-card .dh-folder-menu {
            top: calc(100% + 8px);
            right: 0;
            left: auto;
        }

        .dh-folder-card {
            position: relative;
            z-index: 1;
            overflow: visible;
        }

        .dh-folder-card.menu-open {
            z-index: 120;
        }

        .dh-folder-menu {
            display: none;
        }

        .dh-folder-menu.is-open {
            display: block;
        }

        .dh-folder-line {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            white-space: normal;
            word-break: break-word;
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

        .dh-folder-menu-item {
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

        .dh-folder-menu-item:hover {
            background: #f8fafc !important;
            color: #0f172a;
        }

        .dh-folder-menu-item.danger {
            color: #e11d48;
        }

        .dh-folder-menu-item.danger:hover {
            background: #fff1f2 !important;
            color: #be123c;
        }

        #dh-list-wrap {
            overflow: visible;
        }

        #dh-list-wrap table {
            overflow: visible;
        }

        #dh-list-wrap tbody,
        #dh-list-wrap tr,
        #dh-list-wrap td {
            overflow: visible;
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
        }

        .dh-list-action-anchor.menu-open-anchor {
            z-index: 240;
        }

        .dh-list-action-cell.menu-open-cell {
            z-index: 230;
        }

        .dh-folder-row .dh-folder-menu {
            top: calc(100% + 8px);
            right: 0;
            left: auto;
        }

        .dh-folder-row:last-child .dh-folder-menu,
        .dh-folder-row:nth-last-child(2) .dh-folder-menu {
            top: auto;
            bottom: calc(100% + 8px);
        }

        .dh-folder-menu {
            z-index: 260;
        }

        @media (max-width: 1024px) {
            .dh-drive-shell {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .dh-drive-topbar {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>

    <div id="dh-root"
        data-existing-names='@json($existingNames)'
        class="max-w-7xl mx-auto font-plus space-y-5">

        {{-- top breadcrumb --}}
        <div class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
            <a href="{{ route('admin.dashboard') }}"
                class="inline-flex items-center gap-2 rounded-full px-1 py-1 font-semibold text-slate-700 no-underline hover:text-slate-900 leading-none transition">
                <i class="fa-solid fa-arrow-left text-[11px] leading-none translate-y-[1px]"></i>
                <span class="leading-none">Back to dashboard</span>
            </a>

            <span class="text-slate-300">/</span>

            <span class="font-semibold text-slate-700 leading-none">Document Hub</span>
        </div>

        <div class="dh-drive-shell">
            {{-- Sidebar --}}
            <aside class="dh-glass rounded-3xl p-4 md:p-5 h-fit">
                <div class="flex items-center gap-3 px-2 pb-4 border-b border-slate-200/80">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-100 text-sky-600">
                        <i class="fa-solid fa-folder-tree text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-base font-semibold text-slate-900">Document Hub</h1>
                        <p class="text-[11px] text-slate-500">Drive-style folder space</p>
                    </div>
                </div>

                <div class="mt-4 space-y-2">
                    @if($isAdmin)
                    <button type="button"
                        id="dh-open-create"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-sky-500 px-4 py-3 text-sm font-semibold text-white shadow-md hover:bg-sky-600 cursor-pointer">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>New Folder</span>
                    </button>
                    @endif

                    <div class="space-y-2 pt-2">
                        <div class="dh-sidebar-link active flex items-center justify-between rounded-2xl border border-slate-200 px-3 py-3 text-[12px] font-semibold transition-all">
                            <span class="inline-flex items-center gap-2">
                                <i class="fa-regular fa-folder-open text-[12px]"></i>
                                My Folders
                            </span>
                            <span class="rounded-full bg-white/20 px-2 py-0.5 text-[10px]">{{ $totalFolders }}</span>
                        </div>

                        <div class="dh-sidebar-link flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-3 py-3 text-[12px] font-semibold text-slate-700 transition-all">
                            <span class="inline-flex items-center gap-2">
                                <i class="fa-regular fa-file-lines text-[12px]"></i>
                                Files
                            </span>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] text-slate-600">{{ $totalFiles }}</span>
                        </div>

                        @if($isAdmin)
                        <a href="{{ route('dh.trash.index') }}"
                            class="dh-sidebar-link flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-3 py-3 text-[12px] font-semibold text-slate-700 transition-all">
                            <span class="inline-flex items-center gap-2">
                                <i class="fa-regular fa-trash-can text-[12px]"></i>
                                Trash
                            </span>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] text-slate-600">View</span>
                        </a>
                        @endif
                    </div>

                    <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <h3 class="text-[12px] font-semibold text-slate-800">Access</h3>
                        <p class="mt-2 text-[11px] leading-5 text-slate-500">
                            @if($isConsultant)
                            You can browse folders and upload files inside folder pages. Only admins can create, rename, move to trash, or delete folders.
                            @else
                            You can create folders, manage structure, and keep your document space organised.
                            @endif
                        </p>
                    </div>
                </div>
            </aside>

            {{-- Main area --}}
            <div class="space-y-4">
                {{-- top toolbar --}}
                <div class="dh-glass dh-drive-topbar rounded-3xl px-4 py-4 md:px-5 md:py-5">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl md:text-2xl font-semibold text-slate-900">My Drive</h2>
                            <p class="mt-1 text-xs text-slate-500">
                                Browse root folders, open contents, jump into subfolders, and manage the hub layout.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-2 text-[11px] font-medium text-slate-600">
                                <i class="fa-regular fa-folder"></i>
                                <span>{{ $totalFolders }} folder{{ $totalFolders === 1 ? '' : 's' }}</span>
                            </div>

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
                        </div>
                    </div>

                    <div class="mt-4 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
                        <div class="relative w-full lg:max-w-md">
                            <input
                                id="dh-search"
                                type="text"
                                placeholder="Search folders by name or ID..."
                                class="w-full rounded-2xl border border-slate-200 bg-white px-11 py-3 text-[12px] text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100">
                            <span class="absolute inset-y-0 left-4 flex items-center text-slate-400 text-xs">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                            <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2">
                                <i class="fa-solid fa-circle text-[8px] text-emerald-500"></i>
                                Root folder space
                            </span>
                        </div>
                    </div>
                </div>

                @if($folders->count())
                {{-- folder grid --}}
                <div id="dh-grid-wrap" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($folders as $folder)
                    @php
                    $records = $folder->records ?? collect();

                    $fileCount = $records->sum(function ($record) {
                    return $record->attachments ? $record->attachments->count() : 0;
                    });

                    $subfolderCount = $folder->children
                    ? $folder->children->where('is_trashed', false)->count()
                    : 0;
                    @endphp

                    <div class="dh-folder-card dh-glass group relative rounded-3xl p-4 transition-all cursor-pointer"
                        data-id="{{ $folder->id }}"
                        data-name="{{ $folder->folder_name }}"
                        data-files="{{ $fileCount }}"
                        data-subfolders="{{ $subfolderCount }}"
                        data-delete-url="{{ route('dh.folders.destroy', $folder) }}"
                        data-open-url="{{ route('dh.show', $folder) }}"
                        data-subfolders-url="{{ route('dh.subfolders.index', $folder) }}"
                        data-download-url="{{ route('dh.folder.downloadAll', $folder) }}"
                        data-month="{{ $folder->month_label ?? '' }}"
                        data-remarks="{{ $folder->remarks ?? '' }}"
                        data-created="{{ optional($folder->created_at)->format('d M Y, h:i A') ?: '—' }}"
                        data-rename-url="{{ route('dh.folders.rename', $folder) }}"
                        data-trash-url="{{ route('dh.folders.trash', $folder) }}"
                        data-description-url="{{ route('dh.folders.description', $folder) }}">

                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-100 to-cyan-50 text-sky-600 shadow-sm">
                                    <i class="fa-solid fa-folder text-lg"></i>
                                </div>

                                <div class="min-w-0">
                                    <h3 class="dh-folder-line text-sm font-semibold text-slate-900">
                                        {{ $folder->folder_name }}
                                    </h3>
                                    <!-- <p class="mt-1 text-[11px] text-slate-500">
                                        Folder ID: {{ $folder->id }}
                                    </p> -->
                                </div>
                            </div>

                            <div class="relative shrink-0">
                                <button type="button"
                                    class="dh-folder-more-btn inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-700"
                                    data-folder-menu-toggle
                                    aria-label="More options">
                                    <i class="fa-solid fa-ellipsis-vertical text-[12px]"></i>
                                </button>

                                <div class="dh-folder-menu absolute right-0 z-30 w-52 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl"
                                    data-folder-menu>
                                    <a href="{{ route('dh.show', $folder) }}"
                                        class="dh-folder-menu-item">
                                        <i class="fa-regular fa-folder-open w-4 text-slate-400"></i>
                                        Open folder
                                    </a>

                                    @if($fileCount > 0)
                                    <a href="{{ route('dh.folder.downloadAll', $folder) }}"
                                        class="dh-folder-menu-item">
                                        <i class="fa-solid fa-download w-4 text-slate-400"></i>
                                        Download all
                                    </a>
                                    @endif

                                    <button type="button"
                                        class="dh-folder-menu-item"
                                        data-folder-info-btn>
                                        <i class="fa-solid fa-info w-4 text-slate-400"></i>
                                        Folder information
                                    </button>

                                    @if($isAdmin)
                                    <div class="my-1 border-t border-slate-100"></div>

                                    <button type="button"
                                        class="dh-folder-menu-item"
                                        data-folder-description-btn>
                                        <i class="fa-regular fa-note-sticky w-4 text-slate-400"></i>
                                        Edit description
                                    </button>

                                    <button type="button"
                                        class="dh-folder-menu-item"
                                        data-folder-rename-btn>
                                        <i class="fa-regular fa-pen-to-square w-4 text-slate-400"></i>
                                        Rename
                                    </button>

                                    <button type="button"
                                        class="dh-folder-menu-item danger"
                                        data-folder-trash-btn>
                                        <i class="fa-regular fa-trash-can w-4"></i>
                                        Move to trash
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 rounded-2xl bg-slate-50/80 p-3">
                            <div class="flex items-center justify-between text-[11px] text-slate-500">
                                <span class="inline-flex items-center gap-2">
                                    <i class="fa-regular fa-calendar"></i>
                                    {{ $folder->month_label ?: 'No month set' }}
                                </span>
                                <span class="inline-flex items-center gap-2">
                                    <i class="fa-regular fa-clock"></i>
                                    {{ optional($folder->created_at)->format('d M Y') ?: '—' }}
                                </span>
                            </div>

                            <div class="mt-3 min-h-[40px]">
                                @if(!empty($folder->remarks))
                                <p class="text-[11px] leading-5 text-slate-500 break-words">
                                    {{ $folder->remarks }}
                                </p>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="rounded-2xl border border-slate-200 bg-white px-3 py-3">
                                <p class="text-[10px] uppercase tracking-wide text-slate-400">Subfolders</p>
                                <p class="mt-1 text-base font-semibold text-slate-900">{{ $subfolderCount }}</p>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white px-3 py-3">
                                <p class="text-[10px] uppercase tracking-wide text-slate-400">Files</p>
                                <p class="mt-1 text-base font-semibold text-slate-900">{{ $fileCount }}</p>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <a href="{{ route('dh.show', $folder) }}"
                                class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-[11px] font-semibold text-white shadow-sm hover:bg-slate-800"
                                onclick="event.stopPropagation();">
                                <i class="fa-regular fa-folder-open text-[10px]"></i>
                                Open
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div id="dh-list-wrap" class="hidden overflow-hidden rounded-2xl border border-slate-200 bg-white">
                    <table class="w-full text-[12px]">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-4 py-3 text-left">Folder</th>
                                <th class="px-4 py-3 text-left">Month</th>
                                <th class="px-4 py-3 text-left">Created</th>
                                <th class="px-4 py-3 text-left">Counts</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($folders as $folder)
                            @php
                            $records = $folder->records ?? collect();

                            $fileCount = $records->sum(function ($record) {
                            return $record->attachments ? $record->attachments->count() : 0;
                            });

                            $subfolderCount = $folder->children
                            ? $folder->children->where('is_trashed', false)->count()
                            : 0;
                            @endphp

                            <tr class="border-t border-slate-100 hover:bg-slate-50/70 cursor-pointer dh-folder-row"
                                data-id="{{ $folder->id }}"
                                data-name="{{ strtolower($folder->folder_name) }}"
                                data-open-url="{{ route('dh.show', $folder) }}"
                                data-delete-url="{{ route('dh.folders.destroy', $folder) }}"
                                data-rename-url="{{ route('dh.folders.rename', $folder) }}"
                                data-trash-url="{{ route('dh.folders.trash', $folder) }}"
                                data-description-url="{{ route('dh.folders.description', $folder) }}"
                                data-files="{{ $fileCount }}"
                                data-subfolders="{{ $subfolderCount }}"
                                data-month="{{ $folder->month_label ?: 'No month set' }}"
                                data-remarks="{{ $folder->remarks ?? '' }}"
                                data-created="{{ optional($folder->created_at)->format('d M Y, h:i A') ?: '—' }}">
                                <td class="px-4 py-3 align-top">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-sky-100 text-sky-600">
                                            <i class="fa-solid fa-folder text-sm"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-900 break-words">{{ $folder->folder_name }}</p>
                                            <!-- <p class="text-[11px] text-slate-500">Folder ID: {{ $folder->id }}</p> -->

                                            @if(!empty($folder->remarks))
                                            <p class="mt-2 text-[11px] text-slate-500 break-words">
                                                {{ $folder->remarks }}
                                            </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-slate-600">{{ $folder->month_label ?: 'No month set' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ optional($folder->created_at)->format('d M Y') ?: '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ $subfolderCount }} subfolder{{ $subfolderCount === 1 ? '' : 's' }} ·
                                    {{ $fileCount }} file{{ $fileCount === 1 ? '' : 's' }}
                                </td>

                                <td class="dh-list-action-cell px-4 py-3 text-right">
                                    <div class="dh-list-action-anchor">
                                        <button type="button"
                                            class="dh-folder-more-btn inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-700"
                                            data-folder-menu-toggle>
                                            <i class="fa-solid fa-ellipsis-vertical text-[11px]"></i>
                                        </button>

                                        <div class="dh-folder-menu absolute right-0 z-30 w-52 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl"
                                            data-folder-menu>
                                            <a href="{{ route('dh.show', $folder) }}"
                                                class="dh-folder-menu-item">
                                                <i class="fa-regular fa-folder-open w-4 text-slate-400"></i>
                                                Open folder
                                            </a>

                                            @if($fileCount > 0)
                                            <a href="{{ route('dh.folder.downloadAll', $folder) }}"
                                                class="dh-folder-menu-item">
                                                <i class="fa-solid fa-download w-4 text-slate-400"></i>
                                                Download all
                                            </a>
                                            @endif

                                            <button type="button"
                                                class="dh-folder-menu-item"
                                                data-folder-info-btn>
                                                <i class="fa-solid fa-info w-4 text-slate-400"></i>
                                                Folder information
                                            </button>

                                            @if($isAdmin)
                                            <div class="my-1 border-t border-slate-100"></div>

                                            <button type="button"
                                                class="dh-folder-menu-item"
                                                data-folder-description-btn>
                                                <i class="fa-regular fa-note-sticky w-4 text-slate-400"></i>
                                                Edit description
                                            </button>

                                            <button type="button"
                                                class="dh-folder-menu-item"
                                                data-folder-rename-btn>
                                                <i class="fa-regular fa-pen-to-square w-4 text-slate-400"></i>
                                                Rename
                                            </button>

                                            <button type="button"
                                                class="dh-folder-menu-item danger"
                                                data-folder-trash-btn>
                                                <i class="fa-regular fa-trash-can w-4"></i>
                                                Move to trash
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
                @else
                {{-- empty state --}}
                <div class="dh-glass rounded-3xl px-6 py-16 text-center">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-[28px] bg-sky-50 text-sky-500">
                        <i class="fa-solid fa-folder-plus text-3xl"></i>
                    </div>

                    <h3 class="mt-5 text-lg font-semibold text-slate-900">Your drive is empty</h3>
                    <p class="mt-2 text-sm text-slate-500 max-w-md mx-auto">
                        Create your first folder to start organising records, uploads, and nested document spaces.
                    </p>

                    @if($isAdmin)
                    <button type="button"
                        id="dh-open-create-empty"
                        class="mt-5 inline-flex items-center gap-2 rounded-2xl bg-sky-500 px-5 py-3 text-sm font-semibold text-white shadow-md hover:bg-sky-600 cursor-pointer">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Create Folder</span>
                    </button>
                    @else
                    <p class="mt-4 text-[12px] text-slate-400">Please contact an admin to create folders.</p>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Create Folder Modal --}}
    <div id="dh-create-modal"
        class="fixed inset-0 z-40 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm px-4">
        <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl border border-slate-200 px-6 py-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">New Folder</h2>
                    <p class="mt-1 text-[11px] text-slate-500">Give this folder a name and optional month.</p>
                </div>
                <button type="button"
                    data-dh-close
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-[11px] text-slate-400 hover:bg-slate-50 hover:text-slate-600">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="dh-create-form" method="POST" action="{{ route('dh.folders.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-[11px] font-medium text-slate-700 mb-1">Folder name *</label>
                    <input type="text" name="folder_name" required
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-[12px] focus:border-sky-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-100">
                </div>

                <div>
                    <label class="block text-[11px] font-medium text-slate-700 mb-1">Month</label>
                    <input type="text" name="month_label" placeholder="e.g. Nov 2025"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-[12px] focus:border-sky-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-100">
                </div>

                <div>
                    <label class="block text-[11px] font-medium text-slate-700 mb-1">Remarks</label>
                    <textarea name="remarks" rows="3"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-[12px] resize-y focus:border-sky-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-100"></textarea>
                </div>

                <div class="mt-4 flex items-center justify-end gap-2 text-[11px]">
                    <button type="button"
                        data-dh-close
                        class="rounded-full px-4 py-2 border border-slate-300 text-slate-600 bg-white hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit"
                        class="rounded-full px-5 py-2 bg-sky-500 text-white font-semibold shadow-sm hover:bg-sky-600">
                        Create Folder
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Duplicate Modal --}}
    <div id="dh-duplicate-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm px-4">
        <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl border border-slate-200 px-6 py-6">
            <div class="flex items-start justify-between mb-3">
                <h2 class="text-sm font-semibold text-slate-900">Folder name already used</h2>
                <button type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-[11px] text-slate-400 hover:bg-slate-50 hover:text-slate-600"
                    data-dh-dup-close>
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <p id="dh-dup-message" class="text-[11px] text-slate-600 leading-relaxed"></p>

            <div class="mt-4 flex items-center justify-end gap-2 text-[11px]">
                <button type="button"
                    class="rounded-full px-4 py-2 border border-slate-300 text-slate-600 bg-white hover:bg-slate-50"
                    data-dh-dup-close>
                    Cancel
                </button>
                <button type="button"
                    class="rounded-full px-5 py-2 bg-rose-500 text-white font-semibold shadow-sm hover:bg-rose-600"
                    id="dh-dup-confirm">
                    Create anyway
                </button>
            </div>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div id="dh-folder-delete-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm px-4">
        <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl border border-slate-200 px-6 py-6">
            <div class="flex items-start justify-between mb-3">
                <h2 class="text-sm font-semibold text-slate-900">Delete folder?</h2>
                <button type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-[11px] text-slate-400 hover:bg-slate-50 hover:text-slate-600"
                    data-dh-folder-del-close>
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <p id="dh-folder-delete-text" class="text-[11px] text-slate-600 leading-relaxed"></p>

            <div class="mt-4 flex items-center justify-end gap-2 text-[11px]">
                <button type="button"
                    class="rounded-full px-4 py-2 border border-slate-300 text-slate-600 bg-white hover:bg-slate-50"
                    data-dh-folder-del-close>
                    Cancel
                </button>
                <button type="button"
                    class="rounded-full px-5 py-2 bg-rose-500 text-white font-semibold shadow-sm hover:bg-rose-600"
                    id="dh-folder-del-confirm">
                    Delete folder
                </button>
            </div>
        </div>
    </div>

    {{-- Folder information Modal --}}
    <div id="dh-folder-info-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm px-4">
        <div class="w-full max-w-lg rounded-3xl bg-white shadow-2xl border border-slate-200 px-6 py-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Folder Information</h2>
                    <p class="mt-1 text-[11px] text-slate-500">Quick details about the selected folder.</p>
                </div>
                <button type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-[11px] text-slate-400 hover:bg-slate-50 hover:text-slate-600"
                    data-dh-folder-info-close>
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Folder name</p>
                    <p id="dh-info-name" class="mt-2 text-sm font-semibold text-slate-900 break-words">—</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Folder ID</p>
                    <p id="dh-info-id" class="mt-2 text-sm font-semibold text-slate-900">—</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Files</p>
                    <p id="dh-info-files" class="mt-2 text-sm font-semibold text-slate-900">—</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Subfolders</p>
                    <p id="dh-info-subfolders" class="mt-2 text-sm font-semibold text-slate-900">—</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Month</p>
                    <p id="dh-info-month" class="mt-2 text-sm font-semibold text-slate-900">—</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Created</p>
                    <p id="dh-info-created" class="mt-2 text-sm font-semibold text-slate-900">—</p>
                </div>
            </div>

            <div class="mt-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[10px] uppercase tracking-wide text-slate-400">Remarks</p>
                <p id="dh-info-remarks" class="mt-2 text-[12px] leading-6 text-slate-700 break-words">—</p>
            </div>

            <div class="mt-5 flex items-center justify-end">
                <button type="button"
                    class="rounded-full px-5 py-2 border border-slate-300 text-slate-700 bg-white hover:bg-slate-50"
                    data-dh-folder-info-close>
                    Close
                </button>
            </div>
        </div>
    </div>
</section>

<div id="dh-rename-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm px-4">
    <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl border border-slate-200 px-6 py-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Rename Folder</h2>
                <p class="mt-1 text-[11px] text-slate-500">Update the folder name safely.</p>
            </div>
            <button type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-400 hover:bg-slate-50"
                data-dh-rename-close>
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div>
            <label class="block text-[11px] font-medium text-slate-700 mb-1">Folder name</label>
            <input type="text"
                id="dh-rename-input"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-[12px] focus:border-sky-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-100">
        </div>

        <div class="mt-5 flex items-center justify-end gap-2">
            <button type="button"
                class="rounded-full px-4 py-2 border border-slate-300 text-slate-600 bg-white hover:bg-slate-50"
                data-dh-rename-close>
                Cancel
            </button>
            <button type="button"
                id="dh-rename-confirm"
                class="rounded-full px-5 py-2 bg-sky-500 text-white font-semibold shadow-sm hover:bg-sky-600">
                Save name
            </button>
        </div>
    </div>
</div>

<div id="dh-description-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm px-4">
    <div class="w-full max-w-lg rounded-3xl bg-white shadow-2xl border border-slate-200 px-6 py-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Edit Folder Details</h2>
                <p class="mt-1 text-[11px] text-slate-500">Update month and description for this folder.</p>
            </div>
            <button type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-400 hover:bg-slate-50"
                data-dh-description-close>
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-[11px] font-medium text-slate-700 mb-1">Month</label>
                <input type="text"
                    id="dh-description-month-input"
                    placeholder="e.g. Nov 2025"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-[12px] focus:border-sky-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-100">
            </div>

            <div>
                <label class="block text-[11px] font-medium text-slate-700 mb-1">Description</label>
                <textarea
                    id="dh-description-input"
                    rows="5"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-[12px] resize-y focus:border-sky-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-100"
                    placeholder="Enter folder description..."></textarea>
            </div>
        </div>

        <div class="mt-5 flex items-center justify-end gap-2">
            <button type="button"
                class="rounded-full px-4 py-2 border border-slate-300 text-slate-600 bg-white hover:bg-slate-50"
                data-dh-description-close>
                Cancel
            </button>
            <button type="button"
                id="dh-description-confirm"
                class="rounded-full px-5 py-2 bg-sky-500 text-white font-semibold shadow-sm hover:bg-sky-600">
                Save details
            </button>
        </div>
    </div>
</div>

<div id="dh-trash-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm px-4">
    <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl border border-slate-200 px-6 py-6">
        <div class="flex items-start justify-between mb-3">
            <h2 class="text-sm font-semibold text-slate-900">Move folder to trash?</h2>
            <button type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-400 hover:bg-slate-50"
                data-dh-trash-close>
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <p id="dh-trash-text" class="text-[11px] text-slate-600 leading-relaxed"></p>

        <div class="mt-4 flex items-center justify-end gap-2">
            <button type="button"
                class="rounded-full px-4 py-2 border border-slate-300 text-slate-600 bg-white hover:bg-slate-50"
                data-dh-trash-close>
                Cancel
            </button>
            <button type="button"
                id="dh-trash-confirm"
                class="rounded-full px-5 py-2 bg-rose-500 text-white font-semibold shadow-sm hover:bg-rose-600">
                Move to trash
            </button>
        </div>
    </div>
</div>

<form id="dh-delete-form" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<form id="dh-rename-form" method="POST" class="hidden">
    @csrf
    @method('PATCH')
    <input type="hidden" name="folder_name" id="dh-rename-folder-name">
</form>

<form id="dh-description-form" method="POST" class="hidden">
    @csrf
    @method('PATCH')
    <input type="hidden" name="month_label" id="dh-description-month-hidden">
    <input type="hidden" name="remarks" id="dh-description-hidden">
</form>

<form id="dh-trash-form" method="POST" class="hidden">
    @csrf
    @method('PATCH')
</form>

@push('scripts')
<script src="{{ asset('js/dh-index.js') }}"></script>
@endpush
@endsection