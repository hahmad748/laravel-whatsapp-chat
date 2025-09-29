<?php

/**
 * Test script to verify User model has required WhatsApp fields
 * Run this in your Laravel project: php artisan tinker --execute="require 'vendor/devsfort/laravel-whatsapp-chat/test-user-model.php';"
 */

echo "🔍 Testing User model for WhatsApp fields...\n\n";

try {
    $user = \App\Models\User::first();

    if (!$user) {
        echo "❌ No users found in database. Please create a user first.\n";
        exit(1);
    }

    echo "✅ User found: {$user->name} (ID: {$user->id})\n\n";

    $requiredFields = [
        'whatsapp_number',
        'whatsapp_verified',
        'whatsapp_verified_at',
        'whatsapp_verification_code',
        'type'
    ];

    $missingFields = [];
    $fillableFields = $user->getFillable();

    echo "📋 Checking required fields:\n";
    foreach ($requiredFields as $field) {
        if (in_array($field, $fillableFields)) {
            echo "✅ {$field} - in fillable array\n";
        } else {
            echo "❌ {$field} - MISSING from fillable array\n";
            $missingFields[] = $field;
        }
    }

    echo "\n📋 Checking database columns:\n";
    $tableColumns = \Schema::getColumnListing('users');
    foreach ($requiredFields as $field) {
        if (in_array($field, $tableColumns)) {
            echo "✅ {$field} - exists in database\n";
        } else {
            echo "❌ {$field} - MISSING from database\n";
            $missingFields[] = $field;
        }
    }

    if (empty($missingFields)) {
        echo "\n🎉 All required fields are present!\n";
        echo "✅ User model is ready for WhatsApp verification.\n";
    } else {
        echo "\n❌ Missing fields: " . implode(', ', array_unique($missingFields)) . "\n";
        echo "\n🔧 To fix this:\n";
        echo "1. Add missing fields to your User model's \$fillable array\n";
        echo "2. Run migration: php artisan make:migration add_whatsapp_fields_to_users_table\n";
        echo "3. Add the fields to your migration and run: php artisan migrate\n";
    }

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Make sure you're running this in a Laravel project with the package installed.\n";
}
