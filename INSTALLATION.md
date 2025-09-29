# Installation Guide

This guide will walk you through installing the Laravel WhatsApp Chat package with either Vue.js or Blade templates.

## Prerequisites

- Laravel 9.0 or higher
- PHP 8.1 or higher
- Composer
- Node.js and NPM (for Vue.js templates)
- WhatsApp Business Cloud API credentials

## Step 1: Install the Package

```bash
composer require devsfort/laravel-whatsapp-chat
```

## Step 2: Run the Installation Command

```bash
php artisan whatsapp-chat:install
```

The installation command will ask you to choose between two template options:

### Option 1: Vue.js Templates (Recommended)
- Modern, reactive interface
- Real-time updates
- Better user experience
- Requires Inertia.js setup

### Option 2: Blade Templates
- Traditional server-side rendering
- No additional dependencies
- Simpler setup
- Good for existing projects

## Step 3: Configure Environment Variables

Add these variables to your `.env` file:

```env
# WhatsApp API Configuration
WHATSAPP_ACCESS_TOKEN=your_access_token_here
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id
WHATSAPP_WEBHOOK_VERIFY_TOKEN=your_webhook_verify_token
WHATSAPP_BASE_URL=https://graph.facebook.com
WHATSAPP_API_VERSION=v18.0
WHATSAPP_ADMIN_PHONE_NUMBER=1234567890

# Broadcasting (for real-time features)
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=your_cluster
```

## Step 4: Update Your User Model

### Add Required Fields to Migration

Create a new migration:

```bash
php artisan make:migration add_whatsapp_fields_to_users_table
```

Add these fields to your migration:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('whatsapp_number')->nullable();
            $table->boolean('whatsapp_verified')->default(false);
            $table->timestamp('whatsapp_verified_at')->nullable();
            $table->enum('type', ['admin', 'user'])->default('user');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_number', 'whatsapp_verified', 'whatsapp_verified_at', 'type']);
        });
    }
};
```

### Update User Model

Add these fields to your User model's `$fillable` array:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'whatsapp_number',        // REQUIRED
        'whatsapp_verified',      // REQUIRED
        'whatsapp_verified_at',   // REQUIRED
        'type',                   // REQUIRED
    ];

    // ... rest of your model
}
```

## Step 5: Run Migrations

```bash
php artisan migrate
```

## Step 6: Set Up Broadcasting (Vue.js Only)

If you chose Vue.js templates, you need to set up broadcasting for real-time features:

### Install Pusher

```bash
composer require pusher/pusher-php-server
npm install pusher-js
```

### Configure Broadcasting

The package will automatically configure broadcasting. Make sure your `.env` has the correct Pusher credentials.

## Step 7: Add Navigation Links

### For Vue.js Templates

Add to your navigation component:

```vue
<template>
    <nav>
        <!-- Other navigation items -->
        <Link href="/chat" class="nav-link">
            WhatsApp Chat
        </Link>
    </nav>
</template>
```

### For Blade Templates

Add to your navigation:

```blade
<nav>
    <!-- Other navigation items -->
    <a href="/chat" class="nav-link">WhatsApp Chat</a>
</nav>
```

## Step 8: Set Up WhatsApp Webhook

1. Go to your WhatsApp Business Cloud API dashboard
2. Set webhook URL to: `https://yourdomain.com/webhook/whatsapp`
3. Use the verify token from your `.env` file
4. Subscribe to `messages` events

## Step 9: Test the Installation

1. Start your Laravel server: `php artisan serve`
2. Visit `/chat` in your browser
3. Try the WhatsApp verification process
4. Test sending messages (in mock mode)

## Post-Installation Setup

### Create Admin User

Create an admin user with the `type` field set to `admin`:

```php
User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => Hash::make('password'),
    'type' => 'admin',
    'whatsapp_number' => '1234567890',
    'whatsapp_verified' => true,
    'whatsapp_verified_at' => now(),
]);
```

### Configure Notifications

The package creates example notification classes. You can customize them or create your own.

### Customize Templates

- **Vue.js**: Edit files in `resources/js/Components/Chat/` and `resources/js/Pages/Chat/`
- **Blade**: Edit files in `resources/views/vendor/whatsapp-chat/`

## Troubleshooting

### Common Issues

1. **"Cannot read properties of undefined (reading 'type')"**
   - Ensure the `type` field is added to your users table
   - Make sure it's in the User model's `$fillable` array

2. **Vue components not loading**
   - Make sure Inertia.js is properly installed
   - Check that the components are published correctly

3. **Real-time features not working**
   - Verify your Pusher configuration
   - Check browser console for JavaScript errors
   - Ensure broadcasting is enabled

4. **WhatsApp messages not sending**
   - Check your API credentials
   - Verify you're not in mock mode
   - Review the application logs

### Getting Help

If you encounter issues:

1. Check the logs in `storage/logs/laravel.log`
2. Enable debug mode in your configuration
3. Review the troubleshooting section in the README
4. Open an issue on GitHub

## Next Steps

After installation:

1. Configure your WhatsApp API credentials
2. Set up your webhook
3. Test the chat functionality
4. Customize the templates to match your design
5. Set up notifications for your use case

## Uninstallation

To remove the package:

1. Remove the package: `composer remove devsfort/laravel-whatsapp-chat`
2. Remove published files (config, views, components)
3. Remove the database fields if no longer needed
4. Remove webhook configuration

---

For more detailed information, see the [README.md](README.md) file.
