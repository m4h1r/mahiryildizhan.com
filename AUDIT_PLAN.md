# AUDIT_PLAN — mahiryildizhan.com
Standards v2.10.0 · 2026-10-16 · model-facing remediation steps

Only open items appear here. Satisfied rules are omitted. The V5 favicon work was
already applied in the audit run (see AUDIT_REPORT.md → "Applied this run").

---

## 1. [E12] Show effective date + version on each legal page — QUICK, safe
Files: `resources/views/public/legal/{privacy,kvkk,cookies,terms}.blade.php`
- Add a single line near the top of each page, both locales:
  `Son güncelleme: 2026-10-16 · v1.0` / `Last updated: 2026-10-16 · v1.0`.
- Keep it static text; this is not a DB version row.
Satisfies: E12 (effective_date/version visible).

## 2. [E12/S17] Generate the /kvkk retention sentence from config — QUICK, safe
File: `resources/views/public/legal/kvkk.blade.php`, reads `config/retention.php`.
- Replace the hand-typed "90 gün / 180 gün" sentence with values read from
  `config('retention.policies.*.days')` so the page and the enforced policy cannot drift.
- Current values already match; this only removes the future drift risk.
Satisfies: E12 (generated, not hand-typed) + S17 (notice ↔ policy consistency).

## 3. [E12/İYS] Record the newsletter messaging decision — QUICK, decision
File: `project-standards.md` → under a `## Legal (E12)` heading.
- Record one of: (a) newsletter is non-commercial personal list → İYS N/A; or
  (b) commercial → İYS registration required, owner assigned.
- No code change; this is a recorded decision so the audit stops flagging it.
Satisfies: E12 (İYS decision recorded).

---
Decisions deferred from prior audit (unchanged, still owner's call — do NOT auto-apply):
- L16 timezone migration (supervised, backed-up) — reversible command ready.
- T1/T2 URL-based localization migration (live-site 301 map) — session-based model documented.
