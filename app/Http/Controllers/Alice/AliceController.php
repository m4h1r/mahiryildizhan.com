<?php

namespace App\Http\Controllers\Alice;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class AliceController extends Controller
{
    protected function paginate(Builder $query, Request $request, string $resourceClass): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 50), 200);
        $result = $query->paginate($perPage);

        return response()->json([
            'data' => $resourceClass::collection($result)->resolve(),
            'meta' => [
                'current_page' => $result->currentPage(),
                'last_page' => $result->lastPage(),
                'per_page' => $result->perPage(),
                'total' => $result->total(),
            ],
            'links' => [
                'next' => $result->nextPageUrl(),
                'prev' => $result->previousPageUrl(),
            ],
        ]);
    }

    protected function success(mixed $data, int $status = 200): JsonResponse
    {
        if ($data instanceof JsonResource) {
            return response()->json(['data' => $data->resolve()], $status);
        }

        return response()->json(['data' => $data], $status);
    }

    protected function notFound(string $message = 'Kayıt bulunamadı'): JsonResponse
    {
        return response()->json([
            'error' => ['code' => 'not_found', 'message' => $message],
        ], 404);
    }

    protected function conflict(string $message): JsonResponse
    {
        return response()->json([
            'error' => ['code' => 'conflict', 'message' => $message],
        ], 409);
    }

    protected function applySort(Builder $query, Request $request, array $allowed): Builder
    {
        $sort = $request->query('sort', '-created_at');
        $fields = array_filter(explode(',', $sort));

        foreach ($fields as $field) {
            $dir = 'asc';
            if (str_starts_with($field, '-')) {
                $dir = 'desc';
                $field = substr($field, 1);
            }
            if (in_array($field, $allowed)) {
                $query->orderBy($field, $dir);
            }
        }

        return $query;
    }

    protected function applyDateRange(Builder $query, Request $request, string $column = 'date'): Builder
    {
        if ($from = $request->query('from')) {
            $query->where($column, '>=', $from);
        }
        if ($to = $request->query('to')) {
            $query->where($column, '<=', $to);
        }

        return $query;
    }

    protected function storeAuditOldData(Request $request, mixed $model): void
    {
        $request->attributes->set('audit_old_data', $model?->toArray());
    }

    protected function formatMoney(?string $amount): string
    {
        if ($amount === null) {
            return '0,00 ₺';
        }

        return number_format((float) $amount, 2, ',', '.') . ' ₺';
    }
}
