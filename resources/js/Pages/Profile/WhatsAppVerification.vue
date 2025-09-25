<template>
    <AppLayout title="WhatsApp Verification">
        <div class="py-12 max-w-2xl mx-auto">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">WhatsApp Verification</h1>
                        <p class="text-gray-600">Verify your WhatsApp number</p>
                    </div>
                </div>

                <!-- Current Status -->
                <div v-if="whatsapp_verified" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-green-800 font-medium">WhatsApp Number Verified</p>
                            <p class="text-green-600 text-sm">{{ whatsapp_number }}</p>
                            <p class="text-green-600 text-xs">Verified on {{ formatDate(whatsapp_verified_at) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Verification Form -->
                <div v-if="!whatsapp_verified">
                    <form @submit.prevent="sendVerificationCode" class="space-y-4">
                        <div>
                            <label for="whatsapp_number" class="block text-sm font-medium text-gray-700 mb-2">
                                WhatsApp Number
                            </label>
                            <input
                                v-model="form.whatsapp_number"
                                type="tel"
                                id="whatsapp_number"
                                placeholder="+1234567890"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                :disabled="sending"
                                required
                            />
                            <p class="text-xs text-gray-500 mt-1">Include country code (e.g., +1234567890)</p>
                        </div>

                        <button
                            type="submit"
                            :disabled="sending || !form.whatsapp_number"
                            class="btn-primary w-full disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <span v-if="sending">Sending...</span>
                            <span v-else>Send Verification Code</span>
                        </button>
                    </form>

                    <!-- Verification Code Form -->
                    <div v-if="verificationSent" class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <h3 class="text-lg font-medium text-blue-900 mb-2">Enter Verification Code</h3>
                        <p class="text-blue-700 text-sm mb-4">
                            We sent a 6-digit code to {{ form.whatsapp_number }}
                        </p>

                        <form @submit.prevent="verifyCode" class="space-y-4">
                            <div>
                                <label for="verification_code" class="block text-sm font-medium text-gray-700 mb-2">
                                    Verification Code
                                </label>
                                <input
                                    v-model="form.verification_code"
                                    type="text"
                                    id="verification_code"
                                    placeholder="123456"
                                    maxlength="6"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-center text-lg tracking-widest"
                                    :disabled="verifying"
                                    required
                                />
                            </div>

                            <div class="flex space-x-3">
                                <button
                                    type="submit"
                                    :disabled="verifying || form.verification_code.length !== 6"
                                    class="flex-1 bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                >
                                    <span v-if="verifying">Verifying...</span>
                                    <span v-else>Verify Code</span>
                                </button>

                                <button
                                    type="button"
                                    @click="resendCode"
                                    :disabled="resending"
                                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                >
                                    <span v-if="resending">Resending...</span>
                                    <span v-else>Resend</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Remove WhatsApp Number -->
                <div v-if="whatsapp_verified" class="mt-6 pt-6 border-t border-gray-200">
                    <button
                        @click="removeWhatsApp"
                        :disabled="removing"
                        class="text-red-600 hover:text-red-700 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <span v-if="removing">Removing...</span>
                        <span v-else>Remove WhatsApp Number</span>
                    </button>
                </div>

                <!-- Error Messages -->
                <div v-if="error" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-red-800 text-sm">{{ error }}</p>
                </div>

                <!-- Success Messages -->
                <div v-if="success" class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-green-800 text-sm">{{ success }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import axios from 'axios'

const props = defineProps({
    whatsapp_number: String,
    whatsapp_verified: Boolean,
    whatsapp_verified_at: String,
})

const form = reactive({
    whatsapp_number: props.whatsapp_number || '',
    verification_code: ''
})

const sending = ref(false)
const verifying = ref(false)
const resending = ref(false)
const removing = ref(false)
const verificationSent = ref(false)
const error = ref('')
const success = ref('')

const sendVerificationCode = async () => {
    sending.value = true
    error.value = ''
    success.value = ''

    try {
        const response = await axios.post(route('whatsapp.verification.send'), {
            whatsapp_number: form.whatsapp_number
        })

        if (response.data.success) {
            verificationSent.value = true
            success.value = response.data.message
        } else {
            error.value = response.data.message
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to send verification code'
    } finally {
        sending.value = false
    }
}

const verifyCode = async () => {
    verifying.value = true
    error.value = ''
    success.value = ''

    try {
        const response = await axios.post(route('whatsapp.verification.verify'), {
            verification_code: form.verification_code
        })

        if (response.data.success) {
            success.value = response.data.message
            // Refresh the page to show updated status
            router.reload()
        } else {
            error.value = response.data.message
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to verify code'
    } finally {
        verifying.value = false
    }
}

const resendCode = async () => {
    resending.value = true
    error.value = ''
    success.value = ''

    try {
        const response = await axios.post(route('whatsapp.verification.send'), {
            whatsapp_number: form.whatsapp_number
        })

        if (response.data.success) {
            success.value = 'Verification code resent successfully'
        } else {
            error.value = response.data.message
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to resend verification code'
    } finally {
        resending.value = false
    }
}

const removeWhatsApp = async () => {
    if (!confirm('Are you sure you want to remove your WhatsApp number? You will not be able to send messages until you verify a new number.')) {
        return
    }

    removing.value = true
    error.value = ''
    success.value = ''

    try {
        const response = await axios.post(route('whatsapp.verification.remove'))

        if (response.data.success) {
            success.value = response.data.message
            // Refresh the page to show updated status
            router.reload()
        } else {
            error.value = response.data.message
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to remove WhatsApp number'
    } finally {
        removing.value = false
    }
}

const formatDate = (dateString) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>
