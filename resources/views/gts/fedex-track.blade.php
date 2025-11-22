@extends('gts')

@section('title', 'FedEx Tracking')

@section('content')
    <div class="fedex-track-wrap">
        <section class="fedex-section">
            <div class="container">
                <h1 class="fedex-title">FedEx Shipment Tracking</h1>
                <p class="fedex-subtitle">Enter a tracking number to see live shipment status.</p>

                <form method="POST" action="{{ route('fedex.track.submit') }}" class="fedex-form">
                    @csrf
                    <div class="form-row">
                        <input type="text" name="tracking_number"
                            value="{{ old('tracking_number', $tracking_number ?? '') }}"
                            placeholder="Enter tracking number" required>
                        <button type="submit">Track Now</button>
                    </div>
                    @error('tracking_number')
                        <p class="error">{{ $message }}</p>
                    @enderror
                </form>

                @isset($result)
                    <div class="fedex-result">
                        <h2>Status: <span>{{ $result['status'] ?? 'Unknown' }}</span></h2>
                        <p><strong>Tracking #:</strong> {{ $result['tracking_number'] ?? '-' }}</p>
                        <p><strong>Location:</strong> {{ $result['status_location_full'] ?? '-' }}</p>
                        <p><strong>Last Update:</strong> {{ $result['status_datetime'] ?? '-' }}</p>
                        <p><strong>Estimated Delivery:</strong> {{ $result['estimated_delivery'] ?? '-' }}</p>
                    </div>
                @endisset
            </div>
        </section>
    </div>
@endsection
