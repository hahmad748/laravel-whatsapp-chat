<template>
    <AppLayout title="WhatsApp Chat">
        <div class="h-screen flex bg-gray-100">
            <!-- Sidebar - Conversations List (Admin only) -->
            <div v-if="isAdmin" class="w-1/3 bg-white border-r border-gray-200 flex flex-col">

                <!-- Search -->
                <div class="p-3 border-b border-gray-200 bg-gray-50">
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search conversations..."
                            class="w-full pl-10 pr-4 py-2 bg-white border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                        />
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Conversations List -->
                <div class="flex-1 overflow-y-auto">
                    <!-- Active Conversations Section -->
                    <div v-if="filteredConversations.length > 0" class="border-b border-gray-200">
                        <div class="px-3 py-2 bg-gray-50">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Active Conversations</h3>
                        </div>
                        <div
                            v-for="conversation in filteredConversations"
                            :key="conversation.from"
                            @click="selectConversation(conversation.from)"
                            :class="[
                                'p-3 border-b border-gray-100 cursor-pointer hover:bg-gray-50 transition-colors',
                                selectedConversation === conversation.from ? 'bg-gray-100' : ''
                            ]"
                        >
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center relative">
                                    <span class="text-white font-semibold text-sm">
                                        {{ conversation.from.slice(-2) }}
                                    </span>
                                    <div v-if="conversation.last_direction === 'inbound'" class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white"></div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ conversation.user_name || conversation.from }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ formatTime(conversation.last_message_at) }}
                                        </p>
                                    </div>
                                    <p class="text-sm text-gray-500 truncate">
                                        <span v-if="conversation.last_direction === 'outbound'" class="text-gray-400">You: </span>
                                        {{ conversation.last_message }}
                                    </p>
                                    <div v-if="conversation.user_name" class="flex items-center justify-between mt-1">
                                        <span class="text-xs text-green-600 font-medium">
                                            {{ conversation.user_name }}
                                        </span>
                                        <div v-if="conversation.last_direction === 'inbound'" class="w-2 h-2 bg-green-500 rounded-full"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Users with WhatsApp Section -->
                    <div v-if="filteredUsersWithWhatsApp && filteredUsersWithWhatsApp.length > 0" class="border-b border-gray-200">
                        <div class="px-3 py-2 bg-gray-50">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Start New Conversation</h3>
                        </div>
                        <div
                            v-for="user in filteredUsersWithWhatsApp"
                            :key="user.id"
                            @click="selectConversation(user.whatsapp_number)"
                            :class="[
                                'p-3 border-b border-gray-100 cursor-pointer hover:bg-gray-50 transition-colors',
                                selectedConversation === user.whatsapp_number ? 'bg-gray-100' : ''
                            ]"
                        >
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center">
                                    <span class="text-white font-semibold text-sm">
                                        {{ user.name.charAt(0).toUpperCase() }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ user.name }}
                                        </p>
                                        <div class="flex items-center space-x-1">
                                            <span class="text-xs text-green-600 font-medium">Verified</span>
                                            <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-500 truncate">
                                        {{ user.whatsapp_number }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ user.email }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="filteredConversations.length === 0 && (!filteredUsersWithWhatsApp || filteredUsersWithWhatsApp.length === 0)" class="p-4 text-center text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <p class="mt-2">No conversations yet</p>
                    </div>
                </div>
            </div>

            <!-- Main Chat Area -->
            <div :class="['flex-1 flex flex-col', isAdmin ? '' : 'w-full']">
                <!-- Chat Header -->
                <div v-if="selectedConversation" class="bg-white border-b border-gray-200 p-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-semibold">
                                {{ selectedConversation.slice(-2) }}
                            </span>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">{{ selectedConversation }}</h2>
                            <p class="text-sm text-gray-500">WhatsApp Business</p>
                        </div>
                    </div>
                </div>

                <!-- User Banner (Non-admin users) -->
                <div v-if="!isAdmin && selectedConversation" class="bg-blue-50 border-b border-blue-200 p-4">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-medium text-blue-900">Send messages via your personal WhatsApp</h3>
                            <p class="text-sm text-blue-700 mt-1">
                                To send messages, use your personal WhatsApp app and message:
                                <span class="font-mono font-semibold">{{ selectedConversation }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Messages Area -->
                <div v-if="selectedConversation" class="flex-1 overflow-y-auto bg-gray-50 p-4">

                    <div v-if="messages.length === 0" class="flex items-center justify-center h-full">
                        <div class="text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <p class="mt-2">No messages yet</p>
                        </div>
                    </div>

                    <div v-else class="space-y-4">
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
                                    'max-w-xs lg:max-w-md px-4 py-2 rounded-lg',
                                    message.direction === 'outbound'
                                        ? 'bg-blue-600 text-white'
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
                </div>

                <!-- Message Input (Admin only) -->
                <div v-if="selectedConversation && isAdmin" class="bg-white border-t border-gray-200 p-4">
                    <form @submit.prevent="sendMessage" class="flex space-x-4">
                        <div class="flex-1">
                            <input
                                v-model="newMessage"
                                type="text"
                                placeholder="Type a message..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :disabled="sending"
                            />
                        </div>
                        <button
                            type="submit"
                            :disabled="!newMessage.trim() || sending"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <span v-if="sending">Sending...</span>
                            <span v-else>Send</span>
                        </button>
                    </form>
                </div>

                <!-- No Conversation Selected -->
                <div v-else class="flex-1 flex items-center justify-center bg-gray-50">
                    <div class="text-center text-gray-500">
                        <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <h3 v-if="isAdmin" class="mt-4 text-lg font-medium text-gray-900">Select a conversation</h3>
                        <h3 v-else class="mt-4 text-lg font-medium text-gray-900">No messages yet</h3>
                        <p v-if="isAdmin" class="mt-2 text-sm text-gray-500">Choose a conversation from the sidebar to start chatting</p>
                        <p v-else class="mt-2 text-sm text-gray-500">When you send a message via WhatsApp, it will appear here</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import axios from 'axios'

const props = defineProps({
    conversations: Array,
    selectedConversation: String,
    messages: Array,
    user: Object,
    isAdmin: Boolean,
    usersWithWhatsApp: Array
})

const searchQuery = ref('')
const newMessage = ref('')
const sending = ref(false)
const messages = ref(props.messages || [])
const conversations = ref(props.conversations || [])
const echo = ref(null)
// Removed pagination variables - using simple message loading

const filteredConversations = computed(() => {
    if (!searchQuery.value) return conversations.value

    return conversations.value.filter(conversation =>
        conversation.from.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        conversation.last_message.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        (conversation.user_name && conversation.user_name.toLowerCase().includes(searchQuery.value.toLowerCase()))
    )
})

const filteredUsersWithWhatsApp = computed(() => {
    if (!searchQuery.value || !props.usersWithWhatsApp) return props.usersWithWhatsApp || []

    return props.usersWithWhatsApp.filter(user =>
        user.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        user.email.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        user.whatsapp_number.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
})

const selectConversation = (from) => {
    router.get(route('chat.index'), { conversation: from }, {
        preserveState: true,
        replace: true
    })
}

const sendMessage = async () => {
    if (!newMessage.value.trim() || sending.value) return

    sending.value = true

    try {
        const response = await axios.post(route('chat.send'), {
            to: props.selectedConversation,
            message: newMessage.value
        })

        if (response.data.success) {
            // Store the message content before clearing
            const messageContent = newMessage.value
            newMessage.value = ''

            // Don't add message here - let real-time events handle it
            // This prevents duplicates when real-time events work
            console.log('Message sent successfully, waiting for real-time event')
        } else {
            alert('Failed to send message: ' + response.data.message)
        }
    } catch (error) {
        console.error('Error sending message:', error)
        alert('Error sending message. Please try again.')
    } finally {
        sending.value = false
    }
}

const loadMessages = async () => {
    if (!props.selectedConversation) return

    try {
        const response = await axios.get(route('chat.messages', props.selectedConversation))

        if (response.data.success) {
            messages.value = response.data.messages
            // Scroll to bottom after loading messages
            scrollToBottom()
        }
    } catch (error) {
        console.error('Error loading messages:', error)
    }
}

const formatTime = (timestamp) => {
    const date = new Date(timestamp)
    const now = new Date()
    const diffInHours = (now - date) / (1000 * 60 * 60)

    if (diffInHours < 24) {
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    } else if (diffInHours < 48) {
        return 'Yesterday'
    } else {
        return date.toLocaleDateString()
    }
}

const scrollToBottom = () => {
    setTimeout(() => {
        const messagesContainer = document.querySelector('.overflow-y-auto')
        if (messagesContainer) {
            console.log('Scrolling to bottom, container height:', messagesContainer.scrollHeight)
            messagesContainer.scrollTop = messagesContainer.scrollHeight
        } else {
            console.warn('Messages container not found for scrolling')
        }
    }, 100)
}

// Removed pagination functions - using simple message loading

// Initialize real-time functionality
onMounted(() => {
    initializeEcho()
    setupRealtimeListeners()
    scrollToBottom()
})

onUnmounted(() => {
    if (echo.value) {
        echo.value.disconnect()
    }
})

const initializeEcho = () => {
    if (window.Echo && window.Echo.private) {
        echo.value = window.Echo
        console.log('Echo initialized successfully', window.Echo)
    } else {
        console.warn('Echo not available. Real-time features disabled.', {
            Echo: window.Echo,
            Pusher: window.Pusher
        })
    }
}

const setupRealtimeListeners = () => {
    if (!echo.value) {
        console.warn('Echo not available for real-time listeners')
        return
    }

    console.log('Setting up real-time listeners...')

    // Listen for new messages in the current conversation
    if (props.selectedConversation) {
        const channelName = `whatsapp.chat.${props.selectedConversation}`
        console.log('Listening to channel:', channelName)

        echo.value.private(channelName)
            .listen('message.received', (data) => {
                console.log('Received message in conversation:', data)
                addMessageToConversation(data)
            })
            .listen('message.sent', (data) => {
                console.log('Sent message in conversation:', data)
                addMessageToConversation(data)
            })
    }

    // Listen for conversation updates
    echo.value.private('whatsapp.conversations')
        .listen('message.received', (data) => {
            console.log('Received message for conversation list:', data)
            updateConversationList(data)
            // Also add to current conversation if it matches
            if (props.selectedConversation === data.from) {
                addMessageToConversation(data)
            }
        })
        .listen('message.sent', (data) => {
            console.log('Sent message for conversation list:', data)
            updateConversationList(data)
            // Also add to current conversation if it matches
            if (props.selectedConversation === data.from) {
                addMessageToConversation(data)
            }
        })
}

const addMessageToConversation = (messageData) => {
    console.log('Adding message to conversation:', messageData)

    // Ensure the message has the correct structure
    const message = {
        id: messageData.id,
        from: messageData.from,
        body: messageData.body,
        direction: messageData.direction,
        type: messageData.type || 'text',
        created_at: messageData.created_at,
        user_id: messageData.user_id,
        user_name: messageData.user_name
    }

    // Check if message already exists to avoid duplicates
    const existingMessage = messages.value.find(m => m.id === message.id)
    if (existingMessage) {
        console.log('Message already exists, skipping:', message.id)
        return
    }

    // Add to messages array (newest messages at the end)
    messages.value.push(message)

    // Scroll to bottom
    scrollToBottom()

    console.log('Message added, total messages:', messages.value.length)
}

const updateConversationList = (messageData) => {
    const existingConversationIndex = conversations.value.findIndex(
        conv => conv.from === messageData.from
    )

    if (existingConversationIndex !== -1) {
        // Update existing conversation
        conversations.value[existingConversationIndex] = {
            ...conversations.value[existingConversationIndex],
            last_message: messageData.body,
            last_message_at: messageData.created_at,
            last_direction: messageData.direction,
            message_count: conversations.value[existingConversationIndex].message_count + 1,
            user_name: messageData.user_name
        }

        // Move to top
        const updatedConversation = conversations.value.splice(existingConversationIndex, 1)[0]
        conversations.value.unshift(updatedConversation)
    } else {
        // Add new conversation
        conversations.value.unshift({
            from: messageData.from,
            last_message: messageData.body,
            last_message_at: messageData.created_at,
            last_direction: messageData.direction,
            message_count: 1,
            user_name: messageData.user_name
        })
    }
}

// Watch for changes in selected conversation
watch(() => props.selectedConversation, (newConversation, oldConversation) => {
    console.log('Selected conversation changed from', oldConversation, 'to', newConversation)

    if (newConversation) {
        loadMessages()
        // Re-setup listeners for the new conversation
        if (echo.value) {
            setupRealtimeListeners()
        }
    }
})

// Fallback: Auto-refresh conversations every 30 seconds if real-time is not available
onMounted(() => {
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
</script>
