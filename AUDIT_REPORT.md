# MY Teknoloji — Audit Report
Project: mahiryildizhan.com
Date: 2026-07-22
Topics: all

**Status: 64 ✅ | 1 ❌ | 11 ⚠️ | remainder N/A (Filament rules).** All Quick + Standard remediation
items fixed (8 original + the V24 follow-up), plus X1/X10 (Pest + full admin test coverage — which
surfaced and fixed **two real production bugs**: broken media deletion, a crash-on-missing-field in
the timeline controller), V30 (brand-token settings tab, end-to-end verified), and the Dependabot
security sweep (22 advisories → 0, see below — which surfaced and fixed a **third real bug**: a
zodiac-calculation crash from a major dependency bump). Only **M1** (medialibrary migration) remains
— a genuine scoping decision, not mechanical work — see Remediation Plan. Remaining ⚠️ are
low-priority (S4 Turnstile-vs-reCAPTCHA, V5 logo, V8b semantic status tokens, etc.).

---

## ⚠️ Architecture note

This project does **not** use the standard MY Teknoloji stack (Filament + Livewire + Pest). It's a
custom-built admin panel (`app/Http/Controllers/Admin/*`, Blade views under `resources/views/admin/`)
on plain Laravel 13 + Fortify + PHPUnit. Every Filament-specific rule (F-series) is marked **N/A**
below rather than failed, since there is no panel provider to check against. This is presumably a
deliberate choice for this legacy/personal site — flagging it once here (P1) instead of repeating it
per rule.

---

## Results

