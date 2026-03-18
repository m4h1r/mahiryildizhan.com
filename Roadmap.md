# CRM + Blog — Rebuild Blueprint from Scratch (v2)

This document is an end-to-end guide prepared to build a new Laravel 12 project from scratch by **using the existing Laravel project only as a reference**. Each section is independently executable; an agent or developer should be able to read this file and reproduce the entire project from scratch in any environment.

---

## 1. Project Objective

- Establish CRM and Blog modules within a single codebase with clean domain separation.
- Preserve data via CSV import/seed; ensure the system is reproducible and idempotent.
- Align the blog side with 2026 SEO standards.
- Build the admin panel to be fast, minimal, and efficient.
- Redesign the public blog frontend to be minimal and typography-focused.
- Normalize financial flows (income/expense) using a dictionary-based structure.

---

## 2. Definitive Decisions

| Area | Decision |
|---|---|
| Framework | Laravel 12 |
| PHP | 8.5+ |
| CSS | Tailwind CSS 4 + Vite |
| JS | Alpine.js (dark mode, minimal UI) |
| Auth | Laravel Breeze (Blade, single admin role) |
| Comment anti-spam | reCAPTCHA v3 |
| CSV location (dev) | `public/csv/*.csv` |
| CSV location (prod) | `storage/app/import/*.csv` |
| Default language | Turkish (`tr`) |
| Graph visualization | Vis.js Network |
| Family tree | Vis.js Network (hierarchical layout) |
| Rich text editor | Tiptap (starter-kit + image) |
| NFT module | Out of scope |
| Photography module | Out of scope |
| QR module | Out of scope |
| Subscriber | Collect emails + Mailchimp API integration |
| Dashboard widgets | Weather (Open-Meteo) + Crypto/FX (CoinGecko + ExchangeRate) |
| Expense flags | `company_expense` + `paid_by_others` will be preserved |
| Reports | Separate `/admin/reports` page |
| Image processing | Intervention Image 3 (WebP + resize pipeline) |
| Queue driver | Database (dev) → Redis (prod) |
| Soft deletes | Post, Comment, Person tables — 30-day purge |
| Security headers | `SecurityHeadersMiddleware` (CSP, HSTS, X-Frame, nosniff) |
| Font | `@fontsource/inter` (npm, zero external requests) |
| Audit trail | `activity_logs` table — mandatory for Expense + Income |

## 3. New Project Bootstrap Commands

```bash
# 1. Create Laravel 12 project
composer create-project laravel/laravel crm-blog-rebuild
cd crm-blog-rebuild

# 2. Verify PHP version (8.5+ required)
php -v

# 3. Tailwind CSS 4 + Vite
npm install
npm install -D tailwindcss @tailwindcss/vite

# 4. Alpine.js
npm install alpinejs

# 5. Vis.js (graph visualization — family tree + node graph)
npm install vis-network vis-data

# 6. Tiptap (rich text editor)
npm install @tiptap/core @tiptap/starter-kit @tiptap/extension-image

# 7. Chart.js (Reports page)
npm install chart.js

# 8. Breeze (Blade) — single admin auth
composer require laravel/breeze --dev
php artisan breeze:install blade
npm run build

# 9. Zodiac (People/Family module)
composer require intervention/zodiac

# 11. Column sortable
composer require kyslik/column-sortable

# 12. Image optimization (WebP, resize)
composer require intervention/image

# 13. Queue table (dev: database driver)
php artisan queue:table

# 14. Font (local npm, no external requests)
npm install @fontsource/inter

# 15. Initial migration (including queue table)
php artisan migrate

# 16. Storage symlink
php artisan storage:link
```

## 4. Target Directory Structure

```
app/
  Http/
    Controllers/
      Admin/
        AdageController.php
        CommentController.php
        CsvImportController.php
        DashboardController.php
        DictionaryController.php    ← Centralized CRUD for all dictionary tables
        ExpenseController.php
        IncomeController.php
        InteractionController.php
        LinkController.php
        NodeConnectionController.php
        NodeController.php
        PersonController.php
        PostController.php
        ReportController.php
        SettingController.php
        StakeholderController.php
        SubscriberController.php
        TimelineController.php
      Public/
        BlogController.php
        CommentController.php
        FrontEndController.php
        LocaleController.php
        SearchController.php
        SubscriberController.php
    Middleware/
      SetLocale.php
      EnsureAdmin.php
    Requests/
      StorePostRequest.php
      StoreExpenseRequest.php
      StorePersonRequest.php
      StoreStakeholderRequest.php
      StoreIncomeRequest.php
      StoreCommentRequest.php
  Models/
    Adage.php
    BloodType.php
    Comment.php
    Currency.php
    ExpenseType.php
    Expense.php
    EyeColor.php
    Gender.php
    HairColor.php
    Income.php
    IncomeSource.php
    IncomeType.php
    Interaction.php
    InteractionType.php
    Link.php
    Node.php
    NodeConnection.php
    Person.php
    Post.php
    PostCategory.php
    PostLanguage.php
    Setting.php
    Stakeholder.php
    Subscriber.php
    TimelineEvent.php
    User.php
  Services/
    CsvImportService.php
    MailchimpService.php
    RecaptchaService.php
    SitemapService.php
    SlugService.php
database/
  migrations/          ← Ordered list in Section 7
  seeders/
    DatabaseSeeder.php
    DictionarySeeder.php
  csv/                 ← Dev CSV files placed here
resources/
  views/
    admin/
      layout.blade.php
      dashboard.blade.php
      reports.blade.php
      import.blade.php
      settings.blade.php
      adages/ posts/ comments/ expenses/ incomes/
      stakeholders/ people/ interactions/ nodes/
      timeline/ subscribers/ dictionaries/ links/
    public/
      layout.blade.php
      welcome.blade.php
      blog/ timeline/ biolink/ about/
    components/
      admin/
      public/
      seo-meta.blade.php
  css/app.css
  js/app.js
routes/
  web.php      ← Includes public + admin route files
  admin.php    ← /admin prefix, auth + admin middleware
  public.php   ← Anonymous public pages
  api.php      ← AJAX endpoints (VAT/Tax lookup, etc.)
```

## 5. Domain Scope

### 5.1 Behaviors to Be Preserved Exactly

| Domain | Notes |
|---|---|
| Adage | Same table structure and CRUD logic |
| TimelineEvent | milestone/process event_type, public timeline, JSON tags/metadata |
| Node | name, text_color, text_size, image |
| NodeConnection | node_from_id, node_to_id — directed graph |
| Interaction | person_id, date, type, effect, notes; bound to a person |
---

### 5.2 Behaviors to Be Improved

| Domain | Current Issue | New Approach |
|---|---|---|
| Post | category/language as string fields | category_id FK, language_id FK |
| Post | No SEO/metrics fields | Full SEO + metrics columns will be added |
| Income | source, type, currency as string | Dictionary FKs |
| Expense | seller text, type string, currency string | stakeholder_id FK, expense_type_id FK, currency_id FK |
| Expense | ExpenseCategory as a separate model | Merged into expense_types dictionary table |
| Person | gender, eye_color, blood_type, hair_color as string | Dictionary FKs |
| Family Tree | Built with Blade + CSS flexbox | Vis.js Network (hierarchical layout) |
| Node Graph | Existing custom JS implementation | Vis.js Network (force-directed) |
| Interaction type | String field | interaction_types dictionary FK |

---

## 6. Full Data Model (All Columns)

### 6.1 Dictionary Tables

CRUD operations can be performed via the Admin panel. All contain id, name, [slug nullable], timestamps.

```
genders            id, name, slug, timestamps
eye_colors         id, name, slug, timestamps
blood_types        id, name, timestamps
hair_colors        id, name, slug, timestamps
post_categories    id, name, slug, description nullable, timestamps
post_languages     id, name, code (ISO 639-1), timestamps
income_sources     id, name, timestamps
income_types       id, name, timestamps
currencies         id, code (ISO 4217), name, symbol, timestamps
expense_types      id, name, timestamps
interaction_types  id, name, timestamps
```
### 6.2 users

