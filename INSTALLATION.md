# Installation Guide

## Fresh Laravel Project Setup

### Prerequisites

- PHP 8.1 or higher
- Composer
- Node.js and NPM
- MySQL/PostgreSQL/SQLite
- Pusher account (for real-time features)

### Step 1: Create New Laravel Project

```bash
# Create new Laravel project
composer create-project laravel/laravel my-whatsapp-app
cd my-whatsapp-app

# Install Inertia.js (required for Vue components)
composer require inertiajs/inertia-laravel
php artisan inertia:install --vue

# Install frontend dependencies
npm install
npm run build
```

### Step 2: Install WhatsApp Chat Package

```bash
composer require devsfort/laravel-whatsapp-chat
```

### Step 3: Publish Configuration

```bash
# Publish configuration file
php artisan vendor:publish --provider="DevsFort\LaravelWhatsappChat\WhatsAppChatServiceProvider" --tag="whatsapp-chat-config"
```

### Step 4: Publish Migrations

```bash
# Publish migrations
php artisan vendor:publish --provider="DevsFort\LaravelWhatsappChat\WhatsAppChatServiceProvider" --tag="whatsapp-chat-migrations"
```

### Step 5: Run Migrations

```bash
php artisan migrate
```

### Step 6: Add User Fields

Create a migration to add WhatsApp fields to your users table:

```bash
php artisan make:migration add_whatsapp_fields_to_users_table
```

Add this content to the migration file:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('whatsapp_number')->nullable();
            $table->boolean('whatsapp_verified')->default(false);
            $table->timestamp('whatsapp_verified_at')->nullable();
            $table->string('whatsapp_verification_code')->nullable();
            $table->string('type')->default('user'); // For admin functionality
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_number',
                'whatsapp_verified',
                'whatsapp_verified_at',
                'whatsapp_verification_code',
                'type'
            ]);
        });
    }
};
```

Run the migration:

```bash
php artisan migrate
```

### Step 7: Update User Model

Update your `app/Models/User.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'whatsapp_number',
        'whatsapp_verified',
        'whatsapp_verified_at',
        'whatsapp_verification_code',
        'type',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'whatsapp_verification_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'whatsapp_verified' => 'boolean',
        'whatsapp_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
```

### Step 8: Configure Environment

Add these variables to your `.env` file:

```env
# WhatsApp Business Cloud API
WHATSAPP_ACCESS_TOKEN=your_access_token_here
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id
WHATSAPP_WEBHOOK_VERIFY_TOKEN=your_webhook_verify_token
WHATSAPP_WEBHOOK_URL=https://yourdomain.com/whatsapp/webhook

# Package Settings
WHATSAPP_USE_MOCK_MODE=true
WHATSAPP_AUTO_MOCK_ON_TOKEN_EXPIRY=true
WHATSAPP_BROADCASTING_ENABLED=true
WHATSAPP_UI_ENABLED=true

# Broadcasting (for real-time updates)
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_app_key
PUSHER_APP_SECRET=your_pusher_secret
PUSHER_APP_CLUSTER=your_pusher_cluster
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_ENCRYPTED=true
```

### Step 9: Configure Broadcasting

Ensure your `config/broadcasting.php` includes Pusher configuration:

```php
'pusher' => [
    'driver' => 'pusher',
    'key' => env('PUSHER_APP_KEY'),
    'secret' => env('PUSHER_APP_SECRET'),
    'app_id' => env('PUSHER_APP_ID'),
    'options' => [
        'cluster' => env('PUSHER_APP_CLUSTER'),
        'useTLS' => true,
    ],
],
```

### Step 10: Add Navigation Links

Update your main layout file (e.g., `resources/js/Layouts/AppLayout.vue`):

```vue
<template>
    <div>
        <!-- Your existing layout -->
        
        <!-- Add WhatsApp Chat Link -->
        <nav>
            <!-- For admin users -->
            <Link v-if="user.type === 'admin'" :href="route('whatsapp.chat.index')" class="nav-link">
                <i class="fab fa-whatsapp"></i> Chat
            </Link>
            
            <!-- For verified users -->
            <Link v-else-if="user.whatsapp_verified" :href="route('whatsapp.chat.index')" class="nav-link">
                <i class="fab fa-whatsapp"></i> Chat
            </Link>
            
            <!-- For unverified users -->
            <Link v-else :href="route('whatsapp.verification.show')" class="nav-link">
                <i class="fab fa-whatsapp"></i> Verify WhatsApp
            </Link>
        </nav>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    user: Object
})
</script>
```

### Step 11: Start Queue Worker

```bash
php artisan queue:work
```

### Step 12: Test the Installation

```bash
# Check token status
php artisan whatsapp:token-status

