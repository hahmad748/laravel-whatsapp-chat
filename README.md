# Laravel WhatsApp Chat Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hahmad748/laravel-whatsapp-chat.svg?style=flat-square)](https://packagist.org/packages/hahmad748/laravel-whatsapp-chat)
[![Total Downloads](https://img.shields.io/packagist/dt/hahmad748/laravel-whatsapp-chat.svg?style=flat-square)](https://packagist.org/packages/hahmad748/laravel-whatsapp-chat)
[![License](https://img.shields.io/packagist/l/hahmad748/laravel-whatsapp-chat.svg?style=flat-square)](https://packagist.org/packages/hahmad748/laravel-whatsapp-chat)
[![Tests](https://github.com/hahmad748/laravel-whatsapp-chat/workflows/Tests/badge.svg)](https://github.com/devsfort/laravel-whatsapp-chat/actions)

A comprehensive WhatsApp Business Cloud API integration package for Laravel with real-time chat functionality, user verification, and broadcasting support.

## ✨ Features

- 🚀 **WhatsApp Business Cloud API Integration**
- 💬 **Real-time Chat Interface** with Vue.js components
- 🔐 **User Verification System** for WhatsApp numbers
- 📡 **Broadcasting Support** with Pusher
- 🎭 **Mock Mode** for development and testing
- 🔄 **Automatic Token Expiry Handling**
- 📱 **Responsive UI** matching WhatsApp design
- 🛡️ **CSRF Protection** and security features
- 📊 **Message Status Tracking** (sent, delivered, read)
- 👥 **Multi-user Support** with conversation management

## 🚀 Quick Start

### Installation

```bash
composer require devsfort/laravel-whatsapp-chat
```

### Publish Configuration

```bash
php artisan vendor:publish --provider="DevsFort\LaravelWhatsappChat\WhatsAppChatServiceProvider" --tag="whatsapp-chat-config"
```

### Publish Migrations

```bash
php artisan vendor:publish --provider="DevsFort\LaravelWhatsappChat\WhatsAppChatServiceProvider" --tag="whatsapp-chat-migrations"
```

### Run Migrations

```bash
php artisan migrate
```

### Add User Fields

Add WhatsApp fields to your users table:

```php
// Migration
Schema::table('users', function (Blueprint $table) {
    $table->string('whatsapp_number')->nullable();
    $table->boolean('whatsapp_verified')->default(false);
    $table->timestamp('whatsapp_verified_at')->nullable();
    $table->string('whatsapp_verification_code')->nullable();
    $table->string('type')->default('user');
});
```

### Update User Model

```php
// app/Models/User.php
protected $fillable = [
    'name', 'email', 'password',
    'whatsapp_number', 'whatsapp_verified', 
    'whatsapp_verified_at', 'whatsapp_verification_code', 'type'
];
```

### Configure Environment

```env
# WhatsApp Business Cloud API
WHATSAPP_ACCESS_TOKEN=your_access_token_here
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id
WHATSAPP_WEBHOOK_VERIFY_TOKEN=your_webhook_verify_token

# Package Settings
WHATSAPP_USE_MOCK_MODE=true
WHATSAPP_BROADCASTING_ENABLED=true

# Broadcasting
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_app_key
PUSHER_APP_SECRET=your_pusher_secret
PUSHER_APP_CLUSTER=your_pusher_cluster
```

## 📖 Documentation

- [Installation Guide](INSTALLATION.md)
- [Package Summary](PACKAGE_SUMMARY.md)
- [Contributing](CONTRIBUTING.md)
- [Changelog](CHANGELOG.md)

## 🎯 Usage

### Basic Chat Interface

The package provides a complete chat interface that can be accessed at `/whatsapp/chat`:

- **Admin users**: Can send messages to verified users
- **Verified users**: Can view messages from admin
- **Unverified users**: Redirected to verification page

### Send Messages Programmatically

```php
use DevsFort\LaravelWhatsappChat\Services\WhatsAppService;

$whatsappService = app(WhatsAppService::class);

// Send a text message
$result = $whatsappService->sendTextMessage('923134167555', 'Hello from Laravel!');

if ($result['success']) {
    echo "Message sent successfully!";
} else {
    echo "Error: " . $result['message'];
}
```

### Check Token Status

```bash
php artisan whatsapp:token-status
```

## 🔧 Configuration

### Custom User Model

```php
// config/whatsapp-chat.php
'user_model' => 'App\\Models\\CustomUser',
```

### Custom Routes

```php
// config/whatsapp-chat.php
'route_prefix' => 'whatsapp', // Change this
```

### Custom Middleware

```php
// config/whatsapp-chat.php
'middleware' => ['web', 'auth', 'custom-middleware'],
```

## 🧪 Testing

```bash
# Run tests
composer test

# Run with coverage
composer test-coverage
```

## 📋 Requirements

- PHP 8.1+
- Laravel 10+ or 11+
- Vue.js 3.0+ (for frontend components)
- Pusher (for broadcasting)
- Inertia.js (for Vue components)

## 🔒 Security

- CSRF protection on all routes
- Webhook verification
- User authentication required
- Phone number verification
- Secure broadcasting channels

## 🚀 Production Setup

1. Get real WhatsApp Business API credentials
2. Set `WHATSAPP_USE_MOCK_MODE=false`
3. Configure webhook URL
4. Set up queue worker
5. Configure broadcasting

## 🤝 Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## 📄 License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## 🔧 Troubleshooting

### Common Issues

**Error: "Cannot read properties of undefined (reading 'type')"**
- **Cause**: The `type` field is missing from your User model
- **Solution**: Add the `type` field to your users table migration and User model
- **Steps**:
  1. Run: `php artisan make:migration add_whatsapp_fields_to_users_table`
  2. Add `$table->string('type')->default('user');` to the migration
  3. Add `'type'` to the `$fillable` array in your User model
  4. Run: `php artisan migrate`

**Error: "Class 'App\Http\Controllers\Controller' not found"**
- **Cause**: Missing base Controller class
- **Solution**: Create the base Controller class or update the package controllers

**Error: "Route not found"**
- **Cause**: Routes not properly registered
- **Solution**: Clear route cache: `php artisan route:clear`

## 🆘 Support

- [GitHub Issues](https://github.com/hahmad748/laravel-whatsapp-chat/issues)
- [Documentation](https://github.com/hahmad748/laravel-whatsapp-chat/wiki)
- [Discussions](https://github.com/hahmad748/laravel-whatsapp-chat/discussions)

## 🙏 Credits

- [Haseeb Ahmad](https://github.com/hahmad748) - Author
- [DevsFort](https://devsfort.com) - Company
- [Laravel](https://laravel.com) - Framework
- [WhatsApp Business API](https://developers.facebook.com/docs/whatsapp) - API

## 📊 Stats

![GitHub stars](https://img.shields.io/github/stars/hahmad748/laravel-whatsapp-chat?style=social)
![GitHub forks](https://img.shields.io/github/forks/hahmad748/laravel-whatsapp-chat?style=social)
![GitHub watchers](https://img.shields.io/github/watchers/hahmad748/laravel-whatsapp-chat?style=social)
