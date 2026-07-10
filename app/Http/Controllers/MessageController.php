<?php

namespace App\Http\Controllers;

use App\Http\Resources\MessageResource;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MessageController extends Controller
{
    /**
     * Get all conversations (unique contacts) for the authenticated user.
     */
    public function conversations(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        // Get unique user IDs the current user has chatted with
        $contactIds = Message::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->get()
            ->map(fn($m) => $m->sender_id === $userId ? $m->receiver_id : $m->sender_id)
            ->unique()
            ->values();

        $contacts = User::whereIn('id', $contactIds)->get();

        return response()->json([
            'data' => $contacts->map(fn($contact) => [
                'user' => [
                    'id'            => $contact->id,
                    'name'          => $contact->name,
                    'profile_image' => $contact->profile_image,
                ],
                'last_message' => Message::where(
                    fn($q) => $q->where('sender_id', $userId)->where('receiver_id', $contact->id)
                )->orWhere(
                    fn($q) => $q->where('sender_id', $contact->id)->where('receiver_id', $userId)
                )->latest()->first()?->content,
            ]),
        ]);
    }

    /**
     * Get messages between the authenticated user and another user.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate(['with' => 'required|exists:users,id']);

        $userId      = $request->user()->id;
        $contactId   = $request->with;

        $messages = Message::with(['sender', 'receiver'])
            ->where(fn($q) => $q->where('sender_id', $userId)->where('receiver_id', $contactId))
            ->orWhere(fn($q) => $q->where('sender_id', $contactId)->where('receiver_id', $userId))
            ->orderBy('created_at')
            ->get();

        // Mark received messages as read
        Message::where('sender_id', $contactId)
            ->where('receiver_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return MessageResource::collection($messages);
    }

    /**
     * Send a message.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id|different:' . $request->user()->id,
            'content'     => 'required|string|max:5000',
        ]);

        $message = Message::create([
            ...$validated,
            'sender_id' => $request->user()->id,
        ]);

        $message->load(['sender', 'receiver']);

        return response()->json(new MessageResource($message), 201);
    }

    /**
     * Get unread message count.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = Message::where('receiver_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        return response()->json(['unread_count' => $count]);
    }
}
