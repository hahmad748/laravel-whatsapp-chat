<?php

/**
 * Package Validation Script
 * Validates the Laravel WhatsApp Chat package structure and files
 */

echo "🔍 Validating Laravel WhatsApp Chat Package...\n\n";

$errors = [];
$warnings = [];

// Check required directories
$requiredDirs = [
    'src/Console/Commands',
    'src/Http/Controllers',
    'src/Services',
    'src/Models',
    'src/Events',
    'src/stubs',
    'resources/js/Components/Chat',
    'resources/js/Pages/Chat',
    'resources/js/Pages/Profile',
    'resources/views/chat',
    'resources/views/profile',
    'database/migrations',
    'config'
];

foreach ($requiredDirs as $dir) {
    if (!is_dir($dir)) {
        $errors[] = "Missing directory: $dir";
    }
}

// Check required files
$requiredFiles = [
    'src/Console/Commands/InstallWhatsAppChatCommand.php',
    'src/Http/Controllers/ChatController.php',
    'src/Http/Controllers/WhatsAppVerificationController.php',
    'src/Http/Controllers/WhatsAppWebhookController.php',
    'src/Services/WhatsAppService.php',
    'src/Models/WhatsAppMessage.php',
    'src/Events/WhatsAppMessageReceived.php',
    'src/Events/WhatsAppMessageSent.php',
    'src/WhatsAppChatServiceProvider.php',
    'resources/js/Components/Chat/ChatSidebar.vue',
    'resources/js/Components/Chat/ChatHeader.vue',
    'resources/js/Components/Chat/ChatMessages.vue',
    'resources/js/Components/Chat/ChatInput.vue',
    'resources/js/Components/Chat/UserBanner.vue',
    'resources/js/Components/Chat/AssignNumberModal.vue',
    'resources/js/Pages/Chat/Index.vue',
    'resources/js/Pages/Profile/WhatsAppVerification.vue',
    'resources/views/chat/index.blade.php',
    'resources/views/profile/whatsapp-verification.blade.php',
    'src/stubs/MessageReceivedNotification.stub',
    'src/stubs/MessageSentNotification.stub',
    'src/stubs/WhatsAppChannel.stub',
    'src/routes/whatsapp-routes.php',
    'config/whatsapp-chat.php',
    'composer.json',
    'README.md',
    'INSTALLATION.md',
    'PACKAGE_SUMMARY.md'
];

foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        $errors[] = "Missing file: $file";
    }
}

// Check Vue components for required functionality
$vueComponents = [
    'resources/js/Components/Chat/ChatSidebar.vue' => ['registeredConversations', 'externalConversations'],
    'resources/js/Pages/Chat/Index.vue' => ['registeredConversations', 'externalConversations']
];

foreach ($vueComponents as $component => $requiredFeatures) {
    if (file_exists($component)) {
        $content = file_get_contents($component);

        foreach ($requiredFeatures as $feature) {
            if (strpos($content, $feature) === false) {
                $warnings[] = "Vue component $component may be missing $feature functionality";
            }
        }
    }
}

// Check AssignNumberModal specifically
if (file_exists('resources/js/Components/Chat/AssignNumberModal.vue')) {
    $content = file_get_contents('resources/js/Components/Chat/AssignNumberModal.vue');
    if (strpos($content, 'assign-number') === false) {
        $warnings[] = "AssignNumberModal component may be missing assign functionality";
    }
}

// Check Blade templates
$bladeTemplates = [
    'resources/views/chat/index.blade.php' => ['Registered Users', 'External Numbers']
];

foreach ($bladeTemplates as $template => $requiredSections) {
    if (file_exists($template)) {
        $content = file_get_contents($template);

        foreach ($requiredSections as $section) {
            if (strpos($content, $section) === false) {
                $warnings[] = "Blade template $template may be missing $section section";
            }
        }
    }
}

// Check service provider for installation command
if (file_exists('src/WhatsAppChatServiceProvider.php')) {
    $content = file_get_contents('src/WhatsAppChatServiceProvider.php');
    if (strpos($content, 'InstallWhatsAppChatCommand') === false) {
        $errors[] = "Service provider missing installation command registration";
    }
}

// Check composer.json
if (file_exists('composer.json')) {
    $composer = json_decode(file_get_contents('composer.json'), true);

    if (!isset($composer['name']) || $composer['name'] !== 'devsfort/laravel-whatsapp-chat') {
        $errors[] = "Invalid package name in composer.json";
    }

    if (!isset($composer['autoload']['psr-4']['DevsFort\\LaravelWhatsappChat\\'])) {
        $errors[] = "Missing PSR-4 autoloading in composer.json";
    }
}

// Display results
if (empty($errors) && empty($warnings)) {
    echo "✅ Package validation successful!\n";
    echo "🎉 All required files and directories are present.\n";
    echo "🚀 Package is ready for distribution.\n\n";

    echo "📦 Package Features:\n";
    echo "   • Vue.js and Blade template support\n";
    echo "   • Installation command with template selection\n";
    echo "   • Separated admin conversation sections\n";
    echo "   • External number assignment functionality\n";
    echo "   • Real-time messaging with Laravel Broadcasting\n";
    echo "   • WhatsApp verification system\n";
    echo "   • Notification system\n";
    echo "   • Comprehensive documentation\n\n";

    echo "🔧 Installation:\n";
    echo "   composer require devsfort/laravel-whatsapp-chat\n";
    echo "   php artisan whatsapp-chat:install\n\n";

    exit(0);
} else {
    if (!empty($errors)) {
        echo "❌ Errors found:\n";
        foreach ($errors as $error) {
            echo "   • $error\n";
        }
        echo "\n";
    }

    if (!empty($warnings)) {
        echo "⚠️  Warnings:\n";
        foreach ($warnings as $warning) {
            echo "   • $warning\n";
        }
        echo "\n";
    }

    echo "🔧 Please fix the issues above before distributing the package.\n";
    exit(1);
}
