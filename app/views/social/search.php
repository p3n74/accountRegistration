<?php $title = 'Search Users - ' . APP_NAME; ?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="space-y-8">
            <!-- Header -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <div class="h-10 w-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center mr-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900">Search Users</h1>
                    </div>
                    <a href="/social" class="text-gray-600 hover:text-gray-900 flex items-center">
                        <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Social
                    </a>
                </div>
                
                <!-- Search Form -->
                <form method="GET" class="relative">
                    <div class="flex">
                        <input type="text" name="q" value="<?= htmlspecialchars($query) ?>" 
                               placeholder="Search for users..." 
                               class="flex-1 px-4 py-3 border border-gray-300 rounded-l-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               id="searchInput">
                        <button type="submit" class="bg-blue-500 text-white px-6 py-3 rounded-r-xl hover:bg-blue-600 transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Search Results -->
            <?php if ($query): ?>
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b">
                        <h2 class="text-xl font-bold text-gray-900">
                            Search Results for "<?= htmlspecialchars($query) ?>"
                            <?php if (!empty($searchResults)): ?>
                                <span class="text-sm font-normal text-gray-600">(<?= count($searchResults) ?> found)</span>
                            <?php endif; ?>
                        </h2>
                    </div>
                    
                    <?php if (!empty($searchResults)): ?>
                        <div class="divide-y divide-gray-100">
                            <?php foreach ($searchResults as $user): ?>
                                <div class="p-6 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="h-16 w-16 rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 flex items-center justify-center">
                                                <span class="text-white text-lg font-semibold">
                                                    <?= strtoupper(substr($user['fname'], 0, 1)) ?>
                                                </span>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-900">
                                                    <?= htmlspecialchars($user['fname'] . ' ' . $user['lname']) ?>
                                                </h3>
                                                <?php if ($user['username']): ?>
                                                    <p class="text-sm text-gray-600">@<?= htmlspecialchars($user['username']) ?></p>
                                                <?php endif; ?>
                                                <div class="flex items-center space-x-4 mt-1 text-xs text-gray-500">
                                                    <?php $fc = $user['follower_count'] ?? 0; $flwc = $user['following_count'] ?? 0; ?>
                                                    <span id="followers-text-<?= $user['uid'] ?>"><?= $fc ?> <?= $fc == 1 ? 'follower' : 'followers' ?></span>
                                                    <span id="following-text-<?= $user['uid'] ?>"><?= $flwc ?> following</span>
                                                    <?php if ($user['is_student']): ?>
                                                        <span class="bg-emerald-100 text-emerald-800 px-2 py-1 rounded">Student</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="flex space-x-3">
                                            <a href="/social/messages/<?= $user['uid'] ?>" 
                                               class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors flex items-center space-x-2">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                                </svg>
                                                <span>Message</span>
                                            </a>
                                            
                                            <?php if (isset($user['is_following']) && $user['is_following']): ?>
                                                <button onclick="unfollowUser('<?= $user['uid'] ?>', this)" 
                                                        class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                                                    Following
                                                </button>
                                            <?php else: ?>
                                                <button onclick="followUser('<?= $user['uid'] ?>', this)" 
                                                        class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                                                    Follow
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-12 text-center">
                            <svg class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No users found</h3>
                            <p class="text-gray-600">Try searching with different keywords</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-12 text-center">
                    <svg class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Search for Carolinians</h3>
                    <p class="text-gray-600">Enter a name or username to find fellow students and staff</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Auto-focus search input
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('searchInput').focus();
    });

    // Follow user
    async function followUser(userId) {
        try {
            const response = await fetch(`/api/users/${userId}/follow`, { method: 'POST' });
            const data = await response.json();
            if (data.success) {
                showNotification('User followed successfully!');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification(data.error || 'Failed to follow user');
            }
        } catch (error) {
            showNotification('Network error occurred');
        }
    }

    // Unfollow user
    async function unfollowUser(userId) {
        try {
            const response = await fetch(`/api/users/${userId}/unfollow`, { method: 'DELETE' });
            const data = await response.json();
            if (data.success) {
                showNotification('User unfollowed successfully!');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification(data.error || 'Failed to unfollow user');
            }
        } catch (error) {
            showNotification('Network error occurred');
        }
    }

    // Show notification
    function showNotification(message) {
        const div = document.createElement('div');
        div.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 transform transition-all duration-300';
        div.textContent = message;
        document.body.appendChild(div);
        setTimeout(() => {
            div.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => div.remove(), 300);
        }, 3000);
    }
</script> 