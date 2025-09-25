import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [vue()],
    build: {
        lib: {
            entry: 'resources/js/index.js',
            name: 'LaravelWhatsappChat',
            fileName: 'laravel-whatsapp-chat'
        },
        rollupOptions: {
            external: ['vue', 'axios', 'pusher-js'],
            output: {
                globals: {
                    vue: 'Vue',
                    axios: 'axios',
                    'pusher-js': 'Pusher'
                }
            }
        }
    }
});
