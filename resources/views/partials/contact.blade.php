<section id="contact-section" class="contact">
    <div class="contact-wrap">

        {{-- Left: quick info / CTAs --}}
        <div class="contact-aside">
            <h2>Let’s talk logistics</h2>
            <p class="muted">Tell us about your shipment and we’ll get back fast.</p>

            <ul class="contact-list">
                <li><i class="fa-solid fa-envelope"></i> <a href="mailto:ops@globaltradeservices.ae">ops@globaltradeservices.ae</a></li>
                <li><i class="fa-solid fa-mobile-screen-button"></i> <a href="tel:+971581146187">+971 58 114 6187</a></li>
                <li><i class="fa-solid fa-phone"></i> <a href="tel:045774859">045774859</a></li>
                <li><i class="fa-brands fa-whatsapp"></i> <a target="_blank" href="https://wa.me/971523194073?text=Hi%20GTS,%20I%27d%20like%20to%20inquire%20about%20shipping.">WhatsApp chat</a></li>
                <li><i class="fa-solid fa-location-dot"></i> Warehouse 03, Al mana Building Deira Al khabisi Dubai, United Arab Emirates, PO BOX 11108</li>
            </ul>

            <div class="cta-pills">
                <a class="pill" href="{{ url('/') }}#services"><i class="fa-solid fa-box"></i> Our services</a>
                <button type="button" class="pill js-open-quote">
                    <i class="fa-solid fa-file-invoice-dollar"></i> Request a quote
                </button>
            </div>
        </div>

        {{-- Right: form --}}
        <div class="contact-form-card">
            @if (session('contact_ok'))
            <div class="contact-success">{{ session('contact_ok') }}</div>
            @endif

            @if(old('intent') === 'quote')
            <div class="contact-success" style="background:#eef5fd;border-color:#d7e8ff;color:#0f2d4a;">
                Request a quote
            </div>
            @endif

            <form id="contactForm" method="POST" action="{{ route('contact.submit') }}" enctype="multipart/form-data">
                @csrf
                <input type="text" name="company_website" tabindex="-1" autocomplete="off" class="hp">

                <input type="hidden" name="intent" id="intentField" value="{{ old('intent') }}">

                <div class="grid">
                    <div class="fl-group">
                        <input class="fl-control" type="text" name="name" placeholder=" " autocomplete="name" autocapitalize="words" required value="{{ old('name') }}">
                        <label class="fl-label">Full name*</label>
                        @error('name') <small class="e">{{ $message }}</small> @enderror
                    </div>

                    <div class="fl-group">
                        <input class="fl-control" type="email" name="email" placeholder=" " autocomplete="email" required value="{{ old('email') }}">
                        <label class="fl-label">Email*</label>
                        @error('email') <small class="e">{{ $message }}</small> @enderror
                    </div>

                    <div class="fl-group">
                        <input class="fl-control" type="tel" name="phone" placeholder=" " autocomplete="tel" inputmode="tel" pattern="[0-9+\s()-]*" value="{{ old('phone') }}">
                        <label class="fl-label">Phone (WhatsApp)</label>
                        @error('phone') <small class="e">{{ $message }}</small> @enderror
                    </div>

                    {{-- Service --}}
                    <div class="fl-group select-modern {{ old('service') ? 'filled' : '' }}">
                        <select name="service" class="fl-control" required>
                            <option value="" disabled {{ old('service') ? '' : 'selected' }} hidden>Service</option>
                            <option value="Air Cargo" @selected(old('service')=='Air Cargo' )>Air Cargo</option>
                            <option value="Sea Freight" @selected(old('service')=='Sea Freight' )>Sea Freight</option>
                            <option value="Warehousing" @selected(old('service')=='Warehousing' )>Warehousing</option>
                            <option value="Customs Clearance" @selected(old('service')=='Customs Clearance' )>Customs Clearance</option>
                            <option value="Amazon FBA Prep" @selected(old('service')=='Amazon FBA Prep' )>Amazon FBA Prep</option>
                            <option value="Other" @selected(old('service')=='Other' )>Other</option>
                        </select>
                        <label class="fl-label">Service*</label>
                        @error('service') <small class="e">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="fl-group">
                    <textarea class="fl-control" name="message" rows="5" placeholder=" " required>{{ old('message') }}</textarea>
                    <label class="fl-label">Message*</label>
                    @error('message') <small class="e">{{ $message }}</small> @enderror
                </div>

                <div class="grid">
                    {{-- Preferred contact --}}
                    <div class="fl-group select-modern {{ old('contact_pref') ? 'filled' : '' }}">
                        <select name="contact_pref" class="fl-control">
                            <option value="" disabled {{ old('contact_pref') ? '' : 'selected' }} hidden>Preferred contact</option>
                            <option value="Email" @selected(old('contact_pref')=='Email' )>Email</option>
                            <option value="Phone" @selected(old('contact_pref')=='Phone' )>Phone</option>
                            <option value="WhatsApp" @selected(old('contact_pref')=='WhatsApp' )>WhatsApp</option>
                        </select>
                        <label class="fl-label">Preferred contact</label>
                    </div>

                    {{-- File upload --}}
                    <div class="ui-file fl-group">
                        <div class="file-wrap">
                            <i class="fa-solid fa-paperclip file-icon"></i>
                            <input type="file" id="attachment" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
                            <span class="file-label" id="fileLabel">Choose file</span>
                            <button type="button" class="btn-file" onclick="document.getElementById('attachment').click();">Browse</button>
                        </div>
                        <label class="fl-label" style="top:-9px;font-size:.78rem;">Attach file (optional)</label>
                        @error('attachment') <small class="e">{{ $message }}</small> @enderror
                    </div>
                </div>

                <label class="consent">
                    <input type="checkbox" name="consent" value="1" {{ old('consent')?'checked':'' }} required>
                    <span>I agree to be contacted about my request.</span>
                    @error('consent') <small class="e">{{ $message }}</small> @enderror
                </label>

                <button type="submit" class="btn-primary" id="contactBtn">
                    <span class="t">Send message</span>
                    <span class="spinner" style="display:none;"></span>
                </button>
            </form>
        </div>
    </div>

    {{-- map embed --}}
    <div class="map-wrap">
        <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1516.978306732508!2d55.34352696114656!3d25.27049154084054!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e5f5dcb5a4fba2f%3A0xf77f7c3441237cd0!2sGTS%20LOGISTICS%20AIR%20CARGO%20SERVICES%20CO%20LLC!5e0!3m2!1sen!2sae!4v1760092574019!5m2!1sen!2sae"
            width="600"
            height="450"
            style="border:0;"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </div>
