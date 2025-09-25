// Laravel WhatsApp Chat Package - Vue Components
import { createApp } from 'vue';
import ChatIndex from './Pages/Chat/Index.vue';
import WhatsAppVerification from './Pages/Profile/WhatsAppVerification.vue';

// Export components for use in applications
export {
    ChatIndex,
    WhatsAppVerification
};

// Auto-register components if used as a plugin
export default {
    install(app) {
        app.component('WhatsAppChatIndex', ChatIndex);
        app.component('WhatsAppVerification', WhatsAppVerification);
    }
};
