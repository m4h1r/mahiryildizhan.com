# Dashboard Envanteri — Sync v2 / S1

> Amaç: admin menüsünü Alice Panel ile bire bir **6 veri taşıyan başlığa** indirmek.
> Hedef başlıklar: **Kişiler · Görevler · Ajanda · Projeler · Finans · Okuma**
>
> **Bu belge bir karar belgesidir — hiçbir REMOVE/taşıma uygulanmadı.** Onay sonrası
> uygulanacak. Veri silen migration yok; "REMOVE" = menüden çıkar, rota canlı kalır.

Durum: 136 admin rotası, 7 menü grubu, Filament yok (Blade admin). S5 varlıklarından
**Projeler, Ajanda, Okuma henüz yok** (model + tablo S5'te açılacak), bu yüzden bu 3 başlık
S1'de yalnız iskelet/placeholder, dolum S5'te.

## Karar anahtarı
- **KEEP** — birincil 6 başlıktan birine ait, menüde kalır.
- **MERGE** — başka bir başlığın altına taşınır (ayrı satır olmaktan çıkar).
- **MOVE→Gelişmiş** — canlı ve gerekli ama 6 başlığa girmiyor; "Gelişmiş" altına gizlenir.
- **REMOVE** — menüden tamamen çıkar (rota + veri durur). Yalnız kullanılmayanlar için öneri.

## Mevcut menü → karar

### Grup: Main
| Sayfa | Rota | Karar | Gerekçe |
|---|---|---|---|
| Dashboard | `dashboard` | **KEEP** | Giriş ekranı; 6 başlık dışıdır ama menü değil ana sayfadır. |
| Posts | `admin.posts.*` | **MOVE→Gelişmiş › Site** | Public blog içeriği — Alice senkronuna girmez; canlı kalır, birincil menüde değil. |
| Media | `admin.media.*` | **MOVE→Gelişmiş › Site** | Blog medya kütüphanesi; public site aracı. |
| Comments | `admin.comments.*` | **MOVE→Gelişmiş › Site** | Blog yorum moderasyonu; public site aracı. |

### Grup: People & Relationships → **Kişiler**
| Sayfa | Rota | Karar | Gerekçe |
|---|---|---|---|
| People | `admin.people.*` | **KEEP → Kişiler** | `people` varlığı = birincil başlık. Assessments/channels/faces zaten alt-rota. |
| Interactions | `admin.interactions.*` | **MERGE → Kişiler** | Kişiye bağlı etkileşimler; Kişiler başlığı altına. |
| Nodes | `admin.nodes.*` | **MOVE→Gelişmiş › Bilgi** | Bilgi grafiği; Alice 6 varlığında yok. Kullanım düşükse REMOVE adayı. |
| Node Connections | `admin.node-connections.*` | **MOVE→Gelişmiş › Bilgi** | Grafik kenarları; Nodes ile birlikte. |

### Grup: Content
| Sayfa | Rota | Karar | Gerekçe |
|---|---|---|---|
| Timeline | `admin.timeline.*` | **MOVE→Gelişmiş › Bilgi** | Tarihsel olay akışı (knowledge). **Alice `agenda_events` DEĞİL** — ikisi karışmamalı. |
| Adages | `admin.adages.*` | **MOVE→Gelişmiş › Bilgi** | Vecize koleksiyonu; 6 başlıkta yok. Kullanım düşükse REMOVE adayı. |

### Grup: Personel → **Görevler**
| Sayfa | Rota | Karar | Gerekçe |
|---|---|---|---|
| Yapılacaklar | `admin.todo-items.*` | **KEEP → Görevler** | `todo-items` varlığı = birincil başlık. |
| Bucket List | `admin.bucketlist` | **MERGE → Görevler** | `todo_items.is_bucketlist` üzerinden filtre; ayrı varlık değil (sözleşme S5 todo-items). |
| Satın Alınacaklar | `admin.purchase-items.*` | **MOVE→Gelişmiş › Kişisel** | Görev-benzeri ama Alice 6'da yok; Görevler'i kirletmemek için Gelişmiş. |
| Besinler | `admin.foods.*` | **MOVE→Gelişmiş › Beslenme** | Beslenme takibi; Alice senkron setinden çıkarıldı. Kullanım düşükse REMOVE adayı. |
| Tüketimler | `admin.consumptions.*` | **MOVE→Gelişmiş › Beslenme** | Besinler ile birlikte. |

### Grup: Finance → **Finans**
| Sayfa | Rota | Karar | Gerekçe |
|---|---|---|---|
| Expenses | `admin.expenses.*` | **KEEP → Finans** | Gider kaydı; Finans başlığı çekirdeği. |
| Incomes | `admin.incomes.*` | **KEEP → Finans** | Gelir kaydı. |
| Stakeholders | `admin.stakeholders.*` | **KEEP → Finans** | Finansal karşı taraflar; Finans altında kalır. |
| Reports | `admin.reports` | **KEEP → Finans** | Finans raporları. |
| *(wealth_entries)* | *(S5'te)* | **YENİ → Finans** | S5'te `wealth_entries` eklenince Finans altına. |

### Grup: Systems → **Gelişmiş › Sistem**
| Sayfa | Rota | Karar | Gerekçe |
|---|---|---|---|
| Subscribers | `admin.subscribers.*` | **MOVE→Gelişmiş › Site** | Bülten aboneleri; public site. |
| Import | `admin.import.*` | **MOVE→Gelişmiş › Sistem** | CSV içe aktarma; operasyonel araç. |
| Activity Log | `admin.activity-log.*` | **MOVE→Gelişmiş › Sistem** | Denetim kaydı; operasyonel. |
| About Page | `admin.about` | **MOVE→Gelişmiş › Site** | Hakkında sayfası düzenleyici; public site içeriği. |
| Settings | `admin.settings` | **MOVE→Gelişmiş › Sistem** | SettingsPage (standart) — kalır, Gelişmiş altında. |
| Dictionaries | `admin.dictionaries.*` | **MOVE→Gelişmiş › Sistem** | Lookup tablo yönetimi; nadir kullanım. |

## Önerilen hedef menü (onay sonrası)

**Birincil (Alice 6):**
1. **Kişiler** — People, Interactions
2. **Görevler** — Yapılacaklar (+ Bucket List filtresi)
3. **Ajanda** — *(S5)*
4. **Projeler** — *(S5)*
5. **Finans** — Expenses, Incomes, Stakeholders, Reports (+ wealth_entries S5)
6. **Okuma** — *(S5)*

**Gelişmiş** (varsayılan kapalı):
- **Site:** Posts, Media, Comments, Subscribers, About
- **Bilgi:** Nodes, Node Connections, Timeline, Adages
- **Kişisel:** Satın Alınacaklar
- **Beslenme:** Besinler, Tüketimler
- **Sistem:** Import, Activity Log, Dictionaries, Settings

## Karar gerektiren noktalar (Mahir onayı)
1. **Gerçek REMOVE var mı?** Öneri: bu fazda **hiçbir rota silinmez**; hepsi KEEP/MERGE/Gelişmiş.
   Rotalar canlı kalırsa 301/404 testine gerek yok, yalnız sidebar testi yeter. Silmek istediğin
   sayfa varsa işaretle (Nodes/Adages/Besinler/Tüketimler/Satın Alınacaklar en güçlü adaylar).
2. **"Gelişmiş" tek grup mu, alt başlıklı mı?** Öneri: tek "Gelişmiş" grubu, içinde Site/Bilgi/Beslenme/Sistem alt etiketleri.
3. **Ajanda/Projeler/Okuma** S5'te geldiği için menüye S5'te eklenecek; S1'de yalnız mevcutların yeniden gruplanması uygulanır.
