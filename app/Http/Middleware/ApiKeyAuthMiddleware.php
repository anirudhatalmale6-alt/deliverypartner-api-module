<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Backend\Api\ApiClient;
use App\Models\Backend\Api\ApiLog;

class ApiKeyAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->bearerToken();

        if (!$apiKey) {
            $this->logRequest($request, null, [
                'success' => false,
                'message' => 'Invalid or missing API key',
            ], 'auth_failed', 401);

            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing API key',
            ], 401);
        }

        $client = ApiClient::where('api_key', $apiKey)->first();

        if (!$client) {
            $this->logRequest($request, null, [
                'success' => false,
                'message' => 'Invalid or missing API key',
            ], 'auth_failed', 401);

            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing API key',
            ], 401);
        }

        if (!$client->status) {
            $this->logRequest($request, $client->id, [
                'success' => false,
                'message' => 'API access has been deactivated',
            ], 'auth_failed', 403);

            return response()->json([
                'success' => false,
                'message' => 'API access has been deactivated',
            ], 403);
        }

        $request->merge(['api_client' => $client]);

        return $next($request);
    }

    private function logRequest(Request $request, $clientId, array $response, string $status, int $httpCode)
    {
        ApiLog::create([
            'api_client_id' => $clientId,
            'endpoint' => $request->path(),
            'request_payload' => $request->except(['api_client']),
            'response_payload' => $response,
            'ip_address' => $request->ip(),
            'status' => $status,
            'http_status_code' => $httpCode,
        ]);
    }
}
