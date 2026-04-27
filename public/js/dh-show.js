$(function () {
    var rootEl = document.getElementById('dh-show-root');
    var isAdmin = rootEl && rootEl.dataset.isAdmin === '1';
    var canUpload = rootEl && rootEl.dataset.canUpload === '1';
    var canManage = rootEl && rootEl.dataset.canManage === '1';

    // ---------- Viewer modal (everyone) ----------
    var $viewerModal = $('#dh-viewer-modal');
    var $viewerTitle = $('#dh-viewer-title');
    var $viewerContent = $('#dh-viewer-content');
    var $downloadOne = $('#dh-download-current');

    var $folderUploadDropzone = $('#dh-folder-upload-dropzone');
    var folderSelectedFiles = [];

    const $folderCreateModal = $('#dh-folder-create-modal');

    $('#dh-open-folder-modal').on('click', function () {
        $folderCreateModal.removeClass('hidden').addClass('flex');
    });

    $(document).on('click', '[data-dh-folder-create-close]', function () {
        $folderCreateModal.removeClass('flex').addClass('hidden');
    });

    $folderCreateModal.on('click', function (e) {
        if (e.target === this) {
            $folderCreateModal.removeClass('flex').addClass('hidden');
        }
    });

    function fileKey(file) {
        return [file.name, file.size, file.lastModified, file.type].join('__');
    }

    function mergeUniqueFiles(existing, incoming) {
        const map = new Map();

        (existing || []).forEach(function (file) {
            map.set(fileKey(file), file);
        });

        (incoming || []).forEach(function (file) {
            map.set(fileKey(file), file);
        });

        return Array.from(map.values());
    }

    function syncFolderInputFiles() {
        if (!$folderUploadFile.length) return;
        const dt = new DataTransfer();

        folderSelectedFiles.forEach(function (file) {
            dt.items.add(file);
        });

        $folderUploadFile[0].files = dt.files;
    }

    function removeFolderSelectedFile(index) {
        folderSelectedFiles.splice(index, 1);
        syncFolderInputFiles();
        renderFolderSelectedFiles(folderSelectedFiles);
    }

    function closeViewerModal() {
        $viewerModal.removeClass('flex').addClass('hidden');
        $viewerTitle.text('File Preview');
        $viewerContent.empty();
        $downloadOne.attr('href', '#');
        $(document).off('.dhZoom');
    }

    function openSingleFileViewer($ctx) {
        const title = $ctx.attr('data-file-title') || 'File';
        const inlineUrl = $ctx.attr('data-inline-url') || '#';
        const downloadUrl = $ctx.attr('data-download-url') || '#';
        const mime = String($ctx.attr('data-file-type') || '').toLowerCase();

        $viewerTitle.text(title);
        $downloadOne.attr('href', downloadUrl);

        let html = '';

        if (mime.startsWith('image/')) {
            html = `
            <div class="dh-image-viewer flex h-full w-full items-center justify-center bg-slate-100 p-4 overflow-hidden">
                <img src="${inlineUrl}" 
                    alt="${title}" 
                    class="dh-zoom-img max-h-full max-w-full object-contain rounded-xl cursor-zoom-in transition-transform duration-200">
            </div>
        `;
        } else if (mime.startsWith('video/')) {
            html = `
            <div class="flex h-full w-full items-center justify-center bg-black p-4">
                <video src="${inlineUrl}" controls class="max-h-full max-w-full rounded-xl bg-black"></video>
            </div>
        `;
        } else if (mime.includes('pdf')) {
            html = `
            <div class="h-full w-full bg-slate-100">
                <iframe src="${inlineUrl}" class="block h-full w-full border-0 bg-white" loading="lazy"></iframe>
            </div>
        `;
        } else {
            html = `
            <div class="h-full w-full bg-slate-100">
                <iframe src="${inlineUrl}" class="block h-full w-full border-0 bg-white" loading="lazy"></iframe>
            </div>
        `;
        }

        $viewerContent.html(html);

        setTimeout(() => {
            const $img = $viewerContent.find('.dh-zoom-img');
            if (!$img.length) return;

            let scale = 1;
            let isDragging = false;
            let startX, startY, translateX = 0, translateY = 0;

            // Click zoom
            $img.on('click', function () {
                scale = scale === 1 ? 2 : 1;
                $(this).css({
                    transform: `scale(${scale}) translate(${translateX}px, ${translateY}px)`,
                    cursor: scale > 1 ? 'grab' : 'zoom-in'
                });
            });

            // Mouse wheel zoom
            $img.on('wheel', function (e) {
                e.preventDefault();

                scale += e.originalEvent.deltaY * -0.001;
                scale = Math.min(Math.max(1, scale), 5);

                $(this).css({
                    transform: `scale(${scale}) translate(${translateX}px, ${translateY}px)`
                });
            });

            // Drag to move
            $img.on('mousedown', function (e) {
                if (scale <= 1) return;

                isDragging = true;
                startX = e.pageX - translateX;
                startY = e.pageY - translateY;

                $(this).css('cursor', 'grabbing');
            });

            $(document).on('mousemove.dhZoom', function (e) {
                if (!isDragging) return;

                translateX = e.pageX - startX;
                translateY = e.pageY - startY;

                $img.css({
                    transform: `scale(${scale}) translate(${translateX}px, ${translateY}px)`
                });
            });

            $(document).on('mouseup.dhZoom', function () {
                isDragging = false;
                if (scale > 1) $img.css('cursor', 'grab');
            });

        }, 50);

        $viewerModal.removeClass('hidden').addClass('flex');
    }

    $(document).on('click', '[data-dh-view-close]', function (e) {
        e.preventDefault();
        closeViewerModal();
    });

    $viewerModal.on('click', function (e) {
        if (e.target === this) {
            closeViewerModal();
        }
    });

    $(document).on('click', '[data-file-card]', function (e) {
        if ($(e.target).closest('button, a').length) return;

        const $ctx = $(this);
        if (!$ctx.length) return;

        openSingleFileViewer($ctx);
    });

    $(document).on('click', '[data-file-row]', function (e) {
        if ($(e.target).closest('button, a, .dh-list-action-cell, .dh-list-action-anchor, [data-file-menu]').length) return;

        const $ctx = $(this);
        if (!$ctx.length) return;

        openSingleFileViewer($ctx);
    });

    $('#dh-download-current').on('click', function (e) {
        e.preventDefault();
        var href = $(this).attr('href');
        if (!href || href === '#') return;
        window.open(href, '_blank');
    });

    // ---------- Upload logic (admin + consultant) ----------
    if (canUpload) {
        var $uploadModal = $('#dh-upload-modal');
        var $uploadForm = $('#dh-upload-form');
        var $uploadFile = $('#dh-upload-file');
        var $uploadTitle = $('#dh-upload-title');
        var $existingWrap = $('#dh-existing-wrap');
        var $existingName = $('#dh-existing-name');
        var $selectedCount = $('#dh-selected-count');
        var $selectedList = $('#dh-selected-list');
        var $selectedEmpty = $('#dh-selected-empty');
        var $dropzone = $('#dh-dropzone');
        var droppedFiles = [];

        var $replaceModal = $('#dh-replace-modal');
        var pendingUploadBtn = null;

        var $folderUploadForm = $('#dh-folder-upload-form');
        var $folderUploadFile = $('#dh-folder-upload-file');
        var $folderDropzone = $('#dh-folder-dropzone');
        var $folderQueue = $('#dh-folder-upload-queue');
        var $folderQueueClose = $('#dh-folder-queue-close');
        var $folderQueueSub = $('#dh-folder-queue-sub');
        var $folderSelectedCount = $('#dh-folder-selected-count');
        var $folderSelectedList = $('#dh-folder-selected-list');
        var $folderSelectedEmpty = $('#dh-folder-selected-empty');
        var $folderQueueBar = $('#dh-folder-queue-bar');
        var $folderUploadModal = $('#dh-folder-upload-modal');
        var $openFolderUploadModal = $('#dh-open-upload-modal, #dh-open-upload-modal-empty');
        var $folderUploadDate = $('#dh-folder-upload-date');
        var $folderUploadDescription = $('#dh-folder-upload-description');
        var droppedFolderFiles = [];

        function openFolderUploadModal() {
            folderSelectedFiles = [];
            droppedFolderFiles = [];
            $folderUploadFile.val('');
            $folderUploadDate.val(new Date().toISOString().slice(0, 10));
            $folderUploadDescription.val('');
            renderFolderSelectedFiles(folderSelectedFiles);
            $folderUploadModal.removeClass('hidden').addClass('flex');
        }

        function closeFolderUploadModal() {
            folderSelectedFiles = [];
            droppedFolderFiles = [];
            $folderUploadFile.val('');
            $folderUploadDescription.val('');
            renderFolderSelectedFiles(folderSelectedFiles);
            $folderUploadDropzone.removeClass('ring-2 ring-sky-300 border-sky-400 bg-sky-50/70');
            $folderUploadModal.removeClass('flex').addClass('hidden');
        }

        $openFolderUploadModal.on('click', function () {
            openFolderUploadModal();
        });

        $(document).on('click', '[data-dh-folder-upload-close]', function () {
            closeFolderUploadModal();
        });

        $folderUploadModal.on('click', function (e) {
            if (e.target === this) {
                closeFolderUploadModal();
            }
        });

        function renderFolderSelectedFiles(files) {
            const list = files || [];

            $folderSelectedCount.text('Selected: ' + list.length + ' file' + (list.length === 1 ? '' : 's'));

            if (!list.length) {
                $folderSelectedList.addClass('hidden').empty();
                $folderSelectedEmpty.removeClass('hidden');
                return;
            }

            $folderSelectedEmpty.addClass('hidden');
            $folderSelectedList.removeClass('hidden').empty();

            list.forEach(function (f, i) {
                const sizeKb = f.size ? Math.round(f.size / 1024) : null;

                const $line = $('<div></div>')
                    .addClass('flex items-start justify-between gap-3 px-3 py-2 rounded-xl border border-slate-200 bg-white');

                const $left = $('<div></div>')
                    .addClass('min-w-0 flex-1');

                const $name = $('<div></div>')
                    .addClass('text-[11px] text-slate-700')
                    .css({
                        whiteSpace: 'normal',
                        overflowWrap: 'anywhere',
                        wordBreak: 'break-word'
                    })
                    .text((i + 1) + '. ' + f.name);

                const $meta = $('<div></div>')
                    .addClass('mt-1 text-[10px] text-slate-400')
                    .text(sizeKb ? (sizeKb + ' KB') : '');

                const $remove = $('<button type="button"></button>')
                    .addClass('inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-rose-200 bg-rose-50 text-rose-500 hover:bg-rose-100')
                    .attr('title', 'Remove file')
                    .attr('data-folder-remove-index', i)
                    .html('<i class="fa-solid fa-xmark text-[10px]"></i>');

                $left.append($name).append($meta);
                $line.append($left).append($remove);
                $folderSelectedList.append($line);
            });
        }

        function showFolderQueue(message) {
            $folderQueueSub.text(message || 'Preparing upload...');
            $folderQueue.removeClass('hidden');
        }

        function closeFolderQueue() {
            $folderQueue.addClass('hidden');
            $folderQueueBar.css('width', '0%');
            $folderQueueSub.text('Preparing upload...');
            $folderSelectedCount.text('Selected: 0 files');
            $folderSelectedList.addClass('hidden').empty();
            $folderSelectedEmpty.removeClass('hidden');
        }

        $folderQueueClose.on('click', function () {
            closeFolderQueue();
        });

        $folderUploadFile.on('change', function () {
            const newFiles = this.files ? Array.from(this.files) : [];
            folderSelectedFiles = mergeUniqueFiles(folderSelectedFiles, newFiles);
            droppedFolderFiles = folderSelectedFiles.slice();
            syncFolderInputFiles();
            renderFolderSelectedFiles(folderSelectedFiles);
        });

        $(document).on('click', '[data-folder-remove-index]', function () {
            const index = Number($(this).attr('data-folder-remove-index'));
            if (Number.isNaN(index)) return;
            removeFolderSelectedFile(index);
        });

        $(document).on('dragover drop', function (e) {
            e.preventDefault();
        });

        if ($folderUploadDropzone.length) {
            $folderUploadDropzone.on('dragenter dragover', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $folderUploadDropzone.addClass('ring-2 ring-sky-300 border-sky-400 bg-sky-50/70');
            });

            $folderUploadDropzone.on('dragleave dragend', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $folderUploadDropzone.removeClass('ring-2 ring-sky-300 border-sky-400 bg-sky-50/70');
            });

            $folderUploadDropzone.on('drop', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $folderUploadDropzone.removeClass('ring-2 ring-sky-300 border-sky-400 bg-sky-50/70');

                const dt = e.originalEvent.dataTransfer;
                const newFiles = dt && dt.files ? Array.from(dt.files) : [];
                if (!newFiles.length) return;

                folderSelectedFiles = mergeUniqueFiles(folderSelectedFiles, newFiles);
                droppedFolderFiles = folderSelectedFiles.slice();
                syncFolderInputFiles();
                renderFolderSelectedFiles(folderSelectedFiles);
            });
        }

        $folderUploadForm.on('submit', function (e) {
            const selectedFiles = folderSelectedFiles.length
                ? folderSelectedFiles
                : ($folderUploadFile[0] && $folderUploadFile[0].files ? Array.from($folderUploadFile[0].files) : []);

            const docDate = ($folderUploadDate.val() || '').trim();
            const description = ($folderUploadDescription.val() || '').trim();

            if (!selectedFiles.length) {
                e.preventDefault();
                alert('Please choose at least one file.');
                return;
            }

            if (!docDate) {
                e.preventDefault();
                alert('Please choose the date.');
                return;
            }

            e.preventDefault();

            var action = $folderUploadForm.attr('action');
            var token = $folderUploadForm.find('input[name="_token"]').val();

            var formData = new FormData();
            formData.append('_token', token);
            formData.append('doc_date', docDate);
            formData.append('description', description);

            selectedFiles.forEach(function (f) {
                formData.append('files[]', f);
            });

            closeFolderUploadModal();
            showFolderQueue('Uploading ' + selectedFiles.length + ' item' + (selectedFiles.length === 1 ? '' : 's'));

            $.ajax({
                url: action,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function () {
                    const xhr = new window.XMLHttpRequest();

                    xhr.upload.addEventListener('progress', function (evt) {
                        if (evt.lengthComputable) {
                            const percent = Math.round((evt.loaded / evt.total) * 100);
                            $folderQueueBar.css('width', percent + '%');
                            $folderQueueSub.text('Uploading... ' + percent + '%');
                        }
                    }, false);

                    return xhr;
                },
                success: function () {
                    $folderQueueBar.css('width', '100%');
                    $folderQueueSub.text('Upload complete');
                    setTimeout(() => {
                        closeFolderQueue();
                        window.location.reload();
                    }, 400);
                },
                error: function (xhr) {
                    console.error(xhr);
                    $folderQueueSub.text('Upload failed');
                    $folderQueueBar.css('width', '0%');
                    alert('Failed to upload files to this folder. Please try again.');
                }
            });
        });

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

            list.forEach(function (f, i) {
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

        function openAttachUploadModal(btn) {
            var $btn = $(btn);
            var $row = $btn.closest('tr');

            droppedFiles = [];

            var recordName = $.trim($row.find('td').eq(1).text()) || 'Record';
            var uploadUrl = $btn.data('upload-url');

            var hasFile = String($row.data('has-file')) === '1';
            var existing = $row.data('file-name') || $btn.data('existing-name') || '';

            $uploadTitle.text('Upload Attachments – ' + recordName);
            $uploadForm.attr('action', uploadUrl);

            $uploadFile.val('');
            renderSelectedFiles([]);

            if (hasFile && existing) {
                $existingWrap.removeClass('hidden');
                $existingName.text(existing);
            } else {
                $existingWrap.addClass('hidden');
                $existingName.text('');
            }

            $uploadModal.removeClass('hidden').addClass('flex');
        }

        function closeAttachUploadModal() {
            droppedFiles = [];
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

        function uploadFilesDirect(files) {

            const action = $folderUploadForm.attr('action');
            const token = $folderUploadForm.find('input[name="_token"]').val();

            const docDate = new Date().toISOString().slice(0, 10);
            const description = '';

            let formData = new FormData();
            formData.append('_token', token);
            formData.append('doc_date', docDate);
            formData.append('description', description);

            const allowed = [
                'application/pdf',
                'image/',
                'video/',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ];

            const allowedExtensions = ['.xls', '.xlsx', '.xlsm', '.csv'];
            const MAX = 25 * 1024 * 1024;

            const invalidSizeFiles = [];
            const invalidTypeFiles = [];

            const validFiles = files.filter(f => {

                if (!f.name) return false;

                const isExtensionValid = allowedExtensions.some(ext =>
                    f.name.toLowerCase().endsWith(ext)
                );

                const isTypeValid =
                    (f.type && allowed.some(type => f.type.startsWith(type))) || isExtensionValid;

                if (!isTypeValid) {
                    invalidTypeFiles.push(f.name);
                    return false;
                }

                if (f.size > MAX) {
                    invalidSizeFiles.push(f.name);
                    return false;
                }

                return true;
            });

            // Combined alert (clean UX)
            let messages = [];

            if (invalidSizeFiles.length) {
                messages.push('These files exceed 25MB:\n' + invalidSizeFiles.join('\n'));
            }

            if (invalidTypeFiles.length) {
                messages.push('Unsupported file types:\n' + invalidTypeFiles.join('\n'));
            }

            if (messages.length) {
                alert(messages.join('\n\n'));
            }

            if (!validFiles.length) {
                alert('No valid files to upload.');
                return;
            }

            validFiles.forEach(f => formData.append('files[]', f));

            // Loader
            showFolderQueue(
                'Uploading ' + validFiles.length + ' file' + (validFiles.length === 1 ? '' : 's')
            );

            $.ajax({
                url: action,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function () {
                    const xhr = new XMLHttpRequest();

                    xhr.upload.addEventListener('progress', function (evt) {
                        if (evt.lengthComputable) {
                            const percent = Math.round((evt.loaded / evt.total) * 100);
                            $folderQueueBar.css('width', percent + '%');
                            $folderQueueSub.text('Uploading... ' + percent + '%');
                        }
                    });

                    return xhr;
                },
                success: function () {
                    $folderQueueBar.css('width', '100%');
                    $folderQueueSub.text('Upload complete');

                    setTimeout(() => {
                        closeFolderQueue();
                        window.location.reload();
                    }, 400);
                },
                error: function (xhr) {
                    console.error(xhr);
                    $folderQueueSub.text('Upload failed');
                    alert('Upload failed. Try again.');
                }
            });
        }

        $(document).on('click', '[data-dh-upload]', function (e) {
            e.preventDefault();

            var $btn = $(this);
            var $row = $btn.closest('tr');

            var hasFile = String($row.data('has-file')) === '1';
            var existing = $row.data('file-name') || $btn.data('existing-name') || '';

            if (hasFile && existing) {
                pendingUploadBtn = this;
                openReplaceModal(existing);
            } else {
                openAttachUploadModal(this);
            }
        });

        $(document).on('click', '[data-dh-replace-cancel]', function (e) {
            e.preventDefault();
            pendingUploadBtn = null;
            closeReplaceModal();
        });

        $('#dh-replace-confirm').on('click', function (e) {
            e.preventDefault();
            if (!pendingUploadBtn) return;
            closeReplaceModal();
            openAttachUploadModal(pendingUploadBtn);
            pendingUploadBtn = null;
        });

        $(document).on('click', '[data-dh-upload-close]', function (e) {
            e.preventDefault();
            closeAttachUploadModal();
        });

        $uploadModal.on('click', function (e) {
            if (e.target === this) {
                closeAttachUploadModal();
            }
        });

        $uploadFile.on('change', function () {
            droppedFiles = [];
            const files = this.files ? Array.from(this.files) : [];
            renderSelectedFiles(files);
        });

        if ($dropzone.length) {
            $dropzone.on('dragenter dragover', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $dropzone.addClass('ring-1 ring-sky-500 bg-slate-900/80');
            });

            $dropzone.on('dragleave dragend', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $dropzone.removeClass('ring-1 ring-sky-500 bg-slate-900/80');
            });

            $dropzone.on('drop', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $dropzone.removeClass('ring-1 ring-sky-500 bg-slate-900/80');

                var dt = e.originalEvent.dataTransfer;
                if (!dt || !dt.files || !dt.files.length) return;

                droppedFiles = Array.from(dt.files || []);
                $uploadFile.val('');
                renderSelectedFiles(droppedFiles);
            });
        }

        // ==============================
        // GLOBAL DROP (FINAL STABLE)
        // ==============================
        var $mainDropArea = $('#dh-main-drop-area');

        if ($mainDropArea.length) {

            let dragCounter = 0;

            $mainDropArea.on('dragenter', function (e) {
                e.preventDefault();
                e.stopPropagation();

                dragCounter++;

                $(this).addClass('dh-drop-active ring-2 ring-sky-400 bg-sky-50');
            });

            $mainDropArea.on('dragleave', function (e) {
                e.preventDefault();
                e.stopPropagation();

                dragCounter--;

                if (dragCounter <= 0) {
                    dragCounter = 0;

                    $(this).removeClass('dh-drop-active ring-2 ring-sky-400 bg-sky-50');
                }
            });

            $mainDropArea.on('drop', function (e) {
                e.preventDefault();
                e.stopPropagation();

                dragCounter = 0;

                $(this).removeClass('dh-drop-active ring-2 ring-sky-400 bg-sky-50');

                const files = Array.from(e.originalEvent.dataTransfer.files || []);
                if (!files.length) return;

                uploadFilesDirect(files);
            });
        }

        $uploadForm.on('submit', function (e) {
            if (droppedFiles && droppedFiles.length) {
                e.preventDefault();

                var action = $uploadForm.attr('action');
                var token = $uploadForm.find('input[name="_token"]').val();

                var formData = new FormData();
                formData.append('_token', token);

                droppedFiles.forEach(function (f) {
                    formData.append('files[]', f);
                });

                $.ajax({
                    url: action,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function () {
                        closeAttachUploadModal();
                        window.location.reload();
                    },
                    error: function (xhr) {
                        console.error(xhr);
                        alert('Failed to upload files. Please try again.');
                    }
                });

                return;
            }

            return true;
        });



        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') {
                closeAttachUploadModal();
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

        $(document).on('click', '[data-dh-delete]', function (e) {
            e.preventDefault();

            var $btn = $(this);
            var $row = $btn.closest('tr');

            var hasFile = String($row.data('has-file')) === '1';
            var name = $btn.data('record-name') || 'this record';
            var url = $btn.data('delete-url') || '';

            if (!url) {
                return;
            }

            if (!hasFile) {
                $deleteForm.attr('action', url);
                $deleteForm.trigger('submit');
                return;
            }

            openDeleteModal(name, url);
        });

        $(document).on('click', '[data-dh-delete-cancel]', function (e) {
            e.preventDefault();
            closeDeleteModal();
        });

        $('#dh-delete-confirm').on('click', function (e) {
            e.preventDefault();
            if (!pendingDeleteUrl) {
                closeDeleteModal();
                return;
            }

            $deleteForm.attr('action', pendingDeleteUrl);
            $deleteForm.trigger('submit');
        });

        $deleteModal.on('click', function (e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
                closeViewerModal();
            }
        });
    }

    if (!isAdmin && !canUpload) {
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') closeViewerModal();
        });
    }

    if (canManage) {
        $(document).on('click', '[data-file-rename]', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const $ctx = getFileContext(this);
            pendingFileRenameUrl = $ctx.data('rename-url') || '';
            $fileRenameInput.val($ctx.data('file-title') || '');
            closeFileMenus();
            $fileRenameModal.removeClass('hidden').addClass('flex');
            setTimeout(() => $fileRenameInput.trigger('focus'), 50);
        });

        $(document).on('click', '[data-file-edit-description]', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const $ctx = getFileContext(this);
            if (!$ctx.length) return;

            const currentDescription = String($ctx.attr('data-file-description') || '').trim();
            const descriptionUrl =
                $(this).data('description-url') ||
                $ctx.attr('data-description-url') ||
                '';

            const fileTitle = $ctx.attr('data-file-title') || 'File';

            if (!descriptionUrl) {
                alert('Description update URL not found.');
                return;
            }

            closeFileMenus();
            openDescriptionModal($ctx, descriptionUrl, currentDescription, fileTitle);
        });

        $(document).on('click', '[data-file-move]', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const $ctx = getFileContext(this);
            pendingFileMoveUrl = $ctx.data('move-url') || '';

            selectedMoveFolderId = '';
            $fileMoveFolderId.val('');
            moveTreeState.term = '';
            moveTreeState.expanded = {};

            closeFileMenus();
            $fileMoveSearch.val('');
            renderFileMoveTree();
            $fileMoveModal.removeClass('hidden').addClass('flex');

            setTimeout(function () {
                $fileMoveSearch.trigger('focus');
            }, 60);
        });

        $(document).on('click', '[data-file-trash]', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const $ctx = getFileContext(this);
            const title = $ctx.data('file-title') || 'this file';

            pendingFileTrashUrl = $ctx.data('trash-url') || '';
            $fileTrashText.text(`You are about to move "${title}" to trash.`);
            closeFileMenus();
            $fileTrashModal.removeClass('hidden').addClass('flex');
        });

        $(document).on('click', '[data-folder-rename]', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const $ctx = getFolderContext(this);
            pendingFolderRenameUrl = $ctx.data('folder-rename-url') || '';
            $folderRenameInput.val($ctx.data('folder-title') || '');
            closeFileMenus();
            $folderRenameModal.removeClass('hidden').addClass('flex');
            setTimeout(() => $folderRenameInput.trigger('focus'), 50);
        });

        $(document).on('click', '[data-folder-trash]', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const $ctx = getFolderContext(this);
            const title = $ctx.data('folder-title') || 'this folder';

            pendingFolderTrashUrl = $ctx.data('folder-trash-url') || '';
            $folderTrashText.text(`You are about to move "${title}" to trash.`);
            closeFileMenus();
            $folderTrashModal.removeClass('hidden').addClass('flex');
        });

        $(document).off('click.dhDel').on('click.dhDel', '.dh-del', function () {
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
                .done(function (res) {
                    if (!res || !res.ok) return;

                    $(`.dh-file-item[data-att-id="${id}"]`).remove();
                    $(`[data-file-card][data-att-id="${id}"]`).remove();
                    $(`[data-file-row][data-att-id="${id}"]`).remove();

                    closeViewerModal();

                    if (!$('[data-file-card]').length && !$('[data-file-row]').length) {
                        window.location.reload();
                    }
                })
                .fail(function (xhr) {
                    console.error(xhr);
                    alert('Failed to delete file.');
                });
        });
    }

    const $gridBtn = $('#dh-grid-view-btn');
    const $listBtn = $('#dh-list-view-btn');
    const $gridWrap = $('#dh-grid-wrap');
    const $listWrap = $('#dh-list-wrap');

    const $foldersGridWrap = $('#dh-folders-grid-wrap');
    const $foldersListWrap = $('#dh-folders-list-wrap');

    function setFileView(view) {
        if (view === 'list') {
            $gridWrap.addClass('hidden');
            $listWrap.removeClass('hidden');

            if ($foldersGridWrap.length) $foldersGridWrap.addClass('hidden');
            if ($foldersListWrap.length) $foldersListWrap.removeClass('hidden');

            $gridBtn.removeClass('is-active');
            $listBtn.addClass('is-active');
        } else {
            $listWrap.addClass('hidden');
            $gridWrap.removeClass('hidden');

            if ($foldersListWrap.length) $foldersListWrap.addClass('hidden');
            if ($foldersGridWrap.length) $foldersGridWrap.removeClass('hidden');

            $listBtn.removeClass('is-active');
            $gridBtn.addClass('is-active');
        }

        localStorage.setItem('dhFileView', view);
    }

    $gridBtn.on('click', function () {
        setFileView('grid');
    });

    $listBtn.on('click', function () {
        setFileView('list');
    });

    setFileView(localStorage.getItem('dhFileView') || 'grid');

    let activeFileMenu = null;
    let activeFileTrigger = null;

    function moveMenuToBody($menu, $anchor, $context) {
        if (!$menu || !$menu.length) return;

        if (!$menu.data('menu-home')) {
            $menu.data('menu-home', $anchor[0]);
        }

        if ($context && $context.length) {
            $menu.data('menu-context', $context[0]);
        }

        if (!$menu.parent().is('body')) {
            $('body').append($menu);
        }
    }

    function restoreMenuHome($menu) {
        if (!$menu || !$menu.length) return;

        const home = $menu.data('menu-home');
        if (home && !$menu.parent().is($(home))) {
            $(home).append($menu);
        }

        $menu.removeData('menu-context');
    }

    function closeFileMenus() {
        $('[data-file-menu], [data-folder-menu]').each(function () {
            const $menu = $(this);

            $menu.addClass('hidden').css({
                top: '',
                left: '',
                right: '',
                bottom: '',
                visibility: '',
                pointerEvents: '',
                zIndex: ''
            });

            restoreMenuHome($menu);
        });

        $('.dh-drive-file-card').removeClass('menu-open');
        $('.dh-list-row-menu-open').removeClass('dh-list-row-menu-open');
        $('.dh-menu-open-cell').removeClass('dh-menu-open-cell');
        $('.dh-menu-open-anchor').removeClass('dh-menu-open-anchor');
        $('#dh-folders-box, #dh-files-box').removeClass('dh-box-menu-open');

        activeFileMenu = null;
        activeFileTrigger = null;
    }

    function positionFileMenu($trigger, $menu) {
        if (!$trigger.length || !$menu.length) return;

        const gap = 8;
        const pad = 10;
        const triggerRect = $trigger[0].getBoundingClientRect();

        $menu.css({
            position: 'fixed',
            top: '0px',
            left: '0px',
            right: 'auto',
            bottom: 'auto',
            visibility: 'hidden',
            pointerEvents: 'none'
        });

        const menuRect = $menu[0].getBoundingClientRect();
        const menuWidth = menuRect.width;
        const menuHeight = menuRect.height;
        const vw = window.innerWidth;
        const vh = window.innerHeight;

        // Default: open directly below button, right-aligned to button
        let left = triggerRect.right - menuWidth;
        let top = triggerRect.bottom + gap;

        // Clamp horizontally
        if (left < pad) left = pad;
        if (left + menuWidth > vw - pad) {
            left = vw - menuWidth - pad;
        }

        // If no space below, open upward
        if (top + menuHeight > vh - pad) {
            top = triggerRect.top - menuHeight - gap;
        }

        // Final vertical clamp
        if (top < pad) top = pad;
        if (top + menuHeight > vh - pad) {
            top = Math.max(pad, vh - menuHeight - pad);
        }

        $menu.css({
            left: `${left}px`,
            top: `${top}px`,
            right: 'auto',
            bottom: 'auto',
            visibility: '',
            pointerEvents: ''
        });
    }

    function openListAnchorMenu($anchor, $menu) {
        if (!$anchor.length || !$menu.length) return;

        restoreMenuHome($menu);

        const $cell = $anchor.closest('.dh-list-action-cell');
        const $row = $anchor.closest('tr');
        const $box = $anchor.closest('#dh-folders-box, #dh-files-box');

        $('.dh-list-row-menu-open').removeClass('dh-list-row-menu-open');
        $('.dh-menu-open-cell').removeClass('dh-menu-open-cell');
        $('.dh-menu-open-anchor').removeClass('dh-menu-open-anchor');
        $('#dh-folders-box, #dh-files-box').removeClass('dh-box-menu-open');

        $row.addClass('dh-list-row-menu-open');
        $cell.addClass('dh-menu-open-cell');
        $anchor.addClass('dh-menu-open-anchor');

        if ($box.length) {
            $box.addClass('dh-box-menu-open');
        }

        $menu.removeClass('hidden').css({
            position: 'absolute',
            top: 'calc(100% + 8px)',
            right: '0',
            left: 'auto',
            bottom: 'auto',
            visibility: '',
            pointerEvents: '',
            zIndex: 260
        });

        activeFileMenu = $menu;
        activeFileTrigger = $anchor;
    }

    function getFolderContext(el) {
        const $direct = $(el).closest('[data-folder-card], [data-folder-row]');
        if ($direct.length) return $direct;

        const $menu = $(el).closest('[data-folder-menu]');
        if ($menu.length) {
            const ctx = $menu.data('menu-context');
            if (ctx) {
                return $(ctx);
            }
        }

        return $();
    }

    function getFileContext(el) {
        const $direct = $(el).closest('[data-file-card], [data-file-row]');
        if ($direct.length) return $direct;

        const $menu = $(el).closest('[data-file-menu]');
        if ($menu.length) {
            const ctx = $menu.data('menu-context');
            if (ctx) {
                return $(ctx);
            }
        }

        return $();
    }

    $(document).on('click', '[data-folder-menu-toggle]', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $btn = $(this);
        const $anchor = $btn.closest('.dh-file-menu-anchor, .dh-list-action-anchor');
        const $menu = $anchor.find('[data-folder-menu]').first();
        const $context = $btn.closest('[data-folder-card], [data-folder-row]');
        const isListAnchor = $anchor.hasClass('dh-list-action-anchor');

        const isSameMenuOpen =
            activeFileMenu &&
            activeFileMenu.length &&
            $menu[0] === activeFileMenu[0] &&
            !$menu.hasClass('hidden');

        closeFileMenus();

        if (isSameMenuOpen) return;
        if (!$menu.length) return;

        if (isListAnchor) {
            openListAnchorMenu($anchor, $menu);
            return;
        }

        moveMenuToBody($menu, $anchor, $context);
        $menu.removeClass('hidden');

        requestAnimationFrame(() => {
            positionFileMenu($btn, $menu);
            activeFileMenu = $menu;
            activeFileTrigger = $btn;
        });
    });

    $(document).on('click', '[data-file-menu-toggle]', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $btn = $(this);
        const $anchor = $btn.closest('.dh-file-menu-anchor, .dh-list-action-anchor');
        const $menu = $anchor.find('[data-file-menu]').first();
        const $context = $btn.closest('[data-file-card], [data-file-row]');
        const $card = $btn.closest('.dh-drive-file-card');
        const isListAnchor = $anchor.hasClass('dh-list-action-anchor');

        const isSameMenuOpen =
            activeFileMenu &&
            activeFileMenu.length &&
            $menu[0] === activeFileMenu[0] &&
            !$menu.hasClass('hidden');

        closeFileMenus();

        if (isSameMenuOpen) return;
        if (!$menu.length) return;

        if (isListAnchor) {
            openListAnchorMenu($anchor, $menu);
            return;
        }

        if ($card.length) {
            $card.addClass('menu-open');
        }

        moveMenuToBody($menu, $anchor, $context);
        $menu.removeClass('hidden');

        requestAnimationFrame(() => {
            positionFileMenu($btn, $menu);
            activeFileMenu = $menu;
            activeFileTrigger = $btn;
        });
    });

    $(document).on('click', function (e) {
        if (
            !$(e.target).closest(
                '[data-file-menu], [data-file-menu-toggle], [data-folder-menu], [data-folder-menu-toggle], .dh-list-action-cell, .dh-list-action-anchor'
            ).length
        ) {
            closeFileMenus();
        }
    });

    $(document).on('click', '.dh-list-action-cell, .dh-list-action-anchor, [data-file-menu], [data-folder-menu]', function (e) {
        e.stopPropagation();
    });

    $(window).on('resize scroll', function () {
        if (!activeFileMenu || !activeFileTrigger || !activeFileMenu.length || !activeFileTrigger.length) return;

        if (activeFileTrigger.hasClass('dh-list-action-anchor')) {
            return;
        }

        positionFileMenu(activeFileTrigger, activeFileMenu);
    });

    $(document).on('click', '[data-folder-card]', function (e) {
        if ($(e.target).closest('button, a').length) return;

        const url = $(this).data('folder-url');
        if (!url) return;

        window.location.href = url;
    });

    const $folderRenameModal = $('#dh-folder-rename-modal');
    const $folderRenameForm = $('#dh-folder-rename-form');
    const $folderRenameInput = $('#dh-folder-rename-input');
    const $folderRenameName = $('#dh-folder-rename-name');

    let pendingFolderRenameUrl = '';

    function closeFolderRenameModal() {
        pendingFolderRenameUrl = '';
        $folderRenameInput.val('');
        $folderRenameModal.removeClass('flex').addClass('hidden');
    }



    $(document).on('click', '[data-dh-folder-rename-close]', function (e) {
        e.preventDefault();
        closeFolderRenameModal();
    });

    $folderRenameModal.on('click', function (e) {
        if (e.target === this) closeFolderRenameModal();
    });

    $('#dh-folder-rename-confirm').on('click', function (e) {
        e.preventDefault();

        const newName = ($folderRenameInput.val() || '').trim();
        if (!newName || !pendingFolderRenameUrl) return;

        $folderRenameForm.attr('action', pendingFolderRenameUrl);
        $folderRenameName.val(newName);
        closeFolderRenameModal();
        $folderRenameForm.trigger('submit');
    });

    const $folderTrashModal = $('#dh-folder-trash-modal');
    const $folderTrashForm = $('#dh-folder-trash-form');
    const $folderTrashText = $('#dh-folder-trash-text');

    let pendingFolderTrashUrl = '';

    function closeFolderTrashModal() {
        pendingFolderTrashUrl = '';
        $folderTrashModal.removeClass('flex').addClass('hidden');
    }



    $(document).on('click', '[data-dh-folder-trash-close]', function (e) {
        e.preventDefault();
        closeFolderTrashModal();
    });

    $folderTrashModal.on('click', function (e) {
        if (e.target === this) closeFolderTrashModal();
    });

    $('#dh-folder-trash-confirm').on('click', function (e) {
        e.preventDefault();
        if (!pendingFolderTrashUrl) return;

        $folderTrashForm.attr('action', pendingFolderTrashUrl);
        closeFolderTrashModal();
        $folderTrashForm.trigger('submit');
    });

    const $folderInfoModal = $('#dh-folder-info-modal');

    function closeFolderInfoModal() {
        $folderInfoModal.removeClass('flex').addClass('hidden');
    }

    $(document).on('click', '[data-folder-info]', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $ctx = getFolderContext(this);

        $('#dh-folder-info-name').text($ctx.data('folder-title') || '—');
        $('#dh-folder-info-date').text($ctx.data('folder-created') || '—');
        $('#dh-folder-info-remarks').text($ctx.data('folder-remarks') || '—');

        closeFileMenus();
        $folderInfoModal.removeClass('hidden').addClass('flex');
    });

    $(document).on('click', '[data-dh-folder-info-close]', function (e) {
        e.preventDefault();
        closeFolderInfoModal();
    });

    $folderInfoModal.on('click', function (e) {
        if (e.target === this) closeFolderInfoModal();
    });

    const $descriptionModal = $('#dh-description-modal');
    const $descriptionInput = $('#dh-description-input');
    const $descriptionFileName = $('#dh-description-file-name');
    const $descriptionSave = $('#dh-description-save');
    const $descriptionClose = $('#dh-description-close');
    const $descriptionCancel = $('#dh-description-cancel');

    let activeDescriptionContext = null;
    let activeDescriptionUrl = '';

    function openDescriptionModal($ctx, descriptionUrl, currentDescription, fileTitle) {
        activeDescriptionContext = $ctx;
        activeDescriptionUrl = descriptionUrl || '';

        $descriptionInput.val(currentDescription || '');
        $descriptionFileName.text(fileTitle ? ('File: ' + fileTitle) : '');
        $descriptionModal.removeClass('hidden').addClass('flex');

        setTimeout(() => {
            $descriptionInput.trigger('focus');
        }, 30);
    }

    function closeDescriptionModal() {
        $descriptionModal.removeClass('flex').addClass('hidden');
        $descriptionInput.val('');
        $descriptionFileName.text('');
        activeDescriptionContext = null;
        activeDescriptionUrl = '';
    }



    $descriptionClose.on('click', function () {
        closeDescriptionModal();
    });

    $descriptionCancel.on('click', function () {
        closeDescriptionModal();
    });

    $descriptionModal.on('click', function (e) {
        if (e.target === this) {
            closeDescriptionModal();
        }
    });

    $descriptionSave.on('click', function () {
        if (!activeDescriptionContext || !activeDescriptionUrl) {
            closeDescriptionModal();
            return;
        }

        const newDescription = String($descriptionInput.val() || '');

        $.ajax({
            url: activeDescriptionUrl,
            method: 'PATCH',
            data: {
                description: newDescription,
                _token: $('meta[name="csrf-token"]').attr('content')
            }
        }).done(function (res) {
            if (!res || !res.ok) {
                alert('Failed to update description.');
                return;
            }

            const cleanValue = String(res.description || '').trim();
            const $ctx = activeDescriptionContext;

            if (!$ctx || !$ctx.length) {
                closeDescriptionModal();
                return;
            }

            const attId = String($ctx.attr('data-att-id') || '').trim();
            if (!attId) {
                closeDescriptionModal();
                return;
            }

            // update both grid card + list row with same attachment id
            const $allTargets = $(
                `[data-file-card][data-att-id="${attId}"], [data-file-row][data-att-id="${attId}"]`
            );

            $allTargets.each(function () {
                const $target = $(this);

                $target.attr('data-file-description', cleanValue);
                $target.data('file-description', cleanValue);

                let $text = $target.find('[data-file-description-text]').first();
                const $holder = $target.find('[data-file-description-holder]').first();

                if (cleanValue) {
                    if ($text.length) {
                        $text
                            .removeClass('text-slate-400 italic')
                            .addClass($target.is('[data-file-row]') ? 'text-slate-500' : 'text-slate-500')
                            .text(cleanValue);
                    } else if ($holder.length) {
                        const textClass = $target.is('[data-file-row]')
                            ? 'text-[11px] text-slate-500 break-words'
                            : 'text-[12px] text-slate-500';

                        $holder.html(`<p class="${textClass}" data-file-description-text></p>`);
                        $holder.find('[data-file-description-text]').text(cleanValue);
                    }
                } else {
                    const emptyClass = $target.is('[data-file-row]')
                        ? 'text-[11px] text-slate-400 italic'
                        : 'text-[12px] text-slate-400 italic';

                    if ($holder.length) {
                        $holder.html(`<p class="${emptyClass}" data-file-description-text>No description</p>`);
                    }
                }
            });

            closeDescriptionModal();
        }).fail(function (xhr) {
            console.error(xhr);
            alert('Failed to update description.');
        });
    });

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && $descriptionModal.hasClass('flex')) {
            closeDescriptionModal();
        }
    });

    $(document).on('click', '[data-file-preview]', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $ctx = getFileContext(this);
        if (!$ctx.length) return;

        closeFileMenus();
        openSingleFileViewer($ctx);
    });

    $('#dh-file-search').on('input', function () {
        const term = ($(this).val() || '').trim().toLowerCase();

        $('[data-folder-card], [data-file-card], [data-folder-row], [data-file-row]').each(function () {
            const folderName = String($(this).data('folder-name') || '').toLowerCase();
            const fileName = String($(this).data('file-name') || '').toLowerCase();
            const name = folderName || fileName;

            $(this).toggleClass('hidden', !!term && !name.includes(term));
        });
    });

    $(document).on('click', '[data-folder-row]', function (e) {
        if ($(e.target).closest('button, a, .dh-list-action-cell, .dh-list-action-anchor, [data-folder-menu], [data-folder-menu-toggle]').length) return;

        const url = $(this).data('folder-url');
        if (!url) return;

        window.location.href = url;
    });

    const $fileRenameModal = $('#dh-file-rename-modal');
    const $fileRenameForm = $('#dh-file-rename-form');
    const $fileRenameInput = $('#dh-file-rename-input');
    const $fileRenameName = $('#dh-file-rename-name');

    let pendingFileRenameUrl = '';

    function closeFileRenameModal() {
        pendingFileRenameUrl = '';
        $fileRenameInput.val('');
        $fileRenameModal.removeClass('flex').addClass('hidden');
    }



    $(document).on('click', '[data-dh-file-rename-close]', function (e) {
        e.preventDefault();
        closeFileRenameModal();
    });

    $fileRenameModal.on('click', function (e) {
        if (e.target === this) closeFileRenameModal();
    });

    $('#dh-file-rename-confirm').on('click', function (e) {
        e.preventDefault();

        const newName = ($fileRenameInput.val() || '').trim();
        if (!newName || !pendingFileRenameUrl) return;

        $fileRenameForm.attr('action', pendingFileRenameUrl);
        $fileRenameName.val(newName);
        closeFileRenameModal();
        $fileRenameForm.trigger('submit');
    });

    $(document).on('click', '[data-file-share]', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $ctx = getFileContext(this);
        const shareUrl = $ctx.data('share-url');

        if (!shareUrl) return;

        closeFileMenus();

        $.ajax({
            url: shareUrl,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        }).done(function (res) {
            if (!res || !res.share_url) {
                alert('Share link was not returned.');
                return;
            }

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(res.share_url)
                    .then(() => alert('Share link copied'))
                    .catch(() => alert(res.share_url));
            } else {
                alert(res.share_url);
            }
        }).fail(function (xhr) {
            console.error(xhr);
            alert('Failed to generate share link.');
        });
    });

    const $fileMoveModal = $('#dh-file-move-modal');
    const $fileMoveForm = $('#dh-file-move-form');
    const $fileMoveFolderId = $('#dh-file-move-folder-id');
    const $fileMoveSearch = $('#dh-file-move-search');
    const $fileMoveTree = $('#dh-file-move-tree');
    const $fileMoveEmpty = $('#dh-file-move-empty');
    const $fileMoveSelectedLabel = $('#dh-file-move-selected-label');
    const $fileMoveClear = $('#dh-file-move-clear');

    let pendingFileMoveUrl = '';
    let selectedMoveFolderId = '';
    let moveTreeData = [];
    let moveTreeState = {
        expanded: {},
        term: ''
    };

    function getMoveTreeData() {
        if (!$fileMoveTree.length) return [];

        const raw = $fileMoveTree.attr('data-folder-tree') || '[]';

        try {
            return JSON.parse(raw);
        } catch (err) {
            console.error('Invalid move tree data:', err);
            return [];
        }
    }

    function buildMoveTree(items) {
        const byParent = {};
        const byId = {};

        (items || []).forEach(function (item) {
            const id = String(item.id);
            const parentId = item.parent_id == null ? 'root' : String(item.parent_id);

            byId[id] = item;

            if (!byParent[parentId]) byParent[parentId] = [];
            byParent[parentId].push(item);
        });

        Object.keys(byParent).forEach(function (key) {
            byParent[key].sort(function (a, b) {
                return String(a.name || '').localeCompare(String(b.name || ''));
            });
        });

        return { byParent, byId };
    }

    function getMoveDescendantIds(folderId, treeMap) {
        const ids = [];
        const children = (treeMap.byParent[String(folderId)] || []);

        children.forEach(function (child) {
            ids.push(String(child.id));
            ids.push.apply(ids, getMoveDescendantIds(child.id, treeMap));
        });

        return ids;
    }

    function folderMatchesMoveSearch(folder, treeMap, term) {
        if (!term) return true;

        const own = String(folder.name || '').toLowerCase().includes(term);
        if (own) return true;

        const children = treeMap.byParent[String(folder.id)] || [];
        return children.some(function (child) {
            return folderMatchesMoveSearch(child, treeMap, term);
        });
    }

    function escapeMoveHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderMoveTreeNodes(parentId, level, treeMap, blockedIds) {
        const children = treeMap.byParent[parentId] || [];
        const term = moveTreeState.term;
        let html = '';

        children.forEach(function (folder) {
            if (!folderMatchesMoveSearch(folder, treeMap, term)) return;

            const id = String(folder.id);
            const childList = treeMap.byParent[id] || [];
            const hasChildren = childList.length > 0;
            const isExpanded = term ? true : !!moveTreeState.expanded[id];
            const isSelected = selectedMoveFolderId === id;
            const isBlocked = blockedIds.has(id);

            html += `
            <div class="dh-move-tree-node ${isSelected ? 'is-selected' : ''} ${isBlocked ? 'is-disabled' : ''}" data-move-node="${id}">
                <div class="flex items-center gap-2">
                    ${hasChildren ? `
                        <button type="button"
                            class="dh-move-tree-toggle"
                            data-move-toggle="${id}"
                            aria-label="${isExpanded ? 'Collapse' : 'Expand'}">
                            <i class="fa-solid ${isExpanded ? 'fa-chevron-down' : 'fa-chevron-right'} text-[10px]"></i>
                        </button>
                    ` : `<span class="dh-move-tree-spacer"></span>`}

                    <button type="button"
                        class="dh-move-tree-btn"
                        data-move-pick="${id}"
                        ${isBlocked ? 'disabled' : ''}>
                        <span class="dh-move-tree-left">
                            <i class="fa-solid fa-folder text-sky-500 text-[13px] shrink-0"></i>
                            <span class="min-w-0">
                                <span class="dh-move-tree-title block">${escapeMoveHtml(folder.name)}</span>
                                <span class="dh-move-tree-meta block">
                                    ${level === 0 ? 'Main folder' : 'Subfolder'}
                                    ${folder.is_current ? ' • Current folder' : ''}
                                </span>
                            </span>
                        </span>

                        <span class="dh-move-tree-check">
                            <i class="fa-solid fa-check text-[10px]"></i>
                        </span>
                    </button>
                </div>

                ${hasChildren && isExpanded ? `
                    <div class="dh-move-tree-children mt-2">
                        ${renderMoveTreeNodes(id, level + 1, treeMap, blockedIds)}
                    </div>
                ` : ''}
            </div>
        `;
        });

        return html;
    }

    function renderFileMoveTree() {
        moveTreeData = getMoveTreeData();

        const treeMap = buildMoveTree(moveTreeData);
        const blockedIds = new Set();

        const currentFolderNode = moveTreeData.find(function (item) {
            return !!item.is_current;
        });

        if (currentFolderNode) {
            blockedIds.add(String(currentFolderNode.id));

            getMoveDescendantIds(currentFolderNode.id, treeMap).forEach(function (id) {
                blockedIds.add(String(id));
            });
        }

        const html = renderMoveTreeNodes('root', 0, treeMap, blockedIds);

        $fileMoveTree.html(html);
        $fileMoveEmpty.toggleClass('hidden', !!html.trim());

        const selectedItem = moveTreeData.find(function (item) {
            return String(item.id) === String(selectedMoveFolderId);
        });

        if (selectedItem) {
            $fileMoveSelectedLabel
                .removeClass('text-slate-400 italic')
                .addClass('text-slate-700 not-italic')
                .text(selectedItem.name);
            $fileMoveClear.removeClass('hidden');
        } else {
            $fileMoveSelectedLabel
                .removeClass('text-slate-700 not-italic')
                .addClass('text-slate-400 italic')
                .text('No folder selected');
            $fileMoveClear.addClass('hidden');
        }
    }

    function openParentsForMoveItem(folderId) {
        const items = getMoveTreeData();
        const byId = {};

        items.forEach(function (item) {
            byId[String(item.id)] = item;
        });

        let current = byId[String(folderId)];

        while (current && current.parent_id != null) {
            moveTreeState.expanded[String(current.parent_id)] = true;
            current = byId[String(current.parent_id)];
        }
    }

    function closeFileMoveModal(options) {
        const opts = options || {};
        const preserveFormValue = !!opts.preserveFormValue;

        pendingFileMoveUrl = '';
        selectedMoveFolderId = '';
        moveTreeState.term = '';
        moveTreeState.expanded = {};
        $fileMoveSearch.val('');

        if (!preserveFormValue) {
            $fileMoveFolderId.val('');
        }

        $fileMoveModal.removeClass('flex').addClass('hidden');
        $fileMoveTree.empty();
        $fileMoveEmpty.addClass('hidden');
        $fileMoveSelectedLabel
            .removeClass('text-slate-700 not-italic')
            .addClass('text-slate-400 italic')
            .text('No folder selected');
        $fileMoveClear.addClass('hidden');
    }

    $(document).on('click', '[data-dh-file-move-close]', function (e) {
        e.preventDefault();
        closeFileMoveModal();
    });

    $fileMoveModal.on('click', function (e) {
        if (e.target === this) closeFileMoveModal();
    });

    $(document).on('click', '[data-move-toggle]', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const id = String($(this).attr('data-move-toggle'));
        moveTreeState.expanded[id] = !moveTreeState.expanded[id];
        renderFileMoveTree();
    });

    $(document).on('click', '[data-move-pick]', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $btn = $(this);
        if ($btn.is(':disabled')) return;

        selectedMoveFolderId = String($btn.attr('data-move-pick') || '');
        $fileMoveFolderId.val(selectedMoveFolderId);
        openParentsForMoveItem(selectedMoveFolderId);
        renderFileMoveTree();
    });

    $fileMoveSearch.on('input', function () {
        moveTreeState.term = String($(this).val() || '').trim().toLowerCase();
        renderFileMoveTree();
    });

    $fileMoveClear.on('click', function (e) {
        e.preventDefault();
        selectedMoveFolderId = '';
        $fileMoveFolderId.val('');
        renderFileMoveTree();
    });

    $('#dh-file-move-confirm').on('click', function (e) {
        e.preventDefault();

        if (!selectedMoveFolderId || !pendingFileMoveUrl) {
            alert('Please select a destination folder.');
            return;
        }

        $fileMoveForm.attr('action', pendingFileMoveUrl);
        $fileMoveFolderId.val(selectedMoveFolderId);

        closeFileMenus();
        closeFileMoveModal({ preserveFormValue: true });
        $fileMoveForm.trigger('submit');
    });

    const $fileTrashModal = $('#dh-file-trash-modal');
    const $fileTrashForm = $('#dh-file-trash-form');
    const $fileTrashText = $('#dh-file-trash-text');

    let pendingFileTrashUrl = '';

    function closeFileTrashModal() {
        pendingFileTrashUrl = '';
        $fileTrashModal.removeClass('flex').addClass('hidden');
    }



    $(document).on('click', '[data-dh-file-trash-close]', function (e) {
        e.preventDefault();
        closeFileTrashModal();
    });

    $fileTrashModal.on('click', function (e) {
        if (e.target === this) closeFileTrashModal();
    });

    $('#dh-file-trash-confirm').on('click', function (e) {
        e.preventDefault();
        if (!pendingFileTrashUrl) return;

        $fileTrashForm.attr('action', pendingFileTrashUrl);
        closeFileTrashModal();
        $fileTrashForm.trigger('submit');
    });

    const $fileInfoModal = $('#dh-file-info-modal');

    function closeFileInfoModal() {
        $fileInfoModal.removeClass('flex').addClass('hidden');
    }

    $(document).on('click', '[data-file-info]', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $ctx = getFileContext(this);

        $('#dh-file-info-name').text($ctx.data('file-title') || '—');
        $('#dh-file-info-type').text($ctx.data('file-type') || '—');
        $('#dh-file-info-size').text($ctx.data('file-size') || '—');
        $('#dh-file-info-date').text($ctx.data('file-created') || '—');
        $('#dh-file-info-description').text($ctx.data('file-description') || '—');

        closeFileMenus();
        $fileInfoModal.removeClass('hidden').addClass('flex');
    });

    $(document).on('click', '[data-dh-file-info-close]', function (e) {
        e.preventDefault();
        closeFileInfoModal();
    });

    $fileInfoModal.on('click', function (e) {
        if (e.target === this) closeFileInfoModal();
    });

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            closeFileMenus();
            closeFileRenameModal();
            closeFileMoveModal();
            closeFileTrashModal();
            closeFileInfoModal();
        }
    });
});