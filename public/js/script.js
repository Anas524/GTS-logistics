let tabTimeout;

$(document).ready(function () {
    let lastScrollTop = 0;
    const scrollThreshold = 5; // Minimum scroll delta to trigger
    const $topBar = $('.top-bar');
    const $header = $('.main-header');
    const $heroSlider = $('.hero-slider');

    // Auto-open login tab when server says so
    if (window.__OPEN_LOGIN__) {
        const $tab = $('#loginTab');
        if ($tab.length) {
            $tab.stop(true, true).fadeIn(250);
            $('html, body').animate({ scrollTop: $tab.offset().top - 100 }, 400);
        }
    }

    $(window).on('scroll', function () {
        let st = $(this).scrollTop();
        const isMobile = window.innerWidth <= 768;

        // Ignore minor scroll movements
        if (Math.abs(st - lastScrollTop) <= scrollThreshold) return;

        if (st <= 0) {
            // Top of page
            if (!isMobile) $topBar.slideDown(200);
            $header.css('top', isMobile ? '0px' : '45px');
            $heroSlider.removeClass('fullscreen shifted');
            $('.header-tab').css('top', isMobile ? '80px' : '95px');
        } else if (st > lastScrollTop) {
            // Scrolling down
            if (!isMobile) $topBar.slideUp(200);
            $header.css('top', '0px');
            $heroSlider.addClass('fullscreen shifted');
            $('.header-tab').css('top', isMobile ? '60px' : '70px');
        } else {
            // Scrolling up
            if (!isMobile) $topBar.slideDown(200);
            $header.css('top', isMobile ? '0px' : '45px');
            $heroSlider.removeClass('fullscreen shifted');
            $('.header-tab').css('top', isMobile ? '80px' : '95px');
        }

        lastScrollTop = st;
    });

    const $headerMenu = $('#inlineMenu');

    $('#menuToggle').click(function () {
        if ($headerMenu.hasClass('show')) {
            $headerMenu.removeClass('show').css({ display: 'none' });
        } else {
            $headerMenu.css({ display: 'flex' });
            setTimeout(() => { $headerMenu.addClass('show'); }, 10);
        }
    });

    // Tablet/Mobile only behavior for header
    if (window.innerWidth <= 1024) {
        const $toggle = $('#menuToggle');

        $toggle.on('click', function (e) {
            e.stopPropagation();
            if ($headerMenu.is(':visible')) {
                $headerMenu.slideUp(200).removeClass('show');
            } else {
                $headerMenu.slideDown(200).addClass('show');
            }
        });

        // Close when clicking outside
        $(document).on('click', function (e) {
            if (
                !$headerMenu.is(e.target) &&
                $headerMenu.has(e.target).length === 0 &&
                !$toggle.is(e.target) &&
                $toggle.has(e.target).length === 0
            ) {
                $headerMenu.slideUp(200).removeClass('show');
            }
        });

        // Close on <a> click AND show header-tab
        $headerMenu.find('a.tab-trigger').on('click', function (e) {
            e.preventDefault(); // prevent default anchor jump

            const tabId = $(this).data('tab');

            // Close the menu
            $headerMenu.slideUp(200).removeClass('show');

            // Hide all header-tabs, then show the selected one
            $('.header-tab').slideUp(0); // instantly hide all
            if (tabId) {
                $('#' + tabId).slideDown(300);
            }

            // Optional: Scroll smoothly to header-tab (for mobile view)
            const $target = $('#' + tabId);
            if ($target.length) {
                $('html, body').animate({
                    scrollTop: $target.offset().top - 120 // adjust offset as needed
                }, 600);
            }
        });
    }

    // Header Tab open on hover (generic, non-login)
    $('.tab-trigger').hover(function () {
        clearTimeout(tabTimeout);

        const tabId = $(this).data('tab');
        // hide others, show the one paired with this trigger
        $('.header-tab').not('#' + tabId).stop(true, true).slideUp(100);
        $('#' + tabId).stop(true, true).slideDown(200);

    }, function () {
        const tabId = $(this).data('tab');
        const $paired = $('#' + tabId);

        // schedule close only for its own paired tab (and never for loginTab)
        tabTimeout = setTimeout(() => {
            if (tabId === 'loginTab') return;                            // leave login to its own logic
            if (!$paired.is(':hover') && !containsFocus($paired)) {
                $paired.slideUp(200);
            }
        }, 500);
    });

    // helper used in multiple places
    function containsFocus($el) {
        const a = document.activeElement;
        return a && $el.get(0).contains(a);
    }

    // keep tab open while hovering over ANY header-tab (including login)
    $('.header-tab').hover(function () {
        clearTimeout(tabTimeout);
    }, function () {
        const $self = $(this);

        // generic tabs can auto-close; loginTab is handled by its dedicated logic
        if ($self.attr('id') === 'loginTab') return;

        tabTimeout = setTimeout(() => {
            if (!$self.is(':hover') && !containsFocus($self)) {
                $self.slideUp(200);
            }
        }, 500);
    });

    $(document).on('click', '.toggle-detail', function () {
        const $clickedCard = $(this).closest('.service-card');
        const $clickedDetail = $clickedCard.find('.service-detail');
        const $section = $('.services-section');
        const $header = $('.section-header');
        const index = parseInt($clickedCard.data('index'));

        const isExpanding = !$(this).hasClass('expanded');

        // Collapse all others
        $('.toggle-detail').not(this).removeClass('expanded').text('Read More');
        $('.service-detail').not($clickedDetail).slideUp(300);

        // Remove background if any
        if (isExpanding) {
            const bg = $clickedCard.data('bg');
            $section.css('background-image', `url(${bg})`).addClass('bg-active');
        } else {
            $section.css('background-image', 'none').removeClass('bg-active');
        }

        // Toggle this card
        $(this).toggleClass('expanded').text(isExpanding ? 'Read Less' : 'Read More');
        $clickedDetail.slideToggle(300);

        // Change text color to white if any is expanded, else reset to black
        const anyExpanded = $('.toggle-detail.expanded').length > 0;
        if (anyExpanded || isExpanding) {
            $header.addClass('white-text');
        } else {
            $header.removeClass('white-text');
        }
    });

    // Header service tab navigation
    $('a[href^="#"]').on('click', function (e) {
        e.preventDefault();
        const target = this.hash;
        const $target = $(target);
        if ($target.length) {
            $('html, body').animate({
                scrollTop: $target.offset().top - 100
            }, 600);
        }
    });

    // Header tab animation
    animateCardsOnScroll();
    $(window).on('scroll', animateCardsOnScroll);

    // --- Login tab: open logic and safe-close logic ---
    (function () {
        const $loginTab = $('#loginTab');
        const $loginTrig = $('[data-tab="loginTab"]');
        let closeTimer = null;

        // Open on hover or click of the trigger
        $loginTrig.on('mouseenter click', function (e) {
            e.preventDefault();
            clearTimeout(closeTimer);
            $loginTab.stop(true, true).fadeIn(200);
        });

        // Keep it open while hovered OR focused
        $loginTab.on('mouseenter focusin', function () {
            clearTimeout(closeTimer);
            $(this).stop(true, true).show(); // ensure visible while interacting
        });

        // When mouse leaves, schedule a close — but only if nothing inside has focus
        $loginTab.on('mouseleave', function () {
            scheduleClose();
        });

        // When focus leaves an input/button, re-check if we should close
        $loginTab.on('focusout', function () {
            // Defer to let focus move to another control inside, if any
            setTimeout(() => {
                if (!isHovering($loginTab) && !containsFocus($loginTab)) {
                    scheduleClose();
                }
            }, 0);
        });

        // Close on real outside clicks/taps only
        $(document).on('mousedown touchstart', function (e) {
            const $t = $(e.target);
            if ($t.closest('#loginTab, [data-tab="loginTab"]').length === 0) {
                clearTimeout(closeTimer);
                $loginTab.fadeOut(150);
            }
        });

        // Optional: close on ESC
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') {
                clearTimeout(closeTimer);
                $loginTab.fadeOut(150);
            }
        });

        function scheduleClose() {
            clearTimeout(closeTimer);
            closeTimer = setTimeout(() => {
                // Only close if not hovered and nothing inside has focus
                if (!isHovering($loginTab) && !containsFocus($loginTab)) {
                    $loginTab.fadeOut(150);
                }
            }, 400); // small grace period feels nicer
        }

        function isHovering($el) {
            return $el.is(':hover');
        }
        function containsFocus($el) {
            const active = document.activeElement;
            return active && $el.get(0).contains(active);
        }
    })();

    $('#adminLoginForm').on('submit', function () {
        $('#loginSubmitBtn').hide();
        $('#loginLoader').show();
    });

    //Whatsapp-chat
    $("#whatsapp-chat").on("click", function () {
        $("#chat-popup").fadeIn();
    });

    $("#start-chat").on("click", function () {
        let phoneNumber = "971523194073";
        let message = encodeURIComponent("Hi, I’d like to get in touch with GTS Logistics & Air Cargo Services. Is someone available to chat?");
        let whatsappURL = `https://wa.me/${phoneNumber}?text=${message}`;

        window.open(whatsappURL, "_blank");
    });

    // Hide chat popup when clicked outside
    $(document).on("click", function (event) {
        if (!$(event.target).closest("#whatsapp-chat, #chat-popup").length) {
            $("#chat-popup").fadeOut();
        }
    });

    $('.faq-answer').hide(); // Ensure all answers are hidden on load

    $('.faq-question').on('click', function () {
        const item = $(this).closest('.faq-item');

        //Slide up all others (optional, if you want only one open at a time)
        $('.faq-item').not(item).removeClass('active').find('.faq-answer').slideUp(200);

        const answer = item.find('.faq-answer');

        if (item.hasClass('active')) {
            answer.stop(true, true).slideUp(200);
            item.removeClass('active');
        } else {
            answer.stop(true, true).slideDown(200);
            item.addClass('active');
        }
    });

    const $passwordInput = $('#password');
    const $eyeIcon = $('#eyeIcon');

    $('#togglePassword').on('click', function () {
        const isPassword = $passwordInput.attr('type') === 'password';

        $passwordInput.attr('type', isPassword ? 'text' : 'password');

        $eyeIcon.attr('data-lucide', isPassword ? 'eye-off' : 'eye');
        lucide.createIcons(); // Re-render with new icon
    });

    // Back-to-top
    const topBtn = document.getElementById('back-to-top');
    if (topBtn) {
        const threshold = 700; // px from top before showing
        const toggleTopBtn = () => {
            if (window.scrollY > threshold) {
                topBtn.classList.add('show');
            } else {
                topBtn.classList.remove('show');
            }
        };

        window.addEventListener('scroll', toggleTopBtn, { passive: true });
        toggleTopBtn(); // set initial state

        topBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ---------- Dashboard: search ----------
    const $dashSearch = $('.dash-searchbar input');
    const $cards = $('#admin-dashboard .tool-card');

    $dashSearch.on('input', function () {
        const q = $(this).val().trim().toLowerCase();
        if (!q) { $cards.show(); return; }
        $cards.each(function () {
            const name = ($(this).data('tool-name') || '').toLowerCase();
            const desc = ($(this).data('tool-desc') || '').toLowerCase();
            $(this).toggle(name.includes(q) || desc.includes(q));
        });
    });

    // ---------- Dashboard: gear popover + reset password modal ----------
    const $gearBtn = $('.dash-icon-btn');                  // your gear button
    const $dashMenu = $('.dash-settings-popover');
    const $resetModal = $('#resetPassModal');

    $gearBtn.on('click', function (e) {
        e.stopPropagation();
        $dashMenu.toggle();   // simple toggle
    });

    // close popover on outside click
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.dash-settings-popover, .dash-icon-btn').length) {
            $dashMenu.hide();
        }
    });

    $('#btn-open-reset').off('click').on('click', openResetModal);
    $('#resetCancel').off('click').on('click', closeResetModal);

    // click on overlay to close
    $(document).on('click', function (e) {
        if ($(e.target).is('#resetPassModal')) closeResetModal();
    });

    // ESC to close
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && $('#resetPassModal').hasClass('show')) closeResetModal();
    });

    // submit with AJAX (optional). If you prefer full POST, remove this block.
    $('#resetPassForm').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function () {
                alert('Password updated.');
                $form[0].reset();
                $resetModal.removeClass('show');
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.message || 'Update failed. Check your current password.';
                alert(msg);
            }
        });
    });

    $cards.each(function () {
        const iso = $(this).data('last-updated');
        const txt = timeAgo(iso);
        $(this).find('.tool-updated span').text(txt);
    });

    // when "Open" clicked — record and go
    $('#admin-dashboard').on('click', '.tool-actions .btn-primary-dark', function (e) {
        // let users open in new tab/window with ctrl/cmd/middle-click
        if (e.which === 2 || e.ctrlKey || e.metaKey) return;

        const $a = $(this);
        const $card = $a.closest('.tool-card');
        const id = $card.data('tool-id');

        // Prefer the anchor's href; fallback to data-open-href
        const href = $a.attr('href') || $card.data('open-href');

        if (href) {
            e.preventDefault();
            bumpTool(id);
            window.location.assign(href); // keeps history consistent
        }
    });

    applyOrder();

    // Password eye toggles for reset modal
    $(document).on('click', '.pw-toggle', function () {
        const id = $(this).data('target');
        const $inp = $('#' + id);
        const $icon = $(this).find('i');

        if (!$inp.length) return;
        const nowType = $inp.attr('type') === 'password' ? 'text' : 'password';
        $inp.attr('type', nowType);

        // toggle eye / eye-slash
        if ($icon.hasClass('fa-eye')) {
            $icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else if ($icon.hasClass('fa-eye-slash')) {
            $icon.removeClass('fa-eye-slash').addClass('fa-eye');
        } else {
            // fallback for regular/solid variants
            $icon.toggleClass('fa-eye fa-eye-slash').toggleClass('fa-regular fa-solid');
        }
    });

    // Also close modal if top-right X or footer cancel is clicked
    $('#resetCancel, #resetCancelFooter').on('click', closeResetModal);

    // Auto scroll for after submit newsletter and contact
    const $newsletterSection = $('#newsletter-section');
    const $newsletterMsg  = $newsletterSection.find('.contact-success');

    if ($newsletterMsg .length) {
        $('html, body').animate({
            scrollTop: $newsletterSection.offset().top - 80
        }, 600);
    }

    const $contactSection = $('#contact-section');
    const $contactMsg  = $contactSection.find('.contact-success');
    const $hasErrors = $contactSection.find('small.e').length > 0;

    if ($contactMsg.length || $hasErrors) {
        $('html, body').animate({
            scrollTop: $contactSection.offset().top - 80
        }, 600);
    }
});

