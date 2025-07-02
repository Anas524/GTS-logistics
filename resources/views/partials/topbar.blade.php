<!-- Top Bar -->
<div class="top-bar"></div>

<!-- TABS SECTION -->
<div class="header-tab" id="aboutTab">
    <h3>About</h3>
    <ul>
        <li><a href="#"><i class="fas fa-bullseye"></i> Principles</a></li>
        <li><a href="#"><i class="fas fa-eye"></i> Vision</a></li>
        <li><a href="#"><i class="fas fa-network-wired"></i> Network</a></li>
    </ul>
</div>

<div class="header-tab" id="servicesTab">
    <h3>Services</h3>
    <ul class="services-list">
        <li><a href="#freight-forwarding" data-tab="tab-service1"><i class="fas fa-shipping-fast"></i> Freight Forwarding</a></li>
        <li><a href="#air-cargo" data-tab="tab-service2"><i class="fas fa-plane-departure"></i> Air Cargo Services</a></li>
        <li><a href="#sea-freight" data-tab="tab-service3"><i class="fas fa-ship"></i> Sea Freight</a></li>
        <li><a href="#last-mile" data-tab="tab-service4"><i class="fas fa-truck"></i> Last-Mile Delivery</a></li>
        <li><a href="#warehousing" data-tab="tab-service5"><i class="fas fa-warehouse"></i> Warehousing & Storage</a></li>
        <li><a href="{{ url('/amazon-services') }}"><i class="fas fa-barcode"></i> Amazon FBA Prep & Labeling</a></li>
        <li><a href="#cod" data-tab="tab-service7"><i class="fas fa-money-bill-wave"></i> Cash on Delivery (COD)</a></li>
        <li><a href="#customs" data-tab="tab-service8"><i class="fas fa-file-invoice"></i> Customs Clearance</a></li>
        <li><a href="#e-commerce" data-tab="tab-service9"><i class="fas fa-box-open"></i> E-commerce Fulfillment</a></li>
        <li><a href="#shipping" data-tab="tab-service10"><i class="fas fa-globe"></i> Intl & Domestic Shipping</a></li>
    </ul>
</div>

@if(auth()->check() && auth()->user()->is_admin)
<section id="admin-dashboard" style="display: none;">
    <h2>🎉 Welcome, Admin</h2>
    <p>You are successfully logged in. Click below to access the available tools.</p>
    <div class="tool-buttons">
        <a href="{{ route('calculator.index') }}" class="tool-button">Open Amazon Revenue Calculator</a>
        <a href="" class="tool-button">Open Investment Sheets</a>
    </div>
    <form method="POST" action="{{ route('logout') }}" style="margin-top: 20px;">
        @csrf
        <button type="submit" class="btn btn-outline-dark">Logout</button>
    </form>
</section>
@else
<div class="header-tab full-login-tab modern-admin-login" id="loginTab">
    <h3 class="login-heading"><i class="fas fa-user-shield"></i> Admin Login</h3>
    <form method="POST" action="{{ route('login') }}" id="adminLoginForm">
        @csrf
        <div class="modern-input-group">
            <label for="email"><i data-lucide="mail" style="width: 20px; height: 20px;"></i></label>
            <input type="email" name="email" id="email" placeholder="Email Address" required>
        </div>
        <div class="modern-input-group" style="position: relative;">
            <label for="password"><i data-lucide="lock" style="width: 20px; height: 20px;"></i></label>
            <input type="password" name="password" id="password" placeholder="Password" required>
            <span id="togglePassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                <i data-lucide="eye" id="eyeIcon" style="width: 20px; height: 20px; color: gray"></i>
            </span>
        </div>
        <button type="submit" class="modern-login-btn" id="loginSubmitBtn">Login</button>
        <span class="loader" id="loginLoader" style="display: none;"></span>

        @if ($errors->has('login_error'))
        <div class="alert alert-danger">
            {{ $errors->first('login_error') }}
        </div>
        @endif
    </form>
</div>
@endif

<!-- Show Login Tab if `?login=1` -->
@if(request()->has('login'))
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const loginTab = document.getElementById('loginTab');
        if (loginTab) {
            loginTab.style.display = 'block';
            $('html, body').animate({
                scrollTop: $('#loginTab').offset().top
            }, 500);
        }
    });
</script>
@endif