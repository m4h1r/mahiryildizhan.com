# Alice Bridge — Entegrasyon Kılavuzu

> **Bu doküman Alice'in tek başvuracağı kaynak.** Başka bir yere bakma.

---

## 1. Genel Bilgi

| | |
|---|---|
| **Base URL** | `https://mahiryildizhan.com/api/v1/alice` |
| **Auth** | Bearer Token (Sanctum Personal Access Token) |
| **Timezone** | `Europe/Istanbul` (UTC+3) |
| **Content-Type** | `application/json` |
| **Tarih formatı** | ISO 8601 → `2026-06-15` veya `2026-06-15T10:30:00+03:00` |
| **Para birimi** | `decimal(12,2)` → `1250.50` (TL) |
| **Rate limit** | 120 istek/dakika |

---

## 2. Authentication

Token `php artisan alice:rotate-token` ile üretilir ve `storage/app/alice/.env.alice` dosyasına yazılır.

```bash
# Her istekte bu header zorunlu:
Authorization: Bearer <TOKEN>
Accept: application/json
X-Alice-Source: alice          # opsiyonel ama önerilen — audit log'a yazılır
```

**curl örneği:**
```bash
curl -s \
  -H "Authorization: Bearer $ALICE_PANEL_TOKEN" \
  -H "Accept: application/json" \
  "$ALICE_PANEL_URL/api/v1/alice/meta/currencies"
```

**Sağlık kontrolü:**
```bash
curl -H "Authorization: Bearer $TOKEN" "$BASE_URL/health"
# → {"status":"ok","version":"1.0.0","timestamp":"2026-06-15T10:00:00+03:00"}
```

---

## 3. Hata Formatı

Tüm hatalar bu formatta döner:

```json
{
  "error": {
    "code": "validation_failed",
    "message": "İnsan-okunur hata mesajı",
    "fields": {
      "amount": ["Tutar gereklidir"]
    }
  }
}
```

**HTTP Hata Kodları:**

| Kod | `error.code` | Alice ne yapmalı |
|---|---|---|
| 400 | `bad_request` | İsteği düzelt |
| 401 | `unauthenticated` | Token kontrol et, yenile |
| 403 | `ip_not_allowed` | Hata bildir |
| 404 | `not_found` | Farklı ID dene veya liste çek |
| 409 | `conflict` | Kayıt zaten var (duplicate) |
| 422 | `validation_failed` | `fields` içindeki hatayı düzelt |
| 429 | `too_many_requests` | 60 sn bekle, tekrar dene |
| 500 | `server_error` | Hata bildir |

---

## 4. Pagination, Filtreleme, Sıralama

Tüm liste endpoint'leri destekler:

```
?per_page=50          # max 200, default 50
&page=2               # sayfa numarası
&q=migros             # metin araması (LIKE)
&sort=-created_at     # - prefix = DESC, virgülle ayır: sort=-date,total
&from=2026-06-01      # tarih başlangıcı (date/datetime kolonlarında)
&to=2026-06-30        # tarih bitişi
&with_trashed=1       # silinmiş kayıtları da göster
```

**Liste response yapısı:**
```json
{
  "data": [...],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 50,
    "total": 142
  },
  "links": {
    "next": "https://.../expenses?page=2",
    "prev": null
  }
}
```

---

## 5. Meta Endpoint'leri (Enum Listeleri)

Alice ID almak için bu endpoint'leri kullanır:

```bash
GET /meta/currencies       # → [{id:1, code:"TRY", name:"Türk Lirası", symbol:"₺"}]
GET /meta/expense-types    # → [{id:1, name:"Gıda"}]
GET /meta/income-sources   # → [{id:1, name:"Freelance"}]
GET /meta/income-types     # → [{id:1, name:"Proje Geliri"}]
GET /meta/interaction-types
GET /meta/genders
GET /meta/eye-colors
GET /meta/blood-types
GET /meta/hair-colors
GET /meta/post-categories
GET /meta/post-languages
```

**Kullanım:** Alice önce `GET /meta/currencies` çeker, `TRY` kodunu bulur, `id`sini alır ve yazma endpoint'inde `currency_id` olarak kullanır.

---

## 6. Idempotency

