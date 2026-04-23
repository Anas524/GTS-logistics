@extends('layouts.gts_app')

@section('content')
@php
$user = auth()->user();
$isAdmin = $user && method_exists($user, 'isAdmin') && $user->isAdmin();
$isConsultant = $user && method_exists($user, 'isConsultant') && $user->isConsultant();

$subfolders = $subfolders ?? collect();

$totalSubfolders = $subfolders->count();
$totalFiles = $subfolders->sum(function ($sub) {
$records = $sub->records ?? collect();
return $records->sum(function ($record) {
return $record->attachments ? $record->attachments->count() : 0;
});
});
@endphp

<section id="dh-sub-root"
    class="text-dec-none p-font py-8 px-4"
    data-existing-names='@json($existingSubNames)'
    data-is-admin="{{ $isAdmin ? 1 : 0 }}">

    <style>
        .dh-sub-shell {
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

        .dh-sub-sidebar-link.active,
        .dh-sub-sidebar-link:hover {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: #fff;
            border-color: transparent;
        }

        .dh-sub-card.is-selected {
            border-color: #38bdf8 !important;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.12);
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }

        .dh-sub-menu {
            display: none;
        }

        .dh-sub-menu.is-open {
            display: block;
        }

        .dh-sub-line {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        @media (max-width: 1024px) {
            .dh-sub-shell {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .dh-sub-topbar {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>

    <div class="max-w-7xl mx-auto font-plus space-y-5">
        {{-- Breadcrumb --}}
        <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
            <a href="{{ route('admin.dashboard') }}"
                class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                <i class="fa-solid fa-arrow-left text-[10px]"></i>
                <span>Back to dashboard</span>
            </a>
            <span>/</span>
            <a href="{{ route('dh.index') }}" class="font-semibold text-slate-600 hover:text-slate-900">Document Hub</a>
            <span>/</span>
            <span class="font-semibold text-slate-700">{{ $folder->folder_name }}</span>
        </div>

        <div class="dh-sub-shell">
            {{-- Sidebar --}}
            <aside class="dh-glass rounded-3xl p-4 md:p-5 h-fit">
                <div class="flex items-center gap-3 px-2 pb-4 border-b border-slate-200/80">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                        <i class="fa-solid fa-folder-tree text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-base font-semibold text-slate-900 dh-sub-line">{{ $folder->folder_name }}</h1>
                        <p class="text-[11px] text-slate-500">Subfolders view</p>
                    </div>
                </div>

                <div class="mt-4 space-y-2">
                    @if($isAdmin)
                    <button type="button"
                        id="dh-open-subfolder"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-md hover:bg-slate-800 cursor-pointer">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>New Subfolder</span>
                    </button>
                    @endif

                    <div class="space-y-2 pt-2">
                        <div class="dh-sub-sidebar-link active flex items-center justify-between rounded-2xl border border-slate-200 px-3 py-3 text-[12px] font-semibold transition-all">
                            <span class="inline-flex items-center gap-2">
                                <i class="fa-solid fa-folder-tree text-[12px]"></i>
                                Subfolders
                            </span>
                            <span class="rounded-full bg-white/20 px-2 py-0.5 text-[10px]">{{ $totalSubfolders }}</span>
                        </div>

                        <div class="dh-sub-sidebar-link flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-3 py-3 text-[12px] font-semibold text-slate-700 transition-all">
                            <span class="inline-flex items-center gap-2">
                                <i class="fa-regular fa-file-lines text-[12px]"></i>
                                Files
                            </span>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] text-slate-600">{{ $totalFiles }}</span>
                        </div>

                        @if($isAdmin)
                        <a href="{{ route('dh.trash.index') }}"
                            class="dh-sub-sidebar-link flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-3 py-3 text-[12px] font-semibold text-slate-700 transition-all">
                            <span class="inline-flex items-center gap-2">
                                <i class="fa-regular fa-trash-can text-[12px]"></i>
                                Trash
                            </span>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] text-slate-600">View</span>
                        </a>
                        @endif

                        <a href="{{ route('dh.show', $folder) }}"
                            class="dh-sub-sidebar-link flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-3 py-3 text-[12px] font-semibold text-slate-700 transition-all">
                            <span class="inline-flex items-center gap-2">
                                <i class="fa-regular fa-folder-open text-[12px]"></i>
                                Open Parent Folder
                            </span>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] text-slate-600">Open</span>
                        </a>
                    </div>

                    <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <h3 class="text-[12px] font-semibold text-slate-800">Parent Folder</h3>
                        <p class="mt-2 text-[11px] leading-5 text-slate-500">
                            {{ $folder->month_label ?: 'No month set' }}
                            @if($folder->remarks)
                            · {{ $folder->remarks }}
                            @endif
                        </p>
                    </div>
                </div>
            </aside>

            {{-- Main area --}}
            <div class="space-y-4">
                <div class="dh-glass dh-sub-topbar rounded-3xl px-4 py-4 md:px-5 md:py-5">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl md:text-2xl font-semibold text-slate-900">
                                Subfolders – {{ $folder->folder_name }}
                            </h2>
                            <p class="mt-1 text-xs text-slate-500">
                                Browse the nested structure inside this parent folder and manage each subfolder cleanly.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-2 text-[11px] font-medium text-slate-600">
                                <i class="fa-regular fa-folder"></i>
                                <span>{{ $totalSubfolders }} subfolder{{ $totalSubfolders === 1 ? '' : 's' }}</span>
                            </div>

                            @if($isAdmin)
                            <button
                                type="button"
                                id="dh-sub-delete-btn"
                                class="inline-flex items-center gap-2 rounded-full bg-rose-500 px-4 py-2 text-[11px] font-semibold text-white shadow-md hover:bg-rose-600 disabled:opacity-40 disabled:cursor-not-allowed">
                                <i class="fa-regular fa-trash-can text-[11px]"></i>
                                <span>Delete selected subfolder</span>
                            </button>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
                        <div class="relative w-full lg:max-w-md">
                            <input
                                id="dh-sub-search"
                                type="text"
                                placeholder="Search subfolders by name or ID..."
                                class="w-full rounded-2xl border border-slate-200 bg-white px-11 py-3 text-[12px] text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100">
                            <span class="absolute inset-y-0 left-4 flex items-center text-slate-400 text-xs">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                            <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2">
                                <i class="fa-solid fa-circle text-[8px] text-amber-500"></i>
                                Nested folder area
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2">
                                <i class="fa-regular fa-hand-pointer"></i>
                                Click card to select
                            </span>
                        </div>
                    </div>
                </div>

                @if($subfolders->count())
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($subfolders as $sub)
                    @php
                        $subRecords = $sub->records ?? collect();
                        $subFileCount = $subRecords->sum(function ($record) {
                        return $record->attachments ? $record->attachments->count() : 0;
                        });
                        $subChildCount = $sub->children
                        ? $sub->children->where('is_trashed', false)->count()
                        : 0;
                    @endphp

                    <div class="dh-sub-card dh-glass group relative rounded-3xl p-4 transition-all cursor-pointer"
                        data-id="{{ $sub->id }}"
                        data-name="{{ $sub->folder_name }}"
                        data-files="{{ $subFileCount }}"
                        data-subfolders="{{ $subChildCount }}"
                        data-delete-url="{{ route('dh.folders.destroy', $sub) }}"
                        data-rename-url="{{ route('dh.folders.rename', $sub) }}"
                        data-trash-url="{{ route('dh.folders.trash', $sub) }}"
                        data-open-url="{{ route('dh.show', $sub) }}"
                        data-month="{{ $sub->month_label ?: 'No month set' }}"
                        data-remarks="{{ $sub->remarks ?: 'No remarks' }}"
                        data-created="{{ optional($sub->created_at)->format('d M Y, h:i A') ?: '—' }}">

                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-100 to-yellow-50 text-amber-600 shadow-sm">
                                    <i class="fa-solid fa-folder text-lg"></i>
                                </div>

                                <div class="min-w-0">
                                    <h3 class="dh-sub-line text-sm font-semibold text-slate-900">
                                        {{ $sub->folder_name }}
                                    </h3>
                                    <p class="mt-1 text-[11px] text-slate-500">
                                        Folder ID: {{ $sub->id }}
                                    </p>
                                </div>
                            </div>

                            <div class="relative shrink-0">
                                <button type="button"
                                    class="dh-sub-more-btn inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-700"
                                    data-sub-menu-toggle
                                    aria-label="More options">
                                    <i class="fa-solid fa-ellipsis-vertical text-[12px]"></i>
                                </button>

                                <div class="dh-sub-menu absolute right-0 top-12 z-30 w-52 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl"
                                    data-sub-menu>
                                    <a href="{{ route('dh.show', $sub) }}"
                                        class="flex items-center gap-3 rounded-xl px-3 py-2 text-[12px] font-medium text-slate-700 hover:bg-slate-50">
                                        <i class="fa-regular fa-folder-open w-4 text-slate-400"></i>
                                        Open subfolder
                                    </a>

                                    <button type="button"
                                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-left text-[12px] font-medium text-slate-700 hover:bg-slate-50"
                                        data-sub-info-btn>
                                        <i class="fa-regular fa-circle-info w-4 text-slate-400"></i>
                                        Folder info
                                    </button>

                                    <div class="my-1 border-t border-slate-100"></div>

                                    <button type="button"
                                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-left text-[12px] font-medium text-slate-700 hover:bg-slate-50"
                                        data-sub-rename-btn>
                                        <i class="fa-regular fa-pen-to-square w-4 text-slate-400"></i>
                                        Rename
                                    </button>

                                    <button type="button"
                                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-left text-[12px] font-medium text-rose-600 hover:bg-rose-50"
                                        data-sub-trash-btn>
                                        <i class="fa-regular fa-trash-can w-4"></i>
                                        Move to trash
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 rounded-2xl bg-slate-50/80 p-3">
                            <div class="flex items-center justify-between text-[11px] text-slate-500">
                                <span class="inline-flex items-center gap-2">
                                    <i class="fa-regular fa-calendar"></i>
                                    {{ $sub->month_label ?: 'No month set' }}
                                </span>
                                <span class="inline-flex items-center gap-2">
                                    <i class="fa-regular fa-clock"></i>
                                    {{ optional($sub->created_at)->format('d M Y') ?: '—' }}
                                </span>
                            </div>

                            <p class="mt-3 text-[11px] leading-5 text-slate-500 min-h-[40px]">
                                {{ $sub->remarks ?: 'No remarks added for this subfolder yet.' }}
                            </p>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="rounded-2xl border border-slate-200 bg-white px-3 py-3">
                                <p class="text-[10px] uppercase tracking-wide text-slate-400">Subfolders</p>
                                <p class="mt-1 text-base font-semibold text-slate-900">{{ $subChildCount }}</p>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white px-3 py-3">
                                <p class="text-[10px] uppercase tracking-wide text-slate-400">Files</p>
                                <p class="mt-1 text-base font-semibold text-slate-900">{{ $subFileCount }}</p>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <a href="{{ route('dh.show', $sub) }}"
                                class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-[11px] font-semibold text-white shadow-sm hover:bg-slate-800"
                                onclick="event.stopPropagation();">
                                <i class="fa-regular fa-folder-open text-[10px]"></i>
                                Open
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="dh-glass rounded-3xl px-6 py-16 text-center">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-[28px] bg-amber-50 text-amber-500">
                        <i class="fa-solid fa-folder-plus text-3xl"></i>
                    </div>

                    <h3 class="mt-5 text-lg font-semibold text-slate-900">No subfolders yet</h3>
                    <p class="mt-2 text-sm text-slate-500 max-w-md mx-auto">
                        Create the first subfolder inside this parent folder to organise the next level of documents.
                    </p>

                    @if($isAdmin)
                    <button type="button"
                        id="dh-open-subfolder-empty"
                        class="mt-5 inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-md hover:bg-slate-800 cursor-pointer">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Create Subfolder</span>
                    </button>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    @if($isAdmin)
    {{-- Create Subfolder Modal --}}
    <div id="dh-subfolder-modal"
        class="fixed inset-0 z-40 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm px-4">
        <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl border border-slate-200 px-6 py-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">New Subfolder</h2>
                    <p class="mt-1 text-[11px] text-slate-500">
                        Create a subfolder inside <strong>{{ $folder->folder_name }}</strong>.
                    </p>
                </div>
                <button type="button"
                    data-dh-sub-close
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-400 hover:bg-slate-50 hover:text-slate-600">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="dh-subfolder-form" method="POST" action="{{ route('dh.folders.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $folder->id }}">

                <div>
                    <label class="block text-[11px] font-medium text-slate-700 mb-1">Subfolder name *</label>
                    <input type="text" name="folder_name" required
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-[12px] focus:border-sky-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-100">
                </div>

                <div>
                    <label class="block text-[11px] font-medium text-slate-700 mb-1">Month</label>
                    <input type="text" name="month_label" placeholder="e.g. Feb 2026"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-[12px] focus:border-sky-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-100">
                </div>

                <div>
                    <label class="block text-[11px] font-medium text-slate-700 mb-1">Remarks</label>
                    <textarea name="remarks" rows="3"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-[12px] resize-y focus:border-sky-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-100"></textarea>
                </div>

                <div class="mt-4 flex items-center justify-end gap-2 text-[11px]">
                    <button type="button"
                        data-dh-sub-close
                        class="rounded-full px-4 py-2 border border-slate-300 text-slate-600 bg-white hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit"
                        class="rounded-full px-5 py-2 bg-slate-900 text-white font-semibold shadow-sm hover:bg-slate-800">
                        Create Subfolder
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
                <h2 class="text-sm font-semibold text-slate-900">Subfolder name already used</h2>
                <button type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-400 hover:bg-slate-50 hover:text-slate-600"
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
    <div id="dh-subfolder-delete-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm px-4">
        <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl border border-slate-200 px-6 py-6">
            <div class="flex items-start justify-between mb-3">
                <h2 class="text-sm font-semibold text-slate-900">Delete subfolder?</h2>
                <button type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-400 hover:bg-slate-50 hover:text-slate-600"
                    data-dh-subfolder-del-close>
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <p id="dh-subfolder-delete-text" class="text-[11px] text-slate-600 leading-relaxed"></p>

            <div class="mt-4 flex items-center justify-end gap-2 text-[11px]">
                <button type="button"
                    class="rounded-full px-4 py-2 border border-slate-300 text-slate-600 bg-white hover:bg-slate-50"
                    data-dh-subfolder-del-close>
                    Cancel
                </button>
                <button type="button"
                    class="rounded-full px-5 py-2 bg-rose-500 text-white font-semibold shadow-sm hover:bg-rose-600"
                    id="dh-subfolder-del-confirm">
                    Delete subfolder
                </button>
            </div>
        </div>
    </div>

    {{-- Info Modal --}}
    <div id="dh-sub-info-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm px-4">
        <div class="w-full max-w-lg rounded-3xl bg-white shadow-2xl border border-slate-200 px-6 py-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Subfolder Info</h2>
                    <p class="mt-1 text-[11px] text-slate-500">Quick details about the selected subfolder.</p>
                </div>
                <button type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-400 hover:bg-slate-50 hover:text-slate-600"
                    data-dh-sub-info-close>
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Folder name</p>
                    <p id="dh-sub-info-name" class="mt-2 text-sm font-semibold text-slate-900 break-words">—</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Folder ID</p>
                    <p id="dh-sub-info-id" class="mt-2 text-sm font-semibold text-slate-900">—</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Files</p>
                    <p id="dh-sub-info-files" class="mt-2 text-sm font-semibold text-slate-900">—</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Subfolders</p>
                    <p id="dh-sub-info-subfolders" class="mt-2 text-sm font-semibold text-slate-900">—</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Month</p>
                    <p id="dh-sub-info-month" class="mt-2 text-sm font-semibold text-slate-900">—</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Created</p>
                    <p id="dh-sub-info-created" class="mt-2 text-sm font-semibold text-slate-900">—</p>
                </div>
            </div>

            <div class="mt-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[10px] uppercase tracking-wide text-slate-400">Remarks</p>
                <p id="dh-sub-info-remarks" class="mt-2 text-[12px] leading-6 text-slate-700 break-words">—</p>
            </div>

            <div class="mt-5 flex items-center justify-end">
                <button type="button"
                    class="rounded-full px-5 py-2 border border-slate-300 text-slate-700 bg-white hover:bg-slate-50"
                    data-dh-sub-info-close>
                    Close
                </button>
            </div>
        </div>
    </div>

    {{-- Rename Modal --}}
    <div id="dh-sub-rename-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm px-4">
        <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl border border-slate-200 px-6 py-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Rename Subfolder</h2>
                    <p class="mt-1 text-[11px] text-slate-500">Update the subfolder name safely.</p>
                </div>
                <button type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-400 hover:bg-slate-50"
                    data-dh-sub-rename-close>
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div>
                <label class="block text-[11px] font-medium text-slate-700 mb-1">Subfolder name</label>
                <input type="text"
                    id="dh-sub-rename-input"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-[12px] focus:border-sky-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-100">
            </div>

            <div class="mt-5 flex items-center justify-end gap-2">
                <button type="button"
                    class="rounded-full px-4 py-2 border border-slate-300 text-slate-600 bg-white hover:bg-slate-50"
                    data-dh-sub-rename-close>
                    Cancel
                </button>
                <button type="button"
                    id="dh-sub-rename-confirm"
                    class="rounded-full px-5 py-2 bg-slate-900 text-white font-semibold shadow-sm hover:bg-slate-800">
                    Save name
                </button>
            </div>
        </div>
    </div>

    {{-- Trash Modal --}}
    <div id="dh-sub-trash-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm px-4">
        <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl border border-slate-200 px-6 py-6">
            <div class="flex items-start justify-between mb-3">
                <h2 class="text-sm font-semibold text-slate-900">Move subfolder to trash?</h2>
                <button type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-400 hover:bg-slate-50"
                    data-dh-sub-trash-close>
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <p id="dh-sub-trash-text" class="text-[11px] text-slate-600 leading-relaxed"></p>

            <div class="mt-4 flex items-center justify-end gap-2">
                <button type="button"
                    class="rounded-full px-4 py-2 border border-slate-300 text-slate-600 bg-white hover:bg-slate-50"
                    data-dh-sub-trash-close>
                    Cancel
                </button>
                <button type="button"
                    id="dh-sub-trash-confirm"
                    class="rounded-full px-5 py-2 bg-rose-500 text-white font-semibold shadow-sm hover:bg-rose-600">
                    Move to trash
                </button>
            </div>
        </div>
    </div>

    {{-- Hidden forms --}}
    <form id="dh-delete-form" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <form id="dh-sub-rename-form" method="POST" class="hidden">
        @csrf
        @method('PATCH')
        <input type="hidden" name="folder_name" id="dh-sub-rename-folder-name">
    </form>

    <form id="dh-sub-trash-form" method="POST" class="hidden">
        @csrf
        @method('PATCH')
    </form>
    @endif
</section>

@push('scripts')
<script src="{{ asset('js/dh-subfolders.js') }}"></script>
@endpush
@endsection