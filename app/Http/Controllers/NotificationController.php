<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request, NotificationService $service)
    {
        $user = Auth::user();
        $tab = $request->input('tab', 'all');
        $category = $request->input('category', '');

        $live = $service->getLiveQueuesForUser($user);
        $queues = $live['queues'];

        $query = UserNotification::where('user_id', $user->id)
            ->orderByRaw('read_at IS NOT NULL ASC')
            ->latest('created_at');

        if ($tab === 'unread') {
            $query->whereNull('read_at');
        }

        if ($category !== '' && in_array($category, ['orders', 'inventory', 'returns', 'security', 'system'], true)) {
            $query->where('category', $category);
        }

        $notifications = $query->paginate(20)->withQueryString();
        $unreadCount = UserNotification::where('user_id', $user->id)->whereNull('read_at')->count();

        return view('notifications.index', compact('queues', 'notifications', 'unreadCount', 'tab', 'category'));
    }

    public function poll(Request $request, NotificationService $service)
    {
        $user = Auth::user();
        $payload = $service->getDropdownPayload($user, 8);

        return response()->json([
            'total_count'        => $payload['total_count'],
            'unread_inbox_count' => $payload['unread_inbox_count'],
            'queue_count'        => $payload['queue_count'],
            'toast_enabled'      => $payload['toast_enabled'],
            'badges'             => $payload['badges'],
            'queues'             => $payload['queues'],
            'inbox'              => $payload['inbox']->map(fn (UserNotification $n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'category'   => $n->category,
                'severity'   => $n->severity,
                'title'      => $n->title,
                'message'    => $n->message,
                'action_url' => $n->action_url,
                'is_unread'  => is_null($n->read_at),
                'read_url'   => route('notifications.read', $n),
                'time_ago'   => $n->created_at?->diffForHumans() ?? 'Just now',
            ])->values(),
        ]);
    }

    public function markRead(Request $request, UserNotification $notification)
    {
        if ((int) $notification->user_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized notification access.');
        }

        $notification->markAsRead();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'id' => $notification->id]);
        }

        if (!empty($notification->action_url)) {
            return redirect($notification->action_url);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead(Request $request)
    {
        UserNotification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }
}