Aynı isteğin iki kez gönderilmesini önlemek için:

```bash
# Header ekle:
Idempotency-Key: sha256(json_payload + session_id)

# Python örneği:
import hashlib, json
key = hashlib.sha256((json.dumps(payload) + session_id).encode()).hexdigest()
```

- Aynı key ile ikinci istek → **orijinal response döner**, yeni kayıt oluşmaz
- Header `X-Idempotent-Replayed: true` gelirse replay'dir
- Key 24 saat geçerlidir
- Sadece POST/PATCH/DELETE isteklerinde çalışır

---

## 7. Dry Run

Gerçek yazma yapmadan simülasyon:

```bash
POST /api/v1/alice/expenses?dry_run=1
# → 201 döner ama DB'ye yazılmaz
# → response'da: "dry_run": true, "dry_run_note": "..."
```

---

## 8. Lookup by Name (Otomatik Çözümleme)

`StakeholderController` ve `ExpenseController`'da "isimden ID çözümleme" desteği var:

```json
// stakeholder_id yerine stakeholder string gönder:
{
  "date": "2026-06-15",
  "price": 250.50,
  "currency_id": 1,
  "stakeholder": "Migros"    // "Migros" → stakeholders tablosunda arar
}
```

- Bulunursa → `stakeholder_id` otomatik atanır
- Bulunamazsa → `auto_created: true` ile yeni stakeholder oluşturulur ve response'da belirtilir

---

## 9. Resource Endpoint'leri

### 💰 Giderler (Expenses)

```
GET    /expenses                    # liste
GET    /expenses/{id}               # tekil
POST   /expenses                    # oluştur
PATCH  /expenses/{id}               # güncelle
DELETE /expenses/{id}               # soft-delete
```

**Filtreler:** `from`, `to`, `currency_id`, `expense_type_id`, `stakeholder_id`, `company_expense=1`

**Store payload:**
```json
{
  "date": "2026-06-15",
  "description": "Migros market alışverişi",
  "price": 250.50,
  "quantity": 1,
  "tax": 0,
  "total": 250.50,
  "currency_id": 1,
  "expense_type_id": 2,
  "stakeholder": "Migros",
  "company_expense": false
}
```

**Response:**
```json
{
  "data": {
    "id": 42,
    "date": "2026-06-15",
    "description": "Migros market alışverişi",
    "price": "250.50",
    "quantity": "1.000",
    "tax": "0.00",
    "total": "250.50",
    "total_display": "250,50 ₺",
    "company_expense": false,
    "paid_by_others": false,
    "expense_type": {"id": 2, "name": "Gıda"},
    "currency": {"id": 1, "code": "TRY", "symbol": "₺"},
    "stakeholder": {"id": 5, "title": "Migros"},
    "created_at": "2026-06-15T10:30:00+03:00"
  }
}
```

---

### 💵 Gelirler (Incomes)

```
GET    /incomes
GET    /incomes/{id}
POST   /incomes
PATCH  /incomes/{id}
DELETE /incomes/{id}
```

**Filtreler:** `from`, `to`, `currency_id`, `income_source_id`, `income_type_id`

**Store payload:**
```json
{
  "date": "2026-06-15",
  "amount": 15000.00,
  "description": "MY Site projesi ödemesi",
  "currency_id": 1,
  "income_source_id": 2,
  "income_type_id": 1
}
```

**Response:** `amount_display: "15.000,00 ₺"` dahil

---

### 👤 Kişiler (People)

```
GET    /people
GET    /people/{id}
POST   /people
PATCH  /people/{id}
DELETE /people/{id}
```

**Filtreler:** `q` (isim/email/telefon), `gender_id`

**Store payload:**
```json
{
  "name": "Yusuf",
  "surname": "Naltekin",
  "mobile": "+90 505 123 45 67",
  "email": "yusuf@example.com",
  "birthday": "1990-03-15",
  "gender_id": 1,
  "notes": "Eski iş arkadaşı"
}
```

**Response:** `full_name`, `picture_url`, zodiac bilgisi dahil

---

### 🏢 Paydaşlar (Stakeholders)

```
GET    /stakeholders
GET    /stakeholders/{id}
POST   /stakeholders
PATCH  /stakeholders/{id}
DELETE /stakeholders/{id}
```

