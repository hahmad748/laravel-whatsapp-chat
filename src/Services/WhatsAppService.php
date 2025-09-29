<?php

namespace DevsFort\LaravelWhatsappChat\Services;

use DevsFort\LaravelWhatsappChat\Models\WhatsAppMessage;
use App\Models\User;
use DevsFort\LaravelWhatsappChat\Events\WhatsAppMessageReceived;
use DevsFort\LaravelWhatsappChat\Events\WhatsAppMessageSent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Notifications\MessageReceivedNotification;
use App\Notifications\MessageSentNotification;

class WhatsAppService
{
    protected $accessToken;
    protected $phoneNumberId;
    protected $webhookVerifyToken;
    protected $baseUrl;

    public function __construct()
    {
        $this->accessToken = config('whatsapp.access_token');
        $this->phoneNumberId = config('whatsapp.phone_number_id');
        $this->webhookVerifyToken = config('whatsapp.webhook_verify_token');
        $this->baseUrl = 'https://graph.facebook.com/v18.0';
    }

    /**
     * Send a text message via WhatsApp API
     */
    public function sendTextMessage(string $to, string $message): array
    {
        // Normalize the recipient phone number
        $normalizedTo = $this->normalizePhoneNumber($to);

        // Check if we're in mock mode (for development/testing)
        // Use mock mode when phone number is not verified, when explicitly enabled, or when token is invalid/expired
        $useMockMode = config('whatsapp.use_mock_mode', true) ||
                      $this->accessToken === 'your_access_token_here' ||
                      empty($this->accessToken) ||
                      str_contains($this->accessToken, 'expired') ||
                      str_contains($this->accessToken, 'invalid');

        if ($useMockMode) {
            Log::info('WhatsApp Mock Mode: Message would be sent', [
                'to' => $normalizedTo,
                'original_to' => $to,
                'message' => $message
            ]);

            // Log the message as if it was sent successfully
            $messageModel = $this->logMessage($normalizedTo, $message, 'outbound', 'text', [
                'messages' => [['id' => 'mock_' . time()]]
            ]);

            // Broadcast the message
            broadcast(new WhatsAppMessageSent($messageModel));

            return [
                'success' => true,
                'message_id' => 'mock_' . time(),
                'data' => ['messages' => [['id' => 'mock_' . time()]]]
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/{$this->phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $normalizedTo,
                'type' => 'text',
                'text' => [
                    'body' => $message
                ]
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                // Log the outbound message
                $messageModel = $this->logMessage($normalizedTo, $message, 'outbound', 'text', $responseData);

                // Broadcast the message
                broadcast(new WhatsAppMessageSent($messageModel));

                return [
                    'success' => true,
                    'message_id' => $responseData['messages'][0]['id'] ?? null,
                    'data' => $responseData
                ];
            } else {
                Log::error('WhatsApp API Error', [
                    'status' => $response->status(),
                    'response' => $responseData,
                    'to' => $normalizedTo,
                    'original_to' => $to
                ]);

                // Check if it's an expired token error and auto-mock is enabled
                if (config('whatsapp.auto_mock_on_token_expiry', true) &&
                    $response->status() === 401 &&
                    isset($responseData['error']['code']) &&
                    $responseData['error']['code'] === 190 &&
                    (str_contains($responseData['error']['message'] ?? '', 'expired') ||
                     str_contains($responseData['error']['message'] ?? '', 'Session has expired'))) {

                    Log::warning('WhatsApp Access Token Expired - Switching to Mock Mode', [
                        'error' => $responseData['error']['message'] ?? 'Token expired',
                        'to' => $normalizedTo
                    ]);

                    // Fall back to mock mode for expired tokens
                    $messageModel = $this->logMessage($normalizedTo, $message, 'outbound', 'text', [
                        'messages' => [['id' => 'mock_expired_token_' . time()]]
                    ]);

                    broadcast(new WhatsAppMessageSent($messageModel));

                    return [
                        'success' => true,
                        'message_id' => 'mock_expired_token_' . time(),
                        'data' => ['messages' => [['id' => 'mock_expired_token_' . time()]]],
                        'warning' => 'Message sent in mock mode due to expired access token'
                    ];
                }

                // Check for specific WhatsApp errors
                $errorCode = $responseData['error']['code'] ?? null;
                $errorMessage = $responseData['error']['message'] ?? 'Unknown error';

                // Handle re-engagement error (131047)
                if ($errorCode === 131047) {
                    Log::warning('WhatsApp Re-engagement Error', [
                        'error_code' => $errorCode,
                        'error_message' => $errorMessage,
                        'to' => $normalizedTo,
                        'original_to' => $to,
                        'details' => 'Customer must initiate conversation or message within 24 hours'
                    ]);

                    return [
                        'success' => false,
                        'error' => 'Cannot send message: Customer must initiate the conversation or message within 24 hours',
                        'error_code' => $errorCode,
                        'error_type' => 're_engagement',
                        'data' => $responseData
                    ];
                }

                return [
                    'success' => false,
                    'error' => $errorMessage,
                    'error_code' => $errorCode,
                    'data' => $responseData
                ];
            }
        } catch (Exception $e) {
            Log::error('WhatsApp Service Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'to' => $normalizedTo,
                'original_to' => $to
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send a template message via WhatsApp API
     */
    public function sendTemplateMessage(string $to, string $templateName, array $parameters = []): array
    {
        try {
            $templateData = [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => ['code' => 'en']
                ]
            ];

            if (!empty($parameters)) {
                $templateData['template']['components'] = [
                    [
                        'type' => 'body',
                        'parameters' => array_map(function($param) {
                            return ['type' => 'text', 'text' => $param];
                        }, $parameters)
                    ]
                ];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/{$this->phoneNumberId}/messages", $templateData);

            $responseData = $response->json();

            if ($response->successful()) {
                $this->logMessage($to, "Template: {$templateName}", 'outbound', 'template', $responseData);

                return [
                    'success' => true,
                    'message_id' => $responseData['messages'][0]['id'] ?? null,
                    'data' => $responseData
                ];
            } else {
                Log::error('WhatsApp Template API Error', [
                    'status' => $response->status(),
                    'response' => $responseData
                ]);

                return [
                    'success' => false,
                    'error' => $responseData['error']['message'] ?? 'Unknown error',
                    'data' => $responseData
                ];
            }
        } catch (Exception $e) {
            Log::error('WhatsApp Template Service Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process incoming webhook data
     */
    public function processWebhook(array $data): array
    {
        try {
            $changes = $data['entry'][0]['changes'][0]['value'] ?? [];

            // Handle messages
            if (isset($changes['messages'])) {
                return $this->processMessages($changes['messages']);
            }

            // Handle status updates
            if (isset($changes['statuses'])) {
                return $this->processStatuses($changes['statuses']);
            }

            return ['success' => false, 'message' => 'No messages or statuses found in webhook data'];
        } catch (\Exception $e) {
            Log::error('WhatsApp webhook processing error', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process incoming messages
     */
    protected function processMessages(array $messages): array
    {
        $processed = [];

        foreach ($messages as $message) {
            $from = $this->normalizePhoneNumber($message['from']);
            $messageId = $message['id'];
            $timestamp = $message['timestamp'];
            $type = $message['type'];

            $body = '';
            if ($type === 'text') {
                $body = $message['text']['body'] ?? '';
            } elseif ($type === 'image') {
                $body = '[Image]';
            } elseif ($type === 'document') {
                $body = '[Document]';
            } elseif ($type === 'audio') {
                $body = '[Audio]';
            } elseif ($type === 'video') {
                $body = '[Video]';
            } else {
                $body = "[{$type}]";
            }

            // Log detailed information about the message
            Log::info('Processing WhatsApp message', [
                'from' => $from,
                'body' => $body,
                'type' => $type,
                'message_id' => $messageId
            ]);

            // For inbound messages, always save them - they come TO the admin business number
            // Check if sender is a registered user, but save message regardless
            $senderUser = \App\Models\User::where('whatsapp_number', $from)
                ->where('whatsapp_verified', true)
                ->first();

            $adminUser = \App\Models\User::where('type', 'admin')->first();

            Log::info('Processing inbound message', [
                'from' => $from,
                'body' => $body,
                'type' => $type,
                'sender_user_found' => $senderUser ? true : false,
                'sender_user_id' => $senderUser ? $senderUser->id : null,
                'admin_user_found' => $adminUser ? true : false,
                'admin_user_id' => $adminUser ? $adminUser->id : null
            ]);

            // Always save inbound messages - they come TO the admin
            $messageModel = $this->logMessage($from, $body, 'inbound', $type, $message);

            // Associate with sender user if they exist, otherwise leave user_id as null
            if ($senderUser) {
                $messageModel->update(['user_id' => $senderUser->id]);
                Log::info('Message associated with registered user', [
                    'message_id' => $messageModel->id,
                    'user_id' => $senderUser->id,
                    'user_name' => $senderUser->name
                ]);
            } else {
                Log::info('Message from unregistered number - saved without user association', [
                    'message_id' => $messageModel->id,
                    'from_number' => $from
                ]);
            }

            // Broadcast the message to admin
            if ($adminUser) {
                broadcast(new WhatsAppMessageReceived($messageModel));
                Log::info('Inbound message broadcasted to admin', [
                    'message_id' => $messageModel->id,
                    'from_number' => $from
                ]);
            }

            $processed[] = [
                'from' => $from,
                'message_id' => $messageId,
                'body' => $body,
                'type' => $type,
                'timestamp' => $timestamp
            ];
        }

        return [
            'success' => true,
            'processed' => $processed
        ];
    }

    /**
     * Process status updates (sent, delivered, read, etc.)
     */
    protected function processStatuses(array $statuses): array
    {
        $processed = [];

        foreach ($statuses as $status) {
            $messageId = $status['id'];
            $statusType = $status['status'];
            $timestamp = $status['timestamp'];
            $recipientId = $status['recipient_id'] ?? null;

            Log::info('Processing WhatsApp status update', [
                'message_id' => $messageId,
                'status' => $statusType,
                'recipient_id' => $recipientId,
                'timestamp' => $timestamp
            ]);

            // Update message status in database if it exists
            $message = WhatsAppMessage::where('message_id', $messageId)->first();
            if ($message) {
                $message->update([
                    'status' => $statusType,
                    'status_updated_at' => now()
                ]);

                Log::info('Message status updated', [
                    'message_id' => $messageId,
                    'status' => $statusType
                ]);
            }

            $processed[] = [
                'message_id' => $messageId,
                'status' => $statusType,
                'recipient_id' => $recipientId,
                'timestamp' => $timestamp
            ];
        }

        return ['success' => true, 'processed' => $processed];
    }

    /**
     * Verify webhook challenge
     */
    public function verifyWebhook(string $mode, string $token, string $challenge): bool
    {
        return $mode === 'subscribe' && $token === $this->webhookVerifyToken;
    }

    /**
     * Log message to database
     */
    protected function logMessage(string $from, string $body, string $direction, string $type, array $rawData = []): WhatsAppMessage
    {
        Log::info('logMessage called', [
            'from' => $from,
            'body' => $body,
            'direction' => $direction,
            'type' => $type
        ]);

        // Extract message_id from raw data if available
        $messageId = null;
        if (isset($rawData['id'])) {
            $messageId = $rawData['id'];
        } elseif (isset($rawData['messages'][0]['id'])) {
            $messageId = $rawData['messages'][0]['id'];
        }

        $message = WhatsAppMessage::create([
            'message_id' => $messageId,
            'from' => $from,
            'body' => $body,
            'direction' => $direction,
            'type' => $type,
            'raw_data' => $rawData,
            'processed_at' => now()
        ]);

        Log::info('Message logged to database', ['message_id' => $message->id]);

        // Trigger notifications based on message direction
        Log::info('About to trigger notifications', ['message_id' => $message->id, 'direction' => $direction]);
        $this->triggerMessageNotifications($message, $direction);
        Log::info('Notifications triggered', ['message_id' => $message->id]);

        return $message;
    }

    /**
     * Trigger notifications for message events
     */
    protected function triggerMessageNotifications(WhatsAppMessage $message, string $direction)
    {
        $messageData = [
            'id' => $message->id,
            'from' => $message->from,
            'body' => $message->body,
            'created_at' => $message->created_at->toISOString()
        ];

        Log::info('Triggering notifications', [
            'message_id' => $message->id,
            'direction' => $direction,
            'from' => $message->from,
            'body' => $message->body
        ]);

        if ($direction === 'inbound') {
            // User sent message to admin - notify admin users
            $adminUsers = User::where('type', 'admin')->get();
            Log::info('Notifying admin users about inbound message', ['admin_count' => $adminUsers->count()]);
            foreach ($adminUsers as $admin) {
                $sender = User::where('whatsapp_number', $message->from)->first();
                $admin->notify(new MessageReceivedNotification($message, $sender));
                Log::info('Notification sent to admin', [
                    'admin_id' => $admin->id,
                    'admin_name' => $admin->name,
                    'sender_name' => $sender ? $sender->name : 'Unknown'
                ]);
            }
        } elseif ($direction === 'outbound') {
            // Admin sent message to user - notify the recipient user
            $recipient = User::where('whatsapp_number', $message->from)->first();
            Log::info('Looking for recipient user', [
                'whatsapp_number' => $message->from,
                'recipient_found' => $recipient ? true : false,
                'recipient_id' => $recipient ? $recipient->id : null
            ]);

            if ($recipient) {
                $admin = User::where('type', 'admin')->first();
                $recipient->notify(new MessageReceivedNotification($message, $admin));
                Log::info('Notification sent to recipient user', [
                    'recipient_id' => $recipient->id,
                    'recipient_name' => $recipient->name,
                    'sender_name' => $admin ? $admin->name : 'Admin'
                ]);
            } else {
                Log::warning('No recipient found for outbound message', [
                    'whatsapp_number' => $message->from,
                    'available_users' => User::whereNotNull('whatsapp_number')->pluck('whatsapp_number', 'id')
                ]);
            }
        }
    }

    /**
     * Normalize phone number by removing + sign and extra characters
     */
    protected function normalizePhoneNumber(string $phoneNumber): string
    {
        // Remove + sign and any non-numeric characters except for the first digit
        $normalized = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Ensure it starts with country code (remove leading zeros)
        if (strlen($normalized) > 10 && substr($normalized, 0, 1) === '0') {
            $normalized = substr($normalized, 1);
        }

        return $normalized;
    }

    /**
     * Auto-associate message with user if number is verified
     */
    protected function associateMessageWithUser(WhatsAppMessage $message, string $from): void
    {
        // Find user with this WhatsApp number
        $user = \App\Models\User::where('whatsapp_number', $from)
            ->where('whatsapp_verified', true)
            ->first();

        if ($user) {
            // Update message with user association
            $message->update(['user_id' => $user->id]);
        }
    }

    /**
     * Get conversation list separated by registered and external users
     */
    public function getConversations(): array
    {
        $conversations = WhatsAppMessage::select('from')
            ->selectRaw('MAX(created_at) as last_message_at')
            ->selectRaw('COUNT(*) as message_count')
            ->selectRaw('(SELECT body FROM whats_app_messages w2 WHERE w2.from = whats_app_messages.from ORDER BY w2.created_at DESC LIMIT 1) as last_message')
            ->selectRaw('(SELECT direction FROM whats_app_messages w2 WHERE w2.from = whats_app_messages.from ORDER BY w2.created_at DESC LIMIT 1) as last_direction')
            ->selectRaw('(SELECT u.name FROM users u WHERE u.whatsapp_number = whats_app_messages.from AND u.whatsapp_verified = 1 LIMIT 1) as user_name')
            ->selectRaw('(SELECT u.id FROM users u WHERE u.whatsapp_number = whats_app_messages.from AND u.whatsapp_verified = 1 LIMIT 1) as user_id')
            ->groupBy('from')
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->toArray();

        // Separate registered and external conversations
        $registered = [];
        $external = [];

        foreach ($conversations as $conversation) {
            if ($conversation['user_name']) {
                $conversation['type'] = 'registered';
                $registered[] = $conversation;
            } else {
                $conversation['type'] = 'external';
                $external[] = $conversation;
            }
        }

        return [
            'registered' => $registered,
            'external' => $external,
            'all' => $conversations
        ];
    }

    /**
     * Assign external WhatsApp number to a user
     */
    public function assignNumberToUser(string $phoneNumber, int $userId): array
    {
        try {
            $user = User::find($userId);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }

            // Update user's WhatsApp number
            $user->update([
                'whatsapp_number' => $this->normalizePhoneNumber($phoneNumber),
                'whatsapp_verified' => true,
                'whatsapp_verified_at' => now()
            ]);

            // Update all existing messages from this number to be associated with the user
            WhatsAppMessage::where('from', $this->normalizePhoneNumber($phoneNumber))
                ->update(['user_id' => $userId]);

            Log::info('External number assigned to user', [
                'phone_number' => $phoneNumber,
                'user_id' => $userId,
                'user_name' => $user->name
            ]);

            return [
                'success' => true,
                'message' => 'Number assigned successfully',
                'user' => $user
            ];
        } catch (\Exception $e) {
            Log::error('Error assigning number to user', [
                'phone_number' => $phoneNumber,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error assigning number: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get messages for a specific conversation
     */
    public function getMessages(string $from): array
    {
        return WhatsAppMessage::where('from', $from)
            ->with(['user'])
            ->orderBy('created_at', 'asc') // Get messages in chronological order (oldest first)
            ->get()
            ->toArray();
    }

    /**
     * Get all users with verified WhatsApp numbers
     */
    public function getUsersWithWhatsApp(): array
    {
        return \App\Models\User::where('whatsapp_verified', true)
            ->whereNotNull('whatsapp_number')
            ->select('id', 'name', 'email', 'whatsapp_number', 'whatsapp_verified_at')
            ->orderBy('name', 'asc')
            ->get()
            ->toArray();
    }

}
