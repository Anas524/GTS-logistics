$(function () {
    const $modal = $('#dh-create-modal');
    const $open = $('#dh-open-create');
    const $form = $('#dh-create-form');
    const $name = $form.find('input[name="folder_name"]');

    const $dupModal = $('#dh-duplicate-modal');
    const $dupMsg = $('#dh-dup-message');
    const $dupConfirm = $('#dh-dup-confirm');

    const $infoModal = $('#dh-folder-info-modal');

    const rootEl = document.getElementById('dh-root');
    const existingNames = rootEl ? JSON.parse(rootEl.dataset.existingNames || '[]') : [];

    const $renameModal = $('#dh-rename-modal');
    const $renameForm = $('#dh-rename-form');
    const $renameInput = $('#dh-rename-input');
    const $renameHiddenName = $('#dh-rename-folder-name');

    const $descriptionModal = $('#dh-description-modal');
    const $descriptionForm = $('#dh-description-form');
    const $descriptionMonthInput = $('#dh-description-month-input');
    const $descriptionInput = $('#dh-description-input');
    const $descriptionMonthHidden = $('#dh-description-month-hidden');
    const $descriptionHidden = $('#dh-description-hidden');

    const $trashModal = $('#dh-trash-modal');
    const $trashForm = $('#dh-trash-form');
    const $trashText = $('#dh-trash-text');

    const $gridBtn = $('#dh-grid-view-btn');
    const $listBtn = $('#dh-list-view-btn');
    const $gridWrap = $('#dh-grid-wrap');
    const $listWrap = $('#dh-list-wrap');

    let pendingRenameUrl = '';
    let pendingDescriptionUrl = '';
    let pendingTrashUrl = '';
    let allowDuplicate = false;

    function openCreateModal() {
        $modal.removeClass('hidden').addClass('flex');
    }

    function closeCreateModal() {
        $modal.removeClass('flex').addClass('hidden');
    }

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

    function closeAllMenus() {
        $('[data-folder-menu]').removeClass('is-open');

        $('.dh-folder-card').removeClass('menu-open');
        $('.dh-folder-row').removeClass('menu-open-row');
        $('.dh-list-action-cell').removeClass('menu-open-cell');
        $('.dh-list-action-anchor').removeClass('menu-open-anchor');
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

    function openDescriptionModal(month, description, url) {
        pendingDescriptionUrl = url || '';
        $descriptionMonthInput.val(month || '');
        $descriptionInput.val(description || '');
        $descriptionModal.removeClass('hidden').addClass('flex');
        setTimeout(() => $descriptionMonthInput.trigger('focus'), 50);
    }

    function closeDescriptionModal() {
        pendingDescriptionUrl = '';
        $descriptionMonthInput.val('');
        $descriptionInput.val('');
        $descriptionModal.removeClass('flex').addClass('hidden');
    }

    function openTrashModal(name, id, files, subs, url) {
        pendingTrashUrl = url || '';

        let msg = `You are about to move folder ID ${id} (“${name}”) to trash.`;

        if (files > 0 || subs > 0) {
            msg += ' Its nested contents will remain safe and hidden from the main drive, including ';
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

    function applyFolderSearch(term) {
        const t = (term || '').trim().toLowerCase();

        $('.dh-folder-card, .dh-folder-row').each(function () {
            const $el = $(this);
            const name = String($el.data('name') || '').toLowerCase();
            const id = String($el.data('id') || '');
            const hay = `${name} ${id}`.toLowerCase();

            $el.toggleClass('hidden', !!t && !hay.includes(t));
        });
    }

    function activateGrid() {
        $gridBtn.addClass('is-active');
        $listBtn.removeClass('is-active');
        $gridWrap.removeClass('hidden');
        $listWrap.addClass('hidden');
        closeAllMenus();
    }

    function activateList() {
        $listBtn.addClass('is-active');
        $gridBtn.removeClass('is-active');
        $listWrap.removeClass('hidden');
        $gridWrap.addClass('hidden');
        closeAllMenus();
    }

    $open.on('click', function (e) {
        e.preventDefault();
        if ($form.length) $form[0].reset();
        allowDuplicate = false;
        openCreateModal();
    });

    $('#dh-open-create-empty').on('click', function (e) {
        e.preventDefault();
        $('#dh-open-create').trigger('click');
    });

    $modal.on('click', function (e) {
        if (e.target === this) closeCreateModal();
    });

    $modal.find('[data-dh-close]').on('click', function (e) {
        e.preventDefault();
        closeCreateModal();
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

    $('#dh-search').on('input', function () {
        applyFolderSearch($(this).val());
    });

    $gridBtn.on('click', function (e) {
        e.preventDefault();
        activateGrid();
    });

    $listBtn.on('click', function (e) {
        e.preventDefault();
        activateList();
    });

    $(document).on('click', '.dh-folder-card', function (e) {
        if ($(e.target).closest('a, button, [data-folder-menu]').length) return;

        const url = $(this).data('open-url');
        if (url) window.location.href = url;
    });

    $(document).on('click', '.dh-folder-row', function (e) {
        if ($(e.target).closest('a, button, [data-folder-menu]').length) return;

        const url = $(this).data('open-url');
        if (url) window.location.href = url;
    });

    $(document).on('click', '[data-folder-menu-toggle]', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $btn = $(this);
        const $card = $btn.closest('.dh-folder-card');
        const $row = $btn.closest('.dh-folder-row');
        const $cell = $btn.closest('.dh-list-action-cell');
        const $anchor = $btn.closest('.dh-list-action-anchor');
        const $wrap = $card.length ? $card : $row;
        const $menu = $wrap.find('[data-folder-menu]').first();
        const isOpen = $menu.hasClass('is-open');

        closeAllMenus();

        if (isOpen) return;

        if ($card.length) {
            $card.addClass('menu-open');
        }

        if ($row.length) {
            $row.addClass('menu-open-row');
        }

        if ($cell.length) {
            $cell.addClass('menu-open-cell');
        }

        if ($anchor.length) {
            $anchor.addClass('menu-open-anchor');
        }

        $menu.addClass('is-open');
    });

    $(document).on('click', '[data-folder-info-btn]', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $wrap = $(this).closest('.dh-folder-card, .dh-folder-row');

        $('#dh-info-name').text($wrap.data('name') || '—');
        $('#dh-info-id').text($wrap.data('id') || '—');
        $('#dh-info-files').text($wrap.data('files') || 0);
        $('#dh-info-subfolders').text($wrap.data('subfolders') || 0);
        $('#dh-info-month').text($wrap.data('month') || '—');
        $('#dh-info-created').text($wrap.data('created') || '—');
        $('#dh-info-remarks').text($wrap.data('remarks') || '—');

        closeAllMenus();
        $infoModal.removeClass('hidden').addClass('flex');
    });

    $(document).on('click', '[data-dh-folder-info-close]', function (e) {
        e.preventDefault();
        $infoModal.removeClass('flex').addClass('hidden');
    });

    $infoModal.on('click', function (e) {
        if (e.target === this) {
            $infoModal.removeClass('flex').addClass('hidden');
        }
    });

    $(document).on('click', '[data-folder-rename-btn]', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $wrap = $(this).closest('.dh-folder-card, .dh-folder-row');
        openRenameModal($wrap.data('name') || '', $wrap.data('rename-url') || '');
        closeAllMenus();
    });

    $(document).on('click', '[data-folder-description-btn]', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $wrap = $(this).closest('.dh-folder-card, .dh-folder-row');
        openDescriptionModal(
            $wrap.data('month') || '',
            $wrap.data('remarks') || '',
            $wrap.data('description-url') || ''
        );
        closeAllMenus();
    });

    $(document).on('click', '[data-folder-trash-btn]', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $wrap = $(this).closest('.dh-folder-card, .dh-folder-row');
        openTrashModal(
            $wrap.data('name') || 'this folder',
            $wrap.data('id'),
            parseInt($wrap.data('files') || 0, 10),
            parseInt($wrap.data('subfolders') || 0, 10),
            $wrap.data('trash-url') || ''
        );
        closeAllMenus();
    });

    $(document).on('click', '[data-dh-rename-close]', function (e) {
        e.preventDefault();
        closeRenameModal();
    });

    $renameModal.on('click', function (e) {
        if (e.target === this) closeRenameModal();
    });

    $(document).on('click', '[data-dh-description-close]', function (e) {
        e.preventDefault();
        closeDescriptionModal();
    });

    $descriptionModal.on('click', function (e) {
        if (e.target === this) closeDescriptionModal();
    });

    $('#dh-rename-confirm').on('click', function (e) {
        e.preventDefault();

        const newName = ($renameInput.val() || '').trim();
        if (!newName || !pendingRenameUrl) return;

        $renameForm.attr('action', pendingRenameUrl);
        $renameHiddenName.val(newName);
        closeRenameModal();
        $renameForm.trigger('submit');
    });

    $('#dh-description-confirm').on('click', function (e) {
        e.preventDefault();

        if (!pendingDescriptionUrl) return;

        const newMonth = ($descriptionMonthInput.val() || '').trim();
        const newDescription = ($descriptionInput.val() || '').trim();

        $descriptionForm.attr('action', pendingDescriptionUrl);
        $descriptionMonthHidden.val(newMonth);
        $descriptionHidden.val(newDescription);
        closeDescriptionModal();
        $descriptionForm.trigger('submit');
    });

    $(document).on('click', '[data-dh-trash-close]', function (e) {
        e.preventDefault();
        closeTrashModal();
    });

    $trashModal.on('click', function (e) {
        if (e.target === this) closeTrashModal();
    });

    $('#dh-trash-confirm').on('click', function (e) {
        e.preventDefault();
        if (!pendingTrashUrl) return;

        $trashForm.attr('action', pendingTrashUrl);
        closeTrashModal();
        $trashForm.trigger('submit');
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('.dh-folder-more-btn, [data-folder-menu]').length) {
            closeAllMenus();
        }
    });

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            closeCreateModal();
            closeDupModal();
            closeRenameModal();
            closeDescriptionModal();
            closeTrashModal();
            closeAllMenus();
            $infoModal.removeClass('flex').addClass('hidden');
        }
    });
});