**Filtreler:** `q`, `status` (Active/Passive), `company_type` (Company/Individual)

**Store payload:**
```json
{
  "title": "Migros Ticaret A.Ş.",
  "vkn_tckn": "1234567890",
  "company_type": "Company",
  "city": "İstanbul",
  "country": "TR",
  "phone": "+902121234567",
  "status": "Active"
}
```

---

### 📝 Blog Yazıları (Posts)

```
GET    /posts
GET    /posts/{id}
POST   /posts
PATCH  /posts/{id}
DELETE /posts/{id}
```

**Filtreler:** `q`, `status` (draft/published/archived), `category_id`, `language_id`, `from`/`to` (published_at)

**Store payload:**
```json
{
  "title": "Laravel 13 Neler Getirdi?",
  "body": "<p>İçerik buraya...</p>",
  "excerpt": "Kısa özet",
  "status": "draft",
  "category_id": 1,
  "language_id": 1
}
```

---

### ✅ Yapılacaklar (Todo Items)

```
GET    /todo-items
GET    /todo-items/{id}
POST   /todo-items
PATCH  /todo-items/{id}
DELETE /todo-items/{id}
```

**Filtreler:** `q`, `is_completed=1/0`, `is_bucketlist=1/0`, `from`/`to` (due_date)

**Store payload:**
```json
{
  "title": "Alice entegrasyonunu tamamla",
  "due_date": "2026-06-20",
  "cost_try": 0,
  "is_bucketlist": false
}
```

**Not:** `is_completed: true` gönderildiğinde `completed_at` otomatik set edilir.

---

### 🛒 Alınacaklar (Purchase Items)

```
GET    /purchase-items
GET    /purchase-items/{id}
POST   /purchase-items
PATCH  /purchase-items/{id}
DELETE /purchase-items/{id}
```

**Filtreler:** `q`, `is_completed`, `is_grocery`, `is_bucketlist`

**Store payload:**
```json
{
  "title": "MacBook Pro M4",
  "cost_try": 75000,
  "is_bucketlist": true,
  "is_grocery": false
}
```

---

### 🤝 Etkileşimler (Interactions)

```
GET    /interactions
GET    /interactions/{id}
POST   /interactions
PATCH  /interactions/{id}
DELETE /interactions/{id}
```

**Filtreler:** `person_id`, `interaction_type_id`, `from`/`to`

**Store payload:**
```json
{
  "person_id": 5,
  "interaction_type_id": 1,
  "date": "2026-06-15",
  "effect": "olumlu",
  "notes": "Kahve içtik, proje konuştuk"
}
```

---

### 📅 Zaman Tüneli (Timeline Events)

```
GET    /timeline-events
GET    /timeline-events/{id}
POST   /timeline-events
PATCH  /timeline-events/{id}
DELETE /timeline-events/{id}
```

**Filtreler:** `q`, `event_type` (milestone/process), `is_public`, `category`

**Store payload:**
```json
{
  "title": "Şirketi kurdum",
  "description": "MY Teknoloji'yi resmi olarak kurdum",
  "event_type": "milestone",
  "start_date": "2024-01-15",
  "is_public": true,
  "color": "#3B82F6"
}
```

---

### 🏆 Kilometre Taşları (Milestones)

```
GET    /milestones
POST   /milestones
PATCH  /milestones/{id}
DELETE /milestones/{id}
```

**Store payload:**
```json
{
  "title": "İlk müşteriye projeyi teslim ettim",
  "achieved_at": "2026-06-15T18:00:00",
  "milestoneable_type": "App\\Models\\TodoItem",
  "milestoneable_id": 42
}
```

---

### 💬 Özdeyişler (Adages)

```
GET    /adages
POST   /adages
PATCH  /adages/{id}
DELETE /adages/{id}
```

**Store payload:**
```json
{
  "owner": "Konfüçyüs",
  "adage": "Bilgi güçtür",
  "keywords": "bilgi, güç, eğitim",
  "language_id": 2
}
```

---

### 📧 Aboneler (Subscribers)

```
GET    /subscribers
GET    /subscribers/{id}
POST   /subscribers
PATCH  /subscribers/{id}
DELETE /subscribers/{id}
```

