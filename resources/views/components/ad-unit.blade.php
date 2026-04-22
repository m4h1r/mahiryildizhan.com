@php
    $clientId = \App\Models\Setting::get('adsense_client_id');
    $slotId   = \App\Models\Setting::get('adsense_slot_id');
@endphp
@if($clientId && $slotId)
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="{{ $clientId }}"
     data-ad-slot="{{ $slotId }}"
     data-ad-format="auto"
     data-full-width-responsive="true"></ins>
<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
@endif
