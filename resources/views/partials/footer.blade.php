<footer id="site-footer" class="gts-footer">
    <div class="footer-container">

        <!-- Logo + Contact Info -->
        <div class="footer-column">
            <img src="{{ asset('images/footerlogo.png') }}" alt="GTS Logo" class="footer-logo">
            <ul class="contact-info">
                <li><i class="fa-solid fa-envelope"></i> ops@globaltradeservices.ae</li>
                <li><i class="fa-solid fa-mobile-screen-button"></i> +971 58 114 6187</li>
                <li><i class="fa-solid fa-phone"></i> 045774859</li>
                <li><i class="fa-solid fa-location-dot"></i> Warehouse 03, Al mana Building Deira Al khabisi Dubai,
                    United Arab Emirates, PO BOX 11108</li>
            </ul>
        </div>

        <!-- Company Links -->
        <div class="footer-column">
            <h4>Company</h4>
            <ul>
                <li><i class="fa-solid fa-building"></i> <a href="#">About Us</a></li>
                <li><i class="fa-solid fa-eye"></i> <a href="#">Vision</a></li>
                <li><i class="fa-solid fa-network-wired"></i> <a href="#">Global Network</a></li>
            </ul>
        </div>

        <!-- Services Links -->
        <div class="footer-column">
            <h4>Services</h4>
            <ul>
                <li><i class="fa-solid fa-plane-departure"></i> <a href="#air-cargo">Air Cargo</a></li>
                <li><i class="fa-solid fa-ship"></i> <a href="#sea-freight">Sea Freight</a></li>
                <li><i class="fa-solid fa-boxes-packing"></i> <a href="#warehousing">Warehousing</a></li>
            </ul>
        </div>

        <!-- Newsletter -->
        <div class="footer-column footer-newsletter" id="newsletter-section">
            <h4>Newsletter</h4>
            <p>Stay updated with our latest logistics solutions.</p>
            <form class="newsletter-form" method="POST" action="{{ route('newsletter.subscribe') }}">
                @csrf
                <input type="email" name="email" placeholder="Enter your email" required>
                <button type="submit"><i class="fa-solid fa-paper-plane"></i></button>
            </form>
            @if (session('newsletter_ok'))
                <div class="contact-success" style="margin-top:8px">{{ session('newsletter_ok') }}</div>
            @endif
            <div class="social-icons">
                <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#"><i class="fa-brands fa-instagram"></i></a>
                <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
            </div>
        </div>

    </div>

    <div class="footer-bottom">
        <p>&copy; 2025 GTS Logistics & Air Cargo Services. All rights reserved.</p>
    </div>
</footer>