```
id, name, email, password, remember_token
email_verified_at nullable
timestamps
```
### 6.3 settings

```
id
key           string unique       (e.g., "ga_tracking_id")
value         text nullable
group         string              (analytics | advertising | seo | mailchimp | recaptcha | weather)
is_secret     boolean default false   ← .env override in prod
description   text nullable
timestamps
```
### 6.4 posts

```
id
title              string
slug               string unique
body               longtext
cover              string nullable
category_id        FK post_categories nullable
language_id        FK post_languages nullable
user_id            FK users nullable
seo_title          string nullable (max 60 characters)
seo_description    string nullable (max 160 characters)
seo_keywords       string nullable
canonical_url      string nullable
og_image           string nullable
schema_type        string default 'Article'
reading_time       unsignedInt nullable       ← ceil(word_count / 200) minutes
word_count         unsignedInt nullable       ← str_word_count(strip_tags($body))
view_count         unsignedBigInt default 0
unique_view_count  unsignedBigInt default 0
share_count        unsignedBigInt default 0
like_count         unsignedBigInt default 0
save_count         unsignedBigInt default 0
status             enum(draft, published, archived) default draft
published_at       timestamp nullable
publish_date       date nullable
published          boolean default false
timestamps
```
### 6.5 comments

```
id
post_id           FK posts
user_id           FK users nullable
guest_name        string nullable
guest_email       string nullable
body              text
is_approved       boolean default false
spam_score        float nullable         ← reCAPTCHA v3 score
recaptcha_score   float nullable
ip_hash           string nullable        ← hash('sha256', $ip)
user_agent_hash   string nullable
timestamps
```
### 6.6 adages

```
id, owner string, adage text, keywords string nullable, language string nullable, timestamps
```
### 6.7 stakeholders

```
id
vkn_tckn         string unique
title            string nullable         ← company name
name             string nullable
surname          string nullable
tax_office_name  string nullable
city             string nullable
country          string default 'TR'
address          text nullable
phone            string nullable
email            string nullable
website          string nullable
company_type     enum(Company, Individual) default Company
sector           string nullable
note             text nullable
status           enum(Active, Passive) default Active
created_by       FK users nullable
timestamps
```
### 6.8 expenses

```
id
date              date
stakeholder_id    FK stakeholders nullable
expense_type_id   FK expense_types nullable
currency_id       FK currencies
description       string nullable
price             decimal(12,2) nullable
quantity          decimal(10,3) default 1
tax               decimal(12,2) nullable
total             decimal(12,2) nullable     ← calculated in PHP during store/update: price * quantity
company_expense   boolean default false
paid_by_others    boolean default false
timestamps
```
> The `total` column is calculated and saved on the PHP side during store and update operations.
> This column is used instead of `DB::raw('price * quantity')` for query performance.

### 6.9 incomes

```
id
date              date
income_source_id  FK income_sources nullable
income_type_id    FK income_types nullable
currency_id       FK currencies
amount            decimal(12,2)
description       string nullable
user_id           FK users nullable
timestamps
```
### 6.10 people

```
id
name             string
surname          string
second_surname   string nullable
birthday         date nullable
deathday         date nullable
birth_place      string nullable
death_place      string nullable
father_id        FK people (self) nullable
mother_id        FK people (self) nullable
partner_id       FK people (self) nullable
gender_id        FK genders nullable
eye_color_id     FK eye_colors nullable
blood_type_id    FK blood_types nullable
hair_color_id    FK hair_colors nullable
picture          string nullable
mobile           string nullable
email            string nullable
notes            text nullable
timestamps
```
### 6.11 interactions

```
id
person_id            FK people
interaction_type_id  FK interaction_types nullable
date                 date
effect               string nullable
notes                text nullable
timestamps
```

### 6.12 nodes

```
id, name string, text_color string nullable, text_size string nullable
image string nullable, timestamps
```

### 6.13 node_connections

```
id, node_from_id FK nodes, node_to_id FK nodes, timestamps
```

### 6.14 timeline_events

```
id
title         string
description   text nullable
event_type    enum(milestone, process) default milestone
start_date    date
end_date      date nullable
image         string nullable
icon          string nullable
color         string default '#3B82F6'
is_public     boolean default true
category      string nullable
location      string nullable
tags          json nullable
metadata      json nullable
order         int default 0
timestamps
```

### 6.15 subscribers

```
id
email            string unique
status           enum(active, unsubscribed) default active
subscribed_at    timestamp
unsubscribed_at  timestamp nullable
mailchimp_id     string nullable
timestamps
```

### 6.16 links

```
id
slug           string unique
file_path      string
original_name  string
mime_type      string nullable
size           unsignedBigInt nullable
expires_at     timestamp nullable
download_count unsignedInt default 0
timestamps
```

## 7. Migration Order (Mandatory FK Dependency Order)

```
Order Table
----  ---------------------------------------------------
 01   users
 02   password_reset_tokens
 03   sessions
 04   failed_jobs
 05   personal_access_tokens
--- Dictionary (No FK dependencies) ---
 10   genders
 11   eye_colors
 12   blood_types
 13   hair_colors
 14   post_categories
 15   post_languages
 16   income_sources
 17   income_types
 18   currencies
 19   expense_types
 20   interaction_types
--- Settings ---
 25   settings
--- Main tables (By FK order) ---
 30   stakeholders             ← users FK
 40   people                   ← genders, eye_colors, blood_types, hair_colors FKs; self FKs nullable
 50   posts                    ← post_categories, post_languages, users FK
 60   comments                 ← posts, users FK
 70   expenses                 ← stakeholders, expense_types, currencies FK
 80   incomes                  ← income_sources, income_types, currencies, users FK
 90   interactions             ← people, interaction_types FK
100   nodes
110   node_connections         ← nodes FK
120   timeline_events
130   adages
140   subscribers
150   links
```
> Since the self FKs (`father_id`, `mother_id`, `partner_id`) in the `people` table are nullable,
> they can be defined within the same migration; no circular dependency will occur.

---
## 8. Route Maps (All)

### 8.1 Public Routes (`routes/public.php`)

```
GET  /                           FrontEndController@landing
GET  /blog                       BlogController@index
GET  /blog/{slug}                BlogController@show
GET  /timeline                   TimelineController@publicTimeline
GET  /biolink                    FrontEndController@biolink
GET  /about                      view('public.about')
GET  /search                     SearchController@search
GET  /files/{slug}               LinkController@show
GET  /sitemap.xml                SitemapController@xml
GET  /locale/{locale}            LocaleController@switch
POST /subscribe                  Public\SubscriberController@store
POST /comments                   Public\CommentController@store
```

### 8.2 Admin Routes (`routes/admin.php`)
All `/admin` prefix + `auth` + `admin` middleware.

