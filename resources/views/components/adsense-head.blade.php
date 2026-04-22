@php $clientId = \App\Models\Setting::get('adsense_client_id'); @endphp
@if($clientId)
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $clientId }}" crossorigin="anonymous"></script>
@endif
