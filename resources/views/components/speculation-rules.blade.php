{{-- Speculation Rules (W12) — instant navigation by prefetching same-origin
     links on hover/viewport. Conservative eagerness; admin/auth/action URLs
     are excluded so nothing destructive is prefetched. --}}
<script type="speculationrules" nonce="{{ request()->attributes->get('csp_nonce', '') }}">
{
    "prefetch": [
        {
            "source": "document",
            "where": {
                "and": [
                    { "href_matches": "/*" },
                    { "not": { "href_matches": "/admin*" } },
                    { "not": { "href_matches": "/login*" } },
                    { "not": { "href_matches": "/register*" } },
                    { "not": { "href_matches": "/profile*" } },
                    { "not": { "href_matches": "/logout*" } },
                    { "not": { "selector_matches": "[data-no-prefetch]" } }
                ]
            },
            "eagerness": "moderate"
        }
    ]
}
</script>
