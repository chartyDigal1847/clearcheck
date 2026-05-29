<?php

namespace App\Services;

use Illuminate\Http\Request;

/**
 * DEORIS portal identity held in server session after SSO exchange.
 */
class PortalSession
{
    public const ID = 'sso_id';

    public const ROLE = 'sso_role';

    public const NAME = 'sso_name';

    public const EMAIL = 'sso_email';

    public const AUTHENTICATED_AT = 'sso_authenticated_at';

    public static function isAuthenticated(Request $request): bool
    {
        return $request->session()->has(self::ID)
            && $request->session()->has(self::ROLE);
    }

    /**
     * @return array{id: string|int, name: string, email: string, role: string}|null
     */
    public static function user(Request $request): ?array
    {
        if (! self::isAuthenticated($request)) {
            return null;
        }

        return [
            'id' => $request->session()->get(self::ID),
            'name' => $request->session()->get(self::NAME, 'User'),
            'email' => $request->session()->get(self::EMAIL, ''),
            'role' => $request->session()->get(self::ROLE, 'student'),
        ];
    }

    /**
     * @param  array{id: string|int, name?: string, email?: string, role?: string}  $portalUser
     */
    public static function hydrate(Request $request, array $portalUser, string $mappedRole): void
    {
        $request->session()->flush();
        // Do NOT regenerate() — it changes the session ID after the cookie is
        // already set, so the next navigation arrives with the old (invalid) ID.

        $request->session()->put([
            self::ID => (string) $portalUser['id'],
            self::NAME => $portalUser['name'] ?? 'User',
            self::EMAIL => strtolower($portalUser['email'] ?? ''),
            self::ROLE => $mappedRole,
            self::AUTHENTICATED_AT => now()->timestamp,
        ]);
    }

    public static function mapPortalRole(string $portalRole): string
    {
        return match ($portalRole) {
            'admin', 'admission_officer', 'election_officer' => 'admin',
            'hr', 'cashier', 'librarian' => 'clearance_checker',
            default => 'student',
        };
    }
}
