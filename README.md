# Mahir Yildizhan

Personal website and editorial platform for Mahir Yildizhan.

## Stack

- PHP 8.2+
- Laravel 12
- Blade + Tailwind CSS + Alpine.js
- MySQL or PostgreSQL

## Features

- Public blog-first experience
- Timeline and biolink pages
- Comment flow with moderation controls
- Admin panel for content and finance records
- Turkish-first localization with English translations
- Project-wide personal branding with custom logo and mobile icons

## Local Development

1. Install dependencies:

```bash
composer install
npm install
```

2. Prepare environment:

```bash
cp .env.example .env
php artisan key:generate
```

3. Configure database in `.env`, then run:

```bash
php artisan migrate --seed
```

4. Start development servers:

```bash
php artisan serve
npm run dev
```

## Tests

Run the full test suite:

```bash
php artisan test
```

## Build

Create production assets:

```bash
npm run build
```
