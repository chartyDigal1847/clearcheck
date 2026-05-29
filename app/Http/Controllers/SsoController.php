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

    /**
     * POST /sso/exchange — validate token with DEORIS portal, hydrate session.
     */
    public function exchange(Request $request): JsonResponse
    {
        $token = $request->input('token');

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

        $response = $this->deorisHttp($token)->post(
            rtrim(config('services.auth.url', 'https://deoris.test'), '/')
            .config('services.auth.sso_exchange_path', '/api/v1/sso/exchange'),
            ['token' => $token]
        );

        if (! $response->ok()) {
            Log::warning('[ClearCheck][SSO] Exchange failed', ['status' => $response->status()]);

            return response()->json(['error' => 'Invalid SSO token'], 401);
        }

        $data = $response->json();
        $portalUser = $data['user'] ?? $data['data']['user'] ?? null;

        if (empty($portalUser['id'])) {
            return response()->json(['error' => 'Invalid SSO response'], 401);
        }

        $role = PortalSession::mapPortalRole($portalUser['role'] ?? 'student');

        Log::info('[ClearCheck][SSO] Exchange OK', [
            'portal_id' => $portalUser['id'],
            'role' => $role,
        ]);

        PortalSession::hydrate($request, $portalUser, $role);
        $this->provisioner->provision($role, $portalUser);

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

    private function redirectUrlByRole(string $role): string
    {
        return match ($role) {
            'admin' => route('admin.dashboard'),
            'clearance_checker' => route('checker.queue'),
            default => route('student.clearance'),
        };
    }
}