</section>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Service
        const ssService = new SlimSelect({
            select: 'select[name="service"]',
            settings: {
                placeholderText: 'Select a service',
                showSearch: false
            }
        });

        // Preferred contact
        const ssContact = new SlimSelect({
            select: 'select[name="contact_pref"]',
            settings: {
                placeholderText: 'Preferred contact',
                showSearch: false
            }
        });

        // Keep floating label state in sync
        const markFilled = (sel) => {
            const wrap = sel.closest('.fl-group');
            if (!wrap) return;
            wrap.classList.toggle('filled', !!sel.value);
        };

        const svc = document.querySelector('select[name="service"]');
        const pref = document.querySelector('select[name="contact_pref"]');

        if (svc) {
            markFilled(svc);
            svc.addEventListener('change', () => markFilled(svc));
        }
        if (pref) {
            markFilled(pref);
            pref.addEventListener('change', () => markFilled(pref));
        }
    });

    $(function() {
        const $form = $('#contactForm');
        const $btn = $('#contactBtn');

        if (!$form.length) return;

        $form.on('submit', function(e) {
            // if we already submitted once, block duplicates
            if ($btn.data('submitted')) {
                e.preventDefault();
                return false;
            }
            $btn.data('submitted', true)
                .prop('disabled', true)
                .addClass('is-busy');

            $('#contactBtn .t').hide();
            $('#contactBtn .spinner').show();
        });
    });

    $(function() {
        const $file = $('#attachment');
        const $label = $('#fileLabel');
        if ($file.length) {
            $file.on('change', function() {
                const fileName = this.files && this.files.length ? this.files[0].name : 'Choose file';
                $label.text(fileName);
            });
        }
    });

    $(function() {
        $('select.fl-control').each(function() {
            const $wrap = $(this).closest('.fl-group');

            function sync() {
                $(this).val() ? $wrap.addClass('filled') : $wrap.removeClass('filled');
            }
            $(this).on('change blur', sync);
            sync.call(this);
        });
    });

    (function() {
        const mark = el => {
            const g = el.closest('.fl-group');
            if (!g) return;
            const has = el.tagName === 'SELECT' ? !!el.value : (el.value && el.value.trim() !== '');
            g.classList.toggle('filled', has);
        };
        document.querySelectorAll('#contactForm .fl-control').forEach(el => {
            mark(el);
            el.addEventListener('input', () => mark(el));
            el.addEventListener('change', () => mark(el));
            el.addEventListener('blur', () => mark(el));
        });

        // file label text
        const f = document.getElementById('attachment');
        const lab = document.getElementById('fileLabel');
        if (f && lab) f.addEventListener('change', () => lab.textContent = f.files?.[0]?.name || 'Choose file');
    })();

    $(function() {
        $(document).on('click', '.js-open-quote', function(e) {
            e.preventDefault();

            // mark intent
            $('#intentField').val('quote');

            // visuals + copy
            $('.contact-form-card').addClass('is-quote');
            $('.contact-aside h2').text('Request a quote');
            $('.contact-aside .muted').text("Tell us what you need and we’ll send a quote fast.");
            $('#contactBtn .t').text('Request quote');

            // optional placeholder tweak
            const $msg = $('textarea[name="message"]');
            if ($msg.length && !$msg.val()) {
                $msg.attr('placeholder', 'Items, quantities, origin/destination, timing…');
            }

            // smooth scroll (no shake)
            const $form = $('#contactForm');
            if (!$form.length) return;

            const target = Math.max($form.offset().top - 120, 0);
            const current = $(window).scrollTop();

            // stop any ongoing animations to prevent bounce
            $('html, body').stop(true);

            if (Math.abs(current - target) < 4) {
                $form.find('input[name="name"]').trigger('focus');
            } else {
                $('html, body').animate({
                    scrollTop: target
                }, 350, 'swing', function() {
                    $form.find('input[name="name"]').trigger('focus');
                });
            }
        });
    });
</script>
@endpush