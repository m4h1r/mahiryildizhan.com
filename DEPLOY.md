# Deploy Guide: mahiryildizhan.com
Domain: mahiryildizhan.com | GitHub: m4h1r/mahiryildizhan.com | PHP: 8.4+
Generated: 2026-07-10 by /my-standards audit

---

## Pre-Deploy Checklist (run locally first)

- [ ] `git checkout main && git merge develop --no-ff`  *(create `develop` first — see AUDIT G2)*
- [ ] `npm run build` passes with no errors
- [ ] `php artisan test` — zero failures
- [ ] `php artisan route:list` — no unexpected routes exposed
- [ ] No `console.log()`, debug dumps, or test routes in code
- [ ] `git push origin main` — GitHub repo is up to date

---

## Step 1 — First Deploy (New Site Setup)

> Skip to Step 2 if this site already exists in the panel.

1. Open **https://panel.mahiryildizhan.com**
2. Click **+ New Site**
3. Fill in:
   - **Domain:** `mahiryildizhan.com`
   - **GitHub Repo URL:** `https://github.com/m4h1r/mahiryildizhan.com`
   - **GitHub Token:** *(personal access token with repo scope)*
   - **PHP Version:** `8.4`
4. When prompted: create MySQL DB → Yes · Nginx config → Yes · SSL → Yes (DNS first) · Queue worker → Yes

> **DNS first:** add A records in Cloudflare (DNS only — grey cloud):
> `mahiryildizhan.com` → `[VPS IP]` and `www.mahiryildizhan.com` → `[VPS IP]`

---

## Step 2 — Configure .env

Panel → **Manage** on `mahiryildizhan.com` → **.env** tab:

```env
APP_NAME="Mahir Yıldızhan"
APP_ENV=production
APP_KEY=                    ← auto-generated on first deploy
APP_DEBUG=false
APP_URL=https://mahiryildizhan.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=[auto-filled by panel]
DB_USERNAME=[auto-filled by panel]
DB_PASSWORD=[auto-filled by panel]

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_SECURE_COOKIE=true

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=mahiryildizhan@icloud.com
MAIL_PASSWORD=[app password — 16 chars]
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=mahiryildizhan@icloud.com
MAIL_FROM_NAME="${APP_NAME}"

BACKUP_ARCHIVE_PASSWORD=      ← add after installing spatie/laravel-backup (audit B1)
```

> ⚠️ **Save .env before deploying** — deploy reads it immediately.

---

## Step 3 — Deploy

Dashboard → **Deploy** on `mahiryildizhan.com`. Watch the log:
`git clone` → `composer install` → `npm ci && npm run build` → `php artisan migrate --force`
→ `config:cache` → `route:cache` → `view:cache` → Nginx reload → queue worker restart.
✅ Done when log shows `DEPLOY BAŞARILI: mahiryildizhan.com`.

---

## Step 4 — Post-Deploy Verification

```bash
curl -I https://mahiryildizhan.com                              # → 200 or 301
php8.4 /var/www/mahiryildizhan.com/current/artisan about        # app info, no errors
redis-cli ping                                                  # → PONG
sudo supervisorctl status mahiryildizhan-worker:*               # → RUNNING
php8.4 /var/www/mahiryildizhan.com/current/artisan mail:test --live --no-interaction
```

Check in the panel:
- [ ] **Logs** → no PHP errors, no 500s
- [ ] **Deploys** → latest ✅ Success
- [ ] Site loads at `https://mahiryildizhan.com`
- [ ] Admin at `https://mahiryildizhan.com/admin` (audit F8: consider a non-obvious path)
- [ ] Login works → **change password immediately** if seeded default

---

## Step 5 — Cron Jobs

Panel → **Cron Jobs**:

| Command | Schedule |
|---------|----------|
| `php8.4 /var/www/mahiryildizhan.com/current/artisan schedule:run` | `* * * * *` |
| `php8.4 /var/www/mahiryildizhan.com/current/artisan sitemap:generate` | `0 3 * * *` |

---

## Step 6 — PageSpeed Check

After go-live, in a Claude Code session opened in this project:

```
/my-standards pagespeed https://mahiryildizhan.com
```

Target: **all scores ≥ 90**. Manual: https://pagespeed.web.dev/analysis?url=https://mahiryildizhan.com

---

## Future Deploys

Push to `main` → open `https://panel.mahiryildizhan.com` → **Deploy** on `mahiryildizhan.com` → monitor log.
Or SSH as deployer: `deploy.sh mahiryildizhan.com`

## Rollback

Panel → **Deploys** → previous successful release → **Rollback**
(or SSH: `ln -sfn /var/www/mahiryildizhan.com/releases/[timestamp] /var/www/mahiryildizhan.com/current && sudo systemctl reload nginx`)
