#!/bin/bash

# Laravel WhatsApp Chat Package - Example Setup Script
# This script sets up a fresh Laravel project with the WhatsApp chat package

echo "🚀 Setting up Laravel WhatsApp Chat Package Example..."

# Create new Laravel project
echo "📦 Creating new Laravel project..."
composer create-project laravel/laravel whatsapp-chat-example
cd whatsapp-chat-example

# Install Inertia.js
echo "🔧 Installing Inertia.js..."
composer require inertiajs/inertia-laravel
php artisan inertia:install --vue

# Install WhatsApp Chat Package
echo "💬 Installing WhatsApp Chat Package..."
composer require devsfort/laravel-whatsapp-chat

# Publish configuration
echo "⚙️ Publishing configuration..."
php artisan vendor:publish --provider="DevsFort\LaravelWhatsappChat\WhatsAppChatServiceProvider" --tag="whatsapp-chat-config"

# Publish migrations
echo "🗄️ Publishing migrations..."
php artisan vendor:publish --provider="DevsFort\LaravelWhatsappChat\WhatsAppChatServiceProvider" --tag="whatsapp-chat-migrations"

# Create user fields migration
echo "👤 Adding WhatsApp fields to users table..."
php artisan make:migration add_whatsapp_fields_to_users_table

# Add migration content
cat > database/migrations/$(ls database/migrations/*add_whatsapp_fields* | tail -1) << 'EOF'
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
            $table->string('type')->default('user');
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
EOF

# Run migrations
echo "🏃 Running migrations..."
php artisan migrate

# Install frontend dependencies
echo "📦 Installing frontend dependencies..."
npm install

# Create example .env configuration
echo "🔐 Creating example .env configuration..."
cat >> .env << 'EOF'

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
EOF

# Update User model
echo "👤 Updating User model..."
cat > app/Models/User.php << 'EOF'
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
EOF

# Build frontend assets
echo "🎨 Building frontend assets..."
npm run build

# Create admin user
echo "👑 Creating admin user..."
php artisan tinker --execute="
\$user = new App\Models\User();
\$user->name = 'Admin User';
\$user->email = 'admin@example.com';
\$user->password = bcrypt('password');
\$user->type = 'admin';
\$user->save();
echo 'Admin user created: admin@example.com / password';
"

echo "✅ Setup complete!"
echo ""
echo "🎉 Your Laravel WhatsApp Chat example is ready!"
echo ""
echo "Next steps:"
echo "1. Update your .env file with real WhatsApp credentials"
echo "2. Start the development server: php artisan serve"
echo "3. Start the queue worker: php artisan queue:work"
echo "4. Visit http://localhost:8000 and login with admin@example.com / password"
echo "5. Check token status: php artisan whatsapp:token-status"
echo ""
echo "Happy coding! 🚀"
