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
            {{-- Home: show everywhere except on "/" --}}
            @if (!Request::is('/'))
                <a href="{{ url('/') }}">Home</a>
            @endif

            <a href="" class="tab-trigger" data-tab="aboutTab">About</a>
            <a href="{{ url('/') }}#services" class="tab-trigger" data-tab="servicesTab">Services</a>
            <a href="{{ url('/') }}#contact-section">Contact</a>

            @guest
                <a href="javascript:void(0);" class="tab-trigger" data-tab="loginTab">Admin Login</a>
            @else
                @if(auth()->user()->is_admin && !Request::routeIs('admin.dashboard'))
                    <a href="{{ route('admin.dashboard') }}">Admin DB</a>
                @endif
            @endguest
        </nav>
    </div>
</header>