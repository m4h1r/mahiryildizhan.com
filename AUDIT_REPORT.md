# MY Teknoloji — Audit Report
Project: mahiryildizhan.com
Date: 2026-04-21
Topics: all

---

## Results

| Rule | Status | Detail |
|------|--------|--------|
| **LARAVEL** | | |
| L1 | ❌ | `env()` called directly in app/ — AppServiceProvider.php:34-35, EnsureAdmin.php:19, DashboardController.php:22-24, MailchimpService.php:67,72,77, RecaptchaService.php:12 |
| L3 | ⚠️ | No `app/Enums/` directory |
| L4 | ✅ | No closure routes; groups use typed closures |
| L5 | ✅ | Form Requests exist; no `->all()` in controllers |
| L6 | ✅ | `app/Services/` exists (CsvImport, Image, Mailchimp, Media, Recaptcha, Sitemap) |
| **FILAMENT** | | |
| F1-F8 | — | Filament not installed; custom admin panel used |
| **MEDIA** | | |
| M1 | ❌ | `spatie/laravel-medialibrary` not in composer.json; `filament-curator` not installed |
| M2-M5 | — | N/A — media packages not installed |
| **FRONTEND** | | |
| V1 | ✅ | `public/build` and `node_modules` in .gitignore |
| V3 | ❌ | `@vite()` called in 5 separate files: welcome.blade.php, admin/layout.blade.php, public/layout.blade.php, layouts/guest.blade.php, layouts/app.blade.php |
| V4 | ✅ | No `console.log` in resources/js; no jQuery |
| V5 | ⚠️ | `public/logo.png` missing |
| V6 | ❌ | No MY Teknoloji footer credit in any layout |
| **SECURITY** | | |
| S1 (APP_DEBUG) | ❌ | `APP_DEBUG=true` in .env.example — must be `false` |
| S1 (SESSION) | ⚠️ | `SESSION_SECURE_COOKIE` not set in .env.example |
| S1 (.env gitignore) | ✅ | `.env` in .gitignore |
| S3 | ✅ | No `$request->all()` usage found |
| S4 | ✅ | Recaptcha configured (AppServiceProvider + RecaptchaService) |
| S5 | ❌ | 55 views contain `<form` but 0 have `x-honeypot` |
| S6 | ⚠️ | RateLimiter only on login; no rate limiting on public comment/contact forms |
| **DATABASE** | | |
| D1 | ✅ | All CREATE migrations have timestamps; ALTER migrations exempt |
| D2 | ✅ | No float/double columns in migrations |
| D5 | ✅ | No string status columns found |
| D6 | ⚠️ | 1 factory vs 28 models |
| D7 | ❌ | No `database/seeders/UserSeeder.php` |
| **SEO** | | |
| E1 | ✅ | `/sitemap.xml` route exists (routes/public.php:42) |
| E2 | ⚠️ | No robots.txt route in routes/ |
| E3 | ⚠️ | No ads.txt route in routes/ |
| E4 | ✅ | OG tags in `resources/views/components/seo-meta.blade.php` |
| E5 | ⚠️ | No `resources/views/components/tracking-scripts.blade.php` |
| **GIT** | | |
| G1 | ✅ | 6 commits; remote origin set (github.com/m4h1r/mahiryildizhan.com) |
| G2 | ⚠️ | No `develop` branch |
| G3 | ✅ | `public/build` in .gitignore |
| **LOCALIZATION** | | |
| T2 | — | Single-language site; mcamara package N/A |
| T3 | ⚠️ | No `lang/tr/` directory; 27 views use `__()` helpers |
| **TESTING** | | |
| X1 | ✅ | `pestphp/pest` installed |
| X2 | ✅ | `tests/Feature/` exists (11 test files) |
| X3 | ✅ | No DatabaseSeeder called in tests |
| X4 | ⚠️ | 1 factory vs 28 models |
| X7 | ✅ | `DB_DATABASE=:memory:`, `QUEUE_CONNECTION=sync`, `MAIL_MAILER=array` in phpunit.xml |
| **EMAIL** | | |
| R1 | — | No Mailables exist |
| R2 | ✅ | No `Mail::send()` in production code |
| R3 | — | No Mailables; email layout N/A |
| R4 | ⚠️ | `MAIL_FROM_ADDRESS="hello@example.com"` still placeholder in .env.example |
| R5 | ⚠️ | No `failed_jobs` table migration |
| R6 | — | No Mailables; N/A |
| R9 | ❌ | No `app/Console/Commands/MailTestCommand.php` |

---

## Remediation Plan

### ⚡ Quick (< 10 min)
- [ ] [S1] Set `APP_DEBUG=false` in .env.example — one line change
- [ ] [S1] Add `SESSION_SECURE_COOKIE=true` to .env.example
- [ ] [V6] Add `<x-footer-credit />` component to all 5 layout files
- [ ] [D7] Create `database/seeders/UserSeeder.php` with admin@mahiryildizhan.com / Mahir Yıldızhan
- [ ] [G2] `git checkout -b develop && git push -u origin develop`
- [ ] [R4] Update `MAIL_FROM_ADDRESS` in .env.example to real address

### 🔧 Standard (30–60 min)
- [ ] [L1] Move all `env()` calls in app/ to config files — create `config/services.recaptcha.php`, `config/admin.php`, `config/weather.php`, `config/mailchimp.php`; update ~10 call sites to use `config()`
- [ ] [R9] Scaffold `MailTestCommand` from email.md §10 template — `php artisan make:command MailTestCommand`
- [ ] [E2] Add robots.txt route + inline response in routes/public.php
- [ ] [E3] Add ads.txt route + inline response in routes/public.php
- [ ] [E5] Create `resources/views/components/tracking-scripts.blade.php` with GTM/GA placeholder
- [ ] [R5] `php artisan queue:failed-table && php artisan migrate`

### 🏗️ Larger (own task/phase)
- [ ] [M1] Install + configure media: `composer require spatie/laravel-medialibrary awcodes/filament-curator` → add HasMedia to relevant models → register MediaConversions → configure media disk → register CuratorPlugin. Estimate: 2–3 hours
- [ ] [S5] Add honeypot to all public forms: `composer require msurguy/honeypot` → add `x-honeypot` to each of the 55 form views → add HoneypotMiddleware to public routes. Estimate: 1–2 hours
- [ ] [V3] Consolidate @vite() to single root layout — merge admin/layout, public/layout, layouts/app, layouts/guest into a shared base blade component or single root layout extending pattern. Estimate: 1–2 hours

---
*Generated by /my-standards audit — MY Teknoloji Standards v2026-04-21*
