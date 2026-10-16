# Project Standards — mahiryildizhan.com

Project-level decisions and recorded deviations from the MY Teknoloji standards.
Rules here take precedence over the global standards for this project.

Last reviewed: 2026-10-15

---

## CTA (V31)

- **Primary conversion action:** subscribe to the newsletter (`public.subscribers.store`).
- **Support actions:** read stories / about.
- Declared per route in `config/cta.php`; component `<x-cta-primary>`; copy in `lang/*/ui.php` (`ui.cta.*`).

## Localization (T1/T2/T9/T11)

- **Locales: tr, en** (recorded decision — no additional locale planned).
- **Deviation from the mcamara URL-based standard (T1/T2):** this site uses a
  **session-based** locale (`SetLocale` middleware + `/locale/{locale}`), so both
  languages share one URL. Consequences, accepted:
  - No `/en/` URL prefix; a visible `<x-language-switcher>` is provided instead.
  - **hreflang (T11) is intentionally not emitted** — it requires distinct per-locale
    URLs, which this model does not have. Revisit only if/when the site moves to
    URL-based locales.
  - Content translation for posts is row-based via the `PostLanguage` table (custom),
    not mcamara.
- UI strings live in `lang/tr.json` / `lang/en.json` (literal keys) plus
  `lang/tr/ui.php` / `lang/en/ui.php` (grouped keys). Parity is gated in CI by
  `scripts/lang-parity.php` (T3). A pre-existing key gap between the two JSON
  catalogues is reported by that script and is content work for the owner.

## AI crawlers (E2.1)

- **Decision: allow citation, block training** (the default). `robots.txt` blocks
  `GPTBot`/`ClaudeBot`/`Google-Extended`/`CCBot` and allows
  `OAI-SearchBot`/`Claude-SearchBot`/`PerplexityBot`.

## Data retention (S17)

Declared in `config/retention.php`, enforced daily by `data:prune`:
- Unsubscribed newsletter e-mails: 90 days.
- Unapproved comments: 180 days.
- Soft-deleted comments: 30 days.
Keep these in sync with the KVKK disclosure page (`/kvkk`).

## Legal pages (E11/E12)

- The four legal pages (`/privacy-policy`, `/kvkk`, `/cookie-policy`, `/terms`) are
  static bilingual Blade, edited by the owner. No `legal_document_versions` table is
  used: there is **no customer contract-acceptance flow** on a single-user personal
  blog, so the versioning CMS E12 envisions would be over-engineering. Revision tracking
  is instead: `config/legal.php` (`effective_date` + `version`, shown via
  `<x-legal-meta>`) + git history. Bump `config/legal.php` by hand when the text changes.
- **Retention figures are generated, not hand-typed:** `/kvkk` reads the day counts from
  `config/retention.php`, so the notice and the enforced policy (S17) cannot diverge.
- **İYS (İleti Yönetim Sistemi) — decision: not applicable.** The double opt-in newsletter
  is a **personal, non-commercial** content list for the site owner's own writing; it is
  not *ticari elektronik ileti* on behalf of a business and sends no promotion. If the
  list is ever used to promote MY Teknoloji services/products, this decision must be
  revisited and İYS registration + API integration added before the first such message.

## Media (M1) — deliberate deviation

Does **not** use `spatie/laravel-medialibrary` + `awcodes/filament-curator`. The
project has no Filament panel; media is handled by a custom `Media` model +
`MediaService`/`ImageService` (WebP + responsive variants). This is a conscious,
working deviation — do not "migrate" it without a concrete reason.

## SoftDeletes (L2) — scope

SoftDeletes applied to admin-CRUD content models (Adage, Link, TimelineEvent,
plus the pre-existing set). Deliberately **not** applied to:
- `Subscriber` — lifecycle is status-based (`active`/`unsubscribed`), not deletion;
  a soft-deleted row would also block re-subscribe via the unique `email`.
- `Media` — lifecycle is file-based (deleting the record cleans up disk files).
- `PostCategory` and the other dictionary tables — managed through the generic
  hard-delete dictionary CRUD; soft-deleting only one breaks that uniform flow.

## Timezone (L16) — deliberate deviation, with a migration path

`config/app.php` timezone stays **`Europe/Istanbul`**. The site is live and every
stored timestamp across ~40 tables was written as Istanbul wall-clock; Turkey has
had no DST since 2016, so usage is internally consistent. The standard's own
rationale notes nothing breaks until "the first foreign user, API integration or
chart crossing midnight" — none of which apply to this single-user Turkish site —
so a blind flip of live personal data carries real, irreversible risk for ~zero
practical gain.

A ready, reversible migration tool exists for when the move is made deliberately:
`php artisan app:shift-timestamps-to-utc` (dry-run by default; `--execute` to apply,
`--reverse` to roll back). The safe procedure, to be run **supervised**:
  1. Fresh DB backup.
  2. Maintenance mode on.
  3. Deploy with `APP_TIMEZONE=UTC` **and** convert the datetime display spots to
     `->timezone('Europe/Istanbul')` at the view edge (store-UTC / display-Istanbul).
  4. `php artisan app:shift-timestamps-to-utc --execute`.
  5. Verify a few known timestamps, then maintenance off.
Until then, Istanbul is the recorded, correct-for-this-data choice.

## Monitoring (W6–W9)

- **Horizon (W8):** installed; dashboard gated to `is_admin`. Deploy must run the
  Horizon worker (supervisor) instead of a plain `queue:work`.
- **Pulse (W6):** installed; dashboard gated to `is_admin`; disabled in tests.
- **Health (W9):** Laravel's built-in `/up` endpoint is the health check;
  `spatie/laravel-health` is not added on top for a low-traffic personal site.
- **Flare (W7):** not installed — requires an external (paid) `FLARE_KEY`. Add when
  an account exists.

## Other accepted notes

- **S4:** uses Google reCAPTCHA (existing `RecaptchaService`). Turnstile is the
  standard preference but reCAPTCHA is accepted here.
- **L3:** status/lookup values use dedicated tables rather than PHP enums — valid.
- **L4:** a few tiny endpoints (`home`, `robots.txt`, `theme.switch`, …) are closures
  — acceptable for trivial handlers.
- **X3:** admin reports use `DB::raw` with portable SUM/AVG aggregates; acceptable on
  the SQLite test DB.