function animateCardsOnScroll() {
    $('.service-card').each(function () {
        const cardTop = $(this).offset().top;
        const scrollTop = $(window).scrollTop();
        const windowHeight = $(window).height();

        if (cardTop < scrollTop + windowHeight - 100) {
            $(this).addClass('animate-in');
        }
    });
}

function timeAgo(iso) {
    const d = new Date(iso);
    if (isNaN(d)) return '';
    const s = Math.floor((Date.now() - d.getTime()) / 1000);
    if (s < 60) return 'Updated just now';
    const m = Math.floor(s / 60); if (m < 60) return `Updated ${m}m ago`;
    const h = Math.floor(m / 60); if (h < 24) return `Updated ${h}h ago`;
    const d2 = Math.floor(h / 24); if (d2 < 30) return `Updated ${d2}d ago`;
    const mo = Math.floor(d2 / 30); if (mo < 12) return `Updated ${mo}mo ago`;
    const y = Math.floor(mo / 12); return `Updated ${y}y ago`;
}

const userId = $('#admin-dashboard').data('user-id') || 'anon';
const storeKey = `gts:recent:${userId}`;

function readOrder() {
    try { return JSON.parse(localStorage.getItem(storeKey)) || []; }
    catch { return []; }
}
function writeOrder(ids) { localStorage.setItem(storeKey, JSON.stringify(ids)); }

function bumpTool(id) {
    if (!id) return;
    const list = readOrder().filter(x => x !== id);
    list.unshift(id);
    writeOrder(list.slice(0, 20)); // cap
}

function applyOrder() {
    const order = readOrder();
    const $grid = $('.dash-tools-grid');
    // move known cards to the front in order
    order.slice().reverse().forEach(id => {
        const $card = $grid.find(`.tool-card[data-tool-id="${id}"]`);
        if ($card.length) $card.prependTo($grid);
    });
}

function openResetModal() {
    const $modal = $('#resetPassModal');               
    const $dashMenu = $('.dash-settings-popover');  
    $modal.addClass('show').attr('aria-hidden', 'false');
    $('body').addClass('modal-open');
    // let the dialog paint before focusing the field
    setTimeout(() => {
        $modal.find('input[name="current_password"]').trigger('focus');
    }, 0);
}

function closeResetModal() {
    const $modal = $('#resetPassModal'); 
    $modal.removeClass('show').attr('aria-hidden', 'true');
    $('body').removeClass('modal-open');
}