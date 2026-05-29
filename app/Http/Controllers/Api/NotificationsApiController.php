<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClearCheckNotification;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GET    /api/v1/notifications          — list notifications for authenticated user
 * PATCH  /api/v1/notifications/{id}/read — mark a notification as read
 * PATCH  /api/v1/notifications/read-all  — mark all as read
 * DELETE /api/v1/notifications/{id}      — delete a notification
 */
class NotificationsApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = session('sso_id');

        $query = ClearCheckNotification::where('portal_user_id', $userId)
            ->whereNull('deleted_at');

        if ($request->boolean('unread')) {
            $query->where('is_read', false);
        }

        $notifications = $query->latest()->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => $notifications->map(fn($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'title'      => $n->title,
                'body'       => $n->body,
                'data'       => $n->data,
                'is_read'    => $n->is_read,
                'read_at'    => $n->read_at?->toIso8601String(),
                'created_at' => $n->created_at->toIso8601String(),
            ]),
            'meta' => [
                'unread_count' => ClearCheckNotification::where('portal_user_id', $userId)
                    ->where('is_read', false)->count(),
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'total'        => $notifications->total(),
            ],
        ]);
    }

    public function markRead(int $id): JsonResponse
    {
        $notification = ClearCheckNotification::where('portal_user_id', session('sso_id'))
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json(['message' => 'Marked as read.']);
    }

    public function markAllRead(): JsonResponse
    {
        ClearCheckNotification::where('portal_user_id', session('sso_id'))
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        ActivityLogger::info('api.notifications.mark_all_read');

        return response()->json(['message' => 'All notifications marked as read.']);
    }

    public function destroy(int $id): JsonResponse
    {
        $notification = ClearCheckNotification::where('portal_user_id', session('sso_id'))
            ->findOrFail($id);

        $notification->delete();

        return response()->json(['message' => 'Notification deleted.']);
    }
}
