<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>GTS Logistics & Air Cargo Services</title>

  <link rel="icon" href="{{ asset('images/gtslogo.png') }}">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>window.$ ||= window.jQuery;</script>

  <meta name="csrf-token" content="{{ csrf_token() }}">
  @stack('head')
</head>
<body class="{{ request()->is('amazon-services') ? 'amazon-only' : '' }}">
  @include('partials.topbar')
  @include('partials.header')

  @yield('content')

  @include('partials.footer')

  <script src="{{ asset('js/script.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

  @stack('scripts')
</body>
</html>
