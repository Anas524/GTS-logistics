@extends('layouts.gts_app')

@section('content')
@php
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
@endphp

<section id="admin-leads">
    <div class="dash-shell">
        <div class="dash-headbar">
            <div class="dash-head-left">
                <h1 class="dash-title"><i class="fa-solid fa-address-book"></i> Leads</h1>
                <p class="dash-sub">Contact messages and newsletter signups.</p>
            </div>
            <div class="dash-head-right">
                <form class="dash-searchbar" method="GET" action="{{ route('leads.index') }}">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <input type="search" name="q" value="{{ $q }}" placeholder="Search name/email/phone/service">
                </form>
                <a href="{{ route('admin.dashboard') }}" class="btn-ghost">Back</a>
            </div>
        </div>

        <div class="dash-tiprow just-between">
            <div class="tip-left">
                <i class="fa-regular fa-circle-question" aria-hidden="true"></i>
                <span>Use the tabs to switch between contacts and newsletter signups.</span>
            </div>
            <div class="tab-switch">
                <a href="{{ route('leads.index', ['tab'=>'contacts']) }}"
                    @class(['tab-btn','btn-ghost','is-active'=> $tab==='contacts'])>
                    Contacts
                </a>
                <a href="{{ route('leads.index', ['tab'=>'newsletter']) }}"
                    @class(['tab-btn','btn-ghost','is-active'=> $tab==='newsletter'])>
                    Newsletter
                </a>
            </div>
        </div>

        @if($tab==='contacts')
        <div class="tool-card card-table">
            <table class="leads-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Service</th>
                        <th>When</th>
                        <th>Attachment</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contacts as $c)
                    <tr>
                        <td>{{ $c->name }}</td>
                        <td><a href="mailto:{{ $c->email }}">{{ $c->email }}</a></td>
                        <td>{{ $c->phone }}</td>
                        <td>{{ $c->service }}</td>
                        <td>{{ Carbon::parse($c->created_at)->diffForHumans() }}</td>
                        <td>
                            @if($c->attachment_path)
                            <a href="{{ url('storage/contact_attachments/' . basename($c->attachment_path)) }}" target="_blank" class="btn-ghost sm">Open</a>
                            @else
                            —
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="6">No contacts yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $contacts?->links() }}

        @else
        <div class="tool-card card-table">
            <table class="leads-table">
                @php($tz = config('app.timezone'))
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Subscribed at</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($newsletter as $n)
                    <tr>
                        <td><a href="mailto:{{ $n->email }}">{{ $n->email }}</a></td>
                        <td>{{ \Carbon\Carbon::parse($n->updated_at)->timezone(config('app.timezone'))->format('Y-m-d h:i A') }}</td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="3">No subscribers yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $newsletter?->links() }}
        @endif
    </div>
</section>
@endsection