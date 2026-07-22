<script nonce="{{ request()->attributes->get('csp_nonce', '') }}">
    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }

    var storedConsent = null;
    try {
        storedConsent = localStorage.getItem('cookie_consent');
    } catch (e) {
        storedConsent = null;
    }
    var consentGranted = storedConsent === 'granted';

    gtag('consent', 'default', {
        ad_storage: consentGranted ? 'granted' : 'denied',
        ad_user_data: consentGranted ? 'granted' : 'denied',
        ad_personalization: consentGranted ? 'granted' : 'denied',
        analytics_storage: consentGranted ? 'granted' : 'denied',
        wait_for_update: 500,
    });
</script>
