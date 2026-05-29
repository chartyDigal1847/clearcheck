<?php

namespace App\Http\Controllers;

use App\Models\ClearCheckNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationsController extends Controller
{
    public function index(): View
    {
        $userId = session('sso_id');

        $notifications = ClearCheckNotification::where('portal_user_id', $userId)
            ->whereNull('deleted_at')
            ->latest()
            ->paginate(20);

        $unreadCount = ClearCheckNotification::where('portal_user_id', $userId)
            ->where('is_read', false)
            ->count();

        return view('clearanceport.dashboards.notifications', [
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount,
        ]);
    }

    public function markRead(int $id): RedirectResponse
    {
        ClearCheckNotification::where('portal_user_id', session('sso_id'))
            ->findOrFail($id)
            ->markAsRead();

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead(): RedirectResponse
    {
        ClearCheckNotification::where('portal_user_id', session('sso_id'))
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    public function destroy(int $id): RedirectResponse
    {
        ClearCheckNotification::where('portal_user_id', session('sso_id'))
            ->findOrFail($id)
            ->delete();

        return redirect()->back()->with('success', 'Notification deleted.');
    }
}
