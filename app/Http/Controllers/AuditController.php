<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\ActivityLogger;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function index(): View
    {
        ActivityLogger::info('admin.audit.viewed');

        $query = ActivityLog::query();

        if ($actor = request('actor')) {
            $query->where('actor', 'like', "%{$actor}%");
        }

        if ($action = request('action')) {
            $query->where('action', 'like', "%{$action}%");
        }

        if ($level = request('level')) {
            $query->where('level', $level);
        }

        if ($from = request('from')) {
            $query->where('occurred_at', '>=', $from . ' 00:00:00');
        }

        if ($to = request('to')) {
            $query->where('occurred_at', '<=', $to . ' 23:59:59');
        }

        $logs = $query->orderByDesc('occurred_at')->paginate(30);

        return view('clearanceport.admin.audit', [
            'logs'       => $logs,
            'admin'      => [
                'id'    => session('sso_id'),
                'name'  => session('sso_name', 'Admin'),
                'email' => session('sso_email', ''),
                'role'  => 'admin',
            ],
            'currentTab' => 'audit',
        ]);
    }
}
