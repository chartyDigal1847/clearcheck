<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Placeholder for Laravel auth config only.
 * ClearCheck authenticates via DEORIS portal SSO (session keys).
 */
class PortalAccount extends Authenticatable
{
    protected $table = 'sessions';
}
