$(function () {
    const rootEl = document.getElementById('dh-sub-root');
    const isAdmin = rootEl && rootEl.dataset.isAdmin === '1';

    const $search = $('#dh-sub-search');
    const $cards = $('.dh-sub-card');

    let selectedCard = null;

    function closeAllMenus() {
        $('[data-sub-menu]').removeClass('is-open');
    }

    function setSelectedSub(card) {
        if (selectedCard) {
            $(selectedCard).removeClass('is-selected');
        }

        selectedCard = card;

        const $delBtn = $('#dh-sub-delete-btn');

        if (selectedCard) {
            $(selectedCard).addClass('is-selected');
            const id = $(selectedCard).data('id');

            if ($delBtn.length) {
                $delBtn.prop('disabled', false);
                $delBtn.find('span').text(`Delete subfolder (ID ${id})`);
            }
        } else {
            if ($delBtn.length) {
                $delBtn.prop('disabled', true);
                $delBtn.find('span').text('Delete selected subfolder');
            }
        }
    }

    function filterSubfolders(term) {
        const t = term.trim().toLowerCase();

        $cards.each(function () {
            const $c = $(this);
            const name = String($c.data('name') || '').toLowerCase();
            const id = String($c.data('id') || '');
            const hay = (name + ' ' + id).toLowerCase();

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

    if ($search.length) {
        $search.on('input', function () {
            filterSubfolders($(this).val());
        });
    }

    $cards.on('click', function (e) {
        if ($(e.target).closest('a, button, [data-sub-menu]').length) return;

        if (selectedCard && this === selectedCard) {
            setSelectedSub(null);
        } else {
            setSelectedSub(this);
        }
    });

    if (!isAdmin) {
        $(document).on('click', '[data-sub-menu-toggle]', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const $card = $(this).closest('.dh-sub-card');
            const $menu = $card.find('[data-sub-menu]').first();
            const isOpen = $menu.hasClass('is-open');

            closeAllMenus();

            if (!isOpen) {
                $menu.addClass('is-open');
            }
        });

        $(document).on('click', function (e) {
            if (!$(e.target).closest('.dh-sub-more-btn, [data-sub-menu]').length) {
                closeAllMenus();
            }
        });

        return;
    }

    const $subModal = $('#dh-subfolder-modal');
    const $openSub = $('#dh-open-subfolder');
    const $openEmpty = $('#dh-open-subfolder-empty');
    const $form = $('#dh-subfolder-form');
    const $name = $form.find('input[name="folder_name"]');

    const $dupModal = $('#dh-duplicate-modal');
    const $dupMsg = $('#dh-dup-message');
    const $dupConfirm = $('#dh-dup-confirm');

    const existingNames = rootEl
        ? JSON.parse(rootEl.dataset.existingNames || '[]')
        : [];

    let allowDuplicate = false;

    const $infoModal = $('#dh-sub-info-modal');
    const $renameModal = $('#dh-sub-rename-modal');
    const $renameForm = $('#dh-sub-rename-form');
    const $renameInput = $('#dh-sub-rename-input');
    const $renameHiddenName = $('#dh-sub-rename-folder-name');

    const $trashModal = $('#dh-sub-trash-modal');
    const $trashForm = $('#dh-sub-trash-form');
    const $trashText = $('#dh-sub-trash-text');

    const $delBtn = $('#dh-sub-delete-btn');
    const $delForm = $('#dh-delete-form');
    const $delModal = $('#dh-subfolder-delete-modal');
    const $delText = $('#dh-subfolder-delete-text');
    const $delConfirm = $('#dh-subfolder-del-confirm');

    let pendingDeleteUrl = '';
    let pendingRenameUrl = '';
    let pendingTrashUrl = '';

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

    function closeInfoModal() {
        $infoModal.removeClass('flex').addClass('hidden');
    }

    function openRenameModal(name, url) {
        pendingRenameUrl = url || '';
        $renameInput.val(name || '');
        $renameModal.removeClass('hidden').addClass('flex');
        setTimeout(() => $renameInput.trigger('focus'), 50);
    }

    function closeRenameModal() {
        pendingRenameUrl = '';
        $renameInput.val('');
        $renameModal.removeClass('flex').addClass('hidden');
    }

    function openTrashModal(name, id, files, subs, url) {
        pendingTrashUrl = url || '';

        let msg = `You are about to move subfolder ID ${id} (“${name}”) to trash.`;

        if (files > 0 || subs > 0) {
            msg += ' Its nested contents will remain safe and hidden from the main view';
            msg += ', including ';
            const parts = [];
            if (files > 0) parts.push(files + ' file' + (files === 1 ? '' : 's'));
            if (subs > 0) parts.push(subs + ' subfolder' + (subs === 1 ? '' : 's'));
            msg += parts.join(' and ') + '.';
        }

        $trashText.text(msg);
        $trashModal.removeClass('hidden').addClass('flex');
    }

    function closeTrashModal() {
        pendingTrashUrl = '';
        $trashModal.removeClass('flex').addClass('hidden');
    }

    function openDeleteModal(name, id, files, subs, url) {
        pendingDeleteUrl = url || '';
        let msg = `You are about to delete subfolder ID ${id} (“${name}”).`;

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

    if ($openSub.length) {
        $openSub.on('click', function (e) {
            e.preventDefault();
            $form[0].reset();
            allowDuplicate = false;
            openSubModal();
        });
    }

    if ($openEmpty.length) {
        $openEmpty.on('click', function (e) {
            e.preventDefault();
            if ($openSub.length) {
                $openSub.trigger('click');
            } else {
                $form[0].reset();
                allowDuplicate = false;
                openSubModal();
            }
        });
    }

    $subModal.on('click', function (e) {
        if (e.target === this) closeSubModal();
    });

    $subModal.find('[data-dh-sub-close]').on('click', function (e) {
        e.preventDefault();
        closeSubModal();
    });

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

    $(document).on('click', '[data-sub-menu-toggle]', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $card = $(this).closest('.dh-sub-card');
        const $menu = $card.find('[data-sub-menu]').first();
        const isOpen = $menu.hasClass('is-open');

        closeAllMenus();

        if (!isOpen) {
            $menu.addClass('is-open');
        }
    });

    $(document).on('click', '[data-sub-info-btn]', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $card = $(this).closest('.dh-sub-card');

        $('#dh-sub-info-name').text($card.data('name') || '—');
        $('#dh-sub-info-id').text($card.data('id') || '—');
        $('#dh-sub-info-files').text($card.data('files') || 0);
        $('#dh-sub-info-subfolders').text($card.data('subfolders') || 0);
        $('#dh-sub-info-month').text($card.data('month') || '—');
        $('#dh-sub-info-created').text($card.data('created') || '—');
        $('#dh-sub-info-remarks').text($card.data('remarks') || '—');

        closeAllMenus();
        $infoModal.removeClass('hidden').addClass('flex');
    });

    $(document).on('click', '[data-dh-sub-info-close]', function (e) {
        e.preventDefault();
        closeInfoModal();
    });

    $infoModal.on('click', function (e) {
        if (e.target === this) closeInfoModal();
    });

    $(document).on('click', '[data-sub-rename-btn]', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $card = $(this).closest('.dh-sub-card');
        openRenameModal(
            $card.data('name') || '',
            $card.data('rename-url') || ''
        );
        closeAllMenus();
    });

    $(document).on('click', '[data-sub-trash-btn]', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $card = $(this).closest('.dh-sub-card');
        openTrashModal(
            $card.data('name') || 'this subfolder',
            $card.data('id'),
            parseInt($card.data('files') || 0, 10),
            parseInt($card.data('subfolders') || 0, 10),
            $card.data('trash-url') || ''
        );
        closeAllMenus();
    });

    $(document).on('click', '[data-dh-sub-rename-close]', function (e) {
        e.preventDefault();
        closeRenameModal();
    });

    $renameModal.on('click', function (e) {
        if (e.target === this) closeRenameModal();
    });

    $('#dh-sub-rename-confirm').on('click', function (e) {
        e.preventDefault();

        const newName = ($renameInput.val() || '').trim();
        if (!newName || !pendingRenameUrl) return;

        $renameForm.attr('action', pendingRenameUrl);
        $renameHiddenName.val(newName);
        closeRenameModal();
        $renameForm.trigger('submit');
    });

    $(document).on('click', '[data-dh-sub-trash-close]', function (e) {
        e.preventDefault();
        closeTrashModal();
    });

    $trashModal.on('click', function (e) {
        if (e.target === this) closeTrashModal();
    });

    $('#dh-sub-trash-confirm').on('click', function (e) {
        e.preventDefault();
        if (!pendingTrashUrl) return;

        $trashForm.attr('action', pendingTrashUrl);
        closeTrashModal();
        $trashForm.trigger('submit');
    });

    $('[data-dh-subfolder-del-close]').on('click', function (e) {
        e.preventDefault();
        closeDeleteModal();
    });

    $delModal.on('click', function (e) {
        if (e.target === this) closeDeleteModal();
    });

    $delBtn.on('click', function (e) {
        e.preventDefault();
        if (!selectedCard) return;

        const $c = $(selectedCard);
        const id = $c.data('id');
        const name = $c.data('name') || 'this subfolder';
        const files = parseInt($c.data('files') || 0, 10);
        const subs = parseInt($c.data('subfolders') || 0, 10);
        const url = $c.data('delete-url');

        if (!url) return;

        if (files === 0 && subs === 0) {
            $delForm.attr('action', url);
            $delForm.trigger('submit');
            return;
        }

        openDeleteModal(name, id, files, subs, url);
    });

    $delConfirm.on('click', function (e) {
        e.preventDefault();
        if (!pendingDeleteUrl) return;

        $delForm.attr('action', pendingDeleteUrl);
        closeDeleteModal();
        $delForm.trigger('submit');
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('.dh-sub-more-btn, [data-sub-menu]').length) {
            closeAllMenus();
        }
    });

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            closeSubModal();
            closeDupModal();
            closeDeleteModal();
            closeAllMenus();
            closeInfoModal();
            closeRenameModal();
            closeTrashModal();
        }
    });
});