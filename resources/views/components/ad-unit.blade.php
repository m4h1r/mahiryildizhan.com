@php
    $rawClientId = trim((string) \App\Models\Setting::get('adsense_client_id'));
    $clientId = $rawClientId === ''
        ? null
        : (str_starts_with($rawClientId, 'ca-pub-') ? $rawClientId : (str_starts_with($rawClientId, 'pub-') ? 'ca-'.$rawClientId : $rawClientId));
    $rawSlotId = trim((string) \App\Models\Setting::get('adsense_slot_id'));
    $slotId = preg_replace('/\D+/', '', $rawSlotId);
    $slotId = $slotId !== '' ? $slotId : null;
@endphp
@if($clientId && $slotId)
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="{{ $clientId }}"
     data-ad-slot="{{ $slotId }}"
     data-ad-format="auto"
     data-full-width-responsive="true"></ins>
<script nonce="{{ request()->attributes->get('csp_nonce', '') }}">(adsbygoogle = window.adsbygoogle || []).push({});</script>
@endif