```
GET  /admin/dashboard                    DashboardController@index
GET  /admin/reports                      ReportController@index

--- Blog ---
resource /admin/posts                    PostController (index, create, store, edit, update, destroy)
resource /admin/post-categories          DictionaryController
resource /admin/post-languages           DictionaryController
GET  /admin/comments                     CommentController@index
PUT  /admin/comments/{id}/approve        CommentController@approve
DELETE /admin/comments/{id}              CommentController@destroy

--- CRM: Finance ---
resource /admin/stakeholders             StakeholderController
POST /admin/stakeholders/{id}/duplicate  StakeholderController@duplicate
resource /admin/expenses                 ExpenseController
POST /admin/expenses/{id}/duplicate      ExpenseController@duplicate
resource /admin/incomes                  IncomeController
POST /admin/incomes/{id}/duplicate       IncomeController@duplicate

--- CRM: People ---
resource /admin/people                   PersonController
GET  /admin/people/search                PersonController@search
GET  /admin/people/{id}/tree             PersonController@showTree
GET  /admin/people/{id}/graph            PersonController@showGraph
resource /admin/interactions             InteractionController
POST /admin/interactions/{id}/duplicate  InteractionController@duplicate

--- Knowledge Graph ---
resource /admin/nodes                    NodeController
GET  /admin/nodes/graph                  NodeController@graph
resource /admin/node-connections         NodeConnectionController

--- Timeline & Adages ---
resource /admin/timeline                 TimelineController
resource /admin/adages                   AdageController

--- Subscribers ---
GET  /admin/subscribers                  SubscriberController@index
GET  /admin/subscribers/export           SubscriberController@export
POST /admin/subscribers/{id}/unsubscribe SubscriberController@unsubscribe
DELETE /admin/subscribers/{id}           SubscriberController@destroy

--- Links ---
resource /admin/links                    LinkController

--- CSV Import ---
GET  /admin/import                       CsvImportController@index
POST /admin/import/{table}               CsvImportController@import

--- Dictionaries ---
resource /admin/expense-types            DictionaryController
resource /admin/income-sources           DictionaryController
resource /admin/income-types             DictionaryController
resource /admin/currencies               DictionaryController
resource /admin/genders                  DictionaryController
resource /admin/eye-colors               DictionaryController
resource /admin/blood-types              DictionaryController
resource /admin/hair-colors              DictionaryController
resource /admin/interaction-types        DictionaryController

--- Settings & Profile ---
GET  /admin/settings                     SettingController@index
POST /admin/settings                     SettingController@update
GET  /admin/profile                      ProfileController@show
PUT  /admin/profile                      ProfileController@update
```
### 8.3 API Routes (`routes/api.php`)

```
GET  /api/stakeholders/lookup    StakeholderController@lookup   ← ?vkn= ile AJAX
POST /api/stakeholders/quick     StakeholderController@quickStore
POST /api/posts/{slug}/view      PostController@trackView
```
---

## 9. Controller Method Summary

### DictionaryController
Single controller for all dictionary tables; `$table` is taken from the route parameter.
Methods: `index`, `create`, `store`, `edit`, `update`, `destroy`.
A single view set is shared (`resources/views/admin/dictionaries/`).

### StakeholderController
- `index` (filtered, paginated), `create`, `store`, `edit`, `update`, `destroy`, `duplicate`
- `lookup`: `GET /api/stakeholders/lookup?vkn=` → `{found: bool, data: {id, title, vkn_tckn}}`
- `quickStore`: `POST /api/stakeholders/quick` → `{id, title, vkn_tckn}`

### ExpenseController
- `index`: filtered by date, expense_type_id, currency_id, company_expense, paid_by_others, stakeholder; paginated
- `create`, `store`: `total = price * quantity` is calculated and saved
- `edit`, `update`, `destroy`, `duplicate`

### PostController (Admin)
- `index`, `create`, `store`, `edit`, `update`, `destroy`
- During `store`/`update`: `slug` (SlugService), `word_count`, and `reading_time` are calculated automatically

### BlogController (Public)
- `index`: paginated, only `status = published`
- `show`: post detail; `view_count++`; `unique_view_count` is session-based

### PersonController
- `index`, `create`, `store`, `edit`, `update`, `destroy`, `search` (JSON)
- `showTree`: `{nodes, edges}` JSON + view for Vis.js hierarchical
- `showGraph`: Vis.js force-directed view

### DashboardController
- `index`: weather (Open-Meteo, 15m cache) + crypto/forex (CoinGecko + ExchangeRate, 15m cache) + summary cards

### ReportController
- `index`: `?year=` parameter; annual income/expense/balance + monthly breakdown + expense_type analysis + Chart.js

### CsvImportController
- `index`: table selection form + recent import results
- `import`: CsvImportService call → returns `{inserted, updated, skipped, errors}` JSON report

---

## 10. CSV Import Contract
### 10.1 File Location

```
Dev:  database/csv/{table}.csv  (or public/csv/{table}.csv)
Prod: storage/app/import/{table}.csv
```
`CsvImportService` selects the correct folder based on the `APP_ENV` value.

### 10.2 General Rules

- Header row is mandatory; column names must exactly match DB column names.
- An `upsert_key` is defined for each table (below).
- If FK references cannot be resolved: the record is skipped and logged in the report.
- All import operations run within a `DB::transaction()`.
- Result report: `{inserted: N, updated: N, skipped: N, errors: [...]}`

### 10.3 Table-Based Upsert Keys and Import Order

| Order | Table | Upsert Key | FK Dependent On |
|---|---|---|---|
| 1 | genders | name | — |
| 2 | eye_colors | name | — |
| 3 | blood_types | name | — |
| 4 | hair_colors | name | — |
| 5 | post_categories | slug | — |
| 6 | post_languages | code | — |
| 7 | income_sources | name | — |
| 8 | income_types | name | — |
| 9 | currencies | code | — |
| 10 | expense_types | name | — |
| 11 | interaction_types | name | — |
| 12 | stakeholders | vkn_tckn | — |
| 13 | people | name+surname+birthday | genders, eye_colors, blood_types, hair_colors |
| 14 | posts | slug | post_categories, post_languages |
| 15 | comments | post_slug+guest_email+created_at | posts |
| 16 | incomes | date+amount+income_source | income_sources, income_types, currencies |
| 17 | expenses | date+description+stakeholder_vkn | stakeholders, expense_types, currencies |
| 18 | interactions | person_id+date+type | people, interaction_types |
| 19 | timeline_events | title+start_date | — |
| 20 | adages | adage | — |
| 21 | nodes | name | — |
| 22 | node_connections | node_from+node_to | nodes |
| 23 | subscribers | email | — |

### 10.4 Artisan Command

```bash
php artisan import:csv stakeholders            # Single table
php artisan import:csv --all                   # All tables in sequence
php artisan import:csv stakeholders --dry-run  # Dry run (does not write data)
```
---
## 11. Dashboard Widget Architecture
```
DashboardController@index
├── Cache::remember('weather_kocaeli', 900, fn() =>
│     Http::get('[https://api.open-meteo.com/v1/forecast](https://api.open-meteo.com/v1/forecast)', [
│       'latitude'  => 40.7654,
│       'longitude' => 29.9404,
│       'daily'     => 'weathercode,temperature_2m_max,temperature_2m_min,...',
│       'forecast_days' => 5
│     ]))
│     → if returns null, view shows a graceful fallback
├── Cache::remember('exchange_rates', 900, fn() => [
│     BTC + ETH: Http::get([coingecko.com/api/v3/simple/price](https://coingecko.com/api/v3/simple/price))
│     USD/EUR/GBP → TRY: Http::get([api.exchangerate-api.com/v4/latest/TRY](https://api.exchangerate-api.com/v4/latest/TRY))
│   ])
└── Summary cards (single DB round-trip):
    ├── Post::where('status', 'published')->count()
    ├── Comment::where('is_approved', false)->count()
    ├── Expense::whereMonth('date', now()->month)->sum('total')
    └── Income::whereMonth('date',  now()->month)->sum('amount')
```
## 12. Reports Page Scope (`/admin/reports`)

All financial report logic in the current project is moved to this separate page.

```
ReportController@index — ?year=20xx selectable
├── Annual income total        : Income::whereYear->sum('amount')
├── Annual expense total       : Expense::whereYear->where('paid_by_others', false)->sum('total')
├── Net balance                : income − expense
├── Monthly breakdown (12 months, 4 series — Chart.js bar/line):
│   ├── company_expense = true  → company expenses
│   ├── company_expense = false → personal expenses
│   ├── SUM(tax) per month      → tax/penalty total
│   └── Monthly income total
├── ExpenseType based analysis (table + Chart.js doughnut):
│   for each type: total_amount, daily_avg, monthly_avg
│   (number of days: from beginning of the year to today)
└── Filter: TRY currency (excluding other currencies)
```
---

