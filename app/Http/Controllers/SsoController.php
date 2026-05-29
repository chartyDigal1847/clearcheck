<?php

namespace App\Http\Controllers;

use App\Services\PortalSession;
use App\Services\StudentProvisioner;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SsoController extends Controller
{
    public function __construct(
        private readonly StudentProvisioner $provisioner,
    ) {}

    private function debugLog(string $hypothesisId, string $location, string $message, array $data = []): void
    {
        try {
            $payload = json_encode([
                'sessionId' => '0cc008',
                'runId' => 'run6',
                'hypothesisId' => $hypothesisId,
                'location' => $location,
                'message' => $message,
                'data' => $data,
                'timestamp' => (int) floor(microtime(true) * 1000),
            ], JSON_UNESCAPED_SLASHES);
            if ($payload === false) {
                return;
            }
            file_put_contents('C:/xampp/htdocs/deoris/debug-0cc008.log', $payload . PHP_EOL, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            // Ignore debug log failures.
        }
    }

    /**
     * POST /sso/exchange — validate token with DEORIS portal, hydrate session.
     */
    public function exchange(Request $request): JsonResponse
    {
        $token = $request->input('token');
        // #region agent log
        $this->debugLog('H19', 'ClearCheck\\SsoController::exchange:entry', 'clearcheck exchange called', [
            'hasToken' => !empty($token),
            'hasIdFallback' => $request->filled('id'),
            'sessionId' => $request->session()->getId(),
        ]);
        // #endregion

        if (! $token && $request->filled('id')) {
            $portalUser = [
                'id' => (string) $request->input('id'),
                'name' => (string) $request->input('name', 'User'),
                'email' => (string) $request->input('email', ''),
                'role' => (string) $request->input('role', 'student'),
            ];
            $role = PortalSession::mapPortalRole($portalUser['role']);

            PortalSession::hydrate($request, $portalUser, $role);
            $this->provisioner->provision($role, $portalUser);

            return response()->json([
                'access_token' => null,
                'redirect' => $this->redirectUrlByRole($role),
                'user' => array_merge($portalUser, ['role' => $role]),
            ]);
        }

        if (! $token) {
            if (config('app.env') !== 'local') {
                return response()->json(['error' => 'No token provided'], 400);
            }

            return $this->devExchange($request);
        }

        $response = $this->exchangePortalTokenWithRetry($token);
        // #region agent log
        $this->debugLog('H19', 'ClearCheck\\SsoController::exchange:portalResponse', 'clearcheck portal exchange response', [
            'status' => $response->status(),
            'ok' => $response->ok(),
        ]);
        // #endregion

        if (! $response->ok()) {
            // #region agent log
            $this->debugLog('H19', 'ClearCheck\\SsoController::exchange:invalidToken', 'clearcheck exchange rejected token', [
                'status' => $response->status(),
            ]);
            // #endregion
            Log::warning('[ClearCheck][SSO] Exchange failed', ['status' => $response->status()]);

            $status = $response->status() >= 500 ? 503 : 401;
            $error = $response->status() >= 500 ? 'SSO service unavailable' : 'Invalid SSO token';
            return response()->json(['error' => $error], $status);
        }

        $data = $response->json();
        $portalUser = $data['user'] ?? $data['data']['user'] ?? null;

        if (empty($portalUser['id'])) {
            // #region agent log
            $this->debugLog('H19', 'ClearCheck\\SsoController::exchange:invalidPayload', 'clearcheck payload missing user id', [
                'hasPortalUser' => !empty($portalUser),
            ]);
            // #endregion
            return response()->json(['error' => 'Invalid SSO response'], 401);
        }

        $role = PortalSession::mapPortalRole($portalUser['role'] ?? 'student');

        Log::info('[ClearCheck][SSO] Exchange OK', [
            'portal_id' => $portalUser['id'],
            'role' => $role,
        ]);

        PortalSession::hydrate($request, $portalUser, $role);
        $this->provisioner->provision($role, $portalUser);
        // #region agent log
        $this->debugLog('H19', 'ClearCheck\\SsoController::exchange:sessionHydrated', 'clearcheck session hydrated after exchange', [
            'ssoId' => (string) $portalUser['id'],
            'role' => $role,
            'sessionId' => $request->session()->getId(),
        ]);
        // #endregion

        return response()->json([
            'access_token' => null,
            'redirect' => $this->redirectUrlByRole($role),
            'user' => [
                'id' => $portalUser['id'],
                'name' => $portalUser['name'] ?? 'User',
                'email' => $portalUser['email'] ?? '',
                'role' => $role,
            ],
        ]);
    }

    private function devExchange(Request $request): JsonResponse
    {
        $roleMap = [
            'admin' => 'admin',
            'hr' => 'clearance_checker',
            'student' => 'student',
        ];

        $rawRole = $request->input('_standalone_role', 'admin');
        $role = $roleMap[$rawRole] ?? 'admin';

        $portalUser = [
            'id' => $request->input('_standalone_portal_id', '900001'),
            'name' => $request->input('_standalone_name', 'Dev User'),
            'email' => $request->input('_standalone_email', 'dev@localhost'),
            'role' => $rawRole,
        ];

        Log::info('[ClearCheck][SSO] Standalone dev login', ['role' => $role]);

        PortalSession::hydrate($request, $portalUser, $role);
        $this->provisioner->provision($role, $portalUser);

        return response()->json([
            'access_token' => null,
            'redirect' => $this->redirectUrlByRole($role),
            'user' => array_merge($portalUser, ['role' => $role]),
        ]);
    }

    private function deorisHttp(string $token): PendingRequest
    {
        $client = Http::withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ]);

        if (! config('services.auth.verify_ssl', true)) {
            $client = $client->withoutVerifying();
        }

        return $client;
    }

    private function exchangePortalTokenWithRetry(string $token)
    {
        $url = rtrim(config('services.auth.url', 'https://deoris.test'), '/')
            .config('services.auth.sso_exchange_path', '/api/v1/sso/exchange');

        $lastResponse = null;
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $response = $this->deorisHttp($token)->post($url, ['token' => $token]);
            $lastResponse = $response;

            // #region agent log
            $this->debugLog('H19', 'ClearCheck\\SsoController::exchangePortalTokenWithRetry', 'clearcheck portal exchange attempt', [
                'attempt' => $attempt,
                'status' => $response->status(),
                'ok' => $response->ok(),
            ]);
            // #endregion

            if ($response->ok() || ($response->status() !== 429 && $response->status() < 500)) {
                return $response;
            }
        }

        return $lastResponse;
    }

    private function redirectUrlByRole(string $role): string
    {
        return match ($role) {
            'admin' => route('admin.dashboard'),
            'clearance_checker' => route('checker.queue'),
            default => route('student.clearance'),
        };
    }
}
