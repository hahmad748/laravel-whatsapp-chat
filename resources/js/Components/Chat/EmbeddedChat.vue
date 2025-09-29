<template>
    <div class="flex flex-col bg-gray-100 rounded-lg" style="height: 600px;">

        <!-- User Banner (Non-admin users) -->
        <UserBanner
            v-if="!isAdmin && selectedConversation"
            :selected-conversation="selectedConversation"
            :admin-whats-app-number="adminWhatsAppNumber"
        />

        <!-- Messages Area -->
        <ChatMessages
            ref="chatMessagesRef"
            :messages="messages"
            :is-admin="isAdmin"
        />

        <!-- Message Input (Admin only) -->
        <div v-if="selectedConversation && isAdmin" class="sticky bottom-0 z-10">
            <ChatInput
                :sending="sending"
                @send-message="sendMessage"
            />
        </div>

        <!-- Empty State -->
        <div v-if="!selectedConversation" class="flex-1 flex items-center justify-center text-gray-500">
            <div class="text-center">
                <div class="text-4xl mb-4">💬</div>
                <h3 class="text-lg font-medium mb-2">No conversation selected</h3>
                <p class="text-sm">Select a conversation to start chatting</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import ChatHeader from './ChatHeader.vue'
import ChatMessages from './ChatMessages.vue'
import ChatInput from './ChatInput.vue'
import UserBanner from './UserBanner.vue'

const props = defineProps({
    conversations: Array,
    selectedConversation: String,
    messages: Array,
    user: Object,
    isAdmin: Boolean,
    usersWithWhatsApp: Array,
    adminWhatsAppNumber: String,
})

const selectedConversation = ref(props.selectedConversation || null)
const messages = ref(props.messages || [])
const sending = ref(false)
const chatMessagesRef = ref(null)

// Echo instance
const echo = ref(null)

// Computed properties
const selectedConversationData = computed(() => {
    if (!selectedConversation.value) return null
    return props.conversations?.find(conv => conv.phone_number === selectedConversation.value) || null
})

// Methods
const selectConversation = (conversationId) => {
    selectedConversation.value = conversationId
    loadMessages()
}

const loadMessages = async () => {
    if (!selectedConversation.value) return

    try {
        const response = await axios.get(route('chat.messages', { conversationId: selectedConversation.value }))
        if (response.data.success) {
            messages.value = response.data.messages
            // Scroll to bottom after messages are loaded
            if (chatMessagesRef.value) {
                chatMessagesRef.value.scrollToBottom()
            }
        }
    } catch (error) {
        console.error('Error loading messages:', error)
    }
}

const sendMessage = async (messageText) => {
    if (!selectedConversation.value || sending.value) return

    sending.value = true

    try {
        const response = await axios.post(route('chat.send'), {
            to: selectedConversation.value,
            message: messageText
        })

        if (response.data.success) {
            // Message will be added via real-time event
            console.log('Message sent successfully')
        }
    } catch (error) {
        console.error('Error sending message:', error)

        // Handle specific error types
        if (error.response?.data?.error_type === 're_engagement') {
            alert('Cannot send message: The recipient must initiate the conversation or message within 24 hours.')
        } else if (error.response?.data?.message) {
            alert('Error: ' + error.response.data.message)
        } else {
            alert('Failed to send message. Please try again.')
        }
    } finally {
        sending.value = false
    }
}

const addMessageToConversation = (message) => {
    if (message.from === selectedConversation.value) {
        // Check if message already exists to prevent duplicates
        const exists = messages.value.some(m => m.id === message.id)
        if (!exists) {
            messages.value.push(message)
            // Scroll to bottom when new message arrives
            if (chatMessagesRef.value) {
                chatMessagesRef.value.scrollToBottom()
            }
        }
    }
}

// Initialize Echo for real-time updates
const initializeEcho = () => {
    if (window.Echo && props.user) {
        try {
            echo.value = window.Echo.private(`users.${props.user.id}`)
                .listen('WhatsAppMessageSent', (e) => {
                    console.log('Message sent event received:', e)
                    addMessageToConversation(e.message)
                })
                .listen('WhatsAppMessageReceived', (e) => {
                    console.log('Message received event received:', e)
                    addMessageToConversation(e.message)
                })
        } catch (error) {
            console.error('Error initializing Echo:', error)
        }
    }
}

// Cleanup Echo on unmount
const cleanupEcho = () => {
    if (echo.value && props.user && typeof echo.value.leave === 'function') {
        try {
            echo.value.leave(`users.${props.user.id}`)
        } catch (error) {
            console.error('Error cleaning up Echo:', error)
        }
    }
}

// Lifecycle hooks
onMounted(() => {
    // Initialize with props data
    selectedConversation.value = props.selectedConversation || null
    messages.value = props.messages || []

    // Auto-select conversation for non-admin users
    if (!props.isAdmin && props.selectedConversation) {
        loadMessages()
    }

    // Initialize Echo
    initializeEcho()
})

onUnmounted(() => {
    cleanupEcho()
})

// Watch for changes in selectedConversation
watch(selectedConversation, (newValue) => {
    if (newValue) {
        loadMessages()
    }
})
</script>
