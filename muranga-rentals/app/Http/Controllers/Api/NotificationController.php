<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Notification::where('user_id', $user->id);

        // Filter by read status
        if ($request->has('is_read')) {
            $query->where('is_read', $request->is_read === 'true' || $request->is_read === '1');
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Get a single notification
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        // Check authorization
        if ($notification->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Mark as read when viewed
        if (!$notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $notification
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        // Check authorization
        if ($notification->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => $notification
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        $updated = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => "{$updated} notifications marked as read"
        ]);
    }

    /**
     * Get unread notification count
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();

        $count = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        // Count by type
        $byType = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type');

        return response()->json([
            'success' => true,
            'data' => [
                'total_unread' => $count,
                'by_type' => $byType
            ]
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        // Check authorization
        if ($notification->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully'
        ]);
    }

    /**
     * Delete all read notifications
     */
    public function deleteAllRead(Request $request)
    {
        $user = $request->user();

        $deleted = Notification::where('user_id', $user->id)
            ->where('is_read', true)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deleted} notifications deleted"
        ]);
    }

    /**
     * Get notification statistics
     */
    public function getStatistics(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total_notifications' => Notification::where('user_id', $user->id)->count(),
            'unread_notifications' => Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->count(),
            'read_notifications' => Notification::where('user_id', $user->id)
                ->where('is_read', true)
                ->count(),
            'notifications_today' => Notification::where('user_id', $user->id)
                ->whereDate('created_at', today())
                ->count(),
            'notifications_this_week' => Notification::where('user_id', $user->id)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'by_type' => Notification::where('user_id', $user->id)
                ->select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->get()
                ->pluck('count', 'type'),
            'recent_unread' => Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Update notification preferences
     */
    public function updatePreferences(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'notification_types' => 'array',
            'notification_types.*' => 'string|in:booking,payment,review,maintenance,message,property,system'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        $preferences = [
            'email_notifications' => $request->get('email_notifications', true),
            'sms_notifications' => $request->get('sms_notifications', true),
            'push_notifications' => $request->get('push_notifications', true),
            'notification_types' => $request->get('notification_types', [
                'booking', 'payment', 'review', 'maintenance', 'message', 'property', 'system'
            ])
        ];

        $user->update([
            'notification_preferences' => json_encode($preferences)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated',
            'data' => $preferences
        ]);
    }

    /**
     * Get notification preferences
     */
    public function getPreferences(Request $request)
    {
        $user = $request->user();
        
        $preferences = json_decode($user->notification_preferences ?? '{}', true);
        
        // Set defaults if not set
        $defaultPreferences = [
            'email_notifications' => true,
            'sms_notifications' => true,
            'push_notifications' => true,
            'notification_types' => [
                'booking', 'payment', 'review', 'maintenance', 'message', 'property', 'system'
            ]
        ];

        $preferences = array_merge($defaultPreferences, $preferences);

        return response()->json([
            'success' => true,
            'data' => $preferences
        ]);
    }

    /**
     * Send a test notification
     */
    public function sendTestNotification(Request $request)
    {
        $user = $request->user();

        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'system',
            'title' => 'Test Notification',
            'message' => 'This is a test notification to verify your notification settings are working correctly.',
            'data' => json_encode(['test' => true])
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Test notification sent',
            'data' => $notification
        ]);
    }

    /**
     * Get notifications grouped by date
     */
    public function getGroupedByDate(Request $request)
    {
        $user = $request->user();

        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->groupBy(function ($notification) {
                $date = $notification->created_at;
                
                if ($date->isToday()) {
                    return 'Today';
                } elseif ($date->isYesterday()) {
                    return 'Yesterday';
                } elseif ($date->isCurrentWeek()) {
                    return 'This Week';
                } elseif ($date->isCurrentMonth()) {
                    return 'This Month';
                } else {
                    return $date->format('F Y');
                }
            });

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Mark notification as actioned
     */
    public function markAsActioned(Request $request, $id)
    {
        $user = $request->user();
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        // Check authorization
        if ($notification->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
            'actioned_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as actioned',
            'data' => $notification
        ]);
    }

    /**
     * Get notification types available
     */
    public function getNotificationTypes(Request $request)
    {
        $types = [
            'booking' => [
                'name' => 'Booking Notifications',
                'description' => 'Notifications about booking requests, confirmations, and cancellations',
                'icon' => 'calendar'
            ],
            'payment' => [
                'name' => 'Payment Notifications',
                'description' => 'Notifications about payments, receipts, and transactions',
                'icon' => 'credit-card'
            ],
            'review' => [
                'name' => 'Review Notifications',
                'description' => 'Notifications about new reviews and ratings',
                'icon' => 'star'
            ],
            'maintenance' => [
                'name' => 'Maintenance Notifications',
                'description' => 'Notifications about maintenance requests and updates',
                'icon' => 'tool'
            ],
            'message' => [
                'name' => 'Message Notifications',
                'description' => 'Notifications about new messages',
                'icon' => 'message'
            ],
            'property' => [
                'name' => 'Property Notifications',
                'description' => 'Notifications about property updates and new listings',
                'icon' => 'home'
            ],
            'system' => [
                'name' => 'System Notifications',
                'description' => 'Important system updates and announcements',
                'icon' => 'bell'
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $types
        ]);
    }
}

// Made with Bob
