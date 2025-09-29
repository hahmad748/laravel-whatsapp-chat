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
        try {
            $this->createExampleNotifications();
            $this->info('✅ Example notifications created');
        } catch (\Exception $e) {
            $this->warn('⚠️ Warning: Could not create some notification files: ' . $e->getMessage());
            $this->newLine();
            $this->info('🔧 Manual fix options:');
            $this->line('1. Create directories manually:');
            $this->line('   mkdir -p app/Notifications/Channels');
            $this->line('2. Run the installation command again:');
            $this->line('   php artisan whatsapp-chat:install');
            $this->line('3. Or create the notification files manually from the stubs in the package.');
            $this->newLine();
        }

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

        // Check if web.php exists
        if (!File::exists($routesPath)) {
            $this->warn('⚠️ routes/web.php not found. Please create it manually and run the installation again.');
            return;
        }

        $whatsappRoutes = file_get_contents(__DIR__ . '/../../routes/whatsapp-routes-only.php');

        // Check if routes already exist
        $existingContent = file_get_contents($routesPath);
        if (strpos($existingContent, 'whatsapp-chat') !== false || strpos($existingContent, 'WhatsApp Chat Routes') !== false) {
            $this->warn('⚠️ WhatsApp Chat routes already exist in web.php');
            return;
        }

        // Validate that web.php ends with proper PHP syntax
        if (!preg_match('/\?>\s*$/', $existingContent) && !preg_match('/;\s*$/', trim($existingContent))) {
            $this->warn('⚠️ routes/web.php does not end with proper PHP syntax. Please check the file manually.');
        }

        // Append routes to web.php
        if (file_put_contents($routesPath, "\n\n" . $whatsappRoutes, FILE_APPEND) === false) {
            $this->error('❌ Failed to write routes to web.php. Please check file permissions.');
            return;
        }

        $this->info('✅ Routes added to web.php');
    }

    protected function createExampleNotifications()
    {
        $notificationsPath = app_path('Notifications');
        if (!File::exists($notificationsPath)) {
            File::makeDirectory($notificationsPath, 0755, true);
        }

        // Create Channels directory
        $channelsPath = app_path('Notifications/Channels');
        if (!File::exists($channelsPath)) {
            File::makeDirectory($channelsPath, 0755, true);
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

        // Verify files were created
        $files = [
            app_path('Notifications/MessageReceivedNotification.php'),
            app_path('Notifications/MessageSentNotification.php'),
            app_path('Notifications/Channels/WhatsAppChannel.php'),
        ];

        foreach ($files as $file) {
            if (!File::exists($file)) {
                throw new \Exception("Failed to create notification file: $file");
            }
        }
    }

    protected function createFile($path, $content)
    {
        if (!File::exists($path)) {
            // Ensure directory exists
            $directory = dirname($path);
            if (!File::exists($directory)) {
                if (!File::makeDirectory($directory, 0755, true)) {
                    throw new \Exception("Failed to create directory: $directory");
                }
            }

            if (!File::put($path, $content)) {
                throw new \Exception("Failed to create file: $path");
            }
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
        $this->line('   - whatsapp_verification_code (string, nullable)');
        $this->line('   - type (enum: admin, user)');
        $this->newLine();

        if ($template === 'vue') {
            $this->line('3. Install and configure Inertia.js if not already done:');
            $this->line('   <comment>composer require inertiajs/inertia-laravel</comment>');
            $this->line('   <comment>npm install @inertiajs/vue3</comment>');
            $this->line('   <comment>php artisan inertia:middleware</comment>');
            $this->newLine();
            $this->line('4. (Optional) Install Ziggy for route helper in JavaScript:');
            $this->line('   <comment>composer require tightenco/ziggy</comment>');
            $this->line('   <comment>npm install ziggy-js</comment>');
            $this->line('   Add to your app.js: <comment>import { Ziggy } from "ziggy-js"; window.route = (name, params, absolute, config) => Ziggy.route(name, params, absolute, config);</comment>');
            $this->newLine();
            $this->line('5. Add the chat route to your navigation:');
            $this->line('   <comment>&lt;Link href="/chat"&gt;WhatsApp Chat&lt;/Link&gt;</comment>');
        } else {
            $this->line('3. Add the chat route to your navigation:');
            $this->line('   <comment>&lt;a href="/chat"&gt;WhatsApp Chat&lt;/a&gt;</comment>');
        }

        $this->newLine();
        $this->line('6. Set up your WhatsApp webhook URL:');
        $this->line('   <comment>https://yourdomain.com/webhook/whatsapp</comment>');
        $this->newLine();
        $this->line('7. Run <comment>php artisan serve</comment> and visit <comment>/chat</comment> to test!');
        $this->newLine();
        $this->info('📚 For more information, visit: https://github.com/devsfort/laravel-whatsapp-chat');
    }
}
