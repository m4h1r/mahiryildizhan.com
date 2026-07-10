# MY Teknoloji — Audit Report
Project: laravel/laravel (mahiryildizhan.com — **custom hand-rolled admin, NOT Filament**)
Date: 2026-07-10
Topics: all
Standards: v built 2026-07-03 (157 rules / 17 topics)

> ⚠️ **This project does not use Filament.** The admin panel is a custom Blade app
> (`routes/web.php` → `Route::prefix('admin')`, `resources/views/admin/*`). All
> `[FILAMENT]` and `[MEDIA]` (curator) rules are marked **— N/A** and reinterpreted
> against the always-on intent (e.g. "MFA on every admin panel") where meaningful.

---

## Results

| Rule | Status | Detail |
|------|--------|--------|
| P1 (php) | ⚠️ | `composer.json` php `^8.3` — baseline 8.5, min 8.4. Bump to `^8.4`. |
| P1 (vite) | ⚠️ | `vite ^7.0.7` — baseline Vite 8 (Rolldown). |
| P1 (pest) | ✅ | Pest 4 installed. |
| P1 (filament) | — | Filament not used (custom admin). |
| L1 env() | ✅ | No `env()` calls in `app/`. |
| L4 closures | ✅ | Only `Route::group/prefix` closures (normal), no action closures. |
| L5 all()/Requests | ✅ | No `->all()`; `app/Http/Requests/` present. |
| L6 Services | ✅ | `app/Services/` present. |
| L3 Enums | ⚠️ | No `app/Enums/` — status stored as DB `enum()`, no backed PHP enums/casts. |
| L2 SoftDeletes | ⚠️ | 32 models; no Filament resources to auto-map — **verify admin-CRUD models use SoftDeletes** manually. |
| L9 activitylog | ⚠️ | `spatie/laravel-activitylog` not installed. |
| FILAMENT (all) | — | N/A — custom admin, no Filament panel. |
| M1 medialibrary | ⚠️ | No `spatie/laravel-medialibrary` / curator (custom app — verify uploads are validated & non-public). |
| V1 gitignore | ✅ | `public/build` + `node_modules` ignored. |
| V4 console.log/jquery | ✅ | None found. |
| V6 footer credit | ✅ | `myteknoloji.com` credit present. |
| V5 logo | ⚠️ | No `public/logo.png`. |
| V8b palette | ⚠️ | Only 6 of 11 palette tokens in `@theme`. |
| V8c scrollbar | ⚠️ | No `scrollbar-width:thin` + `scrollbar-color` brand rule (only `admin-scrollbar` webkit). |
| V9 PWA | ⚠️ | No `public/manifest.json` / service worker. |
| V12 error pages | ⚠️ | 404/500/503 present but 419/429 missing; 404/503 `@extends('public.layout')` — should be **standalone**. |
| V13 reading-progress | ⚠️ | No `reading-progress` component on content pages. |
| TY1 @theme/fonts | ✅ | `@theme` block with `--font-body/editorial/mono` slots. |
| TY2 font load | ✅ | `@fontsource/inter` bundled. |
| A3 skip link | ✅ | Skip-to-content in admin + public layouts. |
| A4 focus-visible | ✅ | `:focus-visible` ring; `outline:none` paired with focus ring. |
| A8 reduced-motion | ✅ | `prefers-reduced-motion` block present. |
| S1 env/debug | ✅ | `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, `.env` gitignored. |
| S3 request->all() | ✅ | None. |
| S4 captcha | ✅ | reCAPTCHA wired via Settings. |
| S5 honeypot | ⚠️ | Honeypot only in 1 view (welcome). Verify newsletter/comment/search public forms are covered. |
| S6 rate limit | ✅ | `throttle:6,1` on auth routes. |
| S9 SecurityHeaders | ✅ | `SecurityHeadersMiddleware` registered in `bootstrap/app.php`. |
| S11 session | ⚠️ | `SESSION_DRIVER=database` — prefer `redis`. |
| S13 MFA | ❌ | No 2FA/TOTP anywhere — admin login has no MFA. |
| S14 Password policy | ⚠️ | `Password::defaults()` used but not configured in `AppServiceProvider` (`min(12)->uncompromised()`). |
| D2 money | ✅ | No float/double columns. |
| D5 status | ✅ | Status columns use `enum()`, not string. |
| D7 UserSeeder | ✅ | Present. |
| D6 factories | ⚠️ | 1 factory for 32 models. |
| D9 cascade | ⚠️ | 4 `cascadeOnDelete` (interactions→people, node_connections→nodes, comments→posts) — verify parents are NOT soft-deletable. |
| E1 sitemap | ✅ | `/sitemap.xml` route. |
| E2 robots | ⚠️ | No `/robots.txt` route (only meta robots). |
| E4 OG | ✅ | `seo-meta` component with `og:title`. |
| E6 NoIndex | ⚠️ | No NoIndex middleware for `/admin`/auth routes (one view has meta noindex). |
| E7 consent | ⚠️ | No cookie-consent component (only needed once analytics/tracking added). |
| G1 repo | ✅ | 43 commits, GitHub remote. |
| G2 develop | ⚠️ | No `develop` branch. |
| G5 CI | ❌ | No `.github/workflows/ci.yml`. |
| G5 dependabot | ⚠️ | No `.github/dependabot.yml`. |
| Q1 pint | ❌ | `laravel/pint` installed but **no `pint.json`**. |
| Q2 phpstan | ❌ | No `larastan/larastan` + no `phpstan.neon`. |
| Q3 rector | ⚠️ | No `rector/rector` + no `rector.php`. |
| B1 backup | ❌ | No `spatie/laravel-backup`, not scheduled, no `BACKUP_ARCHIVE_PASSWORD` (KVKK). |
| X2 tests | ✅ | `tests/Feature/` present (Pest). |
| X9 smoke | ❌ | No `tests/Feature/SmokeTest.php`. |
| X11 smoke.sh | ❌ | No `scripts/smoke-test.sh`. |
| R1 queued mail | ❌ | 1 Mailable, 0 `ShouldQueue`. |
| R4 mail from | ✅ | `MAIL_FROM_ADDRESS` set. |
| R9 MailTest | ✅ | `MailTestCommand` present. |
| W9 health | ⚠️ | Custom `/health` route exists; no `spatie/laravel-health` monitoring. |
| F8 admin path | ⚠️ | Admin at predictable `/admin` prefix (custom app — consider obscuring). |

---

## Remediation Plan

### ⚡ Quick (< 10 min)
- [ ] **B1** Install backup: `composer require spatie/laravel-backup` + publish config + add `BACKUP_ARCHIVE_PASSWORD` to `.env.example`.
- [ ] **Q1** Add `pint.json` to project root (MY Teknoloji preset).
- [ ] **G5** Add `.github/dependabot.yml` (composer + npm weekly).
- [ ] **V12** Add branded `resources/views/errors/419.blade.php` + `429.blade.php`.
- [ ] **P1** Bump `php` constraint to `^8.4` in `composer.json`.
- [ ] **E2** Add `/robots.txt` route.
- [ ] **S14** Configure `Password::defaults(fn () => Password::min(12)->uncompromised())` in `AppServiceProvider::boot`.

### 🔧 Standard (30–60 min)
- [ ] **Q2** `composer require --dev larastan/larastan` + add `phpstan.neon` (level 6+).
- [ ] **G5** Add `.github/workflows/ci.yml` (pint --test, phpstan, pest).
- [ ] **X9 / X11** Create `tests/Feature/SmokeTest.php` (all public routes → 200) + `scripts/smoke-test.sh` (curl -I against live URL).
- [ ] **R1** Make Mailable(s) `implements ShouldQueue` + confirm `QUEUE_CONNECTION` + queue worker.
- [ ] **V8b/V8c** Complete 11-token palette in `@theme` + add thin brand-colored `scrollbar-width:thin; scrollbar-color:var(--color-primary) transparent`.
- [ ] **S5** Add `<x-honeypot />` to every public form (newsletter, blog comment, search) — currently only on welcome.
- [ ] **E6** Add `NoIndex` middleware and apply to `/admin` + auth route groups.
- [ ] **D6** Backfill model factories (32 models, 1 factory) — at least for tested models.

### 🏗️ Larger (own task/phase)
- [ ] **S13** Add MFA/TOTP to admin login (e.g. `laravel/fortify` 2FA or `pragmarx/google2fa-laravel`) — always-on rule for every admin panel.
- [ ] **P1** Upgrade Vite 7 → 8 (Rolldown); test build + HMR.
- [ ] **L9** Add `spatie/laravel-activitylog` + `LogsActivity` on key models + an admin activity view.
- [ ] **D9** Audit the 4 `cascadeOnDelete` FKs — if any parent uses SoftDeletes, replace with restrict/nullOnDelete to avoid silent data loss.
- [ ] **V9** Add PWA manifest + icons + service worker (installable).

---
*Generated by /my-standards audit — MY Teknoloji Standards (graph built 2026-07-03)*
