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
        ], 'config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'migrations');

        // Publish Vue components
        $this->publishes([
            __DIR__ . '/../resources/js/Components/Chat' => resource_path('js/Components/Chat'),
            __DIR__ . '/../resources/js/Pages/Chat' => resource_path('js/Pages/Chat'),
            __DIR__ . '/../resources/js/Pages/Profile' => resource_path('js/Pages/Profile'),
        ], 'vue-components');

        // Publish Blade views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/whatsapp-chat'),
        ], 'blade-views');

        // Publish JavaScript files
        $this->publishes([
            __DIR__ . '/../resources/js' => resource_path('js/vendor/whatsapp-chat'),
        ], 'js');

        // Publish CSS and JS assets
        $this->publishes([
            __DIR__ . '/../resources/css' => public_path('vendor/whatsapp-chat/css'),
            __DIR__ . '/../resources/js' => public_path('vendor/whatsapp-chat/js'),
        ], 'assets');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Register routes
        $this->registerRoutes();

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \DevsFort\LaravelWhatsappChat\Console\Commands\WhatsAppTokenStatus::class,
                \DevsFort\LaravelWhatsappChat\Console\Commands\InstallWhatsAppChatCommand::class,
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
                Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
                Route::get('/chat/messages/{conversationId}', [ChatController::class, 'getMessages'])
                    ->name('chat.messages');
                Route::post('/chat/send', [ChatController::class, 'sendMessage'])
                    ->name('chat.send');
                Route::get('/chat/conversations', [ChatController::class, 'getConversations'])
                    ->name('chat.conversations');
            });

            // Verification routes
            Route::middleware(['auth'])->group(function () {
                Route::get('/verification', [WhatsAppVerificationController::class, 'show'])
                    ->name('whatsapp.verification.show');
                Route::post('/verify/send-code', [WhatsAppVerificationController::class, 'sendVerificationCode'])
                    ->name('whatsapp.verification.send');
                Route::post('/verify/verify-code', [WhatsAppVerificationController::class, 'verifyCode'])
                    ->name('whatsapp.verification.verify');
                Route::post('/verify/remove', [WhatsAppVerificationController::class, 'removeWhatsApp'])
                    ->name('whatsapp.verification.remove');
            });

            // Broadcasting authentication
            Route::post('/broadcasting/auth', [BroadcastingAuthController::class, 'authenticate'])
                ->middleware(['auth:web'])
                ->name('whatsapp.broadcasting.auth');
        });
    }
}
