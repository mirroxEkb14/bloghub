<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 20), 50);

        $notifications = $user->notifications()
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return NotificationResource::collection($notifications);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = $request->user()->notifications()->whereNull('read_at')->count();

        return response()->json(['count' => $count]);
    }

    public function markRead(Request $request, Notification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => __('Unauthorized')], 403);
        }

        $notification->update(['read_at' => now()]);

        return response()->json(new NotificationResource($notification->fresh()));
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->notifications()->whereNull('read_at')->update(['read_at' => now()]);

        return response()->json(['message' => __('All notifications marked as read')]);
    }
}
