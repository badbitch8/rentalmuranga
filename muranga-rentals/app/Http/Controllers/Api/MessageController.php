<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    /**
     * Get all conversations for the authenticated user
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get unique conversations with the last message
        $conversations = Message::where(function ($query) use ($user) {
            $query->where('sender_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
        })
        ->with(['sender', 'receiver'])
        ->orderBy('created_at', 'desc')
        ->get()
        ->groupBy(function ($message) use ($user) {
            // Group by the other user in the conversation
            return $message->sender_id === $user->id 
                ? $message->receiver_id 
                : $message->sender_id;
        })
        ->map(function ($messages) use ($user) {
            $lastMessage = $messages->first();
            $otherUserId = $lastMessage->sender_id === $user->id 
                ? $lastMessage->receiver_id 
                : $lastMessage->sender_id;
            
            $otherUser = User::find($otherUserId);
            
            // Count unread messages from this user
            $unreadCount = Message::where('sender_id', $otherUserId)
                ->where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count();

            return [
                'user' => $otherUser,
                'last_message' => $lastMessage,
                'unread_count' => $unreadCount,
                'last_message_time' => $lastMessage->created_at
            ];
        })
        ->sortByDesc('last_message_time')
        ->values();

        return response()->json([
            'success' => true,
            'data' => $conversations
        ]);
    }

    /**
     * Get conversation with a specific user
     */
    public function conversation(Request $request, $userId)
    {
        $user = $request->user();
        $otherUser = User::find($userId);

        if (!$otherUser) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Get all messages between these two users
        $messages = Message::where(function ($query) use ($user, $userId) {
            $query->where('sender_id', $user->id)
                  ->where('receiver_id', $userId);
        })
        ->orWhere(function ($query) use ($user, $userId) {
            $query->where('sender_id', $userId)
                  ->where('receiver_id', $user->id);
        })
        ->with(['sender', 'receiver'])
        ->orderBy('created_at', 'asc')
        ->paginate(50);

        // Mark messages from other user as read
        Message::where('sender_id', $userId)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json([
            'success' => true,
            'data' => [
                'messages' => $messages,
                'other_user' => $otherUser
            ]
        ]);
    }

    /**
     * Send a message
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:2000',
            'property_id' => 'nullable|exists:properties,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf,doc,docx|max:10240'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Check if trying to message themselves
        if ($request->receiver_id == $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot send messages to yourself'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $messageData = [
                'sender_id' => $user->id,
                'receiver_id' => $request->receiver_id,
                'message' => $request->message,
                'property_id' => $request->property_id,
                'booking_id' => $request->booking_id,
                'is_read' => false
            ];

            // Handle file attachment
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $path = $file->store('message-attachments', 'public');
                $messageData['attachment'] = $path;
                $messageData['attachment_name'] = $file->getClientOriginalName();
                $messageData['attachment_type'] = $file->getClientMimeType();
            }

            $message = Message::create($messageData);

            // Create notification for receiver
            Notification::create([
                'user_id' => $request->receiver_id,
                'type' => 'new_message',
                'title' => 'New Message',
                'message' => "{$user->name} sent you a message",
                'data' => json_encode([
                    'message_id' => $message->id,
                    'sender_id' => $user->id,
                    'sender_name' => $user->name
                ])
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => $message->load(['sender', 'receiver'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark a message as read
     */
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();
        $message = Message::find($id);

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found'
            ], 404);
        }

        // Only receiver can mark as read
        if ($message->receiver_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $message->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message marked as read'
        ]);
    }

    /**
     * Mark all messages from a user as read
     */
    public function markAllAsRead(Request $request, $userId)
    {
        $user = $request->user();

        $updated = Message::where('sender_id', $userId)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => "{$updated} messages marked as read"
        ]);
    }

    /**
     * Get unread message count
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();

        $count = Message::where('receiver_id', $user->id)
            ->where('is_read', false)
            ->count();

        // Get count per conversation
        $perConversation = Message::where('receiver_id', $user->id)
            ->where('is_read', false)
            ->select('sender_id', DB::raw('count(*) as count'))
            ->groupBy('sender_id')
            ->with('sender:id,name,profile_image')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_unread' => $count,
                'per_conversation' => $perConversation
            ]
        ]);
    }

    /**
     * Delete a message
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $message = Message::find($id);

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found'
            ], 404);
        }

        // Only sender can delete their message
        if ($message->sender_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own messages'
            ], 403);
        }

        // Soft delete
        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully'
        ]);
    }

    /**
     * Search messages
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
            'user_id' => 'nullable|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $query = Message::where(function ($q) use ($user) {
            $q->where('sender_id', $user->id)
              ->orWhere('receiver_id', $user->id);
        })
        ->where('message', 'like', '%' . $request->query . '%')
        ->with(['sender', 'receiver']);

        // Filter by specific user if provided
        if ($request->has('user_id')) {
            $query->where(function ($q) use ($user, $request) {
                $q->where(function ($subQ) use ($user, $request) {
                    $subQ->where('sender_id', $user->id)
                         ->where('receiver_id', $request->user_id);
                })
                ->orWhere(function ($subQ) use ($user, $request) {
                    $subQ->where('sender_id', $request->user_id)
                         ->where('receiver_id', $user->id);
                });
            });
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    /**
     * Get message statistics
     */
    public function getStatistics(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total_sent' => Message::where('sender_id', $user->id)->count(),
            'total_received' => Message::where('receiver_id', $user->id)->count(),
            'unread_messages' => Message::where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count(),
            'total_conversations' => Message::where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
            })
            ->select(DB::raw('DISTINCT CASE 
                WHEN sender_id = ' . $user->id . ' THEN receiver_id 
                ELSE sender_id 
            END as other_user_id'))
            ->get()
            ->count(),
            'messages_today' => Message::where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
            })
            ->whereDate('created_at', today())
            ->count(),
            'messages_this_week' => Message::where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
            })
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Block a user from messaging
     */
    public function blockUser(Request $request, $userId)
    {
        $user = $request->user();
        $userToBlock = User::find($userId);

        if (!$userToBlock) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Add to blocked users list (stored in user metadata)
        $blockedUsers = json_decode($user->blocked_users ?? '[]', true);
        
        if (!in_array($userId, $blockedUsers)) {
            $blockedUsers[] = $userId;
            $user->update(['blocked_users' => json_encode($blockedUsers)]);
        }

        return response()->json([
            'success' => true,
            'message' => 'User blocked successfully'
        ]);
    }

    /**
     * Unblock a user
     */
    public function unblockUser(Request $request, $userId)
    {
        $user = $request->user();
        
        $blockedUsers = json_decode($user->blocked_users ?? '[]', true);
        $blockedUsers = array_values(array_diff($blockedUsers, [$userId]));
        
        $user->update(['blocked_users' => json_encode($blockedUsers)]);

        return response()->json([
            'success' => true,
            'message' => 'User unblocked successfully'
        ]);
    }

    /**
     * Get blocked users list
     */
    public function getBlockedUsers(Request $request)
    {
        $user = $request->user();
        $blockedUserIds = json_decode($user->blocked_users ?? '[]', true);
        
        $blockedUsers = User::whereIn('id', $blockedUserIds)
            ->select('id', 'name', 'email', 'profile_image')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $blockedUsers
        ]);
    }
}

// Made with Bob
