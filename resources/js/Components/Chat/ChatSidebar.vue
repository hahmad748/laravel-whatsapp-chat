<template>
    <div class="w-1/3 bg-white border-r border-gray-200 flex flex-col">
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
            <!-- Registered Users Section -->
            <div v-if="filteredRegisteredConversations.length > 0" class="border-b border-gray-200">
                <div class="px-3 py-2 bg-green-50">
                    <h3 class="text-xs font-semibold text-green-700 uppercase tracking-wider">
                        Registered Users
                    </h3>
                </div>
                <div
                    v-for="conversation in filteredRegisteredConversations"
                    :key="conversation.from"
                    @click="$emit('select-conversation', conversation.from)"
                    :class="[
                        'p-3 border-b border-gray-100 cursor-pointer hover:bg-gray-50 transition-colors',
                        selectedConversation === conversation.from ? 'bg-gray-100' : ''
                    ]"
                >
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center relative">
                            <span class="text-white font-semibold text-sm">
                                {{ conversation.user_name ? conversation.user_name.charAt(0).toUpperCase() : conversation.from.slice(-2) }}
                            </span>
                            <div v-if="conversation.last_direction === 'inbound'" class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white"></div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ conversation.user_name }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ formatTime(conversation.last_message_at) }}
                                </p>
                            </div>
                            <p class="text-sm text-gray-500 truncate">
                                {{ conversation.last_message }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- External Numbers Section -->
            <div v-if="filteredExternalConversations.length > 0" class="border-b border-gray-200">
                <div class="px-3 py-2 bg-orange-50">
                    <h3 class="text-xs font-semibold text-orange-700 uppercase tracking-wider">
                        External Numbers
                    </h3>
                </div>
                <div
                    v-for="conversation in filteredExternalConversations"
                    :key="conversation.from"
                    @click="$emit('select-conversation', conversation.from)"
                    :class="[
                        'p-3 border-b border-gray-100 cursor-pointer hover:bg-gray-50 transition-colors group',
                        selectedConversation === conversation.from ? 'bg-gray-100' : ''
                    ]"
                >
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-orange-600 rounded-full flex items-center justify-center relative">
                            <span class="text-white font-semibold text-sm">
                                {{ conversation.from.slice(-2) }}
                            </span>
                            <div v-if="conversation.last_direction === 'inbound'" class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white"></div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ conversation.from }}
                                </p>
                                <div class="flex items-center space-x-2">
                                    <p class="text-xs text-gray-500">
                                        {{ formatTime(conversation.last_message_at) }}
                                    </p>
                                    <button
                                        @click.stop="$emit('assign-number', conversation.from)"
                                        class="opacity-0 group-hover:opacity-100 transition-opacity p-1 text-orange-600 hover:text-orange-800"
                                        title="Assign to user"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500 truncate">
                                {{ conversation.last_message }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Start New Conversation Section (Admin only) -->
            <div v-if="isAdmin && usersWithWhatsApp.length > 0" class="border-b border-gray-200">
                <div class="px-3 py-2 bg-blue-50">
                    <h3 class="text-xs font-semibold text-blue-700 uppercase tracking-wider">
                        Start New Conversation
                    </h3>
                </div>
                <div
                    v-for="user in filteredUsersWithWhatsApp"
                    :key="user.id"
                    @click="$emit('select-conversation', user.whatsapp_number)"
                    :class="[
                        'p-3 border-b border-gray-100 cursor-pointer hover:bg-gray-50 transition-colors',
                        selectedConversation === user.whatsapp_number ? 'bg-gray-100' : ''
                    ]"
                >
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-semibold text-sm">
                                {{ user.name.charAt(0).toUpperCase() }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ user.name }}
                            </p>
                            <p class="text-sm text-gray-500 truncate">
                                {{ user.whatsapp_number }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="filteredRegisteredConversations.length === 0 && filteredExternalConversations.length === 0" class="flex-1 flex items-center justify-center text-gray-500">
                <div class="text-center">
                    <div class="text-4xl mb-4">💬</div>
                    <h3 class="text-lg font-medium mb-2">No conversations yet</h3>
                    <p class="text-sm">Start a conversation to see it here</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
    conversations: Array,
    registeredConversations: Array,
    externalConversations: Array,
    usersWithWhatsApp: Array,
    selectedConversation: String,
    isAdmin: Boolean
})

const emit = defineEmits(['select-conversation', 'assign-number'])

const searchQuery = ref('')

const filteredRegisteredConversations = computed(() => {
    if (!props.registeredConversations) return []

    if (!searchQuery.value) return props.registeredConversations

    return props.registeredConversations.filter(conversation =>
        conversation.user_name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        conversation.from.includes(searchQuery.value) ||
        conversation.last_message?.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
})

const filteredExternalConversations = computed(() => {
    if (!props.externalConversations) return []

    if (!searchQuery.value) return props.externalConversations

    return props.externalConversations.filter(conversation =>
        conversation.from.includes(searchQuery.value) ||
        conversation.last_message?.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
})

const filteredUsersWithWhatsApp = computed(() => {
    if (!props.usersWithWhatsApp) return []

    // Filter out users who already have conversations
    const existingNumbers = new Set([
        ...(props.registeredConversations || []).map(c => c.from),
        ...(props.externalConversations || []).map(c => c.from)
    ])

    return props.usersWithWhatsApp.filter(user =>
        !existingNumbers.has(user.whatsapp_number) &&
        (!searchQuery.value ||
         user.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
         user.whatsapp_number.includes(searchQuery.value))
    )
})

const formatTime = (timestamp) => {
    if (!timestamp) return ''

    const date = new Date(timestamp)
    const now = new Date()
    const diffInHours = (now - date) / (1000 * 60 * 60)

    if (diffInHours < 24) {
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    } else if (diffInHours < 168) { // 7 days
        return date.toLocaleDateString([], { weekday: 'short' })
    } else {
        return date.toLocaleDateString([], { month: 'short', day: 'numeric' })
    }
}
</script>
