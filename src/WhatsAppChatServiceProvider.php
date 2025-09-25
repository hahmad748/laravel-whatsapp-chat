<?php

namespace DevsFort\LaravelWhatsappChat;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use DevsFort\LaravelWhatsappChat\Services\WhatsAppService;
use DevsFort\LaravelWhatsappChat\Http\Controllers\WhatsAppWebhookController;
use DevsFort\LaravelWhatsappChat\Http\Controllers\ChatController;
use DevsFort\LaravelWhatsappChat\Http\Controllers\WhatsAppVerificationController;
use DevsFort\LaravelWhatsappChat\Http\Controllers\BroadcastingAuthController;

class WhatsAppChatServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge package config
        $this->mergeConfigFrom(__DIR__ . '/../config/whatsapp-chat.php', 'whatsapp-chat');

        // Register WhatsApp service as singleton
        $this->app->singleton(WhatsAppService::class, function ($app) {
            return new WhatsAppService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config file
        $this->publishes([
            __DIR__ . '/../config/whatsapp-chat.php' => config_path('whatsapp-chat.php'),
        ], 'whatsapp-chat-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'whatsapp-chat-migrations');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/whatsapp-chat'),
        ], 'whatsapp-chat-views');

        // Publish Vue components
        $this->publishes([
            __DIR__ . '/../resources/js' => resource_path('js/vendor/whatsapp-chat'),
        ], 'whatsapp-chat-assets');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'whatsapp-chat');

        // Register routes
        $this->registerRoutes();

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \DevsFort\LaravelWhatsappChat\Console\Commands\WhatsAppTokenStatus::class,
            ]);
        }
    }

    /**
     * Register package routes
     */
    protected function registerRoutes(): void
    {
        Route::group([
            'prefix' => config('whatsapp-chat.route_prefix', 'whatsapp'),
            'middleware' => config('whatsapp-chat.middleware', ['web']),
        ], function () {
            // Webhook routes (no CSRF protection)
            Route::post('/webhook', [WhatsAppWebhookController::class, 'webhook'])
                ->name('whatsapp.webhook');
            Route::get('/webhook', [WhatsAppWebhookController::class, 'verify'])
                ->name('whatsapp.webhook.verify');

            // Chat routes
            Route::middleware(['auth'])->group(function () {
                Route::get('/chat', [ChatController::class, 'index'])->name('whatsapp.chat.index');
                Route::get('/chat/messages/{conversationId}', [ChatController::class, 'getMessages'])
                    ->name('whatsapp.chat.messages');
                Route::post('/chat/send', [ChatController::class, 'sendMessage'])
                    ->name('whatsapp.chat.send');
            });

            // Verification routes
            Route::middleware(['auth'])->group(function () {
                Route::post('/verify/send-code', [WhatsAppVerificationController::class, 'sendVerificationCode'])
                    ->name('whatsapp.verify.send-code');
                Route::post('/verify/verify-code', [WhatsAppVerificationController::class, 'verifyCode'])
                    ->name('whatsapp.verify.verify-code');
            });

            // Broadcasting authentication
            Route::post('/broadcasting/auth', [BroadcastingAuthController::class, 'authenticate'])
                ->middleware(['auth:web'])
                ->name('whatsapp.broadcasting.auth');
        });
    }
}
