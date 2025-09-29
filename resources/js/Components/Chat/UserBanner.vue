<template>
    <div class=" bg-blue-50 border-b border-blue-200 p-4">
        <div class="max-w-7xl mx-auto flex items-center space-x-3">
            <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-medium text-blue-900">
                    WhatsApp Chat with Admin
                </h3>
                <p class="text-sm text-blue-700 mt-1">
                    To send messages, use your verified WhatsApp number and message:
                    <a
                        :href="`https://wa.me/${adminWhatsAppNumber.replace('+', '')}`"
                        target="_blank"
                        class="font-mono font-semibold text-blue-600 hover:text-blue-800 underline"
                    >
                        {{ adminWhatsAppNumber }}
                    </a>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    selectedConversation: String,
    adminWhatsAppNumber: String
})
</script>
