@extends('gts')

@section('content')

@php
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Find most recent activity across both sources
$leadsLast = DB::table('contact_messages')->max('created_at');
$newsLast = DB::table('newsletter_subscriptions')->max('created_at');
$latestLeads = collect([$leadsLast, $newsLast])
->filter()
->map(fn($t) => Carbon::parse($t))
->sortDesc()
->first();

/** @var \App\Models\User|null $user */
$user = auth()->user();
$isAdmin = $user && method_exists($user, 'isAdmin') && $user->isAdmin();
$isConsultant = $user && method_exists($user, 'isConsultant') && $user->isConsultant();
@endphp

<section id="admin-dashboard" data-user-id="{{ auth()->id() }}">
  <div class="dash-shell"><!-- wrapper (styled by style.css) -->

    {{-- Top toolbar (below site header) --}}
    <div class="dash-headbar">
      <div class="dash-head-left">
        <h1 class="dash-title">✨ Welcome back</h1>

        @if($isConsultant)
        <p class="dash-sub">
          You’re signed in as a <strong>consultant</strong>. You can view items in the Document Hub.
        </p>
        @else
        <p class="dash-sub">Choose a tool to get started.</p>
        @endif
      </div>

      <div class="dash-head-right">
        @if($isAdmin)
        <label class="dash-searchbar" aria-label="Find a tool">
          <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
          <input type="search" placeholder="Find a tool…" />
        </label>

        <button class="dash-icon-btn" type="button" aria-label="Settings">
          <i class="fa-solid fa-gear"></i>
        </button>

        <!-- Settings dropdown (hidden by default) -->
        <div class="dash-settings-popover" role="menu" aria-hidden="true">
          <button type="button" class="dash-menu-item" id="btn-open-reset">
            <i class="fa-solid fa-key"></i> Reset password
          </button>
        </div>

        <!-- Reset Password Modal -->
        <div id="resetPassModal" class="dash-modal" aria-hidden="true">
          <div class="dash-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="resetTitle">
            <div class="dash-modal-head">
              <div class="dash-title">
                <i class="fa-solid fa-shield-keyhole"></i>
                <h3 id="resetTitle">Reset your password</h3>
              </div>
              <button type="button" class="dash-close" id="resetCancel" aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>

            <form id="resetPassForm" method="POST" action="{{ route('account.password') }}" autocomplete="on">
              @csrf

              {{-- Show logged-in user --}}
              <div class="dash-userline" title="You are updating the password for this account">
                <i class="fa-regular fa-user"></i>
                <span>{{ auth()->user()->email }}</span>
                {{-- Hidden username field helps some password managers --}}
                <input type="hidden" name="username" value="{{ auth()->user()->email }}">
              </div>

              {{-- Current password --}}
              <label class="dash-field">
                <span>Current password</span>
                <div class="dash-inputwrap">
                  <i class="fa-solid fa-lock"></i>
                  <input
                    type="password"
                    name="current_password"
                    id="currPass"
                    required
                    autocomplete="current-password"
                    inputmode="text">
                  <button type="button" class="pw-toggle" data-target="currPass" aria-label="Show password">
                    <i class="fa-regular fa-eye" aria-hidden="true"></i>
                  </button>
                </div>
              </label>

              {{-- New password --}}
              <label class="dash-field">
                <span>New password</span>
                <div class="dash-inputwrap">
                  <i class="fa-solid fa-key"></i>
                  <input
                    type="password"
                    name="password"
                    id="newPass"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    inputmode="text">
                  <button type="button" class="pw-toggle" data-target="newPass" aria-label="Show password">
                    <i class="fa-regular fa-eye" aria-hidden="true"></i>
                  </button>
                </div>
              </label>

              {{-- Confirm --}}
              <label class="dash-field">
                <span>Confirm new password</span>
                <div class="dash-inputwrap">
                  <i class="fa-solid fa-key"></i>
                  <input
                    type="password"
                    name="password_confirmation"
                    id="confirmPass"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    inputmode="text">
                  <button type="button" class="pw-toggle" data-target="confirmPass" aria-label="Show password">
                    <i class="fa-regular fa-eye" aria-hidden="true"></i>
                  </button>
                </div>
              </label>

              <div class="dash-modal-actions">
                <button type="button" class="btn-ghost" id="resetCancelFooter">Cancel</button>
                <button type="submit" class="btn-primary-dark">Update</button>
              </div>
            </form>
          </div>
        </div>
        @endif

        <form method="POST" action="{{ route('logout') }}" class="dash-logout-inline">
          @csrf
          <button type="submit" class="dash-logout-btn">Logout</button>
        </form>
      </div>
    </div>

    {{-- Tip strip --}}
    @if($isAdmin)
    <div class="dash-tiprow">
      <i class="fa-regular fa-circle-question" aria-hidden="true"></i>
      <span>Tip: Use search to find tools fast. Your most recently used tools appear first.</span>
    </div>
    @elseif($isConsultant)
    <div class="dash-tiprow">
      <i class="fa-regular fa-circle-question" aria-hidden="true"></i>
      <span>Tip: Open the Document Hub card below to browse folders and view attachments. Uploads and deletions are done by admins.</span>
    </div>
    @endif

    {{-- Tools grid --}}
    <div class="dash-tools-grid">

      {{-- Card: Amazon Revenue Calculator --}}
      @if($user && method_exists($user, 'isAdmin') && $user->isAdmin())
      <article class="tool-card"
        data-tool-id="calculator"
        data-tool-name="Amazon Revenue Calculator"
        data-tool-desc="Estimate landed cost, fees, and net margin in seconds. Save scenarios for later."
        data-last-updated="{{ now()->toIso8601String() }}"
        data-open-href="{{ route('calculator.index') }}">
        <div class="tool-card-left">
          <div class="tool-icon">
            <i class="fa-solid fa-calculator" aria-hidden="true"></i>
          </div>
          <div class="tool-meta">
            <div class="tool-title">
              Amazon Revenue Calculator
              <span class="badge badge-updated">Updated</span>
            </div>
            <div class="tool-updated">
              <i class="fa-regular fa-clock" aria-hidden="true"></i>
              <span class="js-updated-calculator">Updated today</span>
            </div>
            <p class="tool-desc">
              Estimate landed cost, fees, and net margin in seconds. Save scenarios for later.
            </p>
          </div>
        </div>

        <div class="tool-actions">
          <a href="{{ route('calculator.index') }}" class="btn-primary-dark">Open</a>
        </div>
      </article>
      @endif

      {{-- Card: Investment Sheets --}}
      @if($user && method_exists($user, 'isAdmin') && $user->isAdmin())
      <article class="tool-card"
        data-tool-id="invest"
        data-tool-name="Investment Sheets"
        data-tool-desc="Compare opportunities, track ROI, and export investor-ready PDFs."
        data-last-updated="{{ now()->subDays(2)->toIso8601String() }}"
        data-open-href="{{ route('investment.index') }}">
        <div class="tool-card-left">
          <div class="tool-icon">
            <i class="fa-regular fa-file-lines" aria-hidden="true"></i>
          </div>
          <div class="tool-meta">
            <div class="tool-title">
              Investment Sheets
            </div>
            <div class="tool-updated">
              <i class="fa-regular fa-clock" aria-hidden="true"></i>
              <span class="js-updated-invest">Updated 2d ago</span>
            </div>
            <p class="tool-desc">
              Compare opportunities, track ROI, and export investor-ready PDFs.
            </p>
          </div>
        </div>

        <div class="tool-actions">
          <a href="{{ route('investment.index') }}" class="btn-primary-dark">Open</a>
        </div>
      </article>
      @endif

      {{-- Card: Leads (Contacts + Newsletter) --}}
      @if($user && method_exists($user, 'isAdmin') && $user->isAdmin())
      <article class="tool-card"
        data-tool-id="leads"
        data-tool-name="Leads"
        data-tool-desc="Contact messages and newsletter signups."
        data-last-updated="{{ isset($latestLeads) ? $latestLeads->toIso8601String() : '' }}"
        data-open-href="{{ route('leads.index') }}">
        <div class="tool-card-left">
          <div class="tool-icon">
            <i class="fa-solid fa-address-book" aria-hidden="true"></i>
          </div>
          <div class="tool-meta">
            <div class="tool-title">Leads</div>
            <div class="tool-updated">
              <i class="fa-regular fa-clock" aria-hidden="true"></i>
              <span class="js-updated-leads">
                {{ isset($latestLeads) ? $latestLeads->diffForHumans() : 'No submissions yet' }}
              </span>
            </div>
            <p class="tool-desc">Contact messages and newsletter signups.</p>
          </div>
        </div>

        <div class="tool-actions">
          <a href="{{ route('leads.index', ['tab' => 'contacts']) }}" class="btn-primary-dark">Open</a>
        </div>
      </article>
      @endif

      {{-- Card: Document Hub --}}
      @if($user && ( (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isConsultant') && $user->isConsultant()) ))
      <article class="tool-card"
        data-tool-id="docs"
        data-tool-name="Document Hub"
        data-tool-desc="Store internal documents and attachments."
        data-last-updated="{{ now()->toIso8601String() }}"
        data-open-href="{{ route('dh.index') }}">
        <div class="tool-card-left">
          <div class="tool-icon">
            <i class="fa-regular fa-folder-open" aria-hidden="true"></i>
          </div>
          <div class="tool-meta">
            <div class="tool-title">
              Document Hub
            </div>
            <div class="tool-updated">
              <i class="fa-regular fa-clock" aria-hidden="true"></i>
              <span>Central place for admin files</span>
            </div>
            <p class="tool-desc">
              Organise folders, add dated records, and upload/view attachments.
            </p>
          </div>
        </div>

        <div class="tool-actions">
          <a href="{{ route('dh.index') }}" class="btn-primary-dark">Open</a>
        </div>
      </article>
      @endif

    </div>
  </div>
</section>
@endsection