## 13. Blog Module — SEO + Metrics

### 13.1 Auto-Calculated Fields (store/update)

```php
$slug        = SlugService::generate($request->title, Post::class); // Turkish → ASCII
$wordCount   = str_word_count(strip_tags($request->body));
$readingTime = max(1, (int)ceil($wordCount / 200)); // minutes
```
### 13.2 `x-seo-meta` Blade Component

```blade
<x-seo-meta :post="$post" />
```

If `$post` is null, site defaults are read from the `settings` table:
`site_name`, `default_og_image`, `default_meta_description`.

Generated meta tags: `<title>`, `<meta description>`, `<link canonical>`,
`<meta og:*>`, `<script type="application/ld+json">` (Article schema).

### 13.3 Metric Collection

- `view_count`: `Post::increment('view_count')` on every `BlogController@show` call
- `unique_view_count`: increment if `session()->has("viewed_{$post->id}")` does not exist; then add to session
- `share_count`, `like_count`, `save_count`: Columns are kept ready, to be implemented in Phase 2

### 13.4 Sitemap

```php
SitemapService::generate()
// All published posts + static pages
// → writes as public/sitemap.xml
// Route /sitemap.xml → SitemapController@xml, serves dynamic or static file
```
---

## 14. Comment System — Anti-Spam Flow

```
POST /comments
  1. Honeypot check          → if filled, return silent 200 (assume bot)
  2. Rate limit              → throttle:1,2 (1 request / 2 minutes / IP)
  3. Validation              → body required, guest_name required, guest_email nullable
  4. RecaptchaService@verify → POST [https://www.google.com/recaptcha/api/siteverify](https://www.google.com/recaptcha/api/siteverify)
     score < 0.5             → validation error
  5. ip_hash                 → hash('sha256', $request->ip())
  6. user_agent_hash         → hash('sha256', $request->userAgent())
  7. Comment::create([..., is_approved: false, spam_score: $score])
  8. Flash: "Your comment has been submitted for review."
```
Admin `/admin/comments`: filter (pending approval / approved), `approve` + `destroy`, `spam_score` visible.
---
## 15. Expense + Stakeholder Tax ID (VKN) Flow

### 15.1 Alpine.js Form Logic
Create/Edit Expense form:
  Tax ID (VKN) input → @input (debounce 500ms)
    └── fetch('/api/stakeholders/lookup?vkn=' + vkn)
        ├── {found: true,  data: {id, title}}  → Write to stakeholder_id hidden field, display title
        └── {found: false}
              → Modal: "This Tax ID is not registered. Would you like to add it?"
                  Yes → Open quickStakeholderModal
                    POST /api/stakeholders/quick {vkn_tckn, title, ...}
                    {success: true, id: N, title: '...'} → Populate stakeholder_id, close modal

### 15.2 Expense Index Filters

`date_from`, `date_to`, `expense_type_id`, `currency_id`,
`company_expense` (boolean), `paid_by_others` (boolean),
`stakeholder_id` (partial text search), `per_page`

## 16. People Module + Vis.js Family Tree

### 16.1 Model Methods to be Preserved

```php
// Gender-based child relationship
public function children() {
    $field = $this->isGenderMale() ? 'father_id' : 'mother_id';
    return $this->hasMany(Person::class, $field)->orderBy('birthday');
}

// All children regardless of gender (family-tree-rules.md Rule 10)
public function allChildren() {
    return Person::where('father_id', $this->id)
        ->orWhere('mother_id', $this->id)
        ->orderBy('birthday')
        ->get();
}

public function father()   { return $this->belongsTo(Person::class, 'father_id'); }
public function mother()   { return $this->belongsTo(Person::class, 'mother_id'); }
public function partner()  { return $this->belongsTo(Person::class, 'partner_id'); }
public function interactions() { return $this->hasMany(Interaction::class, 'person_id'); }

// Zodiac (intervention/zodiac)
public function zodiacName() { return Calculator::make($this->birthday)->localized('tr'); }
```
















### 16.2 Vis.js Family Tree Data Structure

`PersonController@showTree($id)` passes JSON to the view in the following structure:

```json
{
  "nodes": [
    {"id": 1, "label": "Ad Soyad", "level": 2, "group": "grandfather_paternal",
     "shape": "circularImage", "image": "/path/to/pic", "title": "Doğum: ..."}
  ],
  "edges": [
    {"from": 3, "to": 1, "label": "baba"},
    {"from": 4, "to": 1, "label": "anne"}
  ]
}
```

**Level values** (according to family-tree-rules.md):
- `level 2`: grandparents (both paternal and maternal sides)
- `level 1`: parents
- `level 0`: central person, siblings, spouse
- `level -1`: children, children's spouses

**Vis.js options**:
```js
layout: { hierarchical: { direction: "UD", sortMethod: "directed" } }
```

**Color groups** (family-tree-rules.md Color Scheme):
- Male → blue gradient
- Female → pink/rose gradient
- Other → gray gradient
- Spouse → rose/red
- Siblings → indigo gradient

### 16.3 Vis.js Node Graph

`NodeController@graph`: Serves all Node + NodeConnection records in Vis.js Network format.

```js
options: {
  physics: { enabled: true },
  edges: { arrows: { to: { enabled: true } } }
}
```

---

## 17. Subscriber + Mailchimp Integration

### 17.1 Subscribe Flow

```
POST /subscribe {email}
  1. Validation: email required, unique:subscribers
  2. Database Record: Subscriber::create([email, status: 'active', subscribed_at: now()])
  3. MailchimpService@subscribe($email)
     - API key = Setting::get('mailchimp_api_key') ?? env('MAILCHIMP_API_KEY')
     - If key is missing: return ['success' => false]; Log::warning('Mailchimp key missing')
     - If key exists:
         POST https://{datacenter}[.api.mailchimp.com/3.0/lists/](https://.api.mailchimp.com/3.0/lists/){list_id}/members
         Body: {email_address: $email, status: 'subscribed'}
         → Update subscribers.mailchimp_id with member.id from response
  4. Flash / JSON response
```

### 17.2 Admin Subscriber Management Page

- Paginated List: email, status, subscribed_at, mailchimp_id (sync status).
- CSV Export: Capability to export the subscriber list.
- Unsubscribe: DB `status = unsubscribed` + Mailchimp `PATCH status: 'unsubscribed'`

### 17.3 Mailchimp Settings Keys

```
mailchimp_api_key     (is_secret: true)
mailchimp_list_id
mailchimp_datacenter  ← us1, us2, ...
```

---

## 18. Admin Variables/Settings Modul

```
SettingController@index  → Group-based key-value form
SettingController@update → Bulk POST update
```

| Grup | Example Keys |
|---|---|
| analytics | ga_tracking_id, search_console_verification, crux_api_key |
| advertising | adsense_client_id, adsense_slot_id |
| seo | site_name, default_og_image, default_meta_description |
| mailchimp | mailchimp_api_key (secret), mailchimp_list_id, mailchimp_datacenter |
| recaptcha | recaptcha_site_key, recaptcha_secret_key (secret) |
| weather | weather_latitude, weather_longitude, weather_city_name |

Fields with `is_secret = true` are displayed as `type="password"` in the form.

```php
env('RECAPTCHA_SECRET') ?? Setting::get('recaptcha_secret_key')
```

---

## 19. UI/UX Strategies

### 19.1 Public Blog

- Typography-focused, high white space (Inter or system-ui font stack)
- Color palette: Primary logo color + neutral supporting tones
- Dark/Light mode: Alpine.js + localStorage + prefers-color-scheme initial value
- Mobile-first responsive (Tailwind breakpoints)
- JSON-LD Article schema on every post page

