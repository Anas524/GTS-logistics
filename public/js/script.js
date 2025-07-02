let tabTimeout;

$(document).ready(function () {
    let lastScrollTop = 0;
    const scrollThreshold = 5; // Minimum scroll delta to trigger
    const $topBar = $('.top-bar');
    const $header = $('.main-header');
    const $heroSlider = $('.hero-slider');

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

    const $menu = $('#inlineMenu');

    $('#menuToggle').click(function () {
        if ($menu.hasClass('show')) {
            // Slide out instantly, no weird animation
            $menu.removeClass('show');
            $menu.css({ display: 'none' });
        } else {
            $menu.css({ display: 'flex' });
            setTimeout(() => {
                $menu.addClass('show');
            }, 10); // slight delay for animation trigger
        }
    });

    // Tablet/Mobile only behavior for header
    if (window.innerWidth <= 1024) {
        const $menu = $('#inlineMenu');
        const $toggle = $('#menuToggle');

        $toggle.on('click', function (e) {
            e.stopPropagation(); // Prevent bubbling to body
            if ($menu.is(':visible')) {
                $menu.slideUp(200).removeClass('show');
            } else {
                $menu.slideDown(200).addClass('show');
            }
        });

        // Close when clicking outside
        $(document).on('click', function (e) {
            if (
                !$menu.is(e.target) &&
                $menu.has(e.target).length === 0 &&
                !$toggle.is(e.target) &&
                $toggle.has(e.target).length === 0
            ) {
                $menu.slideUp(200).removeClass('show');
            }
        });

        // Close on <a> click AND show header-tab
        $menu.find('a').on('click', function (e) {
            e.preventDefault(); // prevent default anchor jump

            const tabId = $(this).data('tab');

            // Close the menu
            $menu.slideUp(200).removeClass('show');

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

    // Header Tab open on hover
    $('.tab-trigger').hover(function () {
        clearTimeout(tabTimeout);

        const tabId = $(this).data('tab');
        $('.header-tab').stop(true, true).slideUp(100); // hide all
        $('#' + tabId).stop(true, true).slideDown(200); // show hovered
    }, function () {
        // delay tab close only if not hovered over tab
        tabTimeout = setTimeout(() => {
            $('.header-tab').slideUp(200);
        }, 500);
    });

    // keep tab open while hovering over the content area
    $('.header-tab').hover(function () {
        clearTimeout(tabTimeout);
    }, function () {
        tabTimeout = setTimeout(() => {
            $(this).slideUp(200);
        }, 500);
    });

    const swiper = new Swiper(".mySwiper", {
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        effect: "fade",
        fadeEffect: { crossFade: true },
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        }
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

    // Show login form on hover
    $('[data-tab="loginTab"]').hover(
        function () {
            $('#loginTab').stop(true, true).fadeIn(300);
        },
        function () {
            // Optional: Keep showing or hide on mouse leave
            // $('#loginTab').stop(true, true).fadeOut(300);
        }
    );

    // Optional: hide when mouse leaves the form
    $('#loginTab').mouseleave(function () {
        $(this).fadeOut(300);
    });

    $('#adminLoginForm').on('submit', function () {
        $('#loginSubmitBtn').hide();
        $('#loginLoader').show();
    });

    //Whatsapp-chat
    $("#whatsapp-chat").on("click", function () {
        $("#chat-popup").fadeIn();
    });

    $("#start-chat").on("click", function () {
        let phoneNumber = "971581146187";
        let message = encodeURIComponent("Hi, welcome to GTS Logistics & Air Cargo Services! Can I chat with your team?");
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