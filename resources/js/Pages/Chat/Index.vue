<template>
    <AppLayout title="WhatsApp Chat">
        <div class="flex bg-gray-100" style="height: 90vh;">
            <!-- Sidebar (Admin only) -->
            <ChatSidebar
                v-if="isAdmin"
                :conversations="conversations"
                :registered-conversations="registeredConversations"
                :external-conversations="externalConversations"
                :users-with-whats-app="usersWithWhatsApp"
                :selected-conversation="selectedConversation"
                :is-admin="isAdmin"
                @select-conversation="selectConversation"
                @assign-number="showAssignModal"
            />

            <!-- Main Chat Area -->
            <div :class="['flex-1 flex flex-col', isAdmin ? '' : 'w-full']">
                <!-- Chat Header -->
                <ChatHeader
                    v-if="selectedConversation"
                    :selected-conversation="selectedConversation"
                    :is-admin="isAdmin"
                    :conversation="selectedConversationData"
                />

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

            </div>
        </div>

        <!-- Assign Number Modal -->
        <AssignNumberModal
            :show="showAssignModalFlag"
            :phone-number="selectedPhoneNumber"
            :users-with-whats-app="usersWithWhatsApp"
            @close="closeAssignModal"
            @assigned="handleNumberAssigned"
        />
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import ChatSidebar from '@/Components/Chat/ChatSidebar.vue'
import ChatHeader from '@/Components/Chat/ChatHeader.vue'
import ChatMessages from '@/Components/Chat/ChatMessages.vue'
import ChatInput from '@/Components/Chat/ChatInput.vue'
import UserBanner from '@/Components/Chat/UserBanner.vue'
import AssignNumberModal from '@/Components/Chat/AssignNumberModal.vue'
import axios from 'axios'

const props = defineProps({
    conversations: Array,
    registeredConversations: Array,
    externalConversations: Array,
    selectedConversation: String,
    messages: Array,
    user: Object,
    isAdmin: Boolean,
    usersWithWhatsApp: Array,
    adminWhatsAppNumber: String
})

// Reactive state
const selectedConversation = ref(props.selectedConversation || null)
const messages = ref(props.messages || [])
const conversations = ref(props.conversations || [])
const sending = ref(false)
const echo = ref(null)
const chatMessagesRef = ref(null)

// Assign modal state
const showAssignModalFlag = ref(false)
const selectedPhoneNumber = ref('')

// Computed properties
const selectedConversationData = computed(() => {
    if (!selectedConversation.value) return null
    return conversations.value.find(conv => conv.from === selectedConversation.value)
})

// Methods
const selectConversation = async (from) => {
    console.log('Selecting conversation:', from)
    selectedConversation.value = from
    await loadMessages()
}

const loadMessages = async () => {
    if (!selectedConversation.value) return

    try {
        const response = await axios.get(route('chat.messages', { conversationId: selectedConversation.value }))
        if (response.data.success) {
            messages.value = response.data.messages
            console.log('Messages loaded:', messages.value.length)

            // Scroll to bottom after messages are loaded
            setTimeout(() => {
                if (chatMessagesRef.value) {
                    chatMessagesRef.value.scrollToBottom()
                }
            }, 200)
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
            console.log('Message added to conversation:', message)

            // Scroll to bottom when new message is added
            setTimeout(() => {
                if (chatMessagesRef.value) {
                    chatMessagesRef.value.scrollToBottom()
                }
            }, 100)
        }
    }
}

const updateConversationList = (conversations) => {
    conversations.value = conversations
}

// Real-time functionality
const initializeEcho = () => {
    if (window.Echo && props.user) {
        try {
            echo.value = window.Echo
                .private(`users.${props.user.id}`)
                .listen('MessageReceived', (e) => {
                    console.log('Message received event:', e)
                    addMessageToConversation(e.message)
                })
                .listen('MessageSent', (e) => {
                    console.log('Message sent event:', e)
                    addMessageToConversation(e.message)
                })
                .listen('ConversationsUpdated', (e) => {
                    console.log('Conversations updated event:', e)
                    updateConversationList(e.conversations)
                })
        } catch (error) {
            console.warn('Error initializing Echo:', error)
            echo.value = null
        }
    } else {
        console.warn('Echo is not available or user not found')
        echo.value = null
    }
}

// Lifecycle
onMounted(async () => {
    // For regular users, auto-select their conversation if they have one
    if (!props.isAdmin && props.user && props.user.whatsapp_number) {
        const userWhatsAppNumber = props.user.whatsapp_number.replace('+', '')
        const userConversation = conversations.value.find(conv => conv.from === userWhatsAppNumber)

        if (userConversation && !selectedConversation.value) {
            selectedConversation.value = userWhatsAppNumber
            await loadMessages()
        }
    }

    // Initialize real-time updates
    initializeEcho()

    // Fallback: Auto-refresh conversations every 30 seconds if real-time is not available
    if (!echo.value) {
        setInterval(async () => {
            try {
                const response = await axios.get(route('chat.conversations'))
                if (response.data.success) {
                    conversations.value = response.data.conversations
                }
            } catch (error) {
                console.error('Error refreshing conversations:', error)
            }
        }, 30000)
    }
})

onUnmounted(() => {
    if (echo.value && props.user && typeof echo.value.leave === 'function') {
        try {
            echo.value.leave(`users.${props.user.id}`)
        } catch (error) {
            console.warn('Error leaving Echo channel:', error)
        }
    }
    // Reset echo reference
    echo.value = null
})

// Watch for changes in selectedConversation prop
watch(() => props.selectedConversation, (newValue) => {
    if (newValue && newValue !== selectedConversation.value) {
        selectedConversation.value = newValue
        loadMessages()
    }
})

// Assign modal functions
const showAssignModal = (phoneNumber) => {
    selectedPhoneNumber.value = phoneNumber
    showAssignModalFlag.value = true
}

const closeAssignModal = () => {
    showAssignModalFlag.value = false
    selectedPhoneNumber.value = ''
}

const handleNumberAssigned = (data) => {
    // Refresh the page to show updated conversations
    router.reload()
}
</script>
