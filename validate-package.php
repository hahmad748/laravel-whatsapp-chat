<?php

/**
 * Package Validation Script
 * Validates the package structure and files for Packagist submission
 */

echo "🔍 Validating Laravel WhatsApp Chat Package...\n\n";

$errors = [];
$warnings = [];

// Check required files
$requiredFiles = [
    'composer.json',
    'LICENSE',
    'README.md',
    'CHANGELOG.md',
    'CONTRIBUTING.md',
    '.gitignore',
    '.gitattributes',
    'src/WhatsAppChatServiceProvider.php',
    'config/whatsapp-chat.php',
    'database/migrations/',
    'resources/js/Pages/Chat/Index.vue',
    'resources/js/Pages/Profile/WhatsAppVerification.vue',
    'resources/js/index.js',
    'tests/Feature/WhatsAppServiceTest.php',
    'phpunit.xml',
    'package.json',
    'vite.config.js'
];

foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        $errors[] = "Missing required file: $file";
    }
}

// Check composer.json structure
if (file_exists('composer.json')) {
    $composer = json_decode(file_get_contents('composer.json'), true);

    if (!$composer) {
        $errors[] = "Invalid composer.json format";
    } else {
        // Check required fields
        $requiredFields = ['name', 'description', 'type', 'license', 'authors', 'require', 'autoload'];
        foreach ($requiredFields as $field) {
            if (!isset($composer[$field])) {
                $errors[] = "Missing required field in composer.json: $field";
            }
        }

        // Check package name format
        if (isset($composer['name']) && !preg_match('/^[a-z0-9][a-z0-9-]*\/[a-z0-9][a-z0-9-]*$/', $composer['name'])) {
            $errors[] = "Invalid package name format in composer.json";
        }

        // Check namespace consistency
        if (isset($composer['autoload']['psr-4'])) {
            $namespace = array_keys($composer['autoload']['psr-4'])[0];
            if ($namespace !== 'DevsFort\\LaravelWhatsappChat\\') {
                $errors[] = "Namespace mismatch in composer.json: $namespace";
            }
        }
    }
}

// Check namespace consistency in PHP files
$phpFiles = glob('src/**/*.php');
foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'namespace DevsFort\\LaravelWhatsappChat') === false) {
        $errors[] = "Namespace mismatch in $file";
    }
    if (strpos($content, 'YourVendor') !== false) {
        $errors[] = "Found 'YourVendor' in $file - should be 'DevsFort'";
    }
}

// Check for placeholder values
$placeholderFiles = ['README.md', 'INSTALLATION.md', 'PACKAGE_SUMMARY.md'];
foreach ($placeholderFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (strpos($content, 'your-vendor') !== false || strpos($content, 'YourVendor') !== false) {
            $warnings[] = "Found placeholder values in $file";
        }
    }
}

// Check file permissions
$executableFiles = ['example-setup.sh'];
foreach ($executableFiles as $file) {
    if (file_exists($file) && !is_executable($file)) {
        $warnings[] = "File $file should be executable";
    }
}

// Check for sensitive data
$sensitiveFiles = ['.env', '.env.example'];
foreach ($sensitiveFiles as $file) {
    if (file_exists($file)) {
        $warnings[] = "Sensitive file $file should not be in package";
    }
}

// Display results
if (empty($errors) && empty($warnings)) {
    echo "✅ Package validation passed!\n";
    echo "🎉 Ready for Packagist submission!\n";
} else {
    if (!empty($errors)) {
        echo "❌ Errors found:\n";
        foreach ($errors as $error) {
            echo "  - $error\n";
        }
        echo "\n";
    }

    if (!empty($warnings)) {
        echo "⚠️  Warnings:\n";
        foreach ($warnings as $warning) {
            echo "  - $warning\n";
        }
        echo "\n";
    }
}

// Package size check
$packageSize = getDirectorySize('.');
if ($packageSize > 10 * 1024 * 1024) { // 10MB
    $warnings[] = "Package size is large: " . formatBytes($packageSize);
}

echo "📦 Package size: " . formatBytes($packageSize) . "\n";

function getDirectorySize($directory) {
    $size = 0;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $size += $file->getSize();
        }
    }
    return $size;
}

function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    return round($size, $precision) . ' ' . $units[$i];
}

echo "\n🔗 Next steps:\n";
echo "1. Create GitHub repository\n";
echo "2. Push code to GitHub\n";
echo "3. Submit to Packagist\n";
echo "4. Set up GitHub Actions\n";
echo "5. Create releases\n";
