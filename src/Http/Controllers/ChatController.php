<?php

namespace DevsFort\LaravelWhatsappChat\Http\Controllers;

use DevsFort\LaravelWhatsappChat\Services\WhatsAppService;
use DevsFort\LaravelWhatsappChat\Models\WhatsAppMessage;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Display the general chat interface
     */
    public function index(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        // Check if user object exists
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'You must be logged in to access chat.');
        }

        // Get user type safely (default to 'user' if not set)
        $userType = $user->type ?? 'user';
        $isAdmin = $userType === 'admin';

        // Check if user has verified WhatsApp number (only for regular users)
        if (!$isAdmin && !($user->whatsapp_verified ?? false)) {
            return redirect()->route('whatsapp.verification.show')
                ->with('error', 'You must verify your WhatsApp number before accessing chat.');
        }

        $conversations = $this->whatsappService->getConversations();
        $selectedConversation = $request->query('conversation');
        $messages = [];

        if ($selectedConversation) {
            $messages = $this->whatsappService->getMessages($selectedConversation);
        }

        // For admin users, get all users with verified WhatsApp numbers
        $usersWithWhatsApp = [];
        if ($isAdmin) {
            $usersWithWhatsApp = $this->whatsappService->getUsersWithWhatsApp();
        }

        return Inertia::render('Chat/Index', [
            'conversations' => $conversations,
            'selectedConversation' => $selectedConversation,
            'messages' => $messages,
            'user' => $user,
            'isAdmin' => $isAdmin,
            'usersWithWhatsApp' => $usersWithWhatsApp
        ]);
    }

    /**
     * Get messages for a specific conversation
     */
    public function getMessages(Request $request, string $conversationId)
    {
        $messages = $this->whatsappService->getMessages($conversationId);

        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }

    /**
     * Send a message to a specific conversation
     */
    public function sendMessage(Request $request)
    {
        $user = $request->user();

        // Check if user exists
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to send messages.'
            ], 401);
        }

        // Only admins can send messages
        $userType = $user->type ?? 'user';
        if ($userType !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can send messages.'
            ], 403);
        }

        $request->validate([
            'to' => 'required|string',
            'message' => 'required|string|max:4096'
        ]);

        // Send message to the specified WhatsApp number
        $result = $this->whatsappService->sendTextMessage(
            $request->to,
            $request->message
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'message_id' => $result['message_id']
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Failed to send message'
            ], 400);
        }
    }

    /**
     * Get conversation list
     */
    public function getConversations()
    {
        $conversations = $this->whatsappService->getConversations();

        return response()->json([
            'success' => true,
            'conversations' => $conversations
        ]);
    }

    /**
     * Mark messages as read for a specific conversation
     */
    public function markAsRead(Request $request, string $conversationId)
    {
        // This would typically update a read status in the database
        // For now, we'll just return success
        return response()->json([
            'success' => true,
            'message' => 'Messages marked as read'
        ]);
    }
}