# Start development server
php artisan serve
```

Visit your application and you should see the WhatsApp chat functionality!

## Existing Laravel Project Setup

If you already have a Laravel project:

### 1. Install Package

```bash
composer require devsfort/laravel-whatsapp-chat
```

### 2. Publish Configuration

```bash
php artisan vendor:publish --provider="DevsFort\LaravelWhatsappChat\WhatsAppChatServiceProvider" --tag="whatsapp-chat-config"
```

### 3. Publish Migrations

```bash
php artisan vendor:publish --provider="DevsFort\LaravelWhatsappChat\WhatsAppChatServiceProvider" --tag="whatsapp-chat-migrations"
```

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Add User Fields

Add the WhatsApp fields to your existing users table (see Step 6 above).

### 6. Update User Model

Update your User model to include the new fields (see Step 7 above).

### 7. Configure Environment

Add the environment variables (see Step 8 above).

## Customization

### Custom User Model

If you use a different user model:

```php
// config/whatsapp-chat.php
'user_model' => 'App\\Models\\CustomUser',
```

### Custom Routes

The package registers routes under `/whatsapp` by default. You can customize this:

```php
// config/whatsapp-chat.php
'route_prefix' => 'whatsapp', // Change this
```

### Custom Middleware

Add custom middleware to routes:

```php
// config/whatsapp-chat.php
'middleware' => ['web', 'auth', 'custom-middleware'],
```

## Testing

### 1. Test Token Status

```bash
php artisan whatsapp:token-status
```

### 2. Test in Mock Mode

The package automatically uses mock mode when:
- `WHATSAPP_USE_MOCK_MODE=true`
- Access token is not set
- Access token is expired

### 3. Test Real-time Updates

1. Open chat in two browser windows
2. Send a message from one window
3. Verify it appears in the other window

## Troubleshooting

### Common Issues

1. **Token Expired**: Package automatically switches to mock mode
2. **Broadcasting Not Working**: Check Pusher configuration
3. **Messages Not Appearing**: Check user verification status
4. **CSRF Errors**: Webhook routes are automatically excluded

### Debug Commands

```bash
# Check token status
php artisan whatsapp:token-status

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Check logs
tail -f storage/logs/laravel.log
```

## Production Setup

### 1. Get Real WhatsApp Credentials

1. Go to [Facebook Developer Console](https://developers.facebook.com/)
2. Create a WhatsApp Business app
3. Get your access token and phone number ID
4. Set up webhook URL

### 2. Update Environment

```env
WHATSAPP_USE_MOCK_MODE=false
WHATSAPP_ACCESS_TOKEN=your_real_access_token
WHATSAPP_PHONE_NUMBER_ID=your_real_phone_number_id
WHATSAPP_WEBHOOK_VERIFY_TOKEN=your_secure_webhook_token
```

### 3. Configure Webhook

Set your webhook URL in Facebook Developer Console:
```
https://yourdomain.com/whatsapp/webhook
```

### 4. Set Up Queue Worker

For production, use a process manager like Supervisor:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/app/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=8
redirect_stderr=true
stdout_logfile=/path/to/your/app/worker.log
stopwaitsecs=3600
```

## Support

For issues and questions:
1. Check the troubleshooting section
2. Review Laravel logs
3. Open an issue on GitHub
4. Contact the maintainers
