<?php

namespace DevsFort\LaravelWhatsappChat\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use DevsFort\LaravelWhatsappChat\Services\WhatsAppService;

class WhatsAppTokenStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:token-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check WhatsApp access token status and provide recommendations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking WhatsApp Access Token Status...');
        $this->newLine();

        $accessToken = config('whatsapp-chat.access_token');
        $phoneNumberId = config('whatsapp-chat.phone_number_id');
        $useMockMode = config('whatsapp-chat.use_mock_mode');
        $autoMockOnExpiry = config('whatsapp-chat.auto_mock_on_token_expiry');

        // Display current configuration
        $this->info('📋 Current Configuration:');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Access Token', $accessToken ? 'Set (' . substr($accessToken, 0, 10) . '...)' : 'Not Set'],
                ['Phone Number ID', $phoneNumberId ?: 'Not Set'],
                ['Mock Mode', $useMockMode ? 'Enabled' : 'Disabled'],
                ['Auto Mock on Token Expiry', $autoMockOnExpiry ? 'Enabled' : 'Disabled'],
            ]
        );

        $this->newLine();

        // Check if token is set
        if (empty($accessToken) || $accessToken === 'your_access_token_here') {
            $this->warn('⚠️  Access token is not properly configured.');
            $this->info('💡 Recommendation: Set WHATSAPP_ACCESS_TOKEN in your .env file');
            $this->info('   The system will continue to work in mock mode.');
            return;
        }

        // Test the token with a simple API call
        $this->info('🧪 Testing access token...');

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])->get('https://graph.facebook.com/me');

            if ($response->successful()) {
                $this->info('✅ Access token is valid and working!');
                $data = $response->json();
                if (isset($data['name'])) {
                    $this->info("   App Name: {$data['name']}");
                }
            } else {
                $errorData = $response->json();
                $this->error('❌ Access token is invalid or expired.');

                if (isset($errorData['error']['code']) && $errorData['error']['code'] === 190) {
                    $this->warn('⚠️  Token has expired (Error 190)');
                    $this->info('💡 Recommendation: Generate a new access token from Facebook Developer Console');
                    $this->info('   The system will automatically switch to mock mode for expired tokens.');
                } else {
                    $this->error("   Error: " . ($errorData['error']['message'] ?? 'Unknown error'));
                }
            }
        } catch (\Exception $e) {
            $this->error('❌ Failed to test access token: ' . $e->getMessage());
        }

        $this->newLine();

        // Test WhatsApp service
        $this->info('🔧 Testing WhatsApp Service...');
        $whatsappService = new WhatsAppService();

        // Test with a mock message
        $testResult = $whatsappService->sendTextMessage('1234567890', 'Test message from token status check');

        if ($testResult['success']) {
            $this->info('✅ WhatsApp Service is working correctly!');
            if (isset($testResult['warning'])) {
                $this->warn("   Warning: {$testResult['warning']}");
            }
        } else {
            $this->error('❌ WhatsApp Service test failed: ' . ($testResult['error'] ?? 'Unknown error'));
        }

        $this->newLine();
        $this->info('📝 Summary:');
        $this->info('   - Mock mode is ' . ($useMockMode ? 'enabled' : 'disabled'));
        $this->info('   - Auto-mock on token expiry is ' . ($autoMockOnExpiry ? 'enabled' : 'disabled'));
        $this->info('   - The chat system will continue to work regardless of token status');
    }
}
