@extends('layouts.gts_app')

@section('content')
<section class="text-dec-none p-font py-10 px-4">
    <div id="dh-root" data-existing-names='@json($existingNames)' class="max-w-6xl mx-auto font-plus space-y-6">

        {{-- Header / hero ---------------------------------------------------- --}}
        <div class="flex items-center justify-between gap-4">
            <div>
                {{-- Back to dashboard --}}
                <a href="{{ route('admin.dashboard') }}"
                   class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                    ← Back to dashboard
                </a>

                <h1 class="mt-3 text-xl md:text-2xl font-semibold text-slate-900">
                    Document Hub
                </h1>
                <p class="mt-1 text-xs text-slate-500 max-w-xl">
                    Create folders and organise your admin documents and attachments so you can find them quickly.
                </p>
            </div>

            <button type="button"
                    id="dh-open-create"
                    class="inline-flex items-center gap-2 rounded-full bg-sky-500 px-4 py-2 text-xs font-semibold text-white shadow-md hover:bg-sky-600 cursor-pointer">
                + New Folder
            </button>
        </div>

        @php
            $folders = $folders ?? collect();
        @endphp

        @if($folders->count())
            {{-- Search + delete row ------------------------------------------ --}}
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="relative flex-1 max-w-xs">
                    <input
                        id="dh-search"
                        type="text"
                        placeholder="Search by folder name or ID…"
                        class="w-full rounded-full border border-slate-200 bg-white px-10 py-2 text-[11px] focus:border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-400">
                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 text-xs">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                </div>

                <button
                    type="button"
                    id="dh-delete-folder-btn"
                    class="inline-flex items-center gap-2 rounded-full bg-rose-500 px-4 py-2 text-[11px] font-semibold text-white shadow-md hover:bg-rose-600 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
                    <i class="fa-regular fa-trash-can text-[11px]"></i>
                    <span>Delete selected folder</span>
                </button>
            </div>

            {{-- Folder cards ------------------------------------------------- --}}
            <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
                @foreach($folders as $folder)
                    @php
                        $records        = $folder->records ?? collect();
                        $fileCount      = $records->whereNotNull('file_path')->count();
                        $subfolderCount = $folder->children ? $folder->children->count() : 0;
                    @endphp

                    <div
                        class="dh-folder-card group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:border-sky-300 hover:shadow-md transition-all cursor-pointer"
                        data-id="{{ $folder->id }}"
                        data-name="{{ $folder->folder_name }}"
                        data-files="{{ $fileCount }}"
                        data-subfolders="{{ $subfolderCount }}"
                        data-delete-url="{{ route('dh.folders.destroy', $folder) }}"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-3">
                                {{-- Folder icon bubble --}}
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-full bg-sky-50 text-sky-500 group-hover:bg-sky-100">
                                    <span class="text-lg">📁</span>
                                </div>

                                <div>
                                    <h2 class="text-sm font-semibold text-slate-900">
                                        {{ $folder->folder_name }}
                                    </h2>
                                    <p class="text-[11px] text-slate-500">
                                        ID: {{ $folder->id }}
                                    </p>
                                    <p class="text-[11px] text-slate-500 mt-0.5">
                                        {{ $folder->month_label ?: 'No month set' }}
                                        @if($folder->remarks)
                                            · {{ $folder->remarks }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Stats row ---------------------------------------- --}}
                        <div class="mt-3 flex items-center justify-between text-[11px] text-slate-500">
                            <span class="px-3">
                                {{ $subfolderCount }} subfolder{{ $subfolderCount === 1 ? '' : 's' }}
                            </span>
                            <span class="px-3">
                                {{ $fileCount }} file{{ $fileCount === 1 ? '' : 's' }}
                            </span>
                        </div>

                        {{-- Actions row -------------------------------------- --}}
                        <div class="mt-4 flex items-center justify-between">
                            {{-- Subfolders button --}}
                            <a href="{{ route('dh.subfolders.index', $folder) }}"
                               class="inline-flex items-center gap-1 rounded-full border border-sky-500 bg-white px-3 py-1.5 text-[10px] font-semibold text-sky-600 shadow-sm hover:bg-sky-500 hover:text-white"
                               onclick="event.stopPropagation();">
                                Subfolders
                            </a>

                            {{-- Open button --}}
                            <a href="{{ route('dh.show', $folder) }}"
                               class="inline-flex items-center gap-1 rounded-full bg-slate-900 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-slate-800"
                               onclick="event.stopPropagation();">
                                Open
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Empty state --------------------------------------------------- --}}
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white/80 px-6 py-10 text-center text-xs text-slate-500">
                <p class="mb-1">No folders yet.</p>
                <p>
                    Click
                    <button type="button"
                            id="dh-open-create-empty"
                            class="inline-flex items-center gap-1 rounded-full bg-sky-500 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-sky-600 cursor-pointer">
                        + New Folder
                    </button>
                    to create one.
                </p>
            </div>
        @endif
    </div>

    {{-- New Folder Modal ----------------------------------------------------- --}}
    <div id="dh-create-modal"
         class="fixed inset-0 z-40 hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl bg-white shadow-xl border border-slate-200 px-6 py-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">New Folder</h2>
                    <p class="mt-1 text-[11px] text-slate-500">Give this folder a name and optional month.</p>
                </div>
                <button type="button" data-dh-close
                        class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 text-[11px] text-slate-400 hover:bg-slate-50 hover:text-slate-600">
                    ✕
                </button>
            </div>

            <form id="dh-create-form" method="POST" action="{{ route('dh.folders.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[11px] font-medium text-slate-700 mb-1">Folder name *</label>
                    <input type="text" name="folder_name" required
                           class="w-full rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-[12px] focus:border-sky-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-sky-400">
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-700 mb-1">Month</label>
                    <input type="text" name="month_label" placeholder="e.g. Nov 2025"
                           class="w-full rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-[12px] focus:border-sky-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-sky-400">
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-700 mb-1">Remarks</label>
                    <textarea name="remarks" rows="2"
                              class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-[12px] resize-y focus:border-sky-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-sky-400"></textarea>
                </div>

                <div class="mt-4 flex items-center justify-end gap-2 text-[11px]">
                    <button type="button" data-dh-close
                            class="rounded-full px-4 py-1.5 border border-slate-300 text-slate-600 bg-white hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="rounded-full px-5 py-1.5 bg-sky-500 text-white font-semibold shadow-sm hover:bg-sky-600">
                        Create
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

