<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AliceDryRun
{
    private const WRITE_METHODS = ['POST', 'PATCH', 'PUT', 'DELETE'];

    public function handle(Request $request, Closure $next): Response
    {
        $isDryRun = filter_var($request->query('dry_run', false), FILTER_VALIDATE_BOOLEAN);

        if (! $isDryRun || ! in_array($request->method(), self::WRITE_METHODS)) {
            return $next($request);
        }

        $request->attributes->set('is_dry_run', true);

        // Wrap in transaction and roll back to prevent any writes
        $response = null;
        DB::transaction(function () use ($request, $next, &$response) {
            $response = $next($request);
            DB::rollBack();
        });

        // Wrap response to indicate dry run
        $body = json_decode($response->getContent(), true) ?? [];
        $body['dry_run'] = true;
        $body['dry_run_note'] = 'Bu işlem simülasyon modunda çalıştı. Veritabanına hiçbir şey yazılmadı.';

        return response()->json($body, $response->getStatusCode());
    }
}