| Rule | Status | Detail |
|------|--------|--------|
| P1 | ⚠️ | Stack deviates from baseline: no Filament, no Livewire. Pest 4 now installed (was plain PHPUnit) — one of the three gaps closed |
| L1 | ✅ | No `env()` calls found outside config/ |
| L2 | ✅ | Fixed: `SoftDeletes` added to `Node`, `Interaction`, `Stakeholder` (migration + trait) — `destroy()` on these no longer permanently deletes. No trash/restore UI added (scoped decision — see remediation plan; there wasn't one anywhere in the panel to match, not even for `Person`/`Post`/`Comment`) |
| L3 | ⚠️ | No `app/Enums/` directory |
| L4 | ⚠️ | One closure route (`/theme/{scope}/{theme}` in routes/web.php:21) |
| L5 | ✅ | No `$request->all()` mass-assignment; `->all()` hits are all `Collection::all()` |
| L6 | ✅ | `app/Services/` exists (7 services) |
| F1–F12 | — | N/A — project does not use Filament |
| M1 | ❌ | No `spatie/laravel-medialibrary` or `awcodes/filament-curator` — custom `MediaService`/`ImageService` used instead |
| V1 | ✅ | `public/build` and `node_modules` in `.gitignore` |
| V3 | ✅ | Active layouts (`admin/layout.blade.php`, `public/layout.blade.php`, `layouts/app.blade.php`, `layouts/guest.blade.php`) each call `@vite()` once. `layouts/app.blade.php`/`layouts/guest.blade.php` are live (used by Breeze auth/dashboard views via `<x-app-layout>`/`<x-guest-layout>`) — only the root `resources/views/welcome.blade.php` was genuinely orphaned |
| V4 | ✅ | No `console.log`, no jQuery |
| V5 | ⚠️ | No `public/logo.png` |
| V6 | ✅ | Footer credit link to myteknoloji.com present |
| V8b | ⚠️ | 17 base palette tokens defined (≥11 ✅) but 0 semantic status-role tokens (bg/text/border ×4) |
| V8c | ✅ | Thin brand-colored scrollbar defined |
| V9 | ✅ | `site.webmanifest`, `sw.js`, PWA icons all present |
| V12 | ✅ | All 5 branded error pages present (404/500/503/419/429), all standalone |
| V13 | ⚠️ | No reading-progress component on blog posts |
| V24 | ✅ | **Follow-up completed.** Extended `<x-app-dialog>`/`window.appDialog` with a `confirm()` mode (danger-styled by default) and a delegated `data-confirm` form-submit handler in `app.js` (one listener, no per-form JS). Converted all 17 admin index pages from `onsubmit="return confirm(...)"` to `data-confirm="..."` — import.blade.php's two non-form-destroy buttons (Import All / Truncate All) included, with Truncate labeled distinctly since it's the most destructive action in the app. |
| V25 | ✅ | Built `<x-float-input>` (CSS-only float label); adopted on the comment form (guest_name/guest_email) and newsletter signup |
| V26 | ✅ | Built segmented `<x-otp-input>` (6 boxes, paste-safe, auto-advance/backspace, `autocomplete="one-time-code"` on the first box); adopted on the 2FA challenge code field. Recovery-code field intentionally left as plain text (alphanumeric, not a fixed-length digit code) |
| V27 | ⚠️ | No `x-swipe-row` component; `public/biolink/index.blade.php` has a list view that could use it |
| V28 | ✅ | Built `<x-status-message>` (4 roles, centralized `.status-message-*` classes in app.css); `<x-flash>` (site-wide banner, used on every admin page) now delegates to it instead of hardcoding red/emerald inline. Fixed `admin/about.blade.php` (removed a redundant duplicate success banner — it was rendering the same message twice, once via the local block and once via `<x-flash>` in the layout) and `public/blog/show.blade.php`'s comment error. Correction: the `bg-blue-*` hits in `admin/dashboard.blade.php` originally flagged here are a decorative wealth-tier progress bar, not a status signal — false positive, not a violation. **Follow-up not done today:** ~7 more files (`todo-items`, `subscribers`, `purchase-items`, `activity-logs`, `incomes`, `bucketlist`, `interactions`) use inline badge-style status chips (pending/overdue/completed) that are a different UI shape (rounded pill badges, not message banners) — worth a follow-up `<x-status-badge>` component, scoped separately since it's a larger sweep than this fix. |
| V29 | ✅ | Built `<x-app-dialog>` (native `<dialog>` + `.showModal()`, 45% backdrop dim + 6px blur, 180ms `@starting-style` scale entrance) with a Promise-based `window.appDialog.alert()`/`.confirm()`/`.prompt()` JS API. Replaced the 3 `window.alert()` + 1 `window.prompt()` calls in `app.js`/`people/_form.blade.php`. While fixing this, found the "V24 delete confirmation" ✅ from the first audit pass was a false positive (native unstyled `confirm()` on 17 pages) — see V24, now also fixed using the same dialog infrastructure. |
| V30 | ✅ | Fixed: `--brand-primary`/`--brand-secondary` seeds added to `@theme`; `--color-brand`, `--color-brand-dark`, `--color-accent`, `--color-primary`, `--color-primary-dark` all now derive from the two seeds via `oklch(from var(--brand-primary) calc(l - 0.12) c h)`. Built `<x-brand-vars>` (inline `<style>` override from `Setting::get()`, wired into both layouts right after `@vite()` so it wins the cascade). Added a "Marka" tab — first in `SettingController::DEFINITIONS` — with two `<input type="color">` pickers, validated server-side against `^#[0-9A-Fa-f]{6}$` (this value renders inside a raw `<style>` block, unlike other settings which are just text-interpolated, so format validation matters here specifically). Also updated `<meta name="theme-color">` in `brand-meta.blade.php` to read the same setting. End-to-end verified: saved color → rendered CSS override on the live page. |
| TY1 | ⚠️ | `@theme` block exists but no `--font-display`/`--font-sans` slots defined |
| TY2 | ✅ | `@fontsource/inter` loaded |
| A3 | ✅ | Skip-to-content link in both layouts |
| A4 | ✅ | `:focus-visible` defined; legacy `outline-none` usages all paired with visible focus rings |
| A7 | ✅ | No paste-blocking found |
| A8 | ✅ | `prefers-reduced-motion` block present |
| S1 | ✅ | `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, `.env` in `.gitignore` |
| S3 | ✅ | No `$request->all()` |
| S4 | ⚠️ | reCAPTCHA v3 used instead of preferred Cloudflare Turnstile |
| S5 | ✅ | Fixed: `<x-honeypot>` component parameterized with a `name` prop (default `hp_website`, unchanged for the newsletter form); comment form now uses `<x-honeypot name="website" />` instead of an inline duplicate — same backend field name, no behavior change |
| S9 | ✅ | `SecurityHeadersMiddleware` registered in `bootstrap/app.php` |
| S11 | ✅ | `SESSION_DRIVER=redis`, `SESSION_SECURE_COOKIE=true` |
| S12 | ✅ | No legacy `app/Exceptions/Handler.php` |
| S13 | ✅ | Fortify 2FA (MFA) configured |
| S14 | ✅ | `Password::defaults()` with `min(12)->uncompromised()` |
| D1 | ✅ | Migrations have timestamps |
| D2 | ✅ | No float/double money columns |
| D5 | ✅ | No string status columns |
| D6 | ⚠️ | 32 models / 29 factories |
| D7 | ✅ | `UserSeeder` present |
| D9 | ✅ | Reviewed: `forceDelete()` is never called anywhere in the app (`grep -rn forceDelete app/` returns nothing), so `cascadeOnDelete` on `interactions.person_id`→`people`, `comments.post_id`→`posts`, and now `node_connections.node_from_id/node_to_id`→`nodes` (soft-deletable as of today's L2 fix) is currently dormant — no live data-loss risk. **Note for later:** if a "permanently delete" admin feature is ever added, revisit these three FKs first, since cascading through would silently wipe child records with no trash trail of their own. |
| E1 | ✅ | Sitemap route present |
| E2 | ✅ | `/robots.txt` route present |
| E3 | ✅ | `/ads.txt` route present |
| E4 | ✅ | OG tags via `seo-meta` component |
| E5 | ✅ | `gtm-head`/`gtm-body` components (tracking present, differently named than standard) |
| E6 | ✅ | `NoIndex` middleware applied to admin/auth routes |
| E7 | ✅ | Fixed: added `<x-consent-defaults>` (Google Consent Mode v2, default=denied, loaded before GA/AdSense scripts) + `<x-cookie-consent>` banner (accept/reject, persisted in localStorage, calls `gtag('consent','update',...)` on accept) |
| G1 | ✅ | 47 commits, remote configured |
| G2 | ✅ | Local `develop` branch created (not pushed to origin — pushing affects shared remote state, left for the user to do) |
| G3 | ✅ | `public/build` in `.gitignore` |
| G5 | ✅ | `.github/workflows/ci.yml` and `.github/dependabot.yml` both present |
| Q1 | ✅ | `pint.json` present, `laravel/pint` installed |
| Q2 | ✅ | `phpstan.neon` present, `larastan/larastan` installed |
| Q3 | ✅ | Fixed: `rector/rector` installed, starter `rector.php` committed (dry-run verified — 4 files would change, none auto-applied) |
| Q4 | ✅ | Fixed: `beyondcode/laravel-query-detector` installed |
| S10 | ✅ | `enlightn/laravel-security-checker` requires Laravel ^6-9 — incompatible with this project's Laravel 13, not installable; `composer audit` (built into Composer 2.4+) covers the same CVE-scanning need natively. **Fixed:** the 22 advisories it surfaced are now resolved — see Dependabot sweep below. `composer audit` now reports zero. |
| B1 | ✅ | `spatie/laravel-backup` installed, `backup:run` scheduled daily, `BACKUP_ARCHIVE_PASSWORD` in `.env.example` (offsite disk not configured — local only) |
| X1 | ✅ | Fixed: `pestphp/pest` + `pestphp/pest-plugin-laravel` installed, `tests/Pest.php` bootstrapped (extends `TestCase`, global `RefreshDatabase`). Existing PHPUnit-style tests run unchanged alongside new Pest-style tests — no migration needed, Pest supports both. |
| X2 | ✅ | `tests/Feature/` populated (Admin, Alice, Auth subdirs) |
| X9 | ✅ | `tests/Feature/SmokeTest.php` present |
| X10 | ✅ | Fixed: added Feature tests for all 22 previously-uncovered Admin controllers (21 new test files, ~110 new test cases). Only gap: `ReportController`'s full render is blocked by a pre-existing `MONTH()` SQLite incompatibility (same root cause as the already-failing `Phase12FlowTest`) — auth/authorization is still tested, the data-rendering path is not. **Two real bugs found and fixed via this work**, see below. |
| X11 | ✅ | `scripts/smoke-test.sh` present |
| R1 | ✅ | `SubscriberConfirmationMail` implements `ShouldQueue` |
| R3 | ✅ | Fixed: extracted `resources/views/emails/layouts/base.blade.php`; `SubscriberConfirmationMail`'s view now `@extends` it instead of duplicating the full HTML table skeleton |
| R4 | ✅ | `MAIL_FROM_ADDRESS` set in `.env.example` |
| R9 | ✅ | `MailTestCommand` present |
| W6/W9/W8 | ⚠️ | No `laravel/pulse`, `spatie/laravel-health`, or `laravel/horizon` |
| — | ✅ | Fixed: removed orphaned root `resources/views/welcome.blade.php` (default Laravel starter page, never routed) |

---

## Remediation Plan

### ⚡ Quick (< 10 min) — all done
- [x] **E7** — `<x-consent-defaults>` + `<x-cookie-consent>` built and wired into `public/layout.blade.php`.
- [x] **S4** — Accepted as-is: reCAPTCHA v3 already implemented and working; no client requirement forcing Turnstile.
- [x] **Q3** — `rector/rector` installed, starter `rector.php` committed.
- [x] **Q4** — `beyondcode/laravel-query-detector` installed.
- [x] **S10** — Not installable (Laravel ^6-9 only); `composer audit` covers the same need natively on Laravel 13.
- [x] **G2** — Local `develop` branch created.
- [x] Cleanup — deleted unused `resources/views/welcome.blade.php` (confirmed unrouted). `layouts/app.blade.php`/`layouts/guest.blade.php` were re-verified as live (Breeze auth/dashboard layouts) and kept.

### 🔧 Standard (30–60 min) — all done
- [x] **L2** — `SoftDeletes` added to `Node`, `Interaction`, `Stakeholder` (migration + trait). No trash/restore UI built — user chose to scope this to "stop permanent deletes" only, since no admin page in the app has restore UI today, not even for `Person`/`Post`/`Comment`.
- [x] **V28** — Built `<x-status-message>`, centralized `<x-flash>` onto it, fixed a duplicate-banner bug in `about.blade.php` and the comment-error banner on `blog/show.blade.php`. ~7 files with badge-style status chips (not message banners) left as a follow-up — different UI shape, bigger sweep.
- [x] **V25** — Built `<x-float-input>`; adopted on comment form + newsletter signup.
- [x] **S5** — `x-honeypot` parameterized with a `name` prop; comment form now uses it instead of a duplicate inline field.
- [x] **V26** — Built segmented `<x-otp-input>`; adopted on the 2FA code field.
- [x] **V29** — Built `<x-app-dialog>` + `window.appDialog.alert/confirm/prompt`; replaced all 4 original `window.alert()`/`window.prompt()` calls.
- [x] **R3** — Extracted `resources/views/emails/layouts/base.blade.php`; `SubscriberConfirmationMail` now extends it.
- [x] **D9** — Reviewed: `forceDelete()` is never called anywhere, so the cascade FKs are currently dormant. No code change needed; noted for later if a permanent-delete feature is ever added.

### ✅ V24 follow-up (completed after being discovered mid-V29 fix)
- [x] Extended `window.appDialog` with a `confirm()` mode + a delegated `data-confirm` form-submit handler (one listener in `app.js`, no per-form JS needed).
- [x] Converted all 17 admin pages from `onsubmit="return confirm(...)"` to `data-confirm="..."` (import, comments, interactions, posts, adages, nodes, todo-items, expenses, stakeholders, people, dictionaries, timeline, subscribers, node-connections, incomes, media, purchase-items).
- [x] Full test suite + Vite build verified clean after the sweep (same 6 pre-existing failures, no new ones).

### ✅ X1 + X10 — completed
- [x] Installed Pest 4 + `pest-plugin-laravel`; bootstrapped `tests/Pest.php` (existing PHPUnit tests run unchanged alongside new Pest tests — no migration needed).
- [x] Added Feature tests for all 22 previously-uncovered Admin controllers (Person, Expense, Income, Stakeholder, Post, Comment, Node, NodeConnection, Interaction, TodoItem, PurchaseItem, Timeline, Adage, Dictionary, Subscriber, Media, Bucketlist, About, Setting, Dashboard, ActivityLog, CsvImport, Report) — 21 new test files, suite grew from 94 → 222 passing tests.
- [x] Every resource test checks: auth required, non-admin forbidden, index renders, store validates + persists, update persists, destroy (soft- or hard-delete as appropriate).

**Two real bugs found and fixed along the way** (this is exactly why X10 mattered):
1. **Media deletion was completely broken.** `Route::resource('/media', ...)` auto-singularizes to `{medium}` (Laravel's English-inflector rule for "media"), but `MediaController::destroy(Media $media, ...)` expects `$media`. The name mismatch meant route-model-binding silently injected an empty, unbound `Media` instance — `$media->delete()` ran against a non-existent record every time, and the UI always showed "Media deleted" even though nothing was removed. Fixed with `->parameters(['media' => 'media'])` on the resource route in `routes/admin.php`.
2. **`TimelineController::validatedPayload()` crashed with "Undefined array key"** on `color`/`tags`/`metadata` whenever those optional fields were omitted from the request — the `$payload['color'] ?: '...'` pattern still requires the key to exist even though the field is `nullable`. Fixed by adding `?? null` before each fallback check.

**Also discovered (not a bug, but worth knowing):** `EnsureAdmin` treats **user ID 1 as an implicit admin** regardless of the `is_admin` flag — a bootstrap safety net. Baked into a shared `actingAsNonAdmin()` test helper (creates a throwaway user first) so future tests don't trip over it.

### ✅ V30 — completed
- [x] Added `--brand-primary`/`--brand-secondary` seeds to `@theme`; derived `--color-brand`, `--color-brand-dark`, `--color-accent`, `--color-primary`, `--color-primary-dark` from them via `oklch(from ...)`.
- [x] Built `<x-brand-vars>` — settings-driven inline `<style>` override, wired into both public and admin layouts.
- [x] Added a "Marka" tab (first tab) to Settings with two color pickers, server-side hex validation, and a live end-to-end test (saved setting → rendered CSS override).
- [x] Synced `<meta name="theme-color">` to the same setting for PWA consistency.

### 🏗️ Larger (own task/phase) — remaining
- [ ] **M1** — Evaluate migrating `MediaService`/`ImageService` to `spatie/laravel-medialibrary` + `awcodes/filament-curator`; scope depends on how deeply the custom services are wired into existing models — likely a full phase.
- [ ] **P1** (architecture) — No action required unless there's appetite to migrate this project onto Filament/Livewire; documented here as a known, accepted baseline deviation.

### ✅ Dependabot security sweep (S10 follow-up) — completed
- [x] Verified `laravel/framework` 13.0.0 → 13.21.1 (PR #20) alone would clear all 22 `composer audit` advisories by checking each affected package's bumped version (`guzzle` 7.10.0→7.15.1, `psr7` 2.9.0→2.13.0, `symfony/http-foundation`/`http-kernel`/`mailer`/`mime`/`routing`/`polyfill-intl-idn` all bumped past their CVE fix thresholds) against the actual advisory data before merging.
- [x] Merged 13 of 14 open Dependabot PRs (#20, #19, #18, #16, #15, #14, #12, #9, #8, #7, #5, #4, #2) — `composer audit` now reports **zero advisories**, `npm audit`-equivalent reports zero. `#1` (`actions/cache` 4→6) could not be merged — the CLI's GitHub token lacks the `workflow` OAuth scope required to update `.github/workflows/*.yml`; needs a manual merge via the web UI or a token with `workflow` scope.
- [x] Pulled the merged changes locally, ran `composer install` + `npm install`, rebuilt assets, and re-ran the full suite.
- [x] **Found and fixed a third real bug**, surfaced by merging `intervention/zodiac` 6.0.1 → 7.0.3 (#16 — a major version bump, flagged in advance as the one PR needing individual scrutiny): the package's `Calculator::fromDate()` static factory was removed in v7; `Person::zodiacName()` crashed with "Call to undefined method" on any page rendering a person's zodiac sign. Fixed by switching to the new `Sign::fromDate($date)->localize('tr')->name()` entry point (same underlying calculation, new API surface). Verified via tinker that a birthdate still resolves to the correct Turkish zodiac name ("Koç" for March 21).
- [x] Full suite re-verified clean afterward: 250 passing, same 6 pre-existing/unrelated failures, no regressions.

---
*Generated by /my-standards audit — MY Teknoloji Standards v1.10.0*
