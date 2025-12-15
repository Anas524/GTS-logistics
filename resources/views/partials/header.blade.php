<!-- Header -->
<header class="main-header">
    <div class="logo">
        <a href="{{ route('home') }}">
            <img src="{{ asset('images/logo.png') }}" alt="GTS Logo">
        </a>
    </div>

    <div class="right-header">
        <div class="menu-toggle" id="menuToggle">
            <i class="fa-solid fa-ellipsis-vertical"></i>
        </div>
        <nav class="inline-menu" id="inlineMenu">
            {{-- Hide "Home" only when already on home --}}
            @unless (request()->routeIs('home'))
              <a href="{{ route('home') }}">Home</a>
            @endunless

            <a href="" class="tab-trigger" data-tab="aboutTab">About</a>
            <a href="{{ url('/') }}#services" class="tab-trigger" data-tab="servicesTab">Services</a>
            <a href="{{ url('/') }}#contact-section">Contact</a>
            <a href="{{ route('fedex.track.form') }}">FedEx Tracking</a>

            @auth
                @php $user = auth()->user(); @endphp
                @if(
                    $user
                    && (
                        (method_exists($user, 'isAdmin') && $user->isAdmin())
                        || (method_exists($user, 'isConsultant') && $user->isConsultant())
                    )
                    && !request()->routeIs('admin.dashboard')
                )
                    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                @endif
            @else
                <a href="javascript:void(0);" class="tab-trigger" data-tab="loginTab">Login</a>
            @endauth
        </nav>
    </div>
</header>