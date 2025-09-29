<?php

/**
 * Test script to verify installation command directory creation
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\File;

echo "🧪 Testing directory creation logic...\n\n";

// Test directory creation function
function createFile($path, $content) {
    if (!File::exists($path)) {
        // Ensure directory exists
        $directory = dirname($path);
        if (!File::exists($directory)) {
            echo "Creating directory: $directory\n";
            File::makeDirectory($directory, 0755, true);
        }
        echo "Creating file: $path\n";
        File::put($path, $content);
        return true;
    }
    echo "File already exists: $path\n";
    return false;
}

// Test paths
$testPaths = [
    '/tmp/test-notifications/MessageReceivedNotification.php',
    '/tmp/test-notifications/MessageSentNotification.php',
    '/tmp/test-notifications/Channels/WhatsAppChannel.php',
];

echo "Testing file creation with directory auto-creation:\n";
foreach ($testPaths as $path) {
    createFile($path, "<?php\n// Test file\n");
}

echo "\n✅ Directory creation test completed!\n";
echo "Check /tmp/test-notifications/ for created files.\n";
