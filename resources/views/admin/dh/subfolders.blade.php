@extends('layouts.gts_app')

@section('content')
<section id="dh-sub-root"
         class="text-dec-none p-font py-10 px-4"
         data-existing-names='@json($existingSubNames)'>
    <div class="max-w-6xl mx-auto font-plus space-y-6">

        {{-- Back buttons ------------------------------------------------------ --}}
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('dh.index') }}"
               class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                ← Back to Document Hub
            </a>
        </div>

        {{-- Header ------------------------------------------------------------ --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl md:text-2xl font-semibold text-slate-900">
                    Subfolders – {{ $folder->folder_name }}
                </h1>
                <p class="mt-1 text-[11px] md:text-xs text-slate-500">
                    {{ $folder->month_label ?: 'No month set' }}
                    @if($folder->remarks) · {{ $folder->remarks }} @endif
                </p>
            </div>

            <button type="button"
                    id="dh-open-subfolder"
                    class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-slate-800 cursor-pointer">
                + New Subfolder
            </button>
        </div>

        @php
            $subfolders = $subfolders ?? collect();
        @endphp

        @if($subfolders->count())
            {{-- Search + delete row ------------------------------------------ --}}
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="relative flex-1 max-w-xs">
                    <input
                        id="dh-sub-search"
                        type="text"
                        placeholder="Search by subfolder name or ID…"
                        class="w-full rounded-full border border-slate-200 bg-white px-10 py-2 text-[11px] focus:border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-400">
                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 text-xs">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                </div>

                <button
                    type="button"
                    id="dh-sub-delete-btn"
                    class="inline-flex items-center gap-2 rounded-full bg-rose-500 px-4 py-2 text-[11px] font-semibold text-white shadow-md hover:bg-rose-600 disabled:opacity-40 disabled:cursor-not-allowed">
                    <i class="fa-regular fa-trash-can text-[11px]"></i>
                    <span>Delete selected subfolder</span>
                </button>
            </div>

            {{-- Subfolder cards ---------------------------------------------- --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($subfolders as $sub)
                    @php
                        $subRecords     = $sub->records ?? collect();
                        $subFileCount   = $subRecords->whereNotNull('file_path')->count();
                        $subChildCount  = $sub->children ? $sub->children->count() : 0;
                    @endphp

                    <div
                        class="dh-sub-card group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:border-sky-300 hover:shadow-md transition-all cursor-pointer"
                        data-id="{{ $sub->id }}"
                        data-name="{{ $sub->folder_name }}"
                        data-files="{{ $subFileCount }}"
                        data-subfolders="{{ $subChildCount }}"
                        data-delete-url="{{ route('dh.folders.destroy', $sub) }}"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                {{-- Folder icon circle --}}
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-50 text-amber-500 group-hover:bg-amber-100">
                                    <i class="fa-solid fa-folder"></i>
                                </div>
                                <div>
                                    <h2 class="text-sm font-semibold text-slate-900">
                                        {{ $sub->folder_name }}
                                    </h2>
                                    <p class="text-[11px] text-slate-500">
                                        ID: {{ $sub->id }}
                                    </p>
                                    <p class="mt-0.5 text-[11px] text-slate-500">
                                        {{ $sub->month_label ?: 'No month set' }}
                                    </p>
                                </div>
                            </div>

                            <div class="text-right text-[11px] text-slate-500">
                                <div>{{ $subFileCount }} file{{ $subFileCount === 1 ? '' : 's' }}</div>
                                @if($subChildCount > 0)
                                    <div class="mt-0.5">{{ $subChildCount }} subfolder{{ $subChildCount === 1 ? '' : 's' }}</div>
                                @endif
                                <a href="{{ route('dh.show', $sub) }}"
                                   class="mt-2 inline-flex items-center gap-1 rounded-full bg-slate-900 px-3 py-1.5 text-[10px] font-semibold text-white hover:bg-slate-800"
                                   onclick="event.stopPropagation();">
                                    Open
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                @endforelse
            </div>
        @else
            {{-- Empty state --------------------------------------------------- --}}
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white/80 px-6 py-10 text-center text-[11px] text-slate-500">
                <p class="mb-1">No subfolders yet.</p>
                <p>
                    Click
                    <button type="button"
                            id="dh-open-subfolder-empty"
                            class="inline-flex items-center gap-1 rounded-full bg-slate-900 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-slate-800 cursor-pointer">
                        + New Subfolder
                    </button>
                    to create one.
                </p>
            </div>
        @endif
    </div>

    {{-- New Subfolder Modal -------------------------------------------------- --}}
    <div id="dh-subfolder-modal"
         class="fixed inset-0 z-40 hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl bg-white shadow-xl border border-slate-200 px-6 py-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">New Subfolder</h2>
                    <p class="mt-1 text-[11px] text-slate-500">
                        Create a subfolder inside <strong>{{ $folder->folder_name }}</strong>.
                    </p>
                </div>
                <button type="button" data-dh-sub-close
                        class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 text-[11px] text-slate-400 hover:bg-slate-50 hover:text-slate-600">
                    ✕
                </button>
            </div>

            <form id="dh-subfolder-form" method="POST" action="{{ route('dh.folders.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $folder->id }}">

                <div>
                    <label class="block text-[11px] font-medium text-slate-700 mb-1">Subfolder name *</label>
                    <input type="text" name="folder_name" required
                           class="w-full rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-[12px] focus:border-sky-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-sky-400">
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-700 mb-1">Month</label>
                    <input type="text" name="month_label" placeholder="e.g. Feb 2026"
                           class="w-full rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-[12px] focus:border-sky-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-sky-400">
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-700 mb-1">Remarks</label>
                    <textarea name="remarks" rows="2"
                              class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-[12px] resize-y focus:border-sky-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-sky-400"></textarea>
                </div>

                <div class="mt-4 flex items-center justify-end gap-2 text-[11px]">
                    <button type="button" data-dh-sub-close
                            class="rounded-full px-4 py-1.5 border border-slate-300 text-slate-600 bg-white hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="rounded-full px-5 py-1.5 bg-slate-900 text-white font-semibold shadow-sm hover:bg-slate-800">
                        Create
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Duplicate subfolder modal ------------------------------------------- --}}
    <div id="dh-duplicate-modal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl bg-white shadow-xl border border-slate-200 px-6 py-6">
            <div class="flex items-start justify-between mb-3">
                <h2 class="text-sm font-semibold text-slate-900">
                    Subfolder name already used
                </h2>
                <button type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 text-[11px] text-slate-400 hover:bg-slate-50 hover:text-slate-600"
                        data-dh-dup-close>
                    ✕
                </button>
            </div>

            <p id="dh-dup-message" class="text-[11px] text-slate-600 leading-relaxed">
                You already have a subfolder with this name under this folder. Are you sure you want to create another
                one with the same name?
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

    {{-- Delete subfolder confirmation modal --------------------------------- --}}
    <div id="dh-subfolder-delete-modal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl bg-white shadow-xl border border-slate-200 px-6 py-6">
            <div class="flex items-start justify-between mb-3">
                <h2 class="text-sm font-semibold text-slate-900">
                    Delete subfolder?
                </h2>
                <button type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 text-[11px] text-slate-400 hover:bg-slate-50 hover:text-slate-600"
                        data-dh-subfolder-del-close>
                    ✕
                </button>
            </div>

            <p id="dh-subfolder-delete-text" class="text-[11px] text-slate-600 leading-relaxed">
                <!-- Filled by JS -->
            </p>

            <div class="mt-4 flex items-center justify-end gap-2 text-[11px]">
                <button type="button"
                        class="rounded-full px-4 py-1.5 border border-slate-300 text-slate-600 bg-white hover:bg-slate-50"
                        data-dh-subfolder-del-close>
                    Cancel
                </button>
                <button type="button"
                        class="rounded-full px-5 py-1.5 bg-rose-500 text-white font-semibold shadow-sm hover:bg-rose-600"
                        id="dh-subfolder-del-confirm">
                    Delete subfolder
                </button>
            </div>
        </div>
    </div>
</section>

{{-- Hidden delete form (reuse same destroy route) --}}
<form id="dh-delete-form" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
    $(function () {
        const $subModal   = $('#dh-subfolder-modal');
        const $openSub    = $('#dh-open-subfolder');
        const $openEmpty  = $('#dh-open-subfolder-empty');
        const $form       = $('#dh-subfolder-form');
        const $name       = $form.find('input[name="folder_name"]');

        const $dupModal   = $('#dh-duplicate-modal');
        const $dupMsg     = $('#dh-dup-message');
        const $dupConfirm = $('#dh-dup-confirm');

        // existing subfolder names for this parent only (lowercased)
        const subRootEl     = document.getElementById('dh-sub-root');
        const existingNames = subRootEl
            ? JSON.parse(subRootEl.dataset.existingNames || '[]')
            : [];

        let allowDuplicate = false;

        function openSubModal() {
            $subModal.removeClass('hidden').addClass('flex');
        }
        function closeSubModal() {
            $subModal.removeClass('flex').addClass('hidden');
        }

        function openDupModal(name) {
            $dupMsg.text(
                'You already have a subfolder named "' + name +
                '" under this folder. Are you sure you want to create another one with the same name?'
            );
            $dupModal.removeClass('hidden').addClass('flex');
        }
        function closeDupModal() {
            $dupModal.removeClass('flex').addClass('hidden');
        }

        // open from header button
        $openSub.on('click', function (e) {
            e.preventDefault();
            $form[0].reset();
            allowDuplicate = false;
            openSubModal();
        });

        // open from empty-state button (if visible)
        if ($openEmpty && $openEmpty.length) {
            $openEmpty.on('click', function (e) {
                e.preventDefault();
                $openSub.trigger('click');
            });
        }

        // close modal
        $subModal.on('click', function (e) {
            if (e.target === this) closeSubModal();
        });
        $subModal.find('[data-dh-sub-close]').on('click', function (e) {
            e.preventDefault();
            closeSubModal();
        });

        // duplicate check before submit
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

        // duplicate modal buttons
        $('[data-dh-dup-close]').on('click', function (e) {
            e.preventDefault();
            closeDupModal();
        });

        $dupConfirm.on('click', function (e) {
            e.preventDefault();
            allowDuplicate = true;
            closeDupModal();
            $form.trigger('submit'); // submit again, now allowed
        });

        // --- Search + select + delete for subfolders -----------------------
        const $search     = $('#dh-sub-search');
        const $cards      = $('.dh-sub-card');
        const $delBtn     = $('#dh-sub-delete-btn');
        const $delForm    = $('#dh-delete-form');
        const $delModal   = $('#dh-subfolder-delete-modal');
        const $delText    = $('#dh-subfolder-delete-text');
        const $delConfirm = $('#dh-subfolder-del-confirm');

        let selectedCard      = null;
        let pendingDeleteUrl  = '';

        function setSelectedSub(card) {
            if (selectedCard) {
                $(selectedCard).removeClass('ring-2 ring-sky-400 ring-offset-2 ring-offset-slate-50');
            }
            selectedCard = card;
            if (selectedCard) {
                $(selectedCard).addClass('ring-2 ring-sky-400 ring-offset-2 ring-offset-slate-50');
                const name = $(selectedCard).data('name') || '';
                const id   = $(selectedCard).data('id');
                $delBtn.prop('disabled', false);
                $delBtn.find('span').text(`Delete subfolder (ID ${id})`);
            } else {
                $delBtn.prop('disabled', true);
                $delBtn.find('span').text('Delete selected subfolder');
            }
        }

        // click on card to select
        $cards.on('click', function (e) {
            if ($(e.target).closest('a').length) return; // ignore inner links

            if (selectedCard && this === selectedCard) {
                setSelectedSub(null);
            } else {
                setSelectedSub(this);
            }
        });

        function filterSubfolders(term) {
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

            if (selectedCard && $(selectedCard).hasClass('hidden')) {
                setSelectedSub(null);
            }
        }

        $search.on('input', function () {
            filterSubfolders($(this).val());
        });

        // delete modal helpers
        function openDeleteModal(name, id, files, subs, url) {
            pendingDeleteUrl = url || '';
            let msg = `You are about to delete subfolder ID ${id} (“${name}”) inside folder "{{ $folder->folder_name }}".`;

            if (files > 0 || subs > 0) {
                msg += ' This will also permanently delete ';

                const parts = [];
                if (files > 0) {
                    parts.push(files + ' file' + (files === 1 ? '' : 's'));
                }
                if (subs > 0) {
                    parts.push(subs + ' subfolder' + (subs === 1 ? '' : 's'));
                }
                msg += parts.join(' and ') + ', including any files inside those subfolders.';
            }

            msg += ' This action cannot be undone. Are you sure you want to continue?';

            $delText.text(msg);
            $delModal.removeClass('hidden').addClass('flex');
        }

        function closeDeleteModal() {
            $delModal.removeClass('flex').addClass('hidden');
            pendingDeleteUrl = '';
        }

        $('[data-dh-subfolder-del-close]').on('click', function (e) {
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

            const $c    = $(selectedCard);
            const id    = $c.data('id');
            const name  = $c.data('name') || 'this subfolder';
            const files = parseInt($c.data('files') || 0, 10);
            const subs  = parseInt($c.data('subfolders') || 0, 10);
            const url   = $c.data('delete-url');

            if (!url) return;

            // if no files & no sub-subfolders => delete directly without modal
            if (files === 0 && subs === 0) {
                $delForm.attr('action', url);
                $delForm.trigger('submit');
                return;
            }

            openDeleteModal(name, id, files, subs, url);
        });

        // confirm delete
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
                closeSubModal();
                closeDupModal();
                closeDeleteModal();
            }
        });
    });
</script>
@endpush
@endsection
