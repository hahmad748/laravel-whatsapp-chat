<template>
    <div ref="messagesContainer" class="flex-1 overflow-y-auto bg-gray-50 p-4">
        <!-- Messages List -->
        <div v-if="messages && messages.length > 0" :class="['space-y-4', isAdmin ? '' : 'max-w-7xl mx-auto']">
            <div
                v-for="message in messages"
                :key="message.id"
                :class="[
                    'flex',
                    message.direction === 'outbound' ? 'justify-end' : 'justify-start'
                ]"
            >
                <div
                    :class="[
                        'px-4 py-2 rounded-lg',
                        isAdmin ? 'max-w-xs lg:max-w-md' : 'max-w-2xl',
                        message.direction === 'outbound'
                            ? 'bg-blue-900 text-white'
                            : 'bg-white text-gray-900 border border-gray-200'
                    ]"
                >
                    <p class="text-sm">{{ message.body }}</p>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs opacity-75">
                            {{ formatTime(message.created_at) }}
                        </span>
                        <span v-if="message.direction === 'outbound'" class="text-xs opacity-75 ml-2">
                            ✓✓
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="flex items-center justify-center h-full">
            <div class="text-center text-gray-900">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No messages yet</h3>
                <p class="mt-2 text-sm text-gray-500">
                    {{ isAdmin ? 'Choose a conversation from the sidebar to start chatting' : 'Your conversation with Admin will appear here' }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'

const props = defineProps({
    messages: Array,
    isAdmin: Boolean
})

const messagesContainer = ref(null)

const formatTime = (timestamp) => {
    const date = new Date(timestamp)
    return date.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    })
}

const scrollToBottom = () => {
    if (messagesContainer.value) {
        nextTick(() => {
            // Small delay to ensure DOM is fully rendered
            setTimeout(() => {
                messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
            }, 100)
        })
    }
}

// Expose scrollToBottom function to parent component
defineExpose({
    scrollToBottom
})

// Scroll to bottom when component mounts
onMounted(() => {
    scrollToBottom()
})

// Scroll to bottom when messages change
watch(() => props.messages, () => {
    scrollToBottom()
}, { deep: true })
</script>
