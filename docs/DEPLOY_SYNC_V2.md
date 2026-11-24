# Deploy Runbook — Sync v2 (feat/alice-sync-v2 → production)

> Hedef: `mahiryildizhan.com` production'ına **veri kaybı ve kesinti olmadan** Sync v2'yi almak.
> Branch: `feat/alice-sync-v2` (GitHub'a pushlandı). Bu dal 7 additive migration ekler
> (yeni tablolar + yeni kolonlar), hepsi reversible. Hiçbir veri silinmez.

---

## Bu deploy'da tek gerçek risk: bozuk Pulse migration'ı

Panel'in **Deploy** butonu (DEPLOY.md Step 3) otomatik olarak `php artisan migrate --force`
çalıştırır. Kuyruğun **başında** `2026_10_15_032107_create_pulse_tables.php` var ve
**Pending** durumda. Bu migration `unhex(md5(key))` sanal kolonu oluşturur; bazı DB
motorlarında şu hatayı verir:

```
SQLSTATE[HY000] 3763: Expression of generated column 'key_hash'
contains a disallowed function: md5
```

Patlarsa `migrate --force` **orada durur** → sync tabloları kurulmaz **ve** pipeline'ın
geri kalanı (config:cache, route:cache, nginx reload, worker restart) çalışmaz → deploy
yarıda kalır.

**Ama:** bu hata motor/versiyona bağlıdır. Prod MySQL yereldekinden farklı olabilir ve
Pulse orada sorunsuz çalışmış/çalışacak olabilir. **Bu yüzden ilk iş prod'un gerçek
durumunu görmek — varsayma.**

---

## Adım 0 — Önce DB yedeği al (her senaryoda)

SSH ile prod sunucusuna gir, sonra:

```bash
cd /var/www/mahiryildizhan.com/current
php8.4 artisan backup:run --only-db        # spatie/laravel-backup kuruluysa
# yoksa:
# mysqldump -u <user> -p <database> > ~/pre-sync-v2-$(date +%F).sql
```

Geri dönüş güvencesi budur. Devam etmeden yedeğin oluştuğunu doğrula.

---

## Adım 1 — Prod'un migration durumunu gör (KARAR NOKTASI)

Henüz hiçbir şey deploy etmeden, prod sunucusunda:

```bash
cd /var/www/mahiryildizhan.com/current
php8.4 artisan migrate:status | grep -i pulse
```

- **`Ran` görünüyorsa** → Pulse zaten uygulanmış, prod DB'si `md5`'i kabul ediyor.
  **Hiçbir özel işlem yok.** Doğrudan **Adım 3 (normal deploy)**'a geç.

- **`Pending` görünüyorsa** → `migrate --force` buna takılacak. Önce **Adım 2** ile çöz.

> Pulse satırı hiç çıkmıyorsa migration tablosu adlandırması farklıdır;
> `php8.4 artisan migrate:status` çıktısının tamamına bak.

---

## Adım 2 — (Yalnız Pulse "Pending" ise) blokajı kaldır

Üç seçenek, tercih sırasıyla:

**2a — Pulse gerçekten kullanılıyorsa: migration'ı prod motoruna göre düzelt.**
Hata MariaDB vs MySQL motor tespitinden gelir. Önce motoru doğrula:
```bash
php8.4 artisan tinker --execute="echo DB::selectOne('select version() v')->v;"
```
MariaDB çıkarsa migration'daki `virtualAs('unhex(md5(`key`))')` ifadesi o motorda geçersiz;
Pulse paketini güncelle (`composer update laravel/pulse`) veya `key_hash` kolonunu
`storedAs` yerine uygulama tarafı hash'e çevir. Bu bir **kod değişikliği** → ayrı küçük
bir commit + test gerektirir; istersen bunu ben yaparım.

**2b — Pulse kullanılmıyorsa (dashboard'a ihtiyaç yoksa): migration'ı devre dışı bırak.**
Dosyayı daldan çıkar (sil veya `database/migrations/`'dan taşı) + `config/pulse.php` /
`PulseServiceProvider` referanslarını kaldır → ayrı commit. Böylece kuyrukta kalmaz.

**2c — Geçici köprü (kod değişmeden, sadece bu deploy için): elle kontrollü migrate.**
Panel Deploy'u kullanmak yerine SSH'ta sync migration'larını tek tek `--path` ile uygula,
Pulse'a dokunma:
```bash
cd /var/www/mahiryildizhan.com/current
for m in \
  2026_11_22_000001_create_sync_changes_table \
  2026_11_22_000002_add_version_to_sync_models \
  2026_11_22_000003_add_external_ref_to_person_assessments \
  2026_11_22_000004_create_projects_table \
  2026_11_22_000005_create_agenda_events_table \
  2026_11_22_000006_create_wealth_entries_table \
  2026_11_22_000007_create_reading_logs_table ; do
    php8.4 artisan migrate --force --path=database/migrations/$m.php
done
php8.4 artisan migrate:status | grep 2026_11_22    # 7'si de Ran olmalı
```
> Not: 2c yalnız DB'yi kurar. Panel Deploy'u yine `migrate --force` deneyip Pulse'ta
> patlayabilir; o yüzden 2c kullanıyorsan kod güncellemesini de **panelsiz** yap
> (`git pull` + `composer install --no-dev` + `config:cache route:cache view:cache` +
> `nginx reload` + worker restart) ya da kalıcı çözüm için 2a/2b'yi seç.

**Öneri:** Pulse'u kullanmıyorsan 2b en temizi; kullanıyorsan 2a. 2c sadece acil köprü.

---

## Adım 3 — main'e al ve deploy et

Kod değişikliği (2a/2b) yaptıysan onları da bu dala commit'leyip pushla, sonra:

```bash
# local
git checkout main
git pull origin main
git merge --no-ff feat/alice-sync-v2
git push origin main
```

Panel → **https://panel.mahiryildizhan.com** → `mahiryildizhan.com` → **Deploy**.
Log'u izle: `git clone` → `composer install` → `npm ci && npm run build` →
`php artisan migrate --force` (artık temiz geçmeli) → `config:cache` → `route:cache` →
`view:cache` → nginx reload → worker restart. `DEPLOY BAŞARILI` görününce tamam.

---

## Adım 4 — Deploy sonrası doğrulama

```bash
# 1) Sözleşme versiyonu 1.4.0 mı
curl -H "Authorization: Bearer $TOKEN" https://mahiryildizhan.com/api/v1/alice/sync/head
# beklenen: {"api_version":"1.4.0","cursor":N,"server_time":"..."}

# 2) Sağlık
curl https://mahiryildizhan.com/api/v1/alice/health      # version: 1.4.0

# 3) Smoke
bash scripts/smoke-test.sh                               # tümü yeşil
```

Panelde: Logs → 500 yok · Deploys → son ✅ Success · site açılıyor.

---

## Adım 5 — Cursor değerini Pi'ye bildir

Adım 4'teki `sync/head` yanıtındaki **`cursor` (N)** değerini Alice Panel (Pi) tarafına ver.
Pi bu cursor'dan itibaren `GET /sync/snapshot` ile ilk dolumu yapar, sonra `GET /sync/changes?after=N`
ile artımlı senkrona geçer. Bu değer olmadan Pi nereden başlayacağını bilemez.

---

## Rollback (bir şey ters giderse)

- **Kod:** Panel → Deploys → önceki başarılı sürüm → **Rollback**.
- **DB:** sync migration'ları reversible — `php8.4 artisan migrate:rollback --step=7 --force`
  (yalnız bu dalın eklediği 7'sini geri alır) ya da Adım 0'daki yedeği geri yükle.
- Yeni tablolar additive olduğundan eski kod yeni tabloları görmezden gelir; acil durumda
  sadece kod rollback'i bile siteyi eski haline döndürür.
