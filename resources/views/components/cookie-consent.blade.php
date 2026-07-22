<div
    id="cookie-consent-banner"
    class="fixed inset-x-0 bottom-0 z-50 hidden border-t border-gray-200 bg-white/95 px-4 py-4 backdrop-blur dark:border-gray-800 dark:bg-gray-950/95"
    role="dialog"
    aria-live="polite"
    aria-label="{{ __('Cookie consent') }}"
>
    <div class="mx-auto flex max-w-6xl flex-col items-start gap-3 md:flex-row md:items-center md:justify-between">
        <p class="text-sm text-gray-600 dark:text-gray-300">
            {{ __('We use cookies for analytics and ads. You can accept or reject non-essential cookies.') }}
        </p>
        <div class="flex shrink-0 gap-2">
            <button type="button" data-consent-reject class="rounded-full border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">
                {{ __('Reject') }}
            </button>
            <button type="button" data-consent-accept class="rounded-full bg-[#1d1d1f] px-4 py-2 text-xs font-semibold text-white dark:bg-white dark:text-black">
                {{ __('Accept') }}
            </button>
        </div>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce', '') }}">
    (function () {
        var STORAGE_KEY = 'cookie_consent';
        var banner = document.getElementById('cookie-consent-banner');
        if (!banner) {
            return;
        }

        var stored = null;
        try {
            stored = localStorage.getItem(STORAGE_KEY);
        } catch (e) {
            stored = null;
        }

        function updateConsent(granted) {
            if (typeof window.gtag !== 'function') {
                return;
            }
            window.gtag('consent', 'update', {
                ad_storage: granted ? 'granted' : 'denied',
                ad_user_data: granted ? 'granted' : 'denied',
                ad_personalization: granted ? 'granted' : 'denied',
                analytics_storage: granted ? 'granted' : 'denied',
            });
        }

        if (stored === null) {
            banner.classList.remove('hidden');
        }

        banner.querySelector('[data-consent-accept]').addEventListener('click', function () {
            try {
                localStorage.setItem(STORAGE_KEY, 'granted');
            } catch (e) {}
            updateConsent(true);
            banner.classList.add('hidden');
        });

        banner.querySelector('[data-consent-reject]').addEventListener('click', function () {
            try {
                localStorage.setItem(STORAGE_KEY, 'denied');
            } catch (e) {}
            updateConsent(false);
            banner.classList.add('hidden');
        });
    })();
</script>
