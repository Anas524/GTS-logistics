<h2>New Contact Request – GTS</h2>

<p><strong>Name:</strong> {{ $data['name'] }}</p>
<p><strong>Email:</strong> {{ $data['email'] }}</p>
@if(!empty($data['phone']))
<p><strong>Phone:</strong> {{ $data['phone'] }}</p>
@endif

<p><strong>Service:</strong> {{ $data['service'] }}</p>
@if(!empty($data['contact_pref']))
<p><strong>Preferred Contact:</strong> {{ $data['contact_pref'] }}</p>
@endif

<hr>
<p><strong>Message:</strong></p>
<p>{!! nl2br(e($data['message'])) !!}</p>