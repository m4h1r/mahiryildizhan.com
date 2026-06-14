<?php

namespace App\Http\Middleware;

use App\Models\AliceAuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AliceAudit
{
    private const WRITE_METHODS = ['POST', 'PATCH', 'PUT', 'DELETE'];

    private const METHOD_ACTION_MAP = [
        'POST' => 'created',
        'PATCH' => 'updated',
        'PUT' => 'updated',
        'DELETE' => 'deleted',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! in_array($request->method(), self::WRITE_METHODS)) {
            return $response;
        }

        // Only log successful write operations
        if ($response->getStatusCode() >= 400) {
            return $response;
        }

        $action = self::METHOD_ACTION_MAP[$request->method()] ?? $request->method();

        // Extract table name from route (e.g., api/v1/alice/expenses → expenses)
        $segments = explode('/', $request->path());
        $tableName = end($segments);
        // If last segment is an ID, use the one before
        if (is_numeric($tableName)) {
            $tableName = prev($segments);
        }
        $tableName = str_replace('-', '_', $tableName);

        $body = json_decode($response->getContent(), true) ?? [];
        $recordId = $body['data']['id'] ?? null;

        AliceAuditLog::create([
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'old_data' => $request->attributes->get('audit_old_data'),
            'new_data' => $body['data'] ?? null,
            'source' => $request->header('X-Alice-Source', 'alice'),
            'ip' => $request->ip(),
            'idempotency_key' => $request->attributes->get('idempotency_key'),
            'user_agent' => $request->userAgent(),
            'dry_run' => (bool) $request->attributes->get('is_dry_run', false),
        ]);

        return $response;
    }
}
