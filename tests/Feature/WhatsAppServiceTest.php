<?php

namespace DevsFort\LaravelWhatsappChat\Tests\Feature;

use Tests\TestCase;
use DevsFort\LaravelWhatsappChat\Services\WhatsAppService;
use DevsFort\LaravelWhatsappChat\Models\WhatsAppMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WhatsAppServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $whatsappService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->whatsappService = new WhatsAppService();
    }

    /** @test */
    public function it_can_send_text_message_in_mock_mode()
    {
        config(['whatsapp-chat.use_mock_mode' => true]);

        $result = $this->whatsappService->sendTextMessage('1234567890', 'Test message');

        $this->assertTrue($result['success']);
        $this->assertStringStartsWith('mock_', $result['message_id']);
    }

    /** @test */
    public function it_can_process_webhook_data()
    {
        $webhookData = [
            'entry' => [
                [
                    'changes' => [
                        [
                            'value' => [
                                'messages' => [
                                    [
                                        'id' => 'test_message_id',
                                        'from' => '1234567890',
                                        'timestamp' => time(),
                                        'type' => 'text',
                                        'text' => [
                                            'body' => 'Hello World'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $result = $this->whatsappService->processWebhook($webhookData);

        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['processed']);
    }

    /** @test */
    public function it_can_get_conversations()
    {
        // Create test messages
        WhatsAppMessage::create([
            'from' => '1234567890',
            'body' => 'Test message',
            'direction' => 'inbound',
            'type' => 'text',
            'raw_data' => []
        ]);

        $conversations = $this->whatsappService->getConversations();

        $this->assertIsArray($conversations);
        $this->assertCount(1, $conversations);
    }

    /** @test */
    public function it_can_normalize_phone_numbers()
    {
        $service = new class extends WhatsAppService {
            public function normalizePhoneNumber(string $phoneNumber): string
            {
                return parent::normalizePhoneNumber($phoneNumber);
            }
        };

        $this->assertEquals('1234567890', $service->normalizePhoneNumber('+1234567890'));
        $this->assertEquals('1234567890', $service->normalizePhoneNumber('+1-234-567-890'));
        $this->assertEquals('1234567890', $service->normalizePhoneNumber('01234567890'));
    }

    /** @test */
    public function it_handles_expired_token_gracefully()
    {
        config([
            'whatsapp-chat.use_mock_mode' => false,
            'whatsapp-chat.auto_mock_on_token_expiry' => true,
            'whatsapp-chat.access_token' => 'expired_token'
        ]);

        $result = $this->whatsappService->sendTextMessage('1234567890', 'Test message');

        $this->assertTrue($result['success']);
        $this->assertStringStartsWith('mock_', $result['message_id']);
    }
}
