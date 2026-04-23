@extends('layouts.gts_app')

@section('content')
<section class="p-font py-8 px-4">

    <style>
        .dh-back-link,
        .dh-back-link:link,
        .dh-back-link:visited,
        .dh-back-link:hover,
        .dh-back-link:active {
            text-decoration: none !important;
        }
    </style>

    <div class="max-w-6xl mx-auto space-y-8">
        <div class="space-y-3">
            <div class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
                <a href="{{ $backUrl }}"
                    class="dh-back-link inline-flex items-center gap-2 rounded-full px-1 py-1 font-semibold text-slate-700 hover:text-slate-900 leading-none transition">
                    <i class="fa-solid fa-arrow-left text-[11px] leading-none translate-y-[1px]"></i>
                    <span class="leading-none">{{ $backLabel }}</span>
                </a>

                <span class="text-slate-300">/</span>

                <a href="{{ route('dh.index') }}"
                    class="dh-back-link font-semibold text-slate-600 hover:text-slate-900 leading-none transition">
                    Document Hub
                </a>

                <span class="text-slate-300">/</span>

                <span class="font-semibold text-slate-700 leading-none">Trash</span>
            </div>

            <div>
                <h1 class="text-xl md:text-2xl font-semibold text-slate-900">Trash</h1>
                <p class="mt-1 text-xs text-slate-500">
                    Restore folders and files moved to trash, or permanently remove them.
                </p>
            </div>
        </div>

        <form id="dh-trash-bulk-form" method="POST" class="space-y-4">
            @csrf
            <div class="hidden" id="dh-trash-folder-inputs"></div>
            <div class="hidden" id="dh-trash-attachment-inputs"></div>

            <div class="hidden items-center justify-between gap-3 rounded-3xl border border-slate-200 bg-white px-4 py-3 shadow-sm" id="dh-trash-bulk-bar">
                <div class="text-[12px] font-semibold text-slate-700">
                    <span id="dh-trash-selected-count">0</span> item(s) selected
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button"
                        id="dh-trash-restore-selected"
                        class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-4 py-2 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-600">
                        <i class="fa-solid fa-rotate-left text-[10px]"></i>
                        Restore Selected
                    </button>

                    <button type="button"
                        id="dh-trash-delete-selected"
                        class="inline-flex items-center gap-2 rounded-full bg-rose-500 px-4 py-2 text-[11px] font-semibold text-white shadow-sm hover:bg-rose-600">
                        <i class="fa-solid fa-trash text-[10px]"></i>
                        Delete Selected
                    </button>
                </div>
            </div>
        </form>

        @if(($folders->count() + $attachments->count()) > 0)

        {{-- Trashed Folders --}}
        @if($folders->count())
        <div class="space-y-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Trashed Folders</h2>
                <p class="text-xs text-slate-500">Folders moved out of the main drive.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($folders as $folder)
                @php
                $records = $folder->records ?? collect();
                $fileCount = $records->sum(fn($record) => $record->attachments ? $record->attachments->count() : 0);
                $subfolderCount = $folder->children ? $folder->children->count() : 0;
                $isSubfolder = !is_null($folder->parent_id);
                @endphp

                <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <label class="mt-1 inline-flex">
                                <input type="checkbox"
                                    class="dh-trash-check rounded border-slate-300 text-sky-500 focus:ring-sky-400"
                                    data-trash-type="folder"
                                    data-trash-id="{{ $folder->id }}">
                            </label>

                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-50 text-rose-500">
                                <i class="fa-regular fa-trash-can text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900">{{ $folder->folder_name }}</h3>
                                <p class="text-[11px] text-slate-500">ID: {{ $folder->id }}</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    {{ $isSubfolder ? 'Subfolder' : 'Root folder' }}
                                    @if($isSubfolder && $folder->parent)
                                    · Parent: {{ $folder->parent->folder_name }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-[11px] text-slate-500 space-y-1">
                        <p>{{ $fileCount }} file{{ $fileCount === 1 ? '' : 's' }}</p>
                        <p>{{ $subfolderCount }} subfolder{{ $subfolderCount === 1 ? '' : 's' }}</p>
                        <p>Trashed: {{ optional($folder->trashed_at)->format('d M Y, h:i A') ?: '—' }}</p>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <form method="POST" action="{{ route('dh.folders.restore', $folder) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-4 py-2 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-600">
                                <i class="fa-solid fa-rotate-left text-[10px]"></i>
                                Restore
                            </button>
                        </form>

                        <form method="POST" action="{{ route('dh.folders.forceDelete', $folder) }}"
                            onsubmit="return confirm('Permanently delete this folder and all its contents? This cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="inline-flex items-center gap-2 rounded-full bg-rose-500 px-4 py-2 text-[11px] font-semibold text-white shadow-sm hover:bg-rose-600">
                                <i class="fa-solid fa-trash text-[10px]"></i>
                                Delete permanently
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Trashed Files --}}
        @if($attachments->count())
        <div class="space-y-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Trashed Files</h2>
                <p class="text-xs text-slate-500">Files removed from folder pages.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($attachments as $attachment)
                @php
                $folder = optional(optional($attachment->record)->folder);
                $isImage = str_starts_with((string) $attachment->mime, 'image/');
                $sizeKb = $attachment->size ? round($attachment->size / 1024, 1) : null;
                @endphp

                <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <label class="mt-1 inline-flex">
                                <input type="checkbox"
                                    class="dh-trash-check rounded border-slate-300 text-sky-500 focus:ring-sky-400"
                                    data-trash-type="attachment"
                                    data-trash-id="{{ $attachment->id }}">
                            </label>

                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-500">
                                <i class="fa-regular {{ $isImage ? 'fa-image' : 'fa-file-lines' }} text-lg"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-slate-900 truncate">
                                    {{ $attachment->original_name ?: 'Attachment '.$attachment->id }}
                                </h3>
                                <p class="text-[11px] text-slate-500">
                                    Folder: {{ $folder?->folder_name ?: 'Unknown folder' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-[11px] text-slate-500 space-y-1">
                        <p>Type: {{ $attachment->mime ?: 'Unknown' }}</p>
                        <p>Size: {{ $sizeKb ? $sizeKb . ' KB' : '—' }}</p>
                        <p>Trashed: {{ optional($attachment->trashed_at)->format('d M Y, h:i A') ?: '—' }}</p>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <form method="POST" action="{{ route('dh.attachments.restore', $attachment) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-4 py-2 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-600">
                                <i class="fa-solid fa-rotate-left text-[10px]"></i>
                                Restore File
                            </button>
                        </form>

                        <form method="POST" action="{{ route('dh.attachments.forceDelete', $attachment) }}"
                            onsubmit="return confirm('Permanently delete this file? This cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="inline-flex items-center gap-2 rounded-full bg-rose-500 px-4 py-2 text-[11px] font-semibold text-white shadow-sm hover:bg-rose-600">
                                <i class="fa-solid fa-trash text-[10px]"></i>
                                Delete permanently
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @else
        <div class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center text-sm text-slate-500">
            Trash is empty.
        </div>
        @endif
    </div>
</section>

@push('scripts')
<script>
    (function() {
        const checks = Array.from(document.querySelectorAll('.dh-trash-check'));
        const bulkBar = document.getElementById('dh-trash-bulk-bar');
        const selectedCount = document.getElementById('dh-trash-selected-count');
        const folderInputs = document.getElementById('dh-trash-folder-inputs');
        const attachmentInputs = document.getElementById('dh-trash-attachment-inputs');
        const form = document.getElementById('dh-trash-bulk-form');
        const restoreBtn = document.getElementById('dh-trash-restore-selected');
        const deleteBtn = document.getElementById('dh-trash-delete-selected');

        function rebuildInputs() {
            const selected = checks.filter(c => c.checked);

            selectedCount.textContent = selected.length;
            bulkBar.classList.toggle('hidden', selected.length === 0);
            bulkBar.classList.toggle('flex', selected.length > 0);

            folderInputs.innerHTML = '';
            attachmentInputs.innerHTML = '';

            selected.forEach((input) => {
                const type = input.dataset.trashType;
                const id = input.dataset.trashId;

                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.value = id;

                if (type === 'folder') {
                    hidden.name = 'folder_ids[]';
                    folderInputs.appendChild(hidden);
                } else if (type === 'attachment') {
                    hidden.name = 'attachment_ids[]';
                    attachmentInputs.appendChild(hidden);
                }
            });
        }

        checks.forEach((check) => {
            check.addEventListener('change', rebuildInputs);
        });

        restoreBtn?.addEventListener('click', function() {
            form.action = "{{ route('dh.trash.restoreSelected') }}";
            form.method = 'POST';

            let methodInput = form.querySelector('input[name="_method"]');
            if (!methodInput) {
                methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                form.appendChild(methodInput);
            }
            methodInput.value = 'PATCH';

            form.submit();
        });

        deleteBtn?.addEventListener('click', function() {
            if (!confirm('Permanently delete selected items? This cannot be undone.')) {
                return;
            }

            form.action = "{{ route('dh.trash.forceDeleteSelected') }}";
            form.method = 'POST';

            let methodInput = form.querySelector('input[name="_method"]');
            if (!methodInput) {
                methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                form.appendChild(methodInput);
            }
            methodInput.value = 'DELETE';

            form.submit();
        });

        rebuildInputs();
    })();
</script>
@endpush

@endsection