{{-- Duplicate name confirmation modal -------------------------------------- --}}
<div id="dh-duplicate-modal"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm">
    <div class="w-full max-w-md rounded-2xl bg-white shadow-xl border border-slate-200 px-6 py-6">
        <div class="flex items-start justify-between mb-3">
            <h2 class="text-sm font-semibold text-slate-900">
                Folder name already used
            </h2>
            <button type="button"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 text-[11px] text-slate-400 hover:bg-slate-50 hover:text-slate-600"
                    data-dh-dup-close>
                ✕
            </button>
        </div>

        <p id="dh-dup-message" class="text-[11px] text-slate-600 leading-relaxed">
            You already have a folder with this name. Are you sure you want to create another one with the same
            name?
        </p>

        <div class="mt-4 flex items-center justify-end gap-2 text-[11px]">
            <button type="button"
                    class="rounded-full px-4 py-1.5 border border-slate-300 text-slate-600 bg-white hover:bg-slate-50"
                    data-dh-dup-close>
                Cancel
            </button>
            <button type="button"
                    class="rounded-full px-5 py-1.5 bg-rose-500 text-white font-semibold shadow-sm hover:bg-rose-600"
                    id="dh-dup-confirm">
                Create anyway
            </button>
        </div>
    </div>
</div>