### 19.2 Admin Panel

- Fixed left sidebar (Blog, CRM-Finance, CRM-People, Graph, Timeline, Settings)
- Top bar: Username + dark/light toggle
- Table screens: Live search input + filter form + sortable headers
- CRUD forms: Shared x-admin-form-layout component
- Flash messages: x-flash Blade component (for all CRUD actions)
- Dark/Light + Responsive mandatory

---

## 20. i18n Roadmap

| Phase | Content |
|---|---|
| Phase 1 | `locale: tr`. All UI texts are centralized via lang/tr.json. |
| Phase 2 | `lang/en.json` is added. Fallback: tr. Language selection in Admin. |
| Later | Language-based content entry on the Admin side (post title/content per language). |

---

## 21. Sprint / Phase Plan

Mandatory at the end of each phase: php artisan migrate:fresh --seed with zero errors.

### Phase 0 — Project Skeleton (2 days)
- [ ] Laravel 12 + Tailwind 4 + Vite + installation of all necessary npm packages.
- [ ] Breeze Blade Auth — setup for a single admin.
- [ ] `SetLocale` + `EnsureAdmin` middleware
- [ ] Create `SecurityHeadersMiddleware` (CSP, X-Frame-Options, nosniff, Referrer-Policy) → add to the web middleware group.
- [ ] Queue: `.env` + `.env.example` → `QUEUE_CONNECTION=database`; `php artisan queue:table && php artisan migrate`
- [ ] Create `routes/admin.php`, `routes/public.php`, `routes/api.php`
- [ ] Admin Layout + Public Layout Blade templates.
- [ ] Dark/Light Mode Alpine.js infrastructure (localStorage + prefers-color-scheme)
- [ ] `x-flash` message Blade component
- [ ] CSS Design Tokens: Add Tailwind @theme block to resources/css/app.css (brand colors, font stack, spacing).
- [ ] Custom Error Pages: Create 404.blade.php, 500.blade.php, and 503.blade.php in resources/views/errors/.
- [ ] Add "skip-to-content" hidden links to both layouts (linked to #main-content anchor).

### Phase 1 — Migration + Model Layer (3 days)
- [ ] All migration files in §7 order (40+ migrations)
- [ ] All Model classes (fillable, casts, relationships complete)
- [ ] `DictionarySeeder`: seed constant values for genders, blood_types, currencies
- [ ] `php artisan migrate --seed` completes successfully

### Phase 2 — Dictionary CRUD Admin Screens (2 days)
- [ ] `DictionaryController` (single controller, `$table` parameter-driven)
- [ ] Shared CRUD view set (`resources/views/admin/dictionaries/`)
- [ ] All dictionaries added to the admin sidebar

### Phase 3 — CSV Import Pipeline (3 days)
- [ ] `CsvImportService`: environment folder selection, header-map, upsert, transaction, log
- [ ] Artisan `import:csv {table} {--all} {--dry-run}`
- [ ] §10.3 upsert logic applied for every table
- [ ] Web UI: `/admin/import` (select table → show report)
- [ ] Verified: running the same CSV twice produces no duplicates

### Phase 4 — Stakeholder + Expense (3 days)
- [ ] `StakeholderController` CRUD + duplicate + VKN search
- [ ] `GET /api/stakeholders/lookup` + `POST /api/stakeholders/quick`
- [ ] `ExpenseController` CRUD + duplicate
- [ ] Alpine.js VKN lookup + quick-create modal
- [ ] Expense index: all filters working

### Phase 5 — Income Module (1 day)
- [ ] `IncomeController` CRUD + duplicate
- [ ] Form with dictionary FK dropdowns

### Phase 6 — Blog Module (4 days)
- [ ] Admin `PostController`: CRUD + auto slug/word_count/reading_time
- [ ] Tiptap integration (editor + image upload)
- [ ] SEO meta fields form
- [ ] Public `BlogController` (index + show + view_count + unique_view_count)
- [ ] `SitemapService` + `/sitemap.xml`
- [ ] `x-seo-meta` Blade component + JSON-LD Article schema

### Phase 7 — Comment System (2 days)
- [ ] Public `CommentController@store` (honeypot + rate limit + reCAPTCHA v3)
- [ ] `RecaptchaService` integration
- [ ] Admin moderation screen (approve + destroy)

### Phase 8 — People + Family Tree (4 days)
- [ ] `PersonController` CRUD + search
- [ ] Form with dictionary FK dropdowns
- [ ] `showTree` → Vis.js hierarchical JSON + view
- [ ] `allChildren()` + `children()` logic preserved
- [ ] family-tree-rules.md rules implemented via Vis.js

### Phase 9 — Interaction (1 day)
- [ ] `InteractionController` CRUD + duplicate
- [ ] Interaction list on the person detail page

### Phase 10 — Node Graph (2 days)
- [ ] `NodeController` + `NodeConnectionController` CRUD
- [ ] `NodeController@graph` → Vis.js Network (force-directed + directed arrows)

### Phase 11 — Adage + Timeline + Subscriber (3 days)
- [ ] `AdageController` CRUD
- [ ] `TimelineController` CRUD + public timeline view
- [ ] Public `SubscriberController` + `MailchimpService` integration
- [ ] Admin subscriber list + CSV export

### Phase 12 — Dashboard + Reports (2 days)
- [ ] `DashboardController` (weather + crypto/forex + summary cards)
- [ ] `ReportController` (monthly breakdown + expense_type analysis + Chart.js)

### Phase 13 — Link + Search + Biolink (2 days)
- [ ] Admin `LinkController` CRUD + Public `LinkController@show`
- [ ] `SearchController` (post + adage + people)
- [ ] Biolink public view

### Phase 14 — Settings/Variables (1 day)
- [ ] `SettingController` (group-based key-value form)
- [ ] `.env` override logic applied across all services

### Phase 15 — Frontend Redesign (5 days)
- [ ] Public blog: typography foundation, whitespace, color palette
- [ ] Admin panel: consistent nav, table styles, form styles
- [ ] Dark/light mode: all screens tested
- [ ] Mobile responsive: all primary flows unbroken

### Phase 16 — Test + QA + Release (3 days)
- [ ] Feature tests: PostController, ExpenseController, CommentController, CsvImportService
- [ ] Smoke tests: all routes return 200/302
- [ ] `php artisan migrate:fresh --seed` + CSV import run cleanly
- [ ] `.env.example` is up to date
- [ ] Go-live checklist completed

---

## 22. Test and Acceptance Criteria

### 22.1 Functional
- [ ] All dictionary CRUD screens work without issues.
- [ ] Expenses are saved using only `stakeholder_id`; no `seller` column exists.
- [ ] VKN lookup: found → auto-bind; not found → modal → quick create → bind.
- [ ] Post `slug` is auto-generated, unique, and Turkish characters are normalized.
- [ ] Comment reCAPTCHA v3 score is stored; `score < 0.5` is rejected.
- [ ] Timeline public page shows only events where `is_public = true`.
- [ ] Vis.js family tree renders the correct hierarchical layout.
- [ ] Mailchimp: subscribe → appears in the list; if no API key, DB-only record.
- [ ] Reports return correct data for both `?year=2025` and `?year=2026`.

### 22.2 Technical
- [ ] `php artisan migrate:fresh --seed` completes with zero errors.
- [ ] Running the same CSV file a second time produces no duplicates.
- [ ] `php artisan import:csv --all --dry-run` does not write any real data.
- [ ] An index is defined on every FK column.
- [ ] `php artisan route:list` contains no undefined actions.

### 22.3 UX
- [ ] Dark/Light mode: no visual issues in either theme across admin and public.
- [ ] Creating an expense, post, or person on mobile does not break the layout.
- [ ] Live search and filtering work on admin table screens.
- [ ] Flash messages appear for all CRUD actions.

---

## 23. GitHub Workflow

```bash
git init
git add .
git commit -m "feat: initial project scaffold"
git remote add origin <PRIVATE_REPO_URL>
git push -u origin main

# At the end of each phase:
git add -A && git commit -m "feat: phase-{N} complete"
```

- Branches: `main` + `feature/phase-N-name`
- PR description: purpose | changed files | migration impact | test evidence

---

## 24. Out of Scope (First Release)

- NFT module
- Photography / Photo gallery module
- QR code module
- Multi-role / advanced ACL
- Language-based content entry on the admin side (deferred to Phase 2)
- Using `public/csv` in the production environment

---

## 25. Reference Files (Legacy Project — For Reference Only)

```
app/Models/Post.php              → field list, relationships
app/Models/Expense.php           → company_expense, paid_by_others logic
app/Models/Income.php            → field list
app/Models/Person.php            → children/allChildren/zodiac logic
app/Models/Stakeholder.php       → VKN unique constraint, scopes, accessors
app/Models/Interaction.php       → linked to person, type/effect
app/Models/Node.php              → linksFrom/linksTo
app/Models/NodeConnection.php    → source/target
app/Models/TimelineEvent.php     → event_type, casts, scopes, accessors
app/Models/Adage.php             → field list
app/Http/Controllers/FrontEndController.php  → dashboard widget logic
app/Http/Controllers/ExpenseController.php   → filter/paginate logic
family-tree-rules.md             → rule reference for Vis.js hierarchical layout
routes/web.php                   → existing route catalog
```

## 27. Technical Quality + Modern Design Standards

This section defines the minimum requirements the project must meet within the framework of current web standards.

### 27.1 Soft Deletes — Data Safety

The following tables use the `SoftDeletes` trait; `$table->softDeletes()` must be added to their migrations:

| Model | Reason |
|---|---|
| `Post` | Prevent accidental deletion of published content |
| `Comment` | Preserve moderation history |
| `Person` | Avoid breaking family-tree relationships |

**Purge policy:** Records soft-deleted more than 30 days ago are hard-deleted via a weekly scheduler.

```php
// app/Console/Kernel.php → schedule()
$schedule->command('model:prune', ['--model' => [Post::class, Comment::class, Person::class]])->weekly();
```

Post trash view in admin: `/admin/posts?trashed=1` — uses `Post::onlyTrashed()`.

---

### 27.2 Queue / Async Jobs

**Driver selection:**

```dotenv
# .env.example
QUEUE_CONNECTION=database   # dev
# QUEUE_CONNECTION=redis    # prod (uncomment)
```

**Job classes:**

| Job | Trigger |
|---|---|
| `MailchimpSubscribeJob` | `dispatch()` after a successful save in `SubscriberController@store` |
| `GenerateSitemapJob` | Daily scheduler + post publish/unpublish events |
| `SendCommentNotificationJob` | Admin email notification when a new comment is pending |

**Bootstrap step:**
```bash
php artisan queue:table
php artisan migrate
# Worker (dev):
php artisan queue:work --tries=3
```

**Scheduler (production crontab):**
```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

### 27.3 Image Optimization Pipeline

**Package:** `intervention/image` v3

**Rules:**

| Context | Max Size | Format | Location |
|---|---|---|---|
| Post cover image | 1200 × 630 px | WebP | `storage/app/public/covers/` |
| Person profile photo | 400 × 400 px | WebP | `storage/app/public/people/` |

**`ImageService` skeleton:**

```php
// app/Services/ImageService.php
class ImageService
{
    public function optimize(UploadedFile $file, int $maxW, int $maxH, string $disk): string
    {
        $image = Image::read($file);
        $image->scaleDown($maxW, $maxH);
        $filename = Str::uuid() . '.webp';
        $image->toWebp(quality: 85)->save(storage_path("app/public/{$disk}/{$filename}"));
        return $filename;
    }
}
```

Inline image uploads via Tiptap also go through this service.

---

### 27.4 Security Headers (`SecurityHeadersMiddleware`)

**File:** `app/Http/Middleware/SecurityHeadersMiddleware.php`

```php
public function handle(Request $request, Closure $next): Response
{
    $response = $next($request);
    return $response
        ->header('X-Frame-Options', 'SAMEORIGIN')
        ->header('X-Content-Type-Options', 'nosniff')
        ->header('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->header('Permissions-Policy', 'camera=(), microphone=(), geolocation=()')
        ->header('Content-Security-Policy',
            "default-src 'self'; " .
            "script-src 'self' 'nonce-{$request->attributes->get('csp_nonce', '')}' https://www.google.com https://www.gstatic.com; " .
            "img-src 'self' data: https:; " .
            "font-src 'self' data:; " .
            "connect-src 'self'; " .
            "frame-src https://www.google.com"
        );
}
```

Register in the `web` middleware group inside `bootstrap/app.php`.

**Note:** In an HTTPS environment, also add `Strict-Transport-Security: max-age=31536000; includeSubDomains`.

---

### 27.5 Core Web Vitals Targets

| Metric | Target | Implementation Strategy |
|---|---|---|
| LCP (Largest Contentful Paint) | < 2.5 s | `<link rel="preload">` for above-the-fold images on the blog list page |
| INP (Interaction to Next Paint) | < 200 ms | No render-blocking JS on public pages beyond Alpine.js |
| CLS (Cumulative Layout Shift) | < 0.1 | Explicit `width` + `height` attributes required on every `<img>` tag |

**Measurement:** Google Search Console CrUX integration — `crux_api_key` is stored in the `analytics` group of the settings table.

---

### 27.6 Accessibility (WCAG 2.1 AA)

Minimum compliance requirements:

- Every `<img>` tag must have an `alt` attribute (decorative images use `alt=""`).
- Every form input must have a matching `<label for="...">` / `id` pair.
- Use the `:focus-visible` CSS pseudo-class for button/icon focus, not `:focus` alone.
- Normal text color contrast ≥ 4.5:1; large text / UI components ≥ 3:1.
- Icon-only buttons (edit, delete, approve) must have an `aria-label` attribute.
- Both layouts include a visually hidden skip-to-content link at the top of `<body>`:

```html
<a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:p-3 focus:bg-white focus:text-black">
    Skip to content
</a>
```

- All CSS transitions and animations are reset inside `@media (prefers-reduced-motion: reduce)`.

---

### 27.7 CSS Design Tokens (Tailwind 4 `@theme`)

Add to the top of `resources/css/app.css`:

```css
@import "tailwindcss";
@import "@fontsource/inter/400.css";
@import "@fontsource/inter/500.css";
@import "@fontsource/inter/600.css";
@import "@fontsource/inter/700.css";

@theme {
  /* Brand colors (OKLCH — wide-gamut support) */
  --color-brand:          oklch(55% 0.20 250);
  --color-brand-dark:     oklch(45% 0.20 250);
  --color-surface:        oklch(99% 0.00   0);
  --color-surface-dark:   oklch(15% 0.00   0);
  --color-muted:          oklch(60% 0.00   0);

  /* Typography */
  --font-body:  'Inter', ui-sans-serif, system-ui, sans-serif;
  --font-mono:  'JetBrains Mono', ui-monospace, monospace;

  /* Spacing */
  --spacing-section: 4rem;
  --spacing-card:    1.5rem;
}

/* Utility layers */
@layer components {
  .card-admin  { @apply rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-[var(--spacing-card)] shadow-sm; }
  .form-input-admin { @apply w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--color-brand)]; }
  .prose-blog  { @apply prose prose-lg max-w-none dark:prose-invert leading-relaxed; }
}
```

---

### 27.8 Form UX Standards

**Submit loading state (Alpine.js):**

```html
<form x-data="{ loading: false }" @submit="loading = true">
    <!-- form fields -->
    <button type="submit" :disabled="loading" class="btn-primary">
        <span x-show="!loading">Save</span>
        <span x-show="loading" class="flex items-center gap-2">
            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><!-- spinner --></svg>
            Saving…
        </span>
    </button>
