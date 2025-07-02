<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GTS Logistics & Air Cargo Services</title>
    <link rel="icon" href="{{ asset('images/gtslogo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Gabarito:wght@400..900&display=swap" rel="stylesheet">

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="{{ request()->is('amazon-services') ? 'amazon-only' : '' }}">
    {{-- DEBUG AUTH --}}
    @if(auth()->check())
    <script>
        console.log('✅ Logged in as: {{ auth()->user()->email }}');
    </script>
    @else
    <script>
        console.log('❌ Not Logged In');
    </script>
    @endif

    <!-- Entry Animation Overlay -->
    <div id="entryOverlay">
        <img src="{{ asset('images/logo.png') }}" id="entryLogo" alt="GTS Logo" width="180">
    </div>

    @include('partials.topbar')

    @include('partials.header')

    @yield('content')

    <!-- Slider Section-1 -->
    <section class="hero-slider" id="slider-section">
        <div class="swiper mySwiper">
            <div class="swiper-wrapper">

                <!-- Slide 1 (Video) -->
                <div class="swiper-slide">
                    <video autoplay muted loop playsinline>
                        <source src="videos/slide1.mp4" type="video/mp4" />
                    </video>
                    <div class="slide-content">
                        <div class="text-overlay">
                            <h1>Discover GTS Logistics & Air Cargo Services – Welcome!</h1>
                            <p>Your trusted partner for fast, reliable, and cost-effective global freight solutions.</p>
                        </div>
                    </div>
                </div>

                <!-- Slide 2 (Image) -->
                <div class="swiper-slide image-slide">
                    <img src="{{ asset('images/slide2.jpg') }}" alt="Slide 2 Image" class="zoom-effect" />
                    <div class="slide-content">
                        <h1>World Wide Shipping Services</h1>
                        <p class="mb-1">Reliable & Efficient Worldwide Shipping Services – Delivering Anywhere, Anytime!
                        </p>
                        <p>United States of America (USA) - United Kingdom (UK) - Australia - Netherlands - Germany -
                            South Africa - Saudi Arabia - Oman - Kuwait - Bahrain - Qatar</p>
                    </div>
                </div>

                <!-- Slide 3 (Video) -->
                <div class="swiper-slide">
                    <video autoplay muted loop playsinline>
                        <source src="videos/slide3.mp4" type="video/mp4" />
                    </video>
                    <div class="slide-content">
                        <h1>Dangerous goods shipment</h1>
                        <p>Safe and compliant transportation of hazardous materials with expert handling, certified
                            packaging, and global regulatory compliance.</p>
                    </div>
                </div>

                <!-- Slide 4 (Video) -->
                <div class="swiper-slide">
                    <video autoplay muted loop playsinline>
                        <source src="videos/slide4.mp4" type="video/mp4" />
                    </video>
                    <div class="slide-content">
                        <h1>Amazon prep & labeling services</h1>
                        <p>Professional Amazon FBA prep and labeling services ensuring compliance, secure packaging, and
                            hassle-free shipment.</p>
                    </div>
                </div>
            </div>
            <div class="swiper-pagination"></div>
        </div>

    </section>

    <section class="services-section" id="services">
        <div class="section-header">
            <h2>Our Services</h2>
            <p>Discover what GTS Logistics & Air Cargo Services offers globally.</p>
        </div>

        <div class="services-grid">

            <!-- Card-1 -->
            <div class="service-card" id="freight-forwarding" data-bg="images/freight.jpg">
                <div class="service-top">
                    <i class="fas fa-shipping-fast service-icon"></i>
                    <div class="service-text">
                        <h3>Freight Forwarding</h3>
                        <p class="short-desc">
                            We provide reliable and efficient air, sea, and land freight solutions to ensure seamless
                            global trade.
                        </p>
                    </div>
                    <button class="toggle-detail">Read More</button>
                </div>

                <div class="service-detail">
                    <p class="full-desc">
                        We specialize in freight forwarding, ensuring seamless and
                        efficient transportation
                        of goods across air, sea, and land. Freight forwarding is the backbone of global trade,
                        involving the coordination
                        and management of shipments from origin to destination. It includes logistics planning, customs
                        clearance, warehousing,
                        and cargo tracking to ensure a smooth supply chain. Our company leverages advanced logistics
                        solutions to streamline
                        freight operations, offering cost-effective, timely, and secure shipping for businesses
                        worldwide.
                    </p>
                    <h4>Why Choose GTS Logistics & Air Cargo Services?</h4>
                    <ul class="why-gts-list">
                        <li>Global Reach – Extensive network ensuring smooth international shipping.</li>
                        <li>Cost Efficiency – Optimized freight solutions to reduce expenses.</li>
                        <li>Fast & Secure Delivery – Advanced tracking systems and expert handling.</li>
                        <li>Hassle-Free Customs Clearance – Ensuring compliance with global trade regulations.</li>
                        <li>End-to-End Support – From pickup to final delivery, we handle everything.</li>
                    </ul>
                </div>

            </div>

            <!-- Card-2 -->
            <div class="service-card" id="air-cargo" data-bg="images/aircargo.jpeg">
                <div class="service-top">
                    <i class="fas fa-plane-departure service-icon"></i>
                    <div class="service-text">
                        <h3>Air Cargo Services</h3>
                        <p class="short-desc">Reliable and secure air freight shipping services from the UAE to the USA,
                            Canada, and Europe.</p>
                    </div>
                    <button class="toggle-detail">Read More</button>
                </div>
                <div class="service-detail">
                    <p class="full-desc">
                        We specialize in reliable and secure air freight shipping from the UAE to the USA, Canada, and
                        Europe. Our air cargo services ensure fast, efficient, and cost-effective transportation of
                        goods, catering to businesses and individuals seeking timely delivery. <br> With GTS Logistics &
                        Cargo Services, you get fast, secure, and dependable air freight solutions, ensuring your
                        shipments arrive on time, every time. Contact us today for a hassle-free shipping experience!
                    </p>
                    <h4>Benefits of Choosing Our Air Cargo Services</h4>
                    <ul class="why-gts-list">
                        <li>Speed & Efficiency – The quickest way to transport goods internationally.</li>
                        <li>Security & Reliability – Ensuring your cargo is handled with care and safety.</li>
                        <li>Global Reach – Connecting businesses from the UAE to key destinations in the USA, Canada,
                            and Europe.</li>
                        <li>Customized Solutions – Tailored shipping options based on your cargo needs.</li>
                        <li>Temperature-Controlled Shipping – Ideal for pharmaceuticals, perishables, and sensitive
                            products.</li>
                    </ul>
                </div>
            </div>

            <!-- Card-3 -->
            <div class="service-card" id="sea-freight" data-bg="images/seafreight.jpg">
                <div class="service-top">
                    <i class="fas fa-ship service-icon"></i>
                    <div class="service-text">
                        <h3>Sea Freight</h3>
                        <p class="short-desc">Affordable LCL and FCL shipping options for bulk and commercial goods.</p>
                    </div>
                    <button class="toggle-detail">Read More</button>
                </div>
                <div class="service-detail">
                    <p class="full-desc">
                        We specialize in reliable and cost-effective Sea Freight solutions, offering both LCL (Less than
                        Container Load) and FCL (Full Container Load) shipping options. Whether you're a small business
                        looking to ship partial loads or a large enterprise transporting bulk goods, our tailored
                        services ensure efficiency and affordability. <br></br> With GTS Logistics & Air Cargo Services, you
                        get streamlined sea freight solutions designed to enhance your supply chain, reduce costs, and
                        ensure timely delivery of goods worldwide.
                    </p>
                    <h4>Why Choose GTS Logistics for Sea Freight?</h4>
                    <ul class="why-gts-list">
                        <li>Flexible Shipping Options – LCL for smaller shipments and FCL for full container loads.</li>
                        <li>Cost-Effective Solutions – Competitive pricing to optimize your logistics budget.</li>
                        <li>Global Reach – Seamless cargo movement across international ports.</li>
                        <li>Secure & Reliable – Advanced tracking and handling to ensure safe deliveries.</li>
                        <li>Custom Clearance Support – Hassle-free documentation and regulatory compliance.</li>
                    </ul>
                </div>
            </div>

            <!-- Card-4 -->
            <div class="service-card" id="last-mile" data-bg="images/lastmile.jpeg">
                <div class="service-top">
                    <i class="fas fa-truck service-icon"></i>
                    <div class="service-text">
                        <h3>Last-Mile Delivery</h3>
                        <p class="short-desc">Reliable delivery services from door to door, tailored for both businesses
                            and individuals.</p>
                    </div>
                    <button class="toggle-detail">Read More</button>
                </div>
                <div class="service-detail">
                    <p class="full-desc">
                        We specialize in last-mile delivery, ensuring that packages reach their final destination
                        swiftly and securely. Whether for businesses or individuals, our tailored delivery solutions
                        guarantee efficiency, reliability, and customer satisfaction. <br> With GTS Logistics & Air Cargo
                        Services, last-mile delivery is not just about reaching the destination—it’s about delivering
                        excellence, reliability, and convenience.
                    </p>
                    <h4>How GTS Logistics & Air Cargo Services Optimizes Last-Mile Delivery</h4>
                    <ul class="why-gts-list">
                        <li>Advanced Tracking System: Customers can track their shipments in real-time, ensuring
                            transparency and security.</li>
                        <li>Fast & Reliable Delivery: We utilize an extensive network and optimized routes to minimize
                            delays.</li>
                        <li>Flexible Solutions: Our services cater to businesses for bulk shipments and individuals for
                            personal deliveries.</li>
                        <li>Eco-Friendly Options: We adopt sustainable practices to reduce carbon footprints in
                            last-mile logistics.</li>
                    </ul>
                </div>
            </div>

            <!-- Card-5 -->
            <div class="service-card" id="warehousing" data-bg="images/Warehousing.jpg">
                <div class="service-top">
                    <i class="fas fa-warehouse service-icon"></i>
                    <div class="service-text">
                        <h3>Warehousing & Storage</h3>
                        <p class="short-desc">Secure storage and inventory management services in the UAE and USA.</p>
                    </div>
                    <button class="toggle-detail">Read More</button>
                </div>
                <div class="service-detail">
                    <p class="full-desc">
                        At GTS Logistics & Air Cargo Services, we offer secure, efficient, and scalable warehousing and
                        storage solutions in the UAE and USA, ensuring seamless inventory management for businesses of
                        all sizes. Our state-of-the-art storage facilities are equipped with advanced security systems,
                        climate-controlled environments, and optimized space utilization to safeguard your goods. <br>
                        With GTS Logistics & Air Cargo Services, you get a trusted warehousing partner dedicated to
                        optimizing your supply chain and enhancing operational efficiency. Contact us today to
                        streamline your storage and logistics needs!
                    </p>
                    <h4>Strategic Locations – Our warehouses are strategically positioned in key logistics hubs in the
                        UAE and USA, enabling quick access to major transportation routes.</h4>
                    <ul class="why-gts-list">
                        <li>Advanced Inventory Management – We employ cutting-edge tracking systems for real-time stock
                            monitoring, reducing errors and ensuring accurate order fulfillment.</li>
                        <li>Customized Storage Solutions – Whether you need short-term or long-term storage, bulk or
                            specialized handling, we tailor our services to meet your needs.</li>
                        <li>Seamless Distribution – Integrated with our freight and cargo services, we provide smooth
                            transitions from storage to delivery, enhancing supply chain efficiency.</li>
                    </ul>
                    <!-- <h4>Benefits of Choosing GTS Logistics & Air Cargo Services</h4>
                    <ul class="why-gts-list">
                        <li>Security & Reliability – 24/7 surveillance, climate-controlled storage, and strict handling protocols ensure product safety.</li>
                        <li>Cost Efficiency – Optimized storage reduces overhead costs and maximizes inventory control.</li>
                        <li>Faster Delivery – Proximity to major transport networks allows for quicker order processing and distribution.</li>
                        <li>Scalability – Flexible storage options to accommodate business growth and seasonal demands</li>
                    </ul> -->
                </div>
            </div>

            <!-- Card-6 -->
            <div class="service-card" id="amazon-fba" data-bg="images/amazonfba.JPG">
                <div class="service-top">
                    <i class="fas fa-barcode service-icon"></i>
                    <div class="service-text">
                        <h3>Amazon FBA Prep & Labeling</h3>
                        <p class="short-desc">Professional labeling, packaging, and compliance services for sellers on
                            Amazon.</p>
                    </div>
                    <button class="toggle-detail">Read More</button>
                </div>
                <div class="service-detail">
                    <p class="full-desc">
                        Selling on Amazon requires strict packaging, prep, and labeling compliance to avoid delays,
                        penalties, or
                        rejections. At GTS Logistics & Air Cargo Services, we offer Amazon FBA prep and labeling services,
                        ensuring your
                        shipments meet Amazon’s fulfillment requirements for smooth delivery to warehouses.
                    </p>
                    <h4>Amazon FBA Prep & Labeling – Hassle-Free Packaging & Compliance Services</h4>
                    <ul class="why-gts-list">
                        <li>From box sizing and palletizing to LTL (Less Than Truckload) shipments and SPD (Single
                            Parcel Delivery)</li>
                        <li>We handle everything to ensure your shipments reach Amazon fulfillment centers smoothly and
                            on time.</li>
                        <li>To maintain smooth and compliant deliveries, Amazon enforces strict guidelines on box sizes,
                            pallet dimensions, and packaging</li>
                    </ul>
                </div>
            </div>

            <!-- Card-7 -->
            <div class="service-card" id="cod" data-bg="images/cod.jpeg">
                <div class="service-top">
                    <i class="fas fa-money-bill-wave service-icon"></i>
                    <div class="service-text">
                        <h3>Cash on Delivery (COD) Services</h3>
                        <p class="short-desc">Reliable COD solutions across GCC countries, ensuring smooth payments.</p>
                    </div>
                    <button class="toggle-detail">Read More</button>
                </div>
                <div class="service-detail">
                    <p class="full-desc">
                        We offer Cash on Delivery (COD) solutions designed to provide a secure, reliable, and convenient
                        payment collection process for businesses across the GCC countries. Our COD services ensure that
                        you can confidently serve your customers, offering them the flexibility to pay in cash upon
                        delivery while streamlining your payment collection process.
                    </p>
                    <h4>How Our Company Supports Supports Your COD Needs</h4>
                    <ul class="why-gts-list">
                        <li>Secure & Reliable COD Payment Collection: <br>
                            We ensure that all cash transactions during the delivery process are handled securely,
                            offering your customers a trustworthy payment experience.</li>
                        <li>Wide Coverage Across GCC Countries: <br>
                            Our COD services cover UAE, Saudi Arabia, Qatar, Kuwait, Bahrain, Oman, and other GCC
                            countries, ensuring businesses can serve their customers across the entire region.</li>
                        <li>Real-Time Payment Tracking: <br>
                            Our advanced logistics and technology systems allow businesses to track COD payments in
                            real-time, providing transparency and peace of mind.</li>
                    </ul>
                </div>
            </div>

            <!-- Card-8 -->
            <div class="service-card" id="customs" data-bg="images/customs.jpg">
                <div class="service-top">
                    <i class="fas fa-file-invoice service-icon"></i>
                    <div class="service-text">
                        <h3>Customs Clearance & Documentation</h3>
                        <p class="short-desc">Smooth import/export clearance and adherence to regulatory compliance.</p>
                    </div>
                    <button class="toggle-detail">Read More</button>
                </div>
                <div class="service-detail">
                    <p class="full-desc">
                        We specialize in seamless customs clearance and documentation to ensure hassle-free import and
                        export operations. Our expert team handles all necessary paperwork, regulatory compliance, and
                        customs procedures, allowing businesses to focus on their core operations without delays. <br>
                        With GTS Logistics & Air Cargo Services, your goods move efficiently and securely, ensuring smooth
                        business operations and compliance at every step.
                    </p>
                    <h4>How We Help</h4>
                    <ul class="why-gts-list">
                        <li>Efficient Documentation Handling: We prepare and verify all required shipping documents,
                            including invoices, bills of lading, and certificates of origin.</li>
                        <li>Regulatory Compliance: Our specialists ensure adherence to international trade laws and
                            local customs regulations, minimizing the risk of fines or shipment holds.</li>
                        <li>Faster Clearance Process: With our industry expertise and strong customs network, we
                            expedite clearance to avoid unnecessary delays.</li>
                        <li>Cost Optimization: We help businesses reduce import/export costs by optimizing duties,
                            tariffs, and tax exemptions where applicable.</li>
                    </ul>
                </div>
            </div>

            <!-- Card-9 -->
            <div class="service-card" id="e-commerce" data-bg="images/e-commerce.jpg">
                <div class="service-top">
                    <i class="fas fa-box-open service-icon"></i>
                    <div class="service-text">
                        <h3>E-commerce Fulfillment</h3>
                        <p class="short-desc">Effortless order processing, packing, and shipping solutions for online
                            businesses.</p>
                    </div>
                    <button class="toggle-detail">Read More</button>
                </div>
                <div class="service-detail">
                    <p class="full-desc">
                        We provide seamless E-commerce Fulfillment solutions, ensuring smooth order processing, secure
                        packaging, and fast shipping for online businesses. Our advanced logistics network and efficient
                        supply chain management help businesses streamline their operations while enhancing customer
                        satisfaction. <br> With GTS Logistics & Air Cargo Services, e-commerce businesses can focus on sales
                        and growth while we take care of logistics. Let us help you achieve seamless order fulfillment
                        and enhance your online business success!
                    </p>
                    <h4>How GTS Logistics & Air Cargo Services Supports E-commerce</h4>
                    <ul class="why-gts-list">
                        <li>Order Processing: We handle orders with precision, ensuring accurate picking and packing to
                            minimize errors.</li>
                        <li>Secure Packaging: Our team ensures products are packed safely to prevent damage during
                            transit.</li>
                        <li>Fast & Reliable Shipping: With our extensive logistics network, we guarantee timely
                            deliveries worldwide.</li>
                        <li>Inventory Management: Real-time tracking and storage solutions help businesses manage stock
                            efficiently.</li>
                        <li>Customs & Compliance: We simplify international shipping by handling documentation and
                            regulations.</li>
                    </ul>
                </div>
            </div>

            <!-- Card-10 -->
            <div class="service-card" id="shipping" data-bg="images/shipping.jpeg">
                <div class="service-top">
                    <i class="fas fa-globe service-icon"></i>
                    <div class="service-text">
                        <h3>International & Domestic Shipping</h3>
                        <p class="short-desc">Quick, secure, and affordable solutions customized for your specific
                            needs.</p>
                    </div>
                    <button class="toggle-detail">Read More</button>
                </div>
                <div class="service-detail">
                    <p class="full-desc">
                        Looking for hassle-free international shipping from the UAE to Oman, Saudi Arabia, Kuwait, Bahrain, and
                        Qatar? At GTS Logistics & Air Cargo Services, we provide secure, efficient, and cost-effective freight
                        solutions for businesses and individuals. Our services include small parcel delivery, LTL (Less Than
                        Truckload) shipments, and specialized eCommerce shipping solutions. We also offer professional packing
                        services to ensure the safe and secure transportation of your goods, whether for personal or commercial
                        needs.
                    </p>
                    <p class="full-desc">
                        At GTS Logistics & Air Cargo Services, we specialize in seamless international shipping across the GCC and
                        worldwide destinations. Our comprehensive logistics solutions include parcel delivery, LTL (Less Than
                        Truckload) shipments, and eCommerce fulfillment, ensuring reliable and efficient transportation for
                        businesses and individuals. We also provide last-mile delivery, warehousing, and secure storage to
                        streamline your supply chain and meet your shipping needs with speed and precision. Whether you need
                        cross-border shipping, inventory management, or end-to-end logistics solutions, we’ve got you covered.
                    </p>
                    <h4>Global Shipping from UAE & GCC to Worldwide Destinations</h4>
                    <p class="full-desc">
                        We provide seamless shipping solutions from the UAE & GCC to major global destinations. Our services include
                        air & ocean freight, customs clearance, and door-to-door delivery for hassle-free logistics.
                    </p>
                </div>
            </div>

        </div>
    </section>

    <!-- section-2 -->
    <section class="gts-section" id="int-shipping">
        <div class="container modern-int-shipping">
            <div class="intro-header">
                <h2><span class="emoji"><i data-lucide="globe" class="icon"></i></span> International GCC Shipping</h2>
                <p class="tagline">From UAE to the World – Fast, Secure, and Affordable Logistics Solutions</p>
            </div>

            <div class="intro-grid">
                <div class="intro-left">
                    <p>Looking for hassle-free international shipping from the UAE to Oman, Saudi Arabia, Kuwait, Bahrain, and Qatar? At <strong>GTS Logistics & Air Cargo Services</strong>, we provide secure, efficient, and cost-effective freight solutions for businesses and individuals.</p>
                    <p>Our services include small parcel delivery, LTL (Less Than Truckload) shipments, and specialized eCommerce shipping solutions. We also offer professional packing services to ensure the safe and secure transportation of your goods, whether for personal or commercial needs.</p>
                    <p>We specialize in seamless international shipping across the GCC and worldwide destinations. Our comprehensive logistics solutions include parcel delivery, LTL shipments, eCommerce fulfillment, last-mile delivery, warehousing, and secure storage. Whether you need cross-border shipping, inventory management, or end-to-end logistics, we’ve got you covered.</p>
                </div>
                <div class="intro-right">
                    <img src="{{ asset('images/shipping-illustration.png') }}" alt="Shipping Worldwide" class="shipping-graphic" />
                </div>
            </div>

            <div class="destination-tags">
                <h4><i data-lucide="network" class="icon"></i> Global Shipping Destinations</h4>
                <div class="scroll-row">
                    <span class="flag-tag">🇺🇸 United States (USA)</span>
                    <span class="flag-tag">🇬🇧 United Kingdom (UK)</span>
                    <span class="flag-tag">🇨🇦 Canada (CA)</span>
                    <span class="flag-tag">🇦🇺 Australia (AU)</span>
                    <span class="flag-tag">🇳🇱 Netherlands (NL)</span>
                    <span class="flag-tag">🇩🇪 Germany (DE)</span>
                    <span class="flag-tag">🇿🇦 South Africa (ZA)</span>
                    <span class="flag-tag">🇸🇦 Saudi Arabia (KSA)</span>
                    <span class="flag-tag">🇴🇲 Oman (OM)</span>
                    <span class="flag-tag">🇰🇼 Kuwait (KW)</span>
                    <span class="flag-tag">🇧🇭 Bahrain (BH)</span>
                    <span class="flag-tag">🇶🇦 Qatar (QA)</span>
                </div>
            </div>

            <div class="modern-faq">
                <h4><i data-lucide="help-circle" class="icon"></i> Frequently Asked Questions</h4>
                <div class="faq-item">
                    <button class="faq-question"> How long does shipping from UAE to the US take?</button>
                    <div class="faq-answer">
                        <p><strong>Air Freight:</strong> 3–7 days</p>
                        <p><strong>Sea Freight:</strong> 30–45 days (varies by port/customs)</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question"> What is the delivery time for GCC countries?</button>
                    <div class="faq-answer">
                        <p><strong>Air Freight:</strong> 2–5 days</p>
                        <p><strong>Road Freight:</strong> 7–10 days</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question"> Do you offer door-to-door delivery?</button>
                    <div class="faq-answer">
                        <p>Yes! We ensure safe and timely delivery to your destination.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question"> What items are restricted for international shipping?</button>
                    <div class="faq-answer">
                        <p>
                            <i data-lucide="ban" class="icon-deny"></i> Hazardous materials & chemicals<br>
                            <i data-lucide="ban" class="icon-deny"></i> Flammable & explosive items<br>
                            <i data-lucide="ban" class="icon-deny"></i> Perishable goods (without proper packaging)<br>
                            <i data-lucide="ban" class="icon-deny"></i> Illegal substances
                        </p>
                    </div>
                </div>
            </div>

            <div class="closing-summary">
                <h3><i data-lucide="ship" class="icon"></i> Ship Internationally with GTS Logistics & Air Cargo Services!</h3>
                <p>Whether you're sending goods from Dubai, Abu Dhabi, Riyadh, Muscat, Doha, or other GCC cities, we offer the best international shipping rates with door-to-door convenience.</p>
            </div>
        </div>
    </section>

    <!-- section-3 -->
    <section class="gts-section" id="dom-shipping">
        <div class="container modern-dom-shipping">
            <div class="intro-header">
                <h2><i data-lucide="truck" class="icon"></i> Domestic GCC Shipping</h2>
                <p class="tagline">Local & Regional Logistics Solutions Across UAE, KSA, Oman, Qatar, Bahrain & Kuwait</p>
            </div>

            <div class="intro-grid">
                <div class="intro-left">
                    <p>Looking for efficient, cost-effective domestic shipping and logistics services within the UAE and across the GCC region? At <strong>GTS Logistics & Air Cargo Services</strong>, we specialize in local freight solutions, ensuring fast and secure delivery across cities and states.</p>
                    <ul class="highlight-points">
                        <li>✔ Same-Day & Next-Day Express Delivery</li>
                        <li>✔ Bulk Cargo & Last-Mile Services</li>
                        <li>✔ Temperature-Controlled Logistics</li>
                        <li>✔ Business-to-Business (B2B) & eCommerce Fulfillment</li>
                    </ul>
                </div>
                <div class="intro-right">
                    <img src="{{ asset('images/dom-shipping.png') }}" alt="Domestic Shipping" class="shipping-graphic" />
                </div>
            </div>

            <div class="destination-tags">
                <h4><i data-lucide="map-pin" class="icon"></i> Domestic Coverage by Country</h4>
                <div class="scroll-row">
                    <span class="flag-tag">🇦🇪 United Arab Emirates (UAE)</span>
                    <span class="flag-tag">🇸🇦 Saudi Arabia (KSA)</span>
                    <span class="flag-tag">🇴🇲 Oman (OM)</span>
                    <span class="flag-tag">🇶🇦 Qatar (QA)</span>
                    <span class="flag-tag">🇧🇭 Bahrain (BH)</span>
                    <span class="flag-tag">🇰🇼 Kuwait (KW)</span>
                </div>
            </div>

            <div class="modern-faq">
                <h4><i data-lucide="help-circle" class="icon"></i> Frequently Asked Questions</h4>
                <div class="faq-item">
                    <button class="faq-question">How long does domestic shipping take within the GCC?</button>
                    <div class="faq-answer">
                        <p><strong>Same-day & Next-day Delivery:</strong> Available in major cities.</p>
                        <p><strong>Standard Freight:</strong> 2–5 days depending on location.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question">What are the shipping costs for domestic deliveries?</button>
                    <div class="faq-answer">
                        <p>Costs vary based on distance, weight, and delivery type. Contact us for a quote.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question">Do you provide same-day and express delivery?</button>
                    <div class="faq-answer">
                        <p>Yes, we provide same-day and express delivery across all major cities and emirates.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question">What items can be shipped domestically?</button>
                    <div class="faq-answer">
                        <p>
                            <i data-lucide="package" class="icon-package"></i> Documents & Electronics<br>
                            <i data-lucide="package" class="icon-package"></i> Clothing & Furniture<br>
                            <i data-lucide="package" class="icon-package"></i> Perishables with packaging<br>
                            <i data-lucide="package" class="icon-package"></i> Industrial Equipment
                        </p>
                    </div>
                </div>
            </div>

            <div class="closing-summary">
                <h3><i data-lucide="box" class="icon"></i> Reliable Domestic Logistics with GTS</h3>
                <p>Whether you're shipping across Dubai, Riyadh, Doha, or Muscat — trust GTS for fast, secure, and flexible domestic logistics tailored to your needs.</p>
            </div>
        </div>
    </section>

    @include('partials.footer')

    <div id="whatsapp-chat">
        <a onclick="toggleChatPopup()">
            <img src="{{ asset('images/whatsapp-icon.png') }}" alt="WhatsApp Chat">
        </a>
        <div id="chat-popup">
            <div class="header">Hi, Welcome to GTS Logistics & Air Cargo Services! 👋</div>
            <p>Can you chat with our team?</p>
            <button id="start-chat" onclick="startWhatsAppChat()">Start Chat</button>
        </div>
    </div>

    @if(auth()->check() && auth()->user()->is_admin)
    <script>
        $('#slider-section, #about-section, #services, #contact-section, #int-shipping, #dom-shipping, #amazon-services').hide();
        $('#admin-dashboard').fadeIn(300);
    </script>
    @endif

    @if(request()->has('login'))
    <script>
        $('#loginTab').fadeIn(500);
        $('html, body').animate({
            scrollTop: $('#loginTab').offset().top
        }, 500);
    </script>
    @endif

    <script>
        lucide.createIcons();
    </script>

    <script src="{{ asset('js/script.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
</body>

</html>