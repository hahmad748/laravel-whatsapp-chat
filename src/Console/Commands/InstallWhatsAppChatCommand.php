<?php

namespace DevsFort\LaravelWhatsappChat\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class InstallWhatsAppChatCommand extends Command
{
    protected $signature = 'whatsapp-chat:install {--template= : Template type (vue or blade)}';
    protected $description = 'Install Laravel WhatsApp Chat package with Vue or Blade templates';

    public function handle()
    {
        $this->info('🚀 Installing Laravel WhatsApp Chat Package...');
        $this->newLine();

        // Ask for template preference if not provided
        $template = $this->option('template');
        if (!$template) {
            $template = $this->choice(
                'Which template would you like to use?',
                ['vue' => 'Vue.js with Inertia.js (Recommended)', 'blade' => 'Blade Templates'],
                'vue'
            );
        }

        $this->info("📦 Installing with {$template} templates...");
        $this->newLine();

        // Publish configuration
        $this->info('📋 Publishing configuration...');
        Artisan::call('vendor:publish', [
            '--provider' => 'DevsFort\LaravelWhatsappChat\WhatsAppChatServiceProvider',
            '--tag' => 'config'
        ]);
        $this->info('✅ Configuration published');

        // Publish migrations
        $this->info('🗄️ Publishing migrations...');
        Artisan::call('vendor:publish', [
            '--provider' => 'DevsFort\LaravelWhatsappChat\WhatsAppChatServiceProvider',
            '--tag' => 'migrations'
        ]);
        $this->info('✅ Migrations published');

        // Publish assets based on template choice
        if ($template === 'vue') {
            $this->installVueTemplates();
        } else {
            $this->installBladeTemplates();
        }

        // Run migrations
        $this->info('🔄 Running migrations...');
        Artisan::call('migrate');
        $this->info('✅ Migrations completed');

        // Publish routes
        $this->info('🛣️ Publishing routes...');
        $this->publishRoutes();
        $this->info('✅ Routes published');

        // Create example notification classes
        $this->info('🔔 Creating example notification classes...');
        $this->createExampleNotifications();
        $this->info('✅ Example notifications created');

        $this->newLine();
        $this->info('🎉 Laravel WhatsApp Chat Package installed successfully!');
        $this->newLine();

        $this->displayNextSteps($template);
    }

    protected function installVueTemplates()
    {
        $this->info('⚛️ Installing Vue.js templates...');

        // Publish Vue components
        Artisan::call('vendor:publish', [
            '--provider' => 'DevsFort\LaravelWhatsappChat\WhatsAppChatServiceProvider',
            '--tag' => 'vue-components'
        ]);

        // Publish JavaScript files
        Artisan::call('vendor:publish', [
            '--provider' => 'DevsFort\LaravelWhatsappChat\WhatsAppChatServiceProvider',
            '--tag' => 'js'
        ]);

        $this->info('✅ Vue.js templates installed');
    }

    protected function installBladeTemplates()
    {
        $this->info('🔧 Installing Blade templates...');

        // Publish Blade views
        Artisan::call('vendor:publish', [
            '--provider' => 'DevsFort\LaravelWhatsappChat\WhatsAppChatServiceProvider',
            '--tag' => 'blade-views'
        ]);

        // Publish CSS and JS assets
        Artisan::call('vendor:publish', [
            '--provider' => 'DevsFort\LaravelWhatsappChat\WhatsAppChatServiceProvider',
            '--tag' => 'assets'
        ]);

        $this->info('✅ Blade templates installed');
    }

    protected function publishRoutes()
    {
        $routesPath = base_path('routes/web.php');
        $whatsappRoutes = file_get_contents(__DIR__ . '/../../routes/whatsapp-routes.php');

        // Check if routes already exist
        if (strpos(file_get_contents($routesPath), 'whatsapp-chat') !== false) {
            $this->warn('⚠️ WhatsApp Chat routes already exist in web.php');
            return;
        }

        // Append routes to web.php
        file_put_contents($routesPath, "\n\n" . $whatsappRoutes, FILE_APPEND);
    }

    protected function createExampleNotifications()
    {
        $notificationsPath = app_path('Notifications');
        if (!File::exists($notificationsPath)) {
            File::makeDirectory($notificationsPath, 0755, true);
        }

        // Create example notification classes
        $this->createFile(
            app_path('Notifications/MessageReceivedNotification.php'),
            file_get_contents(__DIR__ . '/../../stubs/MessageReceivedNotification.stub')
        );

        $this->createFile(
            app_path('Notifications/MessageSentNotification.php'),
            file_get_contents(__DIR__ . '/../../stubs/MessageSentNotification.stub')
        );

        $this->createFile(
            app_path('Notifications/Channels/WhatsAppChannel.php'),
            file_get_contents(__DIR__ . '/../../stubs/WhatsAppChannel.stub')
        );
    }

    protected function createFile($path, $content)
    {
        if (!File::exists($path)) {
            File::put($path, $content);
        }
    }

    protected function displayNextSteps($template)
    {
        $this->info('📋 Next Steps:');
        $this->newLine();

        $this->line('1. Configure your WhatsApp API credentials in <comment>config/whatsapp-chat.php</comment>');
        $this->line('2. Add the required fields to your User model:');
        $this->line('   - whatsapp_number (string)');
        $this->line('   - whatsapp_verified (boolean)');
        $this->line('   - whatsapp_verified_at (timestamp)');
        $this->line('   - type (enum: admin, user)');
        $this->newLine();

        if ($template === 'vue') {
            $this->line('3. Install and configure Inertia.js if not already done:');
            $this->line('   <comment>composer require inertiajs/inertia-laravel</comment>');
            $this->line('   <comment>npm install @inertiajs/vue3</comment>');
            $this->line('   <comment>php artisan inertia:middleware</comment>');
            $this->newLine();
            $this->line('4. Add the chat route to your navigation:');
            $this->line('   <comment>&lt;Link href="/chat"&gt;WhatsApp Chat&lt;/Link&gt;</comment>');
        } else {
            $this->line('3. Add the chat route to your navigation:');
            $this->line('   <comment>&lt;a href="/chat"&gt;WhatsApp Chat&lt;/a&gt;</comment>');
        }

        $this->newLine();
        $this->line('5. Set up your WhatsApp webhook URL:');
        $this->line('   <comment>https://yourdomain.com/webhook/whatsapp</comment>');
        $this->newLine();
        $this->line('6. Run <comment>php artisan serve</comment> and visit <comment>/chat</comment> to test!');
        $this->newLine();
        $this->info('📚 For more information, visit: https://github.com/devsfort/laravel-whatsapp-chat');
    }
}
