<!-- Header -->
<header class="main-header">
    <div class="logo">
        <a href="#" onclick="location.reload(); return false;">
            <img src="{{ asset('images/logo.png') }}" alt="GTS Logo">
        </a>
    </div>

    <div class="right-header">
        <div class="menu-toggle" id="menuToggle">
            <i class="fa-solid fa-ellipsis-vertical"></i>
        </div>
        <nav class="inline-menu" id="inlineMenu">
            @if (Request::is('amazon-services'))
                <a href="{{ url('/') }}"> Home</a>
            @endif
            <a href="" class="tab-trigger" data-tab="aboutTab">About</a>
            <a href="" class="tab-trigger" data-tab="servicesTab">Services</a>
            <a href="#contact">Contact</a>
            @if(!auth()->check())
                <a href="javascript:void(0);" class="tab-trigger" data-tab="loginTab">Admin Login</a>
            @endif
        </nav>
    </div>
</header>