{{-- Delete folder confirmation modal --------------------------------------- --}}
<div id="dh-folder-delete-modal"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm">
    <div class="w-full max-w-md rounded-2xl bg-white shadow-xl border border-slate-200 px-6 py-6">
        <div class="flex items-start justify-between mb-3">
            <h2 class="text-sm font-semibold text-slate-900">
                Delete folder?
            </h2>
            <button type="button"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 text-[11px] text-slate-400 hover:bg-slate-50 hover:text-slate-600"
                    data-dh-folder-del-close>
                ✕
            </button>
        </div>

        <p id="dh-folder-delete-text" class="text-[11px] text-slate-600 leading-relaxed">
            <!-- Filled by JS -->
        </p>

        <div class="mt-4 flex items-center justify-end gap-2 text-[11px]">
            <button type="button"
                    class="rounded-full px-4 py-1.5 border border-slate-300 text-slate-600 bg-white hover:bg-slate-50"
                    data-dh-folder-del-close>
                Cancel
            </button>
            <button type="button"
                    class="rounded-full px-5 py-1.5 bg-rose-500 text-white font-semibold shadow-sm hover:bg-rose-600"
                    id="dh-folder-del-confirm">
                Delete folder
            </button>
        </div>
    </div>
</div>

{{-- Hidden delete form --}}
<form id="dh-delete-form" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
    $(function () {
        const $modal       = $('#dh-create-modal');
        const $open        = $('#dh-open-create');
        const $form        = $('#dh-create-form');
        const $name        = $form.find('input[name="folder_name"]');

        const $dupModal    = $('#dh-duplicate-modal');
        const $dupMsg      = $('#dh-dup-message');
        const $dupConfirm  = $('#dh-dup-confirm');

        const rootEl       = document.getElementById('dh-root');
        const existingNames = rootEl
            ? JSON.parse(rootEl.dataset.existingNames || '[]')
            : [];

        let allowDuplicate = false;

        // --- Create modal --------------------------------------------------
        function openCreateModal() {
            $modal.removeClass('hidden').addClass('flex');
        }
        function closeCreateModal() {
            $modal.removeClass('flex').addClass('hidden');
        }

        $open.on('click', function (e) {
            e.preventDefault();
            $form[0].reset();
            allowDuplicate = false;
            openCreateModal();
        });

        $('#dh-open-create-empty').on('click', function () {
            $('#dh-open-create').trigger('click');
        });

        $modal.on('click', function (e) {
            if (e.target === this) closeCreateModal();
        });
        $modal.find('[data-dh-close]').on('click', function (e) {
            e.preventDefault();
            closeCreateModal();
        });

        // Duplicate check
        function openDupModal(name) {
            $dupMsg.text(
                'You already have a folder named "' + name +
                '". Are you sure you want to create another folder with the same name?'
            );
            $dupModal.removeClass('hidden').addClass('flex');
        }
        function closeDupModal() {
            $dupModal.removeClass('flex').addClass('hidden');
        }

        $form.on('submit', function (e) {
            if (allowDuplicate) return true;

            const raw = ($name.val() || '').trim();
            if (!raw) return true;

            const lower = raw.toLowerCase();

            if (existingNames.includes(lower)) {
                e.preventDefault();
                openDupModal(raw);
            }
        });

        $('[data-dh-dup-close]').on('click', function (e) {
            e.preventDefault();
            closeDupModal();
        });

        $dupConfirm.on('click', function (e) {
            e.preventDefault();
            allowDuplicate = true;
            closeDupModal();
            $form.trigger('submit');
        });

        // --- Search + selection + delete ----------------------------------
        const $search     = $('#dh-search');
        const $cards      = $('.dh-folder-card');
        const $delBtn     = $('#dh-delete-folder-btn');
        const $delForm    = $('#dh-delete-form');
        const $delModal   = $('#dh-folder-delete-modal');
        const $delText    = $('#dh-folder-delete-text');
        const $delConfirm = $('#dh-folder-del-confirm');

        let selectedCard  = null;
        let pendingDeleteUrl = '';

        function setSelectedCard(card) {
            if (selectedCard) {
                $(selectedCard).removeClass('ring-2 ring-sky-400 ring-offset-2 ring-offset-slate-50');
            }
            selectedCard = card;
            if (selectedCard) {
                $(selectedCard).addClass('ring-2 ring-sky-400 ring-offset-2 ring-offset-slate-50');
                const name = $(selectedCard).data('name') || '';
                const id   = $(selectedCard).data('id');
                $delBtn.prop('disabled', false);
                $delBtn.find('span').text(`Delete folder (ID ${id})`);
            } else {
                $delBtn.prop('disabled', true);
                $delBtn.find('span').text('Delete selected folder');
            }
        }

        // click on card to select (ignore clicks on internal links)
        $cards.on('click', function (e) {
            // if click came from an <a>, ignore (handled already)
            if ($(e.target).closest('a').length) return;

            if (selectedCard && this === selectedCard) {
                // toggle off
                setSelectedCard(null);
            } else {
                setSelectedCard(this);
            }
        });

        function filterFolders(term) {
            const t = term.trim().toLowerCase();

            $cards.each(function () {
                const $c = $(this);
                const name = String($c.data('name') || '').toLowerCase();
                const id   = String($c.data('id') || '');
                const hay  = (name + ' ' + id).toLowerCase();

                if (!t || hay.includes(t)) {
                    $c.removeClass('hidden');
                } else {
                    $c.addClass('hidden');
                }
            });

            // if selected card is now hidden, clear selection
            if (selectedCard && $(selectedCard).hasClass('hidden')) {
                setSelectedCard(null);
            }
        }

        $search.on('input', function () {
            filterFolders($(this).val());
        });

        // Delete modal helpers
        function openDeleteModal(name, id, files, subs, url) {
            pendingDeleteUrl = url || '';
            let msg = `You are about to delete folder ID ${id} (“${name}”).`;

            if (files > 0 || subs > 0) {
                msg += ' This will also permanently delete ';

                const parts = [];
                if (files > 0) {
                    parts.push(files + ' file' + (files === 1 ? '' : 's'));
                }
                if (subs > 0) {
                    parts.push(subs + ' subfolder' + (subs === 1 ? '' : 's'));
                }
                msg += parts.join(' and ') + ', including any files inside subfolders.';
            }

            msg += ' This action cannot be undone. Are you sure you want to continue?';

            $delText.text(msg);
            $delModal.removeClass('hidden').addClass('flex');
        }

        function closeDeleteModal() {
            $delModal.removeClass('flex').addClass('hidden');
            pendingDeleteUrl = '';
        }

        $('[data-dh-folder-del-close]').on('click', function (e) {
            e.preventDefault();
            closeDeleteModal();
        });

        $delModal.on('click', function (e) {
            if (e.target === this) closeDeleteModal();
        });

        // delete button click
        $delBtn.on('click', function (e) {
            e.preventDefault();
            if (!selectedCard) return;

            const $c   = $(selectedCard);
            const id   = $c.data('id');
            const name = $c.data('name') || 'this folder';
            const files = parseInt($c.data('files') || 0, 10);
            const subs  = parseInt($c.data('subfolders') || 0, 10);
            const url   = $c.data('delete-url');

            if (!url) return;

            // if no files & no subfolders => delete directly without modal
            if (files === 0 && subs === 0) {
                $delForm.attr('action', url);
                $delForm.trigger('submit');
                return;
            }

            openDeleteModal(name, id, files, subs, url);
        });

        // Confirm deletion
        $delConfirm.on('click', function (e) {
            e.preventDefault();
            if (!pendingDeleteUrl) return;

            $delForm.attr('action', pendingDeleteUrl);
            closeDeleteModal();
            $delForm.trigger('submit');
        });

        // ESC closes modals
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') {
                closeCreateModal();
                closeDupModal();
                closeDeleteModal();
            }
        });
    });
</script>
@endpush
@endsection
