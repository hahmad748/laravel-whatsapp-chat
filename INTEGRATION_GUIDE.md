# Integration Guide

This guide explains how to integrate the Laravel WhatsApp Chat Package Vue components into your Laravel application.

## Vue Components Integration

### 1. Publish Assets

```bash
# Publish Vue components
php artisan vendor:publish --provider="DevsFort\LaravelWhatsappChat\WhatsAppChatServiceProvider" --tag="whatsapp-chat-assets"
```

This will copy the Vue components to `resources/js/vendor/whatsapp-chat/`.

### 2. Import Components in Your App

#### Option A: Import in your main app.js

```javascript
// resources/js/app.js
import { createApp } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'

// Import WhatsApp Chat components
import { ChatIndex, WhatsAppVerification } from './vendor/whatsapp-chat/components.js'

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel'

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
        
        // Register WhatsApp Chat components globally
        app.component('ChatIndex', ChatIndex)
        app.component('WhatsAppVerification', WhatsAppVerification)
        
        return app.use(plugin).mount(el)
    },
    progress: {
        color: '#4B5563',
    },
})
```

#### Option B: Import in specific pages

```javascript
// resources/js/Pages/Chat/Index.vue
<template>
    <ChatIndex 
        :conversations="conversations"
        :selected-conversation="selectedConversation"
        :messages="messages"
        :user="user"
        :is-admin="isAdmin"
        :users-with-whats-app="usersWithWhatsApp"
    />
</template>

<script setup>
import { ChatIndex } from '../../vendor/whatsapp-chat/components.js'

defineProps({
    conversations: Array,
    selectedConversation: String,
    messages: Array,
    user: Object,
    isAdmin: Boolean,
    usersWithWhatsApp: Array
})
</script>
```

### 3. Update Your Layout

Add navigation links to your main layout:

```vue
<!-- resources/js/Layouts/AppLayout.vue -->
<template>
    <div>
        <!-- Your existing layout -->
        
        <!-- Add WhatsApp Chat Link -->
        <nav>
            <!-- For admin users -->
            <Link v-if="user.type === 'admin'" :href="route('chat.index')" class="nav-link">
                <i class="fab fa-whatsapp"></i> Chat
            </Link>
            
            <!-- For verified users -->
            <Link v-else-if="user.whatsapp_verified" :href="route('chat.index')" class="nav-link">
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

### 4. Configure Broadcasting

Ensure your `resources/js/app.js` includes Echo configuration:

```javascript
// resources/js/app.js
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    authEndpoint: '/whatsapp/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
    },
})
```

### 5. Add Routes to Ziggy

Make sure your routes are available in Ziggy by adding them to your `routes/web.php`:

```php
// routes/web.php
use Illuminate\Support\Facades\Route;

// Your existing routes...

// WhatsApp Chat routes (these are automatically registered by the package)
// But you can also add them manually if needed:
Route::group(['prefix' => 'whatsapp', 'middleware' => ['web']], function () {
    Route::middleware(['auth'])->group(function () {
        Route::get('/chat', [App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
        Route::get('/chat/messages/{conversationId}', [App\Http\Controllers\ChatController::class, 'getMessages'])->name('chat.messages');
        Route::post('/chat/send', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.send');
        Route::get('/chat/conversations', [App\Http\Controllers\ChatController::class, 'getConversations'])->name('chat.conversations');
        
        Route::get('/verification', [App\Http\Controllers\WhatsAppVerificationController::class, 'show'])->name('whatsapp.verification.show');
        Route::post('/verify/send-code', [App\Http\Controllers\WhatsAppVerificationController::class, 'sendVerificationCode'])->name('whatsapp.verification.send');
        Route::post('/verify/verify-code', [App\Http\Controllers\WhatsAppVerificationController::class, 'verifyCode'])->name('whatsapp.verification.verify');
        Route::post('/verify/remove', [App\Http\Controllers\WhatsAppVerificationController::class, 'removeWhatsApp'])->name('whatsapp.verification.remove');
    });
});
```

### 6. Build Assets

```bash
npm run build
# or
npm run dev
```

## Customization

### Custom Styling

The components use Tailwind CSS classes. You can customize the appearance by:

1. **Overriding CSS classes** in your main CSS file
2. **Modifying the components** after publishing them
3. **Using CSS variables** for consistent theming

### Custom Components

You can extend the package components by:

1. **Publishing the components** to your app
2. **Modifying them** as needed
3. **Creating your own components** that use the package services

### Custom Routes

You can customize the routes by:

1. **Modifying the service provider** (not recommended)
2. **Creating your own routes** that use the package controllers
3. **Using route model binding** for more complex scenarios

## Troubleshooting

### Common Issues

1. **Components not found**: Make sure you've published the assets and imported them correctly
2. **Routes not working**: Check that the package routes are registered and Ziggy is configured
3. **Broadcasting not working**: Verify Echo configuration and Pusher credentials
4. **Styling issues**: Ensure Tailwind CSS is properly configured

### Debug Mode

Enable debug mode in your `.env`:

```env
WHATSAPP_DEBUG=true
WHATSAPP_USE_MOCK_MODE=true
```

This will provide additional logging and use mock mode for testing.

## Examples

### Basic Chat Integration

```vue
<!-- resources/js/Pages/Dashboard.vue -->
<template>
    <AppLayout title="Dashboard">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h1 class="text-2xl font-bold mb-4">Dashboard</h1>
                        
                        <!-- Quick access to WhatsApp Chat -->
                        <div class="mb-4">
                            <Link :href="route('chat.index')" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                                Open WhatsApp Chat
                            </Link>
                        </div>
                        
                        <!-- Your dashboard content -->
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
</script>
```

### Admin Panel Integration

```vue
<!-- resources/js/Pages/Admin/Dashboard.vue -->
<template>
    <AdminLayout title="Admin Dashboard">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- WhatsApp Chat Card -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fab fa-whatsapp text-2xl text-green-600"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">WhatsApp Chat</h3>
                                    <p class="text-sm text-gray-500">Manage conversations</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <Link :href="route('chat.index')" class="text-green-600 hover:text-green-700 font-medium">
                                    Open Chat →
                                </Link>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Other admin cards -->
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
</script>
```

## Support

For more help with integration:

- Check the [Installation Guide](INSTALLATION.md)
- Review the [Package Summary](PACKAGE_SUMMARY.md)
- Open an issue on [GitHub](https://github.com/hahmad748/laravel-whatsapp-chat/issues)