**Filtreler:** `q` (email), `status` (pending/active/unsubscribed)

---

### 🕸️ Düğümler (Nodes — Bilgi Grafiği)

```
GET    /nodes
POST   /nodes
PATCH  /nodes/{id}
DELETE /nodes/{id}
```

**Store payload:**
```json
{
  "name": "Laravel",
  "text_color": "#FF2D20",
  "connect_to": [3, 7]
}
```

---

### ⚙️ Ayarlar (Settings)

```
GET    /settings                   # tüm ayarlar (gizli olanlar hariç)
GET    /settings?group=mail        # grup filtresi
GET    /settings?include_secret=1  # gizli dahil
PATCH  /settings/{key}             # {"value": "yeni değer"}
```

---

## 10. Örnek Senaryolar

### Senaryo 1: "500 lira Migros market alışverişi ekle"

```bash
# Adım 1: Currency ID bul
curl "$BASE_URL/meta/currencies" → id=1 (TRY)

# Adım 2: Gider oluştur (Migros name lookup ile)
curl -X POST "$BASE_URL/expenses" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Idempotency-Key: $(echo -n 'migros-500-20260615' | sha256sum | awk '{print $1}')" \
  -d '{
    "date": "2026-06-15",
    "description": "Market alışverişi",
    "price": 500,
    "quantity": 1,
    "tax": 0,
    "total": 500,
    "currency_id": 1,
    "stakeholder": "Migros"
  }'
# → 201, data.total_display: "500,00 ₺"
```

---

### Senaryo 2: "Bu ay market harcamam ne kadar?"

```bash
curl "$BASE_URL/expenses?from=2026-06-01&to=2026-06-30&per_page=200" \
  -H "Authorization: Bearer $TOKEN"
# → meta.total ile kayıt sayısı, data[] içinde sum hesapla
```

---

### Senaryo 3: "Yusuf Naltekin'i kişilere ekle, tel +90 505..."

```bash
curl -X POST "$BASE_URL/people" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Yusuf",
    "surname": "Naltekin",
    "mobile": "+905051234567"
  }'
# → 201, data.full_name: "Yusuf Naltekin"
```

---

### Senaryo 4: "MY Site projesinin durumunu 'tamamlandı' yap"

```bash
# TodoItem ara
curl "$BASE_URL/todo-items?q=MY+Site" -H "Authorization: Bearer $TOKEN"
# → id bul (örn. 15)

# Güncelle
curl -X PATCH "$BASE_URL/todo-items/15" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"is_completed": true}'
# → completed_at otomatik set edilir
```

---

### Senaryo 5: "Son 5 harcamamı göster"

```bash
curl "$BASE_URL/expenses?sort=-date&per_page=5&page=1" \
  -H "Authorization: Bearer $TOKEN"
# → data[] içinde en yeni 5 gider
```

---

## 11. Alınan Mimari Kararlar

| # | Karar | Gerekçe |
|---|---|---|
| 1 | Auth: Sanctum | `personal_access_tokens` tablosu zaten vardı; Passport gereğinden ağır |
| 2 | Idempotency: DB tablosu | Redis aktif değil; DB tablosu daha güvenilir |
| 3 | Para: `decimal(12,2)` | Mevcut tablolar decimal kullanıyor; integer'a migrate etmek riskli |
| 4 | Stakeholder lookup | `"Migros"` gibi string → auto-create with `auto_created:true` flag |
| 5 | Soft delete | Tüm ana modeller `SoftDeletes` trait'i kullanıyor; DELETE kalıcı silme değil |
| 6 | `amount_display` computed | Hem decimal korunur hem Alice okunabilir format alır |
| 7 | Prune weekly | Audit log 365 günlük retention; haftalık temizlik yeterli |

---

## 12. CHANGELOG

### v1.0.0 — 2026-06-15
- İlk sürüm
- 13 resource (expenses, incomes, people, stakeholders, posts, todo-items, purchase-items, interactions, timeline-events, milestones, adages, subscribers, nodes)
- 11 meta endpoint
- Sanctum auth, IP whitelist, audit log, idempotency, dry_run
- `php artisan alice:rotate-token` komutu
