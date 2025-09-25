<?php

namespace DevsFort\LaravelWhatsappChat\Http\Controllers;

use DevsFort\LaravelWhatsappChat\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Verify webhook subscription
     */
    public function verify(Request $request): string
    {
        Log::info('WhatsApp webhook verification attempt', [
            'query_params' => $request->query->all(),
            'headers' => $request->headers->all()
        ]);

        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($this->whatsappService->verifyWebhook($mode, $token, $challenge)) {
            Log::info('WhatsApp webhook verified successfully');
            return $challenge;
        }

        Log::warning('WhatsApp webhook verification failed', [
            'mode' => $mode,
            'token' => $token
        ]);

        abort(403, 'Forbidden');
    }

    /**
     * Handle incoming webhook data
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            // Log the raw request for debugging
            Log::info('WhatsApp webhook received', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'headers' => $request->headers->all(),
                'raw_content' => $request->getContent(),
                'data' => $request->all()
            ]);

            $data = $request->all();

            // Process the webhook data
            $result = $this->whatsappService->processWebhook($data);

            if ($result['success']) {
                Log::info('WhatsApp webhook processed successfully', [
                    'processed_count' => count($result['processed'])
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Webhook processed successfully',
                    'processed' => count($result['processed'])
                ]);
            } else {
                Log::error('WhatsApp webhook processing failed', [
                    'error' => $result['error'] ?? 'Unknown error'
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => $result['error'] ?? 'Processing failed'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp webhook exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
