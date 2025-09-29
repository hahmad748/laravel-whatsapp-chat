<?php

namespace DevsFort\LaravelWhatsappChat\Http\Controllers;

use DevsFort\LaravelWhatsappChat\Services\WhatsAppService;
use DevsFort\LaravelWhatsappChat\Models\WhatsAppMessage;
use App\Models\Deal;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
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

        // Check if user has verified WhatsApp number (only for regular users)
        if ($user->type !== 'admin' && !$user->whatsapp_verified) {
            return redirect()->route('whatsapp.verification.show')
                ->with('error', 'You must verify your WhatsApp number before accessing chat.');
        }

        $conversationsData = $this->whatsappService->getConversations();
        $conversations = $conversationsData['all']; // Keep backward compatibility
        $registeredConversations = $conversationsData['registered'];
        $externalConversations = $conversationsData['external'];

        $selectedConversation = $request->query('conversation');
        $messages = [];

        if ($selectedConversation) {
            $messages = $this->whatsappService->getMessages($selectedConversation);
        }

        // For admin users, get all users with verified WhatsApp numbers
        $usersWithWhatsApp = [];
        if ($user->type === 'admin') {
            $usersWithWhatsApp = $this->whatsappService->getUsersWithWhatsApp();
        }


        return Inertia::render('Chat/Index', [
            'conversations' => $conversations,
            'registeredConversations' => $registeredConversations,
            'externalConversations' => $externalConversations,
            'selectedConversation' => $selectedConversation,
            'messages' => $messages,
            'user' => $user,
            'isAdmin' => $user->type === 'admin',
            'usersWithWhatsApp' => $usersWithWhatsApp,
            'adminWhatsAppNumber' => config('whatsapp.admin_phone_number')
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

        // Only admins can send messages
        if ($user->type !== 'admin') {
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
            // Handle specific error types
            $errorType = $result['error_type'] ?? 'general';
            $statusCode = 400;

            if ($errorType === 're_engagement') {
                $statusCode = 422; // Unprocessable Entity
            }

            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Failed to send message',
                'error_type' => $errorType,
                'error_code' => $result['error_code'] ?? null
            ], $statusCode);
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

    /**
     * Assign external WhatsApp number to a user
     */
    public function assignNumberToUser(Request $request)
    {
        $user = $request->user();

        // Only admins can assign numbers
        if ($user->type !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can assign numbers.'
            ], 403);
        }

        $request->validate([
            'phone_number' => 'required|string',
            'user_id' => 'required|integer|exists:users,id'
        ]);

        $result = $this->whatsappService->assignNumberToUser(
            $request->phone_number,
            $request->user_id
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'user' => $result['user']
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }
    }
}
