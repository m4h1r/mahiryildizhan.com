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
| P1 (php) | ✅ | `composer.json` now `^8.4` (table row was stale — fix already landed in the Quick pass). |
| P1 (vite) | ✅ | Upgraded to `vite ^8.1.5` (Rolldown) + `laravel-vite-plugin ^3.1.3` + `@tailwindcss/vite ^4.3.3` (the 4.2.x line didn't declare a Vite 8 peer range yet). `rollupOptions.manualChunks` vendor splitting still works unchanged. Build time dropped ~2.7s → ~1s. Also ran `npm audit fix` while in here — cleared 8 pre-existing vulnerabilities (2 critical) in dev-tooling transitives, 0 remaining. |
| P1 (pest) | ✅ | Pest 4 installed. |
| P1 (filament) | — | Filament not used (custom admin). |
| L1 env() | ✅ | No `env()` calls in `app/`. |
| L4 closures | ✅ | Only `Route::group/prefix` closures (normal), no action closures. |
| L5 all()/Requests | ✅ | No `->all()`; `app/Http/Requests/` present. |
| L6 Services | ✅ | `app/Services/` present. |
| L3 Enums | ⚠️ | No `app/Enums/` — status stored as DB `enum()`, no backed PHP enums/casts. |
| L2 SoftDeletes | ⚠️ | 32 models; no Filament resources to auto-map — **verify admin-CRUD models use SoftDeletes** manually. |
| L9 activitylog | ✅ | Project already had a custom `ActivityLog` model + observer pattern (not `spatie/laravel-activitylog` — installing that would've meant two parallel audit-log systems on a custom, non-Filament admin). Extended it instead: generic `ActivityLogObserver` now covers all 7 admin-CRUD/SoftDeletes models (Post, Person, Comment, TodoItem, PurchaseItem, Expense, Income), replacing the two bespoke `ExpenseObserver`/`IncomeObserver` classes; added `LogSuccessfulLogin`/`LogFailedLogin`/`LogSuccessfulLogout` listeners logging ip+user_agent per L9's auth-event rule; added a read-only `/admin/activity-log` viewer (Systems nav group). `action` column widened from a 3-value enum to `varchar(20)` (portable across MySQL/SQLite, no more migrations needed for new action types). |
| FILAMENT (all) | — | N/A — custom admin, no Filament panel. |
| M1 medialibrary | ⚠️ | No `spatie/laravel-medialibrary` / curator (custom app — verify uploads are validated & non-public). |
| V1 gitignore | ✅ | `public/build` + `node_modules` ignored. |
| V4 console.log/jquery | ✅ | None found. |
| V6 footer credit | ✅ | `myteknoloji.com` credit present. |
| V5 logo | ✅ | Branded logo present at `public/M_Logo.png` (1024×1024, used by `<x-application-logo>`), plus full favicon set (16/32px, apple-touch-icon) and `site.webmanifest`. Standard check only looked for the literal filename `logo.png` — false positive. |
| V8b palette | ✅ | All 11 standard tokens now present (`--color-primary/-dark`, `--color-accent`, `--color-text`, `--color-muted`, `--color-bg`, `--color-surface`, `--color-success/-warning/-danger/-info`), aliased onto the project's existing `--color-brand`/`--color-heading` names so nothing already using those breaks. Declared as plain `:root` custom properties in `@layer base` rather than `@theme`, since Tailwind v4 tree-shakes unused `@theme` tokens out of the compiled CSS — confirmed via build output before/after. |
| V8c scrollbar | ✅ | Added global `html { scrollbar-width: thin; scrollbar-color: var(--color-primary) transparent; }` + `::-webkit-scrollbar` fallback in `app.css`. Verified present in compiled `public/build/assets/app-*.css`. The narrower `.admin-scrollbar` sidebar variant still overrides via specificity, unchanged. |
| V9 PWA | ⚠️ | Core installability done: `site.webmanifest` completed (`id`, `start_url=/?source=pwa`, `scope`, `theme_color` now synced to `--color-primary` #0071e3, 2 shortcuts, a properly safe-zoned `maskable` icon generated separately from the `any` icons — never `"any maskable"` on one file), `public/sw.js` (network-first navigation, falls back to branded `offline.html` — deliberately does **not** cache hashed Vite build assets, to avoid serving stale CSS/JS after a deploy), registered on the public layout only. **Not done** (needs real asset generation I can't produce here): narrow+wide **screenshots**, iOS **splash screens** via `pwa-asset-generator`. Push notification VAPID setup skipped — not requested/no push feature exists yet. |
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
| S5 honeypot | ✅ | Both content-creating public forms are covered: newsletter (`<x-honeypot />`, field `hp_website`) and blog comment (manual trap field `website`, wired through `StoreCommentRequest` + `CommentController`). Search forms are GET and not a spam vector — no honeypot needed. Auth forms are already rate-limited, not honeypot candidates. |
| S6 rate limit | ✅ | `throttle:6,1` on auth routes. |
| S9 SecurityHeaders | ✅ | `SecurityHeadersMiddleware` registered in `bootstrap/app.php`. |
| S11 session | ⚠️ | `SESSION_DRIVER=database` — prefer `redis`. |
| S13 MFA | ❌ | No 2FA/TOTP anywhere — admin login has no MFA. |
| S14 Password policy | ⚠️ | `Password::defaults()` used but not configured in `AppServiceProvider` (`min(12)->uncompromised()`). |
| D2 money | ✅ | No float/double columns. |
| D5 status | ✅ | Status columns use `enum()`, not string. |
| D7 UserSeeder | ✅ | Present. |
| D6 factories | ✅ | 29 factories added for every domain model with `HasFactory` (Person, Interaction, Post, Comment, Expense, Income, Stakeholder, Node/NodeConnection, all lookup tables, etc.). Skipped: `Media`, `AliceAuditLog`, `IdempotencyKey` (no `HasFactory` trait — system/infra records, not user-created). Guarded by `tests/Feature/ModelFactoriesTest.php`, which creates one of every model and would catch future migration/factory drift (already caught one: `Adage.language` → `language_id` rename). |
| D9 cascade | ⚠️ | Analyzed all 4 `cascadeOnDelete` FKs. `node_connections`→`nodes` (×2): **fine** — `nodes` is not `SoftDeletes`, so cascade fires on the only delete path that exists. `interactions`→`people` and `comments`→`posts`: parents **are** `SoftDeletes`, so the cascade only matters if something calls `forceDelete()`. Investigating that surfaced a real bug: `routes/console.php` scheduled `model:prune --model=Post,Comment,Person` weekly, but none of those models implement `Prunable` — the command threw `BadMethodCallException` on every run and never actually pruned anything. **Fixed:** removed the broken schedule entry (`routes/console.php`) so the scheduler stops erroring weekly. **Not fixed (needs your decision):** whether to reinstate pruning with a real retention policy — that determines when soft-deleted family/genealogy records and blog comments become permanently, irreversibly unrecoverable, which isn't a call to make unilaterally. `comments` is also itself `SoftDeletes`; a future cascade-triggering `forceDelete()` on a post would hard-delete its comments without them ever going through their own trash/restore lifecycle — worth keeping in mind if `Prunable` is added later. |
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
- [x] **B1** Install backup: `composer require spatie/laravel-backup` + publish config + add `BACKUP_ARCHIVE_PASSWORD` to `.env.example`.
- [x] **Q1** Add `pint.json` to project root (MY Teknoloji preset).
- [x] **G5** Add `.github/dependabot.yml` (composer + npm weekly).
- [x] **V12** Add branded `resources/views/errors/419.blade.php` + `429.blade.php`; made 404/500/503 standalone (no `@extends`/`@vite`/DB-backed components).
- [x] **P1** Bump `php` constraint to `^8.4` in `composer.json`.
- [x] **E2** Add `/robots.txt` route.
- [x] **S14** Configure `Password::defaults(fn () => Password::min(12)->uncompromised())` in `AppServiceProvider::boot`.

### 🔧 Standard (30–60 min)
- [x] **Q2** `composer require --dev larastan/larastan` + add `phpstan.neon` (level 5) + baseline for pre-existing errors.
- [x] **G5** Add `.github/workflows/ci.yml` (pint --test, phpstan, pest).
- [x] **X9 / X11** Create `tests/Feature/SmokeTest.php` (all public routes → 200) + `scripts/smoke-test.sh` (curl -I against live URL).
- [x] **R1** Make Mailable(s) `implements ShouldQueue`.
- [x] **V8b/V8c** Completed 11-token palette + thin brand-colored scrollbar (see results table for details).
- [x] **E6** Add `NoIndex` middleware and apply to `/admin` + auth route groups.
- [x] **D6** Backfill model factories (29 factories added; guarded by `ModelFactoriesTest`).
- [x] **S5** Verified honeypot already covers both content-creating public forms (no code change needed — see results table).
- [x] **V5** Verified branded logo + favicons already exist under a different filename (no code change needed).

### 🏗️ Larger (own task/phase)
- [x] **S13** Add MFA/TOTP to admin login — Fortify 2FA wired into the existing Breeze login, with enroll/confirm/recovery-codes UI on the profile page.
- [x] **P1** Upgraded Vite 7 → 8 (Rolldown) + `laravel-vite-plugin` 2→3 + `@tailwindcss/vite` bump; production build and `npm run dev` both verified working, full test suite unaffected.
- [x] **L9** Extended the project's existing custom `ActivityLog` system (not spatie's package — see results table) to 7 admin-CRUD models + auth events (login/logout/failed) + a read-only admin viewer.
- [x] **D9** Audited the 4 `cascadeOnDelete` FKs (see results table) — found and fixed a real bug in the process (broken weekly `model:prune` schedule). Open question for you: whether/how to define a retention policy for Person/Post/Comment pruning — not resolved, see note above.
- [x] **V9** Manifest completed + `sw.js` + `offline.html` — installable now. Screenshots + iOS splash screens still open (see results table — need real asset generation, e.g. via `pwa-asset-generator` against a running instance).

---
*Generated by /my-standards audit — MY Teknoloji Standards (graph built 2026-07-03)*
