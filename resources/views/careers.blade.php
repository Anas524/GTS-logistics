@extends('gts')

@section('title', 'Careers – GTS Logistics Air Cargo Services')

@section('content')
<section class="careers-hero">
    <div class="h-font p-font careers-hero-inner">
        <div class="careers-hero-left">
            <p class="careers-badge">We’re hiring</p>
            <h1>Careers at GTS Logistics Air Cargo Services Co. LLC</h1>
            <p class="careers-hero-text">
                Join our team in <strong>Dubai, UAE</strong> and help us deliver safe, reliable
                logistics and air cargo services with a focus on <strong>Dangerous Goods</strong>
                and <strong>e-commerce shipments</strong>.
            </p>
            <div class="careers-hero-contact">
                Send your CV to:
                <span>ops@globaltradeservices.ae</span> /
                <span>hr@globaltradeservices.ae</span>
            </div>
        </div>
        <div class="careers-hero-right">
            <div class="careers-hero-card">
                <img src="{{ asset('images/gts-logo.png') }}" alt="GTS Logistics Logo" class="careers-hero-logo">
                <p>Open positions:</p>
                <ul>
                    <li>Warehouse Labor / Supervisor</li>
                    <li>Warehouse Labor</li>
                </ul>
                <p class="careers-hero-note">
                    Candidates with <strong>Dangerous Goods</strong> and
                    <strong>Amazon FBA / e-commerce</strong> experience are strongly preferred.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="careers-list">
    <div class="h-font p-font careers-list-inner">

        {{-- ROLE 1: Warehouse Labor / Supervisor --}}
        <article class="career-card" id="warehouse-labor-supervisor">
            <header class="career-card-header">
                <h2>Warehouse Labor / Supervisor</h2>
                <span class="career-location">Dubai, UAE • Full-time</span>
            </header>

            <div class="career-section">
                <h3>Job Description</h3>
                <p>
                    We are looking for a motivated individual with hands-on experience in managing
                    Dangerous Goods (DG) and e-commerce shipments.
                </p>
            </div>

            <div class="career-section">
                <h3>Key Responsibilities</h3>
                <ul>
                    <li>Manage daily warehouse operations for inbound and outbound shipments.</li>
                    <li>Handle Dangerous Goods (ID 8000 Consumer Commodity) as per IATA standards.</li>
                    <li>Perform FNSKU labeling, shipper/consignee label printing, and packing for e-commerce and individual customers.</li>
                    <li>Prepare packing lists and ensure accurate documentation.</li>
                    <li>Ensure packaging compliance and product safety.</li>
                    <li>Coordinate with the logistics and dispatch teams.</li>
                </ul>
            </div>

            <div class="career-section">
                <h3>Requirements</h3>
                <ul>
                    <li>Valid Dangerous Goods Training Certificate (IATA or equivalent).</li>
                    <li>Experience in ID 8000 Consumer Commodity packaging.</li>
                    <li>Familiarity with FNSKU labeling, e-commerce order processing, and Amazon FBA standards is an advantage.</li>
                    <li>Basic computer skills (MS Office, Adobe PDF editing, etc.).</li>
                    <li>Must be able to read, write, speak, and understand basic English.</li>
                    <li>Candidates with previous UAE experience are encouraged to apply.</li>
                </ul>
            </div>

            <div class="career-section">
                <h3>Compensation &amp; Benefits</h3>
                <ul>
                    <li>Salary: <strong>AED 2,000–2,500</strong> per month (depending on experience).</li>
                    <li>Visa provided.</li>
                    <li>Medical insurance.</li>
                    <li>Air ticket every 2 years.</li>
                    <li>Annual performance-based bonus.</li>
                    <li>Job Type: <strong>Full-time</strong>.</li>
                </ul>
            </div>

            <div class="career-section">
                <h3>Skills</h3>
                <ul class="career-skills-grid">
                    <li>Knowledge of IATA regulations and ID 8000 Consumer Commodity handling</li>
                    <li>Experience with Dangerous Goods packaging and documentation</li>
                    <li>Familiarity with Amazon FBA requirements, FNSKU labeling, and e-commerce packing standards</li>
                    <li>Basic understanding of warehouse workflow (inbound, outbound, sorting, dispatch)</li>
                    <li>Ability to operate label printers, barcode scanners, and basic warehouse tools</li>
                    <li>Attention to detail for accurate packing, labeling, and documentation</li>
                    <li>Basic computer literacy (MS Word, Excel, PDF editing, email)</li>
                    <li>Ability to work under pressure and meet shipment deadlines</li>
                    <li>Strong communication and coordination skills</li>
                    <li>Physical ability to lift boxes and perform warehouse tasks</li>
                    <li>Problem-solving skills and ability to work independently</li>
                    <li>Previous UAE logistics/warehouse experience is a strong advantage</li>
                </ul>
            </div>

            <footer class="career-footer">
                <p>
                    To apply, please send your updated CV to:
                    <strong>ops@globaltradeservices.ae</strong> /
                    <strong>hr@globaltradeservices.ae</strong>
                    with the subject line:
                    <em>“Application – Warehouse Labor / Supervisor”</em>.
                </p>
            </footer>
        </article>

        {{-- ROLE 2: Warehouse Labor --}}
        <article class="career-card" id="warehouse-labor">
            <header class="career-card-header">
                <h2>Warehouse Labor</h2>
                <span class="career-location">Dubai, UAE • Full-time</span>
            </header>

            <div class="career-section">
                <h3>Job Description</h3>
                <p>
                    We are looking for hardworking and reliable warehouse labor staff with experience in
                    Dangerous Goods (DG) and e-commerce packing.
                </p>
            </div>

            <div class="career-section">
                <h3>Key Responsibilities</h3>
                <ul>
                    <li>Assist in packing, labeling, and sorting shipments.</li>
                    <li>Handle ID 8000 Consumer Commodity (Dangerous Goods) as per instructions.</li>
                    <li>Perform FNSKU labeling, shipper/consignee label printing, and e-commerce order packing.</li>
                    <li>Maintain cleanliness and organization of the warehouse.</li>
                    <li>Follow safety and packaging standards.</li>
                </ul>
            </div>

            <div class="career-section">
                <h3>Requirements</h3>
                <ul>
                    <li>Dangerous Goods Training Certificate (IATA or equivalent) preferred.</li>
                    <li>Experience in ID 8000 Consumer Commodity packaging.</li>
                    <li>Ability to read, write, speak, and understand basic English.</li>
                    <li>Basic knowledge of MS Office and Adobe PDF preferred.</li>
                    <li>Candidates with UAE experience are encouraged to apply.</li>
                </ul>
            </div>

            <div class="career-section">
                <h3>Compensation &amp; Benefits</h3>
                <ul>
                    <li>Salary: <strong>AED 1,800</strong> per month.</li>
                    <li>Visa provided.</li>
                    <li>Medical insurance.</li>
                    <li>Air ticket every 2 years.</li>
                    <li>Yearly bonus based on performance.</li>
                </ul>
            </div>

            <div class="career-section">
                <h3>Skills</h3>
                <ul class="career-skills-grid">
                    <li>Basic knowledge of Dangerous Goods handling (especially ID 8000 Consumer Commodity)</li>
                    <li>Familiarity with e-commerce packing, labeling, and shipment preparation</li>
                    <li>Ability to perform FNSKU labeling and use basic label printers</li>
                    <li>Attention to detail to ensure correct labeling, packing, and documentation</li>
                    <li>Ability to follow instructions precisely and work efficiently</li>
                    <li>Basic computer skills (MS Office, PDF editing, email)</li>
                    <li>Physical ability to lift boxes and perform manual warehouse tasks</li>
                    <li>Good organizational and time-management skills</li>
                    <li>Ability to work in a fast-paced environment and meet deadlines</li>
                    <li>Reliable, punctual, and disciplined with good work ethics</li>
                    <li>Team player with basic communication skills in English</li>
                    <li>Prior UAE warehouse or logistics experience is an advantage</li>
                </ul>
            </div>

            <footer class="career-footer">
                <p>
                    To apply, please send your updated CV to:
                    <strong>ops@globaltradeservices.ae</strong> /
                    <strong>hr@globaltradeservices.ae</strong>
                    with the subject line:
                    <em>“Application – Warehouse Labor”</em>.
                </p>
            </footer>
        </article>

    </div>
</section>
@endsection
