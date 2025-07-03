<div class="py-4 flex items-center justify-between">
    <div class="flex items-center space-x-4">
        <div class="h-14 w-14 rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 flex items-center justify-center">
            <span class="text-white text-lg font-semibold"><?= strtoupper(substr(
                htmlspecialchars(
                    isset(
                        /*PHP*/ $user['fname']
                    ) ? $user['fname'] : ''
                ), 0, 1)) ?></span>
        </div>
        <div>
            <h3 class="text-md font-semibold text-gray-900"><?php echo htmlspecialchars($user['fname'] . ' ' . $user['lname']); ?></h3>
            <?php if (!empty($user['username'])): ?>
                <p class="text-sm text-gray-600">@<?php echo htmlspecialchars($user['username']); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <div class="flex space-x-3">
        <a href="/social/messages/<?php echo $user['uid']; ?>" class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-200 transition-colors text-sm flex items-center space-x-1">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg><span>Message</span></a>

        <?php if (isset($user['is_following']) && $user['is_following']): ?>
            <button onclick="unfollowUser('<?= $user['uid'] ?>', this)" class="bg-gray-500 text-white px-3 py-1.5 rounded-lg hover:bg-gray-600 transition-colors text-sm">Following</button>
        <?php else: ?>
            <button onclick="followUser('<?= $user['uid'] ?>', this)" class="bg-blue-500 text-white px-3 py-1.5 rounded-lg hover:bg-blue-600 transition-colors text-sm">Follow</button>
        <?php endif; ?>
    </div>
</div>