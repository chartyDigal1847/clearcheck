<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protects routes that require an active SSO session.
 * Redirects unauthenticated requests back to the portal entry point.
 * Optionally enforces a specific role.
 */
class RequireSsoSession
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $ssoId   = session('sso_id');
        $ssoRole = session('sso_role');

        if (!$ssoId || !$ssoRole) {
            ActivityLogger::warning('unauthorized_access_attempt', [
                'url'    => $request->fullUrl(),
                'method' => $request->method(),
                'ip'     => $request->ip(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated. Please log in via the DEORIS Portal.'], 401);
            }

            return redirect('/');
        }

        // Role check — if specific roles are required, enforce them
        if (!empty($roles) && !in_array($ssoRole, $roles, true)) {
            ActivityLogger::warning('forbidden_role_access', [
                'required_roles' => $roles,
                'actual_role'    => $ssoRole,
                'url'            => $request->fullUrl(),
                'ip'             => $request->ip(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Forbidden. Insufficient role.'], 403);
            }

            return redirect('/');
        }

        return $next($request);
    }
}