</form>
```

**Rules:**
- The submit button is `disabled` after the first click → prevents double submission.
- When the server returns a validation error, the button reverts to `loading = false` (non-Livewire Blade: the page reloads and Alpine state resets).
- Every successful CRUD → `session()->flash('success', '...')` → displayed by the `x-flash` component.
- `:invalid` CSS pseudo-class: customizes the browser's native validation visuals.

---

### 27.9 Loading States

| Context | Implementation |
|---|---|
| Table pagination | Visible overlay spinner on page change (Alpine `x-show` + htmx or standard link) |
| Dashboard widgets | 3 separate skeleton cards; swap via `x-show` once data loads |
| VKN lookup | Spinning icon beside the input — for the duration of the 400 ms debounce window |
| Tiptap editor | A `<textarea>` placeholder is shown before JS loads; check `editor.isEditable`, not `editor.isDestroyed` |

Dashboard skeleton example:

```html
<div x-show="loading" class="animate-pulse bg-gray-200 dark:bg-gray-700 rounded-xl h-24 w-full"></div>
<div x-show="!loading" x-cloak><!-- real content --></div>
```

---

### 27.10 Custom Error Pages

All error views extend the public layout and include a `noindex` meta tag.

```
resources/views/errors/404.blade.php  — "Page not found" + Return to Home button
resources/views/errors/500.blade.php  — "Server error" + support contact
resources/views/errors/503.blade.php  — "Maintenance mode" + estimated downtime (optional)
```

```php
// Test in routes/web.php or AppServiceProvider:
Route::get('/test-500', fn() => abort(500))->middleware('auth');
```

---

### 27.11 Font Loading Strategy

**Primary method (recommended): `@fontsource/inter` — zero external requests**

```bash
npm install @fontsource/inter
```

```css
/* resources/css/app.css */
@import "@fontsource/inter/400.css";
@import "@fontsource/inter/500.css";
@import "@fontsource/inter/600.css";
@import "@fontsource/inter/700.css";
```

**Alternative: Google Fonts CDN (introduces network latency)**

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
```

