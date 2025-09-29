<template>
    <div class="bg-white border-b border-gray-200 p-4 ">
        <div :class="['flex items-center space-x-3', isAdmin ? '' : 'max-w-7xl mx-auto']">
            <div class="w-10 h-10 bg-blue-900 rounded-full flex items-center justify-center">
                <span class="text-white font-semibold">
                    {{ displayName.slice(-2) }}
                </span>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900">
                    {{ displayName }}
                </h2>
                <p class="text-sm text-gray-500">WhatsApp Business</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    selectedConversation: String,
    isAdmin: Boolean,
    conversation: Object
})

const displayName = computed(() => {
    if (!props.isAdmin) {
        return 'Admin'
    }

    if (props.conversation && props.conversation.user_name) {
        return props.conversation.user_name
    }

    return props.selectedConversation || 'Unknown'
})
</script>
