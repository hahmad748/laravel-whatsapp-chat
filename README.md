# Laravel WhatsApp Chat Package

A comprehensive WhatsApp Business Cloud API integration package for Laravel with real-time chat functionality, supporting both Vue.js and Blade templates.

## Features

- 🚀 **Real-time Chat**: Powered by Laravel Broadcasting and Pusher
- ⚛️ **Vue.js Support**: Modern Vue 3 components with Inertia.js
- 🔧 **Blade Templates**: Traditional Blade templates as alternative
- 👥 **Multi-user Support**: Admin and user roles with different interfaces
- 📱 **WhatsApp Verification**: Built-in phone number verification system
- 🔔 **Notifications**: Database notifications for message events
- 🎨 **Modern UI**: Beautiful, responsive chat interface
- 📊 **Admin Dashboard**: Separate admin interface with conversation management
- 🔗 **External Number Assignment**: Assign external numbers to registered users
- 📝 **Message History**: Complete conversation history and search
- 🛡️ **Security**: CSRF protection and authentication middleware

## Installation

### 1. Install via Composer

```bash
composer require devsfort/laravel-whatsapp-chat
```

### 2. Run the Installation Command

```bash
php artisan whatsapp-chat:install
```

The installation command will:
- Ask you to choose between Vue.js or Blade templates
- Publish configuration files
- Publish migrations
- Install the appropriate templates
- Set up routes
- Create example notification classes

### 3. Configure Your Environment

Add your WhatsApp API credentials to `.env`:

```env
WHATSAPP_ACCESS_TOKEN=your_access_token_here
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id
WHATSAPP_WEBHOOK_VERIFY_TOKEN=your_webhook_verify_token
WHATSAPP_BASE_URL=https://graph.facebook.com
WHATSAPP_API_VERSION=v18.0
WHATSAPP_ADMIN_PHONE_NUMBER=1234567890
```

### 4. Update Your User Model

Add the required fields to your `users` table migration:

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('whatsapp_number')->nullable();
    $table->boolean('whatsapp_verified')->default(false);
    $table->timestamp('whatsapp_verified_at')->nullable();
    $table->enum('type', ['admin', 'user'])->default('user');
});
```

Add to your User model's `$fillable` array:

```php
protected $fillable = [
    // ... existing fields
    'whatsapp_number',
    'whatsapp_verified',
    'whatsapp_verified_at',
    'type',
];
```

### 5. Run Migrations

```bash
php artisan migrate
```

## Configuration

The package configuration is published to `config/whatsapp-chat.php`. Key settings:

```php
return [
    'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    'webhook_verify_token' => env('WHATSAPP_WEBHOOK_VERIFY_TOKEN'),
    'base_url' => env('WHATSAPP_BASE_URL', 'https://graph.facebook.com'),
    'api_version' => env('WHATSAPP_API_VERSION', 'v18.0'),
    'admin_phone_number' => env('WHATSAPP_ADMIN_PHONE_NUMBER'),
    'use_mock_mode' => env('WHATSAPP_USE_MOCK_MODE', true),
];
```

## Usage

### Vue.js Templates (Recommended)

If you chose Vue.js during installation, the package will install Vue components that work with Inertia.js.

#### 1. Install Inertia.js (if not already installed)

```bash
composer require inertiajs/inertia-laravel
npm install @inertiajs/vue3
php artisan inertia:middleware
```

#### 2. Add to Your Navigation

```vue
<Link href="/chat">WhatsApp Chat</Link>
```

### Blade Templates

If you chose Blade templates, the package will install traditional Blade views.

#### 1. Add to Your Navigation

```blade
<a href="/chat">WhatsApp Chat</a>
```

### Admin Features

Admins get access to:
- **Registered Users Section**: Conversations with verified users
- **External Numbers Section**: Conversations with unregistered numbers
- **Number Assignment**: Assign external numbers to registered users
- **Start New Conversations**: Initiate chats with any verified user

### User Features

Regular users get:
- **Simple Chat Interface**: Clean, focused chat with admin
- **WhatsApp Verification**: Easy phone number verification
- **Message History**: Complete conversation history

## API Endpoints

### Chat Routes
- `GET /chat` - Chat interface
- `GET /chat/conversations` - Get conversation list
- `GET /chat/messages/{conversationId}` - Get messages for conversation
- `POST /chat/send` - Send a message
- `POST /chat/assign-number` - Assign external number to user (admin only)

### Verification Routes
- `GET /profile/whatsapp-verification` - Verification page
- `POST /profile/whatsapp-verification/send` - Send verification code
- `POST /profile/whatsapp-verification/verify` - Verify code
- `POST /profile/whatsapp-verification/remove` - Remove WhatsApp number

### Webhook Routes
- `GET /webhook/whatsapp` - Webhook verification
- `POST /webhook/whatsapp` - Webhook handler

## Webhook Setup

1. Set your webhook URL to: `https://yourdomain.com/webhook/whatsapp`
2. Use the verify token from your configuration
3. Subscribe to `messages` events

## Broadcasting Setup

The package uses Laravel Broadcasting for real-time features. Configure your broadcasting driver in `.env`:

```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=your_cluster
```

## Notifications

The package includes example notification classes:
- `MessageReceivedNotification` - When a message is received
- `MessageSentNotification` - When a message is sent
- `WhatsAppChannel` - Custom WhatsApp notification channel

## Customization

### Vue Components

Vue components are published to `resources/js/Components/Chat/` and `resources/js/Pages/Chat/`. You can customize them as needed.

### Blade Views

Blade views are published to `resources/views/vendor/whatsapp-chat/`. You can customize them or extend them.

### Styling

The package uses Tailwind CSS. You can customize the styles by modifying the published templates.

## Troubleshooting

### Common Issues

1. **"Cannot read properties of undefined (reading 'type')"**
   - Ensure your User model has a `type` field
   - Add the field to your migration and model

2. **Messages not appearing in real-time**
   - Check your broadcasting configuration
   - Ensure Pusher is properly configured
   - Check browser console for JavaScript errors

3. **WhatsApp messages not sending**
   - Verify your API credentials
   - Check if you're in mock mode
   - Review the logs for API errors

### Debug Mode

Enable debug mode in your configuration:

```php
'use_mock_mode' => true, // For development
'use_mock_mode' => false, // For production
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For support, please open an issue on GitHub or contact us at support@devsfort.com.

## Changelog

### v2.0.0
- Added support for both Vue.js and Blade templates
- Implemented external number assignment for admins
- Separated registered and external conversations
- Enhanced admin interface with better organization
- Added installation command with template selection
- Improved notification system
- Better error handling and user feedback

### v1.0.0
- Initial release
- Basic chat functionality
- Vue.js components
- Real-time messaging
- WhatsApp verification
