<?php

namespace DevsFort\LaravelWhatsappChat\Http\Controllers;

use DevsFort\LaravelWhatsappChat\Services\WhatsAppService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class WhatsAppVerificationController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Show WhatsApp verification page
     */
    public function show(): Response
    {
        $user = Auth::user();

        return Inertia::render('Profile/WhatsAppVerification', [
            'whatsapp_number' => $user->whatsapp_number,
            'whatsapp_verified' => $user->whatsapp_verified,
            'whatsapp_verified_at' => $user->whatsapp_verified_at,
        ]);
    }

    /**
     * Send verification code to WhatsApp number
     */
    public function sendVerificationCode(Request $request)
    {
        $request->validate([
            'whatsapp_number' => 'required|string|max:20'
        ]);

        $user = Auth::user();
        $verificationCode = Str::random(6);

        // Normalize the phone number
        $normalizedNumber = $this->normalizePhoneNumber($request->whatsapp_number);

        // Update user with new WhatsApp number and verification code
        $user->update([
            'whatsapp_number' => $normalizedNumber,
            'whatsapp_verified' => false,
            'whatsapp_verification_code' => $verificationCode,
            'whatsapp_verified_at' => null,
        ]);

        // Send verification code via WhatsApp
        $message = "Your verification code is: {$verificationCode}\n\nThis code will expire in 10 minutes.";

        $result = $this->whatsappService->sendTextMessage(
            $normalizedNumber,
            $message
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Verification code sent to your WhatsApp number'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification code: ' . $result['error']
            ], 400);
        }
    }

    /**
     * Verify the WhatsApp number
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'verification_code' => 'required|string|size:6'
        ]);

        $user = Auth::user();

        if (!$user->whatsapp_verification_code) {
            return response()->json([
                'success' => false,
                'message' => 'No verification code found. Please request a new one.'
            ], 400);
        }

        if ($user->whatsapp_verification_code !== $request->verification_code) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code'
            ], 400);
        }

        // Check if code is not expired (10 minutes)
        if ($user->updated_at->diffInMinutes(now()) > 10) {
            return response()->json([
                'success' => false,
                'message' => 'Verification code has expired. Please request a new one.'
            ], 400);
        }

        // Verify the WhatsApp number
        $user->update([
            'whatsapp_verified' => true,
            'whatsapp_verified_at' => now(),
            'whatsapp_verification_code' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'WhatsApp number verified successfully!'
        ]);
    }

    /**
     * Remove WhatsApp number
     */
    public function removeWhatsApp(Request $request)
    {
        $user = Auth::user();

        $user->update([
            'whatsapp_number' => null,
            'whatsapp_verified' => false,
            'whatsapp_verified_at' => null,
            'whatsapp_verification_code' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'WhatsApp number removed successfully'
        ]);
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
}
