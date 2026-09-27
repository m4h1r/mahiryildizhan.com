# Alice TodoItem Endpoint Update

Date: 2026-09-27
Version impact: additive, backward-compatible

## Summary

`todo-items` endpoints now support two optional fields:

- `urgency` (nullable, integer, range 1..5)
- `importance` (nullable, integer, range 1..5)

Both fields are optional. If missing or `null`, treat as `NA` in UI/business interpretation.

## Affected Endpoints

- `GET /api/v1/alice/todo-items`
- `GET /api/v1/alice/todo-items/{id}`
- `POST /api/v1/alice/todo-items`
- `PATCH /api/v1/alice/todo-items/{id}`

## Validation Rules

For `POST` and `PATCH`:

- `urgency`: `nullable|integer|between:1,5`
- `importance`: `nullable|integer|between:1,5`

Invalid values (for example `0`, `6`, `"high"`) return `422 validation_failed`.

## Request Examples

Create with both values:

```json
{
  "title": "Finish release checklist",
  "due_date": "2026-10-01",
  "urgency": 4,
  "importance": 5,
  "is_bucketlist": false
}
```

Create with NA values:

```json
{
  "title": "Read notes",
  "urgency": null,
  "importance": null
}
```

Partial update:

```json
{
  "urgency": 2
}
```

## Response Shape (additions)

`TodoItem` responses now include:

- `urgency`: `1..5` or `null`
- `importance`: `1..5` or `null`

Example:

```json
{
  "data": {
    "id": 15,
    "title": "Finish release checklist",
    "urgency": 4,
    "importance": 5,
    "is_completed": false
  }
}
```

## New Filtering and Sorting

`GET /todo-items` now accepts:

- `urgency=1..5`
- `importance=1..5`

Sorting allow-list now includes:

- `urgency`
- `importance`

Example:

```bash
GET /api/v1/alice/todo-items?urgency=5&sort=-importance
```

## Compatibility Notes

- Existing clients continue to work without sending new fields.
- No existing required field changed.
- Semantics remain nullable (`NA`) by design.