**Mandatory rules:**
- `font-display: swap` must be applied at every font source (`@fontsource` applies it by default).
- System fallback stack: `'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif`.
- Admin panel: same font stack; no separate font package.

---

### 27.12 Audit Trails — Mandatory for Financials

**Why:** To be able to prove the change history in the event of KVKK compliance issues or accounting disputes.

**`activity_logs` table:**

```php
// Migration: create_activity_logs_table
Schema::create('activity_logs', function (Blueprint $table) {
    $table->id();
    $table->string('model_type');           // 'App\Models\Expense'
    $table->unsignedBigInteger('model_id');
    $table->enum('action', ['created', 'updated', 'deleted']);
    $table->unsignedBigInteger('user_id')->nullable();  // null = system/cron
    $table->json('changes')->nullable();    // ['field' => ['old' => ..., 'new' => ...]]
    $table->timestamps();
    $table->index(['model_type', 'model_id']);
});
```

**Model Observers:**

```php
// app/Observers/ExpenseObserver.php
class ExpenseObserver
{
    public function created(Expense $expense): void  { $this->log($expense, 'created'); }
    public function updated(Expense $expense): void  { $this->log($expense, 'updated', $expense->getChanges()); }
    public function deleted(Expense $expense): void  { $this->log($expense, 'deleted'); }

    private function log(Model $model, string $action, array $changes = []): void
    {
        ActivityLog::create([
            'model_type' => get_class($model),
            'model_id'   => $model->getKey(),
            'action'     => $action,
            'user_id'    => auth()->id(),
            'changes'    => $changes ?: null,
        ]);
    }
}
```

The same observer is duplicated as `IncomeObserver`. Both are registered in `AppServiceProvider::boot()`.

**Rule:** Expense and Income records are never hard-deleted — soft-delete only. The observer records the delete action.

---

### 27.13 Summary of Module Directives

Reflection of the standards in this section onto the sprint plan:

| Standard | Sprint Phase |
|---|---|
| Soft deletes (Post, Comment, Person) | Phase 1 — Added to migrations |
| SecurityHeadersMiddleware | Phase 0 — Project skeleton |
| Queue + Jobs | Phase 0 (setup) + Phase 11 (Mailchimp job) |
| ImageService + WebP resize | Phase 6 (Blog) + Phase 8 (People) |
| activity_logs + Observer | Phase 4 (Expense) + Phase 5 (Income) |
| CSS @theme tokens | Phase 0 — CSS foundation |
| Error pages | Phase 0 — Project skeleton |
| Accessibility (alt, aria, focus-visible) | Phase 15 — Frontend polish |
| Core Web Vitals measurement | Phase 16 — QA |
| Font strategy (@fontsource) | Phase 0 — CSS foundation |

