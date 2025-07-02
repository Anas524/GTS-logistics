@extends('gts') {{-- Assuming your main layout is named gts.blade.php --}}

@section('content')
<section class="gts-section" id="amazon-services">
    <div class="container modern-int-shipping">
        <div class="intro-header">
            <h2><span class="emoji"><i data-lucide="box" class="icon"></i></span> Amazon FBA Prep & Labeling</h2>
            <p class="tagline">Hassle-Free Packaging & Compliance Services for Amazon Sellers</p>
        </div>

        <div class="intro-grid">
            <div class="intro-left">
                <p>Selling on Amazon requires strict packaging, prep, and labeling compliance to avoid delays, penalties, or rejections. At <strong>GTS Logistics & Air Cargo Services</strong>, we offer Amazon FBA prep and labeling services, ensuring your shipments meet Amazon’s fulfillment requirements for smooth delivery to warehouses.</p>
                <p>From box sizing and palletizing to LTL (Less Than Truckload) shipments and SPD (Single Parcel Delivery), we handle everything to ensure your shipments reach Amazon fulfillment centers smoothly and on time.</p>
            </div>
            <div class="intro-right">
                <img src="{{ asset('images/amazon-fba.png') }}" alt="Amazon FBA Services" class="shipping-graphic" />
            </div>
        </div>
        <div class="fba-service-table">
            <h4><i data-lucide="package-search"></i> Our FBA Prep Services & Rates</h4>
            <div class="fba-table-row fba-table-head">
                <div class="fba-col service">Service</div>
                <div class="fba-col rate">Rate (AED)</div>
            </div>
            <div class="fba-table-row">
                <div class="fba-col service">📦 Barcode Labeling</div>
                <div class="fba-col rate">AED 0.99 per unit</div>
            </div>
            <div class="fba-table-row">
                <div class="fba-col service">🧽 Bubble Wrapping</div>
                <div class="fba-col rate">AED 1.99 per unit</div>
            </div>
            <div class="fba-table-row">
                <div class="fba-col service">📦 Poly Bagging</div>
                <div class="fba-col rate">AED 1.70 per unit</div>
            </div>
            <div class="fba-table-row">
                <div class="fba-col service">📦 Boxing (max 22.5kg)</div>
                <div class="fba-col rate">AED 40 per box</div>
            </div>
        </div>

        <div class="destination-tags">
            <h4><i data-lucide="clipboard-list" class="icon"></i> Amazon FBA Guidelines</h4>
            <div class="text-content guidelines">
                <ul class="guidelines-list">
                    <li><strong>✔ Box Size & Weight Limits:</strong>
                        <ul>
                            <li class="short-list">Max box weight: 50 lbs (22.6 kg) (unless oversized)</li>
                            <li class="short-list">Max box dimensions: 25 inches (63.5 cm) on any side</li>
                            <li class="short-list">Heavy boxes over 50 lbs need “Team Lift” labels</li>
                            <li class="short-list">Boxes over 100 lbs require “Mechanical Lift” labels</li>
                        </ul>
                    </li>

                    <li><strong>✔ Palletizing Rules:</strong>
                        <ul>
                            <li class="short-list">Use standard 40" x 48" wooden pallets</li>
                            <li class="short-list">Max pallet height: 72 inches (182 cm) including pallet</li>
                            <li class="short-list">Secure with stretch film</li>
                            <li class="short-list">Label each pallet with Amazon shipment ID</li>
                        </ul>
                    </li>

                    <li><strong>✔ Packaging Rules:</strong>
                        <ul>
                            <li class="short-list">Each product must be individually packaged</li>
                            <li class="short-list">Use poly bags for small/loose items</li>
                            <li class="short-list">Fragile items require bubble wrap/foam protection</li>
                            <li class="short-list">Expiration-dated products need clear labels</li>
                        </ul>
                    </li>
                </ul>
                <p>Following these guidelines ensures Amazon accepts your shipments smoothly, reducing delays or extra charges.</p>
            </div>
        </div>

        <div class="modern-faq">
            <h4><i data-lucide="help-circle" class="icon"></i> Frequently Asked Questions</h4>
            <div class="faq-item">
                <button class="faq-question"> Do you provide labeling and packaging for Amazon sellers?</button>
                <div class="faq-answer">
                    <p>Yes, we handle all prep, packaging, labeling, and compliance to meet Amazon FBA standards.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question"> Can you ship directly to Amazon fulfillment centers?</button>
                <div class="faq-answer">
                    <p>Absolutely. We ship directly to Amazon warehouses globally using SPD or LTL shipping methods.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question"> Do you support small or bulk shipments?</button>
                <div class="faq-answer">
                    <p>Yes, we support both small parcel and bulk pallet shipments based on your needs.</p>
                </div>
            </div>
        </div>

        <div class="closing-summary">
            <h3><i data-lucide="truck" class="icon"></i> Partner with GTS for Seamless Amazon FBA Logistics</h3>
            <p>Whether you're launching your first product or scaling your Amazon store, we provide reliable and affordable FBA prep, labeling, and shipping solutions.</p>
        </div>

        <h4 class="faq animate faq-heading">
            <i data-lucide="truck" class="icon"></i> LTL (Less Than Truckload) & Palletized Shipments – Amazon FBA Labeling Procedure
        </h4>
        <p class="faq-para">
            For bulk shipments, Amazon FBA LTL (Less Than Truckload) or palletized shipping is the best choice. However, proper labeling is essential to avoid penalties or shipment rejections.
        </p>

        <div class="modern-faq">
            <div class="faq-item">
                <button class="faq-question"><i data-lucide="barcode" class="icon"></i> Printing FNSKU Labels</button>
                <div class="faq-answer">
                    <p>Each unit must have a Fulfillment Network Stock Keeping Unit (FNSKU), which helps Amazon track and identify products within their system. We generate and print Amazon-compliant FNSKU barcodes to ensure accurate inventory management.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question"><i data-lucide="scan-barcode" class="icon"></i> Applying FNSKU Labels on Individual Units</button>
                <div class="faq-answer">
                    <p>The FNSKU labels must be affixed to every individual unit in a scannable position, covering any other manufacturer barcodes to prevent errors during Amazon’s receiving process.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question"><i data-lucide="package" class="icon"></i> Packing the Units in Secure 5-Ply Boxes</button>
                <div class="faq-answer">
                    <p>
                        To prevent damage, each unit is carefully packed using:
                        <br>✔ Bubble wrap for fragile items
                        <br>✔ Shrink wrapping for added protection
                        <br>✔ Void fillers (paper, foam, air pillows)
                        <br>✔ Tamper-proof security taping
                    </p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question"><i data-lucide="lock" class="icon"></i> Securely Sealing the Boxes</button>
                <div class="faq-answer">
                    <p>Boxes are securely sealed using high-quality tape and straps to withstand handling during transit, ensuring compliance with Amazon’s safety standards.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question"><i data-lucide="label" class="icon"></i> Labeling the Amazon Shipment Boxes</button>
                <div class="faq-answer">
                    <p>
                        Each box must have:
                        <br>✔ Amazon’s warehouse address
                        <br>✔ Shipment ID & tracking barcodes
                        <br>✔ Handling instructions (if any)
                        <br>✔ LTL/FTL pallet labels (if required)
                    </p>
                </div>
            </div>
        </div>

        <p style="margin-top: 30px;"><strong><i data-lucide="info" class="icon"></i> Preferred Labeling Method for LTL Shipments:</strong></p>
        <ul class="clean-ul">
            <li>✔ Use thermal labels to avoid smudging.</li>
            <li>✔ Label size should be 4" x 6" for clear visibility.</li>
            <li>✔ Ensure labels are not damaged or folded.</li>
        </ul>

        <h4 class="faq animate faq-heading">
            <i data-lucide="package-check" class="icon"></i> SPD (Single Parcel Delivery) – Amazon FBA Labeling Best Practices
        </h4>
        <p class="faq-para">
            For smaller shipments, Single Parcel Delivery (SPD) is a quick and cost-effective option. Each package must meet Amazon's strict SPD labeling and packaging requirements.
        </p>

        <div class="modern-faq">
            <div class="faq-item">
                <button class="faq-question"><i data-lucide="box-select" class="icon"></i> Each Box Needs a FBA Box Label</button>
                <div class="faq-answer">
                    <p>Generated from Seller Central and must be clearly visible for scanning.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question"><i data-lucide="truck" class="icon"></i> Carrier Shipping Labels</button>
                <div class="faq-answer">
                    <p>Attach courier-specific labels such as DHL, FedEx, UPS properly.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question"><i data-lucide="ruler" class="icon"></i> Weight & Dimension Compliance</button>
                <div class="faq-answer">
                    <p>Each box must not exceed 50 lbs (22.6 kg) and should follow Amazon’s dimensional limits.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question"><i data-lucide="box" class="icon"></i> Proper Packing</button>
                <div class="faq-answer">
                    <p>Use strong boxes, bubble wrap, poly bags, and fillers to prevent item damage during transit.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question"><i data-lucide="barcode" class="icon"></i> Barcode Readability</button>
                <div class="faq-answer">
                    <p>Do not place labels on seams or corners. Ensure all barcodes are clean and scannable.</p>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection