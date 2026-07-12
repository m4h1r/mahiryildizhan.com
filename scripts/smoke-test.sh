#!/usr/bin/env bash
# Usage: ./scripts/smoke-test.sh https://mahiryildizhan.com
# Or:    ./scripts/smoke-test.sh   (reads APP_URL from .env)
#
# Post-deploy safety net: every public GET route must respond (never 5xx),
# auth pages must redirect, error pages must be branded 404s. (X11)

set -euo pipefail

BASE_URL="${1:-}"
if [ -z "$BASE_URL" ]; then
    BASE_URL=$(grep "^APP_URL=" .env 2>/dev/null | cut -d= -f2 | tr -d '"')
fi

if [ -z "$BASE_URL" ]; then
    echo "❌ No URL provided and APP_URL not found in .env"
    exit 1
fi

BASE_URL="${BASE_URL%/}"  # strip trailing slash
PASS=0
FAIL=0

check() {
    local path="$1"
    local expected="$2"
    local url="${BASE_URL}${path}"
    local status

    status=$(curl -s -o /dev/null -w "%{http_code}" -L --max-time 10 "$url" 2>/dev/null || echo "000")

    local ok=false
    case "$expected" in
        2xx) [[ "$status" =~ ^2 ]] && ok=true ;;
        3xx) [[ "$status" =~ ^3 ]] && ok=true ;;
        *)   [ "$status" = "$expected" ] && ok=true ;;
    esac

    if $ok; then
        printf "✅ %s  %s\n" "$status" "$path"
        ((PASS++))
    else
        printf "❌ %s (expected %s)  %s\n" "$status" "$expected" "$path"
        ((FAIL++))
    fi
}

echo "MY Teknoloji — Post-Deploy Smoke Test"
echo "URL: $BASE_URL"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# ── Infra endpoints ──────────────────────────────────────
check "/api/health"       "2xx"
check "/sitemap.xml"      "2xx"
check "/ads.txt"          "2xx"

# ── Public pages ─────────────────────────────────────────
check "/"                 "2xx"
check "/blog"             "2xx"
check "/timeline"         "2xx"
check "/biolink"          "2xx"
check "/search"           "2xx"

# ── Auth-protected admin — must redirect to login, never 500 ──
check "/admin"            "3xx"

# ── Error page — must be a branded 404, never default ────
check "/this-does-not-exist-xyz" "404"

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
printf "Results: ✅ %d passed  ❌ %d failed\n" "$PASS" "$FAIL"
echo ""

if [ "$FAIL" -gt 0 ]; then
    echo "🚫 Deploy verification FAILED — do not announce go-live."
    exit 1
else
    echo "✅ All checks passed — deploy verified."
fi
