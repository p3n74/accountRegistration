<?php $title = 'Social - ' . APP_NAME; ?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="h-16 w-16 rounded-full bg-white/20 backdrop-blur-sm border-4 border-white shadow-lg flex items-center justify-center mr-6">
                            <svg class="h-8 w-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-white">Social Hub</h1>
                            <p class="text-blue-100 mt-1">Connect with fellow Carolinians</p>
                        </div>
                    </div>
                    <a href="/social/search" class="bg-white/20 backdrop-blur-sm text-white px-4 py-2 rounded-xl hover:bg-white/30 transition-all duration-200 flex items-center space-x-2">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <span>Search</span>
                    </a>
                </div>
            </div>
            <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-purple-50">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600"><?= $followCounts['followers'] ?></div>
                        <div class="text-sm text-gray-600">Followers</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600"><?= $followCounts['following'] ?></div>
                        <div class="text-sm text-gray-600">Following</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600"><?= $unreadCount ?></div>
                        <div class="text-sm text-gray-600">Unread Messages</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Messages -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-xl border border-gray-100">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b">
                    <h2 class="text-xl font-bold text-gray-900">Recent Conversations</h2>
                </div>
                <div class="max-h-96 overflow-y-auto">
                    <?php if (!empty($conversations)): ?>
                        <?php foreach ($conversations as $conv): ?>
                            <div class="border-b border-gray-100 hover:bg-gray-50">
                                <a href="/social/messages/<?= $conv['other_user_id'] ?>" class="block p-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                            <span class="text-white font-semibold">
                                                <?= strtoupper(substr($conv['fname'], 0, 1)) ?>
                                            </span>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex justify-between">
                                                <p class="font-semibold text-gray-900">
                                                    <?= htmlspecialchars($conv['fname'] . ' ' . $conv['lname']) ?>
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    <?= date('M j', strtotime($conv['last_message_time'])) ?>
                                                </p>
                                            </div>
                                            <p class="text-sm text-gray-600 truncate">
                                                <?= htmlspecialchars(substr($conv['last_message'], 0, 50)) ?>...
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-8 text-center">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No conversations yet</h3>
                            <p class="text-gray-600 mb-4">Start connecting with other Carolinians!</p>
                            <a href="/social/search" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                                Find People
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="/social/search" class="flex items-center p-3 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100">
                            <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Search Users
                        </a>
                        <a href="/social/following" class="flex items-center p-3 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100">
                            <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Manage Following
                        </a>
                    </div>
                </div>

                <!-- People You May Know -->
                <?php if (!empty($suggestions)): ?>
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">People You May Know</h3>
                    <div class="space-y-4">
                        <?php foreach (array_slice($suggestions, 0, 3) as $suggestion): ?>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 flex items-center justify-center">
                                        <span class="text-white text-sm font-semibold">
                                            <?= strtoupper(substr($suggestion['fname'], 0, 1)) ?>
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($suggestion['fname'] . ' ' . $suggestion['lname']) ?>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <?= $suggestion['mutual_connections'] ?> mutual
                                        </p>
                                    </div>
                                </div>
                                <button onclick="followUser('<?= $suggestion['uid'] ?>')" class="bg-blue-500 text-white px-3 py-1 rounded-lg text-sm hover:bg-blue-600">
                                    Follow
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.socket.io/4.7.0/socket.io.min.js"></script>
<script>
    let socket = null;
    
    // Initialize WebSocket
    async function initSocket() {
        socket = io('http://localhost:3000');
        socket.on('connect', async () => {
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
                }
            } catch (error) {
                console.error('Token fetch error:', error);
            }
        });
        socket.on('message:received', (data) => {
            showNotification(`New message from ${data.message.sender_fname}`);
        });
    }

    // Follow user
    async function followUser(userId) {
        try {
            const response = await fetch(`/api/users/${userId}/follow`, { method: 'POST' });
            const data = await response.json();
            if (data.success) {
                showNotification('User followed successfully!');
                setTimeout(() => location.reload(), 1000);
            }
        } catch (error) {
            showNotification('Error following user');
        }
    }

    // Show notification
    function showNotification(message) {
        const div = document.createElement('div');
        div.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
        div.textContent = message;
        document.body.appendChild(div);
        setTimeout(() => div.remove(), 3000);
    }

    // Initialize on load
    document.addEventListener('DOMContentLoaded', initSocket);
</script> 