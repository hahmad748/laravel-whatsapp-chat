<?php

/**
 * Manual fix script for notification directory creation
 * Run this if the installation command fails to create notification files
 */

echo "🔧 Fixing notification directory structure...\n\n";

// Create necessary directories
$directories = [
    app_path('Notifications'),
    app_path('Notifications/Channels'),
];

foreach ($directories as $directory) {
    if (!is_dir($directory)) {
        if (mkdir($directory, 0755, true)) {
            echo "✅ Created directory: $directory\n";
        } else {
            echo "❌ Failed to create directory: $directory\n";
        }
    } else {
        echo "📁 Directory already exists: $directory\n";
    }
}

echo "\n🎉 Directory structure fixed!\n";
echo "You can now run the installation command again.\n";
