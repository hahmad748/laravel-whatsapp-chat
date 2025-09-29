<template>
    <div class="bg-white border-t border-gray-200 p-4">
        <form @submit.prevent="handleSubmit" class="flex space-x-4">
            <div class="flex-1">
                <input
                    v-model="message"
                    type="text"
                    placeholder="Type a message..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    :disabled="sending"
                />
            </div>
            <button
                type="submit"
                :disabled="!message.trim() || sending"
                class="px-6 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
                <span v-if="sending" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Sending...
                </span>
                <span v-else>Send</span>
            </button>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
    sending: Boolean
})

const emit = defineEmits(['send-message'])

const message = ref('')

const handleSubmit = () => {
    if (message.value.trim() && !props.sending) {
        emit('send-message', message.value.trim())
        message.value = ''
    }
}
</script>
