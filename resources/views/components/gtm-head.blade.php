@php $gaId = \App\Models\Setting::get('ga_tracking_id'); @endphp
@if($gaId)
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
<script nonce="{{ request()->attributes->get('csp_nonce', '') }}">
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{{ $gaId }}');
</script>
@endif
