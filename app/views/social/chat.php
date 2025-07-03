<?php $title = 'Chat - ' . APP_NAME; ?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Chat Header -->
        <div class="bg-white rounded-t-2xl shadow-xl border border-gray-100 border-b-0">
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 px-6 py-4 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <a href="/social" class="text-white hover:text-blue-100 mr-4">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                        </a>
                        <div class="h-12 w-12 rounded-full bg-white/20 backdrop-blur-sm border-2 border-white flex items-center justify-center mr-4">
                            <span class="text-white font-semibold">
                                <?= strtoupper(substr($otherUser['fname'], 0, 1)) ?>
                            </span>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-white">
                                <?= htmlspecialchars($otherUser['fname'] . ' ' . $otherUser['lname']) ?>
                            </h1>
                            <p class="text-blue-100 text-sm">
                                <span id="onlineStatus">●</span> <span id="statusText">Online</span>
                            </p>
                        </div>
                    </div>
                    <div id="typingIndicator" class="text-blue-100 text-sm hidden">
                        <span class="animate-pulse">Typing...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Messages -->
        <div class="bg-white shadow-xl border-l border-r border-gray-100">
            <div id="messagesContainer" class="h-96 overflow-y-auto p-6 space-y-4">
                <?php foreach ($messages as $message): ?>
                    <div class="flex <?= $message['sender_id'] === $currentUserId ? 'justify-end' : 'justify-start' ?>">
                        <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-2xl <?= $message['sender_id'] === $currentUserId ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-900' ?>">
                            <p class="text-sm"><?= htmlspecialchars($message['message_text']) ?></p>
                            <p class="text-xs mt-1 <?= $message['sender_id'] === $currentUserId ? 'text-blue-100' : 'text-gray-500' ?>">
                                <?= date('g:i A', strtotime($message['created_at'])) ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Message Input -->
        <div class="bg-white rounded-b-2xl shadow-xl border border-gray-100 border-t-0 p-6">
            <div class="flex items-center space-x-4">
                <div class="flex-1">
                    <input type="text" id="messageInput" 
                           placeholder="Type a message..." 
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           maxlength="1000">
                </div>
                <button id="sendButton" 
                        class="bg-blue-500 text-white p-3 rounded-xl hover:bg-blue-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </button>
            </div>
            <div class="mt-2 text-xs text-gray-500">
                Press Enter to send • <span id="charCount">0</span>/1000 characters
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.socket.io/4.7.0/socket.io.min.js"></script>
<script>
    const currentUserId = '<?= $currentUserId ?>';
    const otherUserId = '<?= $otherUser['uid'] ?>';
    const otherUserName = '<?= htmlspecialchars($otherUser['fname']) ?>';
    
    let socket = null;
    let isTyping = false;
    let typingTimeout = null;

    // Initialize WebSocket connection
    async function initializeSocket() {
        socket = io('http://localhost:3000');
        
        socket.on('connect', async () => {
            console.log('Connected to chat server');
            try {
                // Get JWT token from API
                const response = await fetch('/api/auth/token', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                const data = await response.json();
                if (data.success && data.token) {
                    socket.emit('authenticate', { token: data.token });
                } else {
                    console.error('Failed to get token:', data.error);
                    updateOnlineStatus('Authentication failed');
                }
            } catch (error) {
                console.error('Token fetch error:', error);
                updateOnlineStatus('Authentication failed');
            }
        });

        socket.on('auth:success', (data) => {
            console.log('Chat authenticated successfully');
            updateOnlineStatus('Online');
        });

        socket.on('auth:error', (data) => {
            console.error('Chat authentication failed:', data.message);
            updateOnlineStatus('Offline');
        });

        socket.on('message:received', (data) => {
            if (data.message.sender_id === otherUserId) {
                addMessageToChat(data.message, false);
                // Mark as read
                socket.emit('message:mark_read', { otherUserId });
            }
        });

        socket.on('message:sent', (data) => {
            // Update any temporary message with the real one
            if (data.clientMessageId) {
                updateTemporaryMessage(data.clientMessageId, data.message);
            }
        });

        socket.on('message:error', (data) => {
            showNotification('Failed to send message: ' + data.message, 'error');
        });

        socket.on('typing:start', (data) => {
            if (data.userId === otherUserId) {
                showTypingIndicator();
            }
        });

        socket.on('typing:stop', (data) => {
            if (data.userId === otherUserId) {
                hideTypingIndicator();
            }
        });

        socket.on('user:online', (data) => {
            if (data.userId === otherUserId) {
                updateOnlineStatus('Online');
            }
        });

        socket.on('user:offline', (data) => {
            if (data.userId === otherUserId) {
                updateOnlineStatus('Offline');
            }
        });

        socket.on('disconnect', () => {
            updateOnlineStatus('Disconnected');
        });
    }

    // Send message
    function sendMessage() {
        const input = document.getElementById('messageInput');
        const text = input.value.trim();
        
        if (!text || !socket) return;

        const clientMessageId = 'temp-' + Date.now();
        
        // Add message to UI immediately (optimistic UI)
        const tempMessage = {
            id: clientMessageId,
            message_text: text,
            sender_id: currentUserId,
            created_at: new Date().toISOString(),
            temporary: true
        };
        addMessageToChat(tempMessage, true);

        // Send via WebSocket
        socket.emit('message:send', {
            recipientId: otherUserId,
            text: text,
            clientMessageId: clientMessageId
        });

        // Clear input
        input.value = '';
        updateCharCount();
        
        // Stop typing indicator
        if (isTyping) {
            socket.emit('typing:stop', { recipientId: otherUserId });
            isTyping = false;
        }
    }

    // Add message to chat UI
    function addMessageToChat(message, isSent) {
        const container = document.getElementById('messagesContainer');
        const messageDiv = document.createElement('div');
        messageDiv.className = `flex ${isSent ? 'justify-end' : 'justify-start'}`;
        messageDiv.id = message.temporary ? message.id : '';
        
        const time = new Date(message.created_at).toLocaleTimeString([], {hour: 'numeric', minute:'2-digit'});
        const bgClass = isSent ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-900';
        const timeClass = isSent ? 'text-blue-100' : 'text-gray-500';
        
        messageDiv.innerHTML = `
            <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-2xl ${bgClass}">
                <p class="text-sm">${escapeHtml(message.message_text)}</p>
                <p class="text-xs mt-1 ${timeClass}">
                    ${time} ${message.temporary ? '⏳' : ''}
                </p>
            </div>
        `;
        
        container.appendChild(messageDiv);
        container.scrollTop = container.scrollHeight;
    }

    // Update temporary message when confirmed
    function updateTemporaryMessage(clientMessageId, realMessage) {
        const tempElement = document.getElementById(clientMessageId);
        if (tempElement) {
            tempElement.id = realMessage.id;
            const timeElement = tempElement.querySelector('.text-xs');
            if (timeElement) {
                const time = new Date(realMessage.created_at).toLocaleTimeString([], {hour: 'numeric', minute:'2-digit'});
                timeElement.textContent = time;
            }
        }
    }

    // Handle typing indicators
    function handleTyping() {
        if (!socket) return;

        if (!isTyping) {
            socket.emit('typing:start', { recipientId: otherUserId });
            isTyping = true;
        }

        clearTimeout(typingTimeout);
        typingTimeout = setTimeout(() => {
            if (isTyping) {
                socket.emit('typing:stop', { recipientId: otherUserId });
                isTyping = false;
            }
        }, 1000);
    }

    function showTypingIndicator() {
        document.getElementById('typingIndicator').classList.remove('hidden');
    }

    function hideTypingIndicator() {
        document.getElementById('typingIndicator').classList.add('hidden');
    }

    // Update online status
    function updateOnlineStatus(status) {
        const statusElement = document.getElementById('statusText');
        const indicator = document.getElementById('onlineStatus');
        
        statusElement.textContent = status;
        
        if (status === 'Online') {
            indicator.className = 'text-green-400';
        } else if (status === 'Offline') {
            indicator.className = 'text-gray-400';
        } else {
            indicator.className = 'text-red-400';
        }
    }

    // Update character count
    function updateCharCount() {
        const input = document.getElementById('messageInput');
        const count = document.getElementById('charCount');
        count.textContent = input.value.length;
        
        if (input.value.length > 900) {
            count.className = 'text-red-500 font-semibold';
        } else {
            count.className = '';
        }
    }

    // Utility functions
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showNotification(message, type = 'info') {
        const div = document.createElement('div');
        const bgColor = type === 'error' ? 'bg-red-500' : 'bg-green-500';
        div.className = `fixed top-4 right-4 ${bgColor} text-white px-4 py-2 rounded-lg shadow-lg z-50`;
        div.textContent = message;
        document.body.appendChild(div);
        setTimeout(() => div.remove(), 3000);
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendButton');
        
        // Initialize WebSocket
        initializeSocket();
        
        // Focus input
        input.focus();
        
        // Send message on Enter
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendMessage();
            } else {
                handleTyping();
            }
        });
        
        // Send message on button click
        sendButton.addEventListener('click', sendMessage);
        
        // Character count
        input.addEventListener('input', updateCharCount);
        
        // Mark messages as read when page loads
        if (socket && socket.connected) {
            socket.emit('message:mark_read', { otherUserId });
        }
        
        // Scroll to bottom
        const container = document.getElementById('messagesContainer');
        container.scrollTop = container.scrollHeight;
    });

    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        if (socket) {
            socket.disconnect();
        }
    });
</script> 