### PROMPT
```
You are an experienced Laravel full-stack developer. I am building a CRM + Blog project from scratch using Laravel 12; generate the code by applying all the decisions and rules below.

== STACK ==
- PHP 8.5+
- Laravel 12 (Laravel Breeze / Blade, single admin role)
- Tailwind CSS 4 + Vite
- Alpine.js (dark mode, form loading states, VKN lookup)
- Vis.js Network (family tree: hierarchical; node graph: force-directed)
- Tiptap (starter-kit + image extension) — rich text editor
- Chart.js — Reports page
- Intervention Image v3 — WebP resize pipeline
- reCAPTCHA v3 — comment anti-spam
- Mailchimp API v3 — subscriber integration
- Open-Meteo API — weather widget (Kocaeli lat:40.7654, lon:29.9404)
- CoinGecko + ExchangeRate-API — crypto/FX widget
- kyslik/column-sortable — table sorting
- intervention/zodiac — zodiac calculation (People module)
- @fontsource/inter (npm) — local font, no external CDN requests

== AUTH ==
Single admin account. Laravel Breeze (Blade). No registration. EnsureAdmin middleware on every /admin/* route.

== LANGUAGE ==
Default: Turkish (tr). lang/tr.json and lang/en.json. SetLocale middleware. English in Phase 2.

== OUT OF SCOPE ==
NFT module, Photography module, QR module.

== MODULES ==
1. Dictionary:
   genders, blood_types, hair_colors, eye_colors, currencies,
   expense_types, income_types, income_sources, interaction_types, post_categories,
   post_languages — single DictionaryController with $table parameter

2. Stakeholder:
   vkn_tckn unique, company/individual, is_active.
   VKN quick search: GET /api/stakeholders/lookup?q={vkn}
   Quick create: POST /api/stakeholders/quick (from modal)

3. Expense:
   stakeholder_id FK (no seller column), company_expense bool, paid_by_others bool,
   expense_type_id, currency_id, amount, tax, total (computed = amount+tax),
   date, description, is_paid.
   Filters: year/month, type, payment status, currency, paid_by_others.
   Duplicate button for cloning records.

4. Income:
   income_type_id, income_source_id, currency_id, amount, date, description.
   DictionarySeeder: all source/type values

5. Blog (Post):
   title, slug (auto, Turkish normalized), body (Tiptap), excerpt,
   cover_image (WebP, max 1200×630, ImageService), word_count (auto),
   reading_time (auto, 200 wpm), post_category_id, post_language_id,
   is_published, published_at,
   meta_title, meta_description, meta_keywords, og_image,
   canonical_url, view_count, unique_view_count.

   x-seo-meta Blade component + JSON-LD Article schema.
   SitemapService → public/sitemap.xml.
   Admin CRUD + Public BlogController + sitemap.

6. Comment:
   post_id, parent_id (nested), guest_name, guest_email nullable, body,
   status (pending/approved/rejected), ip_hash, user_agent_hash,
   recaptcha_score, honeypot_field.

   Anti-spam flow:
   honeypot → rate-limit throttle:1,2 → reCAPTCHA v3.

7. People (Person):
   first_name, last_name, birth_date, death_date, birth_place,
   gender_id, blood_type_id, hair_color_id, eye_color_id,
   picture (WebP, max 400×400), bio,
   father_id, mother_id, partner_id (self-FK),
   zodiac() auto,
   children() gender-based,
   allChildren() gender-independent.

   Vis.js hierarchical layout:
     level 2: grandparents
     level 1: parents
     level 0: center + siblings + partner
     level -1: children

   Color mapping:
     male=blue, female=pink, other=gray, partner=rose/red, sibling=indigo.

8. Interaction:
   person_id, interaction_type_id, date, summary, notes.

9. Node + NodeConnection:
   Node(name, description, color, icon),
   NodeConnection(from_node_id, to_node_id, type, label).

   Vis.js force-directed graph + directed arrows (arrows.to).

10. Adage:
    owner, adage, keywords, language.

11. Timeline:
    title, description, event_type (milestone/process), event_date,
    tags (JSON), metadata (JSON), is_public.

    Public page shows only is_public=true.

12. Subscriber:
    email unique, status (active/unsubscribed), subscribed_at, mailchimp_id.

    MailchimpService:
      Setting::get('mailchimp_api_key')
      → if missing: DB only + Log::warning.

    Successful sync:
      member.id → update subscribers.mailchimp_id.

    MailchimpSubscribeJob:
      async dispatch (database queue driver).

13. Dashboard (/admin/dashboard):
    - Cache 15 min: Open-Meteo 5-day forecast
    - Cache 15 min: CoinGecko BTC+ETH/USD + ExchangeRate TRY rate
    - Summary cards:
        published posts count,
        pending comments,
        monthly net (income-expense)

14. Reports (/admin/reports?year=YYYY):
    - Annual income/expense/net balance
    - Monthly breakdown — 4 series:
        company expense, personal expense, tax, income (Chart.js)
    - ExpenseType-based table + Chart.js doughnut:
        total, daily avg, monthly avg
    - TRY currency only

15. Link:
    title, url, description, order, is_active.
    /admin/links CRUD.
    Public: /{username}/links → Biolink view.

16. Settings (key-value DB):
    Groups:
      analytics (ga_tracking_id, crux_api_key),
      advertising,
      seo (site_name, default_og_image, default_meta_description),
      mailchimp (mailchimp_api_key is_secret, mailchimp_list_id, mailchimp_datacenter),
      recaptcha (site_key, secret_key is_secret),
      weather (lat, lon, city_name).

    is_secret=true → input type=password.
    Prod override:
      env('KEY') ?? Setting::get('key').

== CSV IMPORT ==
- Dev: database/csv/
- Prod: storage/app/import/

- Artisan:
  php artisan import:csv {table} {--all} {--dry-run}

- Each import runs in a transaction;
  missing FK → skip + report, no duplicates (upsert)

- Web UI:
  /admin/import (select table → chunk import → show report)

== SOFT DELETES ==
Post, Comment, Person:
  SoftDeletes trait + deleted_at column.

Weekly scheduler:
  hard-delete records older than 30 days:
  $schedule->command('model:prune', ['--model'=>[Post::class, Comment::class, Person::class]])->weekly();

Admin trash:
  /admin/posts?trashed=1

== QUEUE / ASYNC ==
QUEUE_CONNECTION=database (dev), redis (prod)

Jobs:
- MailchimpSubscribeJob
- GenerateSitemapJob
- SendCommentNotificationJob

Queue setup:
  php artisan queue:table && php artisan migrate

Worker (dev):
  php artisan queue:work --tries=3

== IMAGE OPTIMIZATION ==
app/Services/ImageService.php:

- Post cover → max 1200×630 WebP → storage/app/public/covers/
- Person image → max 400×400 WebP → storage/app/public/people/
- Tiptap uploads also go through this service

== SECURITY HEADERS ==
app/Http/Middleware/SecurityHeadersMiddleware.php → add to web group.

Headers:
- X-Frame-Options: SAMEORIGIN
- X-Content-Type-Options: nosniff
- Referrer-Policy: strict-origin-when-cross-origin
- Permissions-Policy: camera=(), microphone=(), geolocation=()
- Content-Security-Policy:
  default-src 'self';
  script-src 'self' nonce https://www.google.com https://www.gstatic.com;

HTTPS:
- Strict-Transport-Security: max-age=31536000; includeSubDomains

== AUDIT LOGS (FINANCE) ==
activity_logs table:
  id, model_type, model_id, action(created/updated/deleted),
  user_id nullable, changes JSON nullable, timestamps.

ExpenseObserver + IncomeObserver:
  registered in AppServiceProvider::boot()

Expense & Income:
  NEVER hard-delete — soft-delete only.

== CSS / FONTS ==
npm install @fontsource/inter

resources/css/app.css:

  @import "tailwindcss";
  @import "@fontsource/inter/400.css"; ... /700.css

  @theme {
    --color-brand: oklch(55% 0.20 250);
    --color-brand-dark: oklch(45% 0.20 250);
    --color-surface: oklch(99% 0.00 0);
    --color-surface-dark: oklch(15% 0.00 0);
    --font-body: 'Inter', ui-sans-serif, system-ui, sans-serif;
    --font-mono: 'JetBrains Mono', ui-monospace, monospace;
    --spacing-section: 4rem;
    --spacing-card: 1.5rem;
  }

  @layer components {
    .card-admin { ... }
    .form-input-admin { ... }
    .prose-blog { ... }
  }

== ACCESSIBILITY (WCAG 2.1 AA) ==
- All <img> must have alt attributes (decorative: alt="")
- Form inputs must have matching <label for="">
- Keyboard navigation: :focus-visible (not only :focus)
- Contrast:
    ≥4.5:1 normal text, ≥3:1 large text
- Icon-only buttons: aria-label
- Skip-to-content link at layout start (#main-content)
- prefers-reduced-motion → disable animations

== FORM UX ==
Alpine:
  x-data="{loading:false}"
  submit → loading=true → disable button + spinner

Success:
  session()->flash('success', ...) + x-flash component

Validation:
  :invalid CSS + clear user-friendly messages

== LOADING STATES ==
- Dashboard widgets:
    skeleton loader (animate-pulse)
- VKN lookup:
    input spinner (debounce 400ms)
- Tiptap:
    textarea placeholder until hydration completes

== CORE WEB VITALS ==
- LCP < 2.5s:
    preload blog cover images
- INP < 200ms:
    Alpine only, no blocking JS
- CLS < 0.1:
    all <img> must include width + height

== ERROR PAGES ==
- 404 → "Page not found"
- 500 → "Server error"
- 503 → "Maintenance mode"

== ROUTING ==
routes/web.php → require admin.php + public.php
routes/admin.php → prefix /admin + middleware [auth, admin]
routes/public.php → public routes
routes/api.php → AJAX endpoints

== DIRECTORY STRUCTURE ==
app/Http/Controllers/Admin/
app/Http/Controllers/Public/
app/Http/Middleware/SecurityHeadersMiddleware.php
app/Services/*
app/Observers/*
resources/views/errors/
resources/views/components/seo-meta.blade.php

== IMPLEMENTATION PHASES ==
Phase 0 → skeleton
Phase 1 → migrations + models
...
Phase 16 → testing + go-live

After each phase:
- php artisan migrate:fresh --seed
- php artisan route:list
- manual browser test
- git commit

== STRICT RULES ==
NEVER:
- add NFT/Photography/QR modules
- create seller column
- allow public registration
- hard-delete financial data
- skip WebP conversion
- disable CSP headers

Start by implementing Phase 0 and verify full setup.
```