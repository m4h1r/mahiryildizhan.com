@php
	$rawClientId = trim((string) \App\Models\Setting::get('adsense_client_id'));
	$clientId = $rawClientId === ''
		? null
		: (str_starts_with($rawClientId, 'ca-pub-') ? $rawClientId : (str_starts_with($rawClientId, 'pub-') ? 'ca-'.$rawClientId : $rawClientId));
@endphp
@if($clientId)
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $clientId }}" crossorigin="anonymous"></script>
@endif
