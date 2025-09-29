@extends('layouts.app')

@section('title', 'WhatsApp Chat')

@section('content')
<div class="flex bg-gray-100" style="height: 90vh;">
    @if($isAdmin)
    <!-- Sidebar (Admin only) -->
    <div class="w-1/3 bg-white border-r border-gray-200 flex flex-col">
        <!-- Search -->
        <div class="p-3 border-b border-gray-200 bg-gray-50">
            <div class="relative">
                <input
                    type="text"
                    placeholder="Search conversations..."
                    class="w-full pl-10 pr-4 py-2 bg-white border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                    id="searchInput"
                />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Conversations List -->
        <div class="flex-1 overflow-y-auto" id="conversationsList">
            <!-- Registered Users Section -->
            @if(count($registeredConversations) > 0)
            <div class="border-b border-gray-200">
                <div class="px-3 py-2 bg-green-50">
                    <h3 class="text-xs font-semibold text-green-700 uppercase tracking-wider">
                        Registered Users
                    </h3>
                </div>
                @foreach($registeredConversations as $conversation)
                <div class="p-3 border-b border-gray-100 cursor-pointer hover:bg-gray-50 transition-colors conversation-item {{ $selectedConversation === $conversation['from'] ? 'bg-gray-100' : '' }}"
                     data-conversation="{{ $conversation['from'] }}">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center relative">
                            <span class="text-white font-semibold text-sm">
                                {{ $conversation['user_name'] ? strtoupper(substr($conversation['user_name'], 0, 1)) : substr($conversation['from'], -2) }}
                            </span>
                            @if($conversation['last_direction'] === 'inbound')
                            <div class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white"></div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ $conversation['user_name'] }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ \Carbon\Carbon::parse($conversation['last_message_at'])->format('H:i') }}
                                </p>
                            </div>
                            <p class="text-sm text-gray-500 truncate">
                                {{ $conversation['last_message'] }}
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <!-- External Numbers Section -->
            @if(count($externalConversations) > 0)
            <div class="border-b border-gray-200">
                <div class="px-3 py-2 bg-orange-50">
                    <h3 class="text-xs font-semibold text-orange-700 uppercase tracking-wider">
                        External Numbers
                    </h3>
                </div>
                @foreach($externalConversations as $conversation)
                <div class="p-3 border-b border-gray-100 cursor-pointer hover:bg-gray-50 transition-colors group conversation-item {{ $selectedConversation === $conversation['from'] ? 'bg-gray-100' : '' }}"
                     data-conversation="{{ $conversation['from'] }}">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-orange-600 rounded-full flex items-center justify-center relative">
                            <span class="text-white font-semibold text-sm">
                                {{ substr($conversation['from'], -2) }}
                            </span>
                            @if($conversation['last_direction'] === 'inbound')
                            <div class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white"></div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ $conversation['from'] }}
                                </p>
                                <div class="flex items-center space-x-2">
                                    <p class="text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($conversation['last_message_at'])->format('H:i') }}
                                    </p>
                                    <button onclick="showAssignModal('{{ $conversation['from'] }}')"
                                            class="opacity-0 group-hover:opacity-100 transition-opacity p-1 text-orange-600 hover:text-orange-800"
                                            title="Assign to user">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500 truncate">
                                {{ $conversation['last_message'] }}
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Start New Conversation Section -->
            @if(count($usersWithWhatsApp) > 0)
            <div class="border-b border-gray-200">
                <div class="px-3 py-2 bg-blue-50">
                    <h3 class="text-xs font-semibold text-blue-700 uppercase tracking-wider">
                        Start New Conversation
                    </h3>
                </div>
                @foreach($usersWithWhatsApp as $user)
                <div class="p-3 border-b border-gray-100 cursor-pointer hover:bg-gray-50 transition-colors conversation-item {{ $selectedConversation === $user->whatsapp_number ? 'bg-gray-100' : '' }}"
                     data-conversation="{{ $user->whatsapp_number }}">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-semibold text-sm">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ $user->name }}
                            </p>
                            <p class="text-sm text-gray-500 truncate">
                                {{ $user->whatsapp_number }}
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Empty State -->
            @if(count($registeredConversations) === 0 && count($externalConversations) === 0)
            <div class="flex-1 flex items-center justify-center text-gray-500">
                <div class="text-center">
                    <div class="text-4xl mb-4">💬</div>
                    <h3 class="text-lg font-medium mb-2">No conversations yet</h3>
                    <p class="text-sm">Start a conversation to see it here</p>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Main Chat Area -->
    <div class="{{ $isAdmin ? 'flex-1' : 'w-full' }} flex flex-col">
        @if($selectedConversation)
        <!-- Chat Header -->
        <div class="bg-white border-b border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                        <span class="text-white font-semibold text-sm">
                            {{ $selectedConversationData['user_name'] ? strtoupper(substr($selectedConversationData['user_name'], 0, 1)) : substr($selectedConversation, -2) }}
                        </span>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ $selectedConversationData['user_name'] ?? $selectedConversation }}
                        </h2>
                        <p class="text-sm text-gray-500">
                            {{ $selectedConversationData['user_name'] ? $selectedConversation : 'External Number' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Banner (Non-admin users) -->
        @if(!$isAdmin)
        <div class="bg-blue-50 border-b border-blue-200 p-4">
            <div class="max-w-7xl mx-auto">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-blue-900">WhatsApp Chat with Admin</h3>
                        <p class="text-sm text-blue-700">Use your verified WhatsApp number to message admin</p>
                    </div>
                    <a href="https://wa.me/{{ $adminWhatsAppNumber }}"
                       target="_blank"
                       class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                        </svg>
                        Message Admin
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- Messages Area -->
        <div class="flex-1 overflow-y-auto p-4 {{ !$isAdmin ? 'max-w-7xl mx-auto' : '' }}" id="messagesContainer">
            @if(count($messages) > 0)
                @foreach($messages as $message)
                <div class="mb-4 {{ $message['direction'] === 'outbound' ? 'flex justify-end' : 'flex justify-start' }}">
                    <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg {{ $message['direction'] === 'outbound' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800' }}">
                        <p class="text-sm">{{ $message['body'] }}</p>
                        <p class="text-xs mt-1 {{ $message['direction'] === 'outbound' ? 'text-blue-100' : 'text-gray-500' }}">
                            {{ \Carbon\Carbon::parse($message['created_at'])->format('H:i') }}
                        </p>
                    </div>
                </div>
                @endforeach
            @else
                <div class="flex items-center justify-center h-full text-gray-500">
                    <div class="text-center">
                        <div class="text-4xl mb-4">💬</div>
                        <h3 class="text-lg font-medium mb-2">No messages yet</h3>
                        <p class="text-sm">Start the conversation by sending a message</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Message Input -->
        <div class="bg-white border-t border-gray-200 p-4 sticky bottom-0 z-10 {{ !$isAdmin ? 'max-w-7xl mx-auto' : '' }}">
            <form id="messageForm" class="flex space-x-2">
                <input type="hidden" name="conversation" value="{{ $selectedConversation }}">
                <input type="text"
                       name="message"
                       placeholder="Type your message..."
                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       required>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
                        id="sendButton">
                    Send
                </button>
            </form>
        </div>
        @else
        <!-- No Conversation Selected -->
        <div class="flex-1 flex items-center justify-center text-gray-500">
            <div class="text-center">
                <div class="text-4xl mb-4">💬</div>
                <h3 class="text-lg font-medium mb-2">No conversation selected</h3>
                <p class="text-sm">Choose a conversation from the sidebar to start chatting</p>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Assign Number Modal -->
<div id="assignModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Assign Number to User</h3>
                <button onclick="closeAssignModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Phone Number Display -->
            <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                <p class="text-lg font-mono text-gray-900" id="modalPhoneNumber"></p>
            </div>

            <!-- User Selection -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
                <select id="userSelect" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Choose a user...</option>
                    @foreach($usersWithWhatsApp as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3">
                <button onclick="closeAssignModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Cancel
                </button>
                <button onclick="assignNumber()" id="assignButton" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    Assign Number
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Chat functionality
document.addEventListener('DOMContentLoaded', function() {
    // Conversation selection
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.addEventListener('click', function() {
            const conversation = this.dataset.conversation;
            if (conversation) {
                window.location.href = '/chat?conversation=' + encodeURIComponent(conversation);
            }
        });
    });

    // Message sending
    document.getElementById('messageForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const sendButton = document.getElementById('sendButton');

        sendButton.disabled = true;
        sendButton.textContent = 'Sending...';

        fetch('/chat/send', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload page to show new message
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error sending message');
        })
        .finally(() => {
            sendButton.disabled = false;
            sendButton.textContent = 'Send';
        });
    });

    // Auto-scroll to bottom
    const messagesContainer = document.getElementById('messagesContainer');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
});

// Assign modal functions
let selectedPhoneNumber = '';

function showAssignModal(phoneNumber) {
    selectedPhoneNumber = phoneNumber;
    document.getElementById('modalPhoneNumber').textContent = phoneNumber;
    document.getElementById('assignModal').classList.remove('hidden');
}

function closeAssignModal() {
    document.getElementById('assignModal').classList.add('hidden');
    document.getElementById('userSelect').value = '';
    selectedPhoneNumber = '';
}

function assignNumber() {
    const userId = document.getElementById('userSelect').value;
    if (!userId) return;

    const assignButton = document.getElementById('assignButton');
    assignButton.disabled = true;
    assignButton.textContent = 'Assigning...';

    fetch('/chat/assign-number', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            phone_number: selectedPhoneNumber,
            user_id: userId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error assigning number');
    })
    .finally(() => {
        assignButton.disabled = false;
        assignButton.textContent = 'Assign Number';
    });
}
</script>
@endsection
