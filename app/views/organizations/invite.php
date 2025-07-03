<?php $title = 'Organization Invitation - ' . APP_NAME; ?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100 flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <?php if ($invitation && $invitation['status'] === 'pending' && !$invitation['expired']): ?>
            <!-- Valid Invitation -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-500 to-indigo-600 px-6 py-8 text-center">
                    <div class="mx-auto h-16 w-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mb-4 border-4 border-white/30">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-white mb-2">You're Invited!</h1>
                    <p class="text-purple-100">Join an organization on My Carolinian</p>
                </div>
                
                <div class="p-8">
                    <div class="text-center mb-8">
                        <div class="h-16 w-16 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <span class="text-white font-bold text-xl">
                                <?= strtoupper(substr($organization['name'], 0, 2)) ?>
                            </span>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($organization['name']) ?></h2>
                        <p class="text-gray-600 mb-1"><?= ucfirst($organization['type']) ?> Organization</p>
                        <p class="text-sm text-gray-500"><?= $organization['member_count'] ?> members</p>
                    </div>
                    
                    <?php if (!empty($organization['short_description'])): ?>
                        <div class="mb-6">
                            <p class="text-gray-600 text-center"><?= htmlspecialchars($organization['short_description']) ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-xl p-4 mb-6">
                        <div class="flex items-center">
                            <div class="h-8 w-8 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center mr-3">
                                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-700">
                                    <span class="font-medium">Role:</span> <?= ucfirst($invitation['role']) ?>
                                </p>
                                <p class="text-xs text-gray-500">
                                    Invited by the organization administrators
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($invitation['message'])): ?>
                        <div class="mb-6">
                            <h3 class="text-sm font-medium text-gray-900 mb-2">Personal Message:</h3>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <p class="text-sm text-gray-700"><?= nl2br(htmlspecialchars($invitation['message'])) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!isset($_SESSION['uid'])): ?>
                        <!-- User not logged in -->
                        <div class="space-y-4">
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                                <div class="flex items-start">
                                    <svg class="h-5 w-5 text-blue-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        <h3 class="text-sm font-medium text-blue-800">Account Required</h3>
                                        <p class="text-sm text-blue-700 mt-1">
                                            You need to sign in or create an account to accept this invitation.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex space-x-3">
                                <a href="/auth/login?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="flex-1 inline-flex justify-center items-center py-3 px-4 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                    Sign In
                                </a>
                                <a href="/auth/register?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="flex-1 inline-flex justify-center items-center py-3 px-4 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-xl text-sm font-medium text-white hover:from-purple-600 hover:to-indigo-700 transition-colors shadow-lg">
                                    Create Account
                                </a>
                            </div>
                        </div>
                    <?php elseif ($invitation['email'] !== ($_SESSION['email'] ?? '')): ?>
                        <!-- Wrong user -->
                        <div class="space-y-4">
                            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                                <div class="flex items-start">
                                    <svg class="h-5 w-5 text-yellow-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    <div>
                                        <h3 class="text-sm font-medium text-yellow-800">Wrong Account</h3>
                                        <p class="text-sm text-yellow-700 mt-1">
                                            This invitation was sent to <?= htmlspecialchars($invitation['email']) ?>, but you're signed in as <?= htmlspecialchars($_SESSION['email'] ?? '') ?>.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex space-x-3">
                                <a href="/auth/logout?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="flex-1 inline-flex justify-center items-center py-3 px-4 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                    Sign Out & Switch
                                </a>
                                <a href="/organizations/<?= $organization['slug'] ?>" class="flex-1 inline-flex justify-center items-center py-3 px-4 bg-gradient-to-r from-gray-500 to-gray-600 rounded-xl text-sm font-medium text-white hover:from-gray-600 hover:to-gray-700 transition-colors">
                                    View Organization
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Valid user, show accept/decline options -->
                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="token" value="<?= htmlspecialchars($invitation['token']) ?>">
                            
                            <div class="flex space-x-3">
                                <button type="submit" name="action" value="decline" class="flex-1 inline-flex justify-center items-center py-3 px-4 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Decline
                                </button>
                                <button type="submit" name="action" value="accept" class="flex-1 inline-flex justify-center items-center py-3 px-4 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-xl text-sm font-medium text-white hover:from-purple-600 hover:to-indigo-700 transition-colors shadow-lg">
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Accept Invitation
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                    
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <p class="text-xs text-gray-500 text-center">
                            Invitation expires on <?= date('M j, Y \a\t g:i A', strtotime($invitation['expires_at'])) ?>
                        </p>
                    </div>
                </div>
            </div>
            
        <?php elseif ($invitation && $invitation['status'] === 'accepted'): ?>
            <!-- Already Accepted -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-8 text-center">
                    <div class="mx-auto h-16 w-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mb-4 border-4 border-white/30">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-white mb-2">Already Accepted</h1>
                    <p class="text-green-100">You're already a member of this organization</p>
                </div>
                
                <div class="p-8 text-center">
                    <h2 class="text-xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($organization['name']) ?></h2>
                    <p class="text-gray-600 mb-6">You accepted this invitation on <?= date('M j, Y', strtotime($invitation['accepted_at'])) ?></p>
                    
                    <a href="/organizations/<?= $organization['slug'] ?>" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl text-sm font-medium text-white hover:from-green-600 hover:to-emerald-700 transition-colors shadow-lg">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        View Organization
                    </a>
                </div>
            </div>
            
        <?php elseif ($invitation && $invitation['expired']): ?>
            <!-- Expired Invitation -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-500 to-gray-600 px-6 py-8 text-center">
                    <div class="mx-auto h-16 w-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mb-4 border-4 border-white/30">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-white mb-2">Invitation Expired</h1>
                    <p class="text-gray-100">This invitation is no longer valid</p>
                </div>
                
                <div class="p-8 text-center">
                    <h2 class="text-xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($organization['name']) ?></h2>
                    <p class="text-gray-600 mb-6">This invitation expired on <?= date('M j, Y', strtotime($invitation['expires_at'])) ?></p>
                    
                    <div class="space-y-4">
                        <p class="text-sm text-gray-500">
                            You can still request to join this organization if it allows public membership requests.
                        </p>
                        <a href="/organizations/<?= $organization['slug'] ?>" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-xl text-sm font-medium text-white hover:from-purple-600 hover:to-indigo-700 transition-colors shadow-lg">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 616 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            View Organization
                        </a>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Invalid Invitation -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-8 text-center">
                    <div class="mx-auto h-16 w-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mb-4 border-4 border-white/30">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-white mb-2">Invalid Invitation</h1>
                    <p class="text-red-100">This invitation link is not valid</p>
                </div>
                
                <div class="p-8 text-center">
                    <p class="text-gray-600 mb-6">
                        The invitation link you followed is invalid or has been canceled. This may happen if the invitation was manually canceled by an organization administrator.
                    </p>
                    
                    <a href="/organizations" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-xl text-sm font-medium text-white hover:from-purple-600 hover:to-indigo-700 transition-colors shadow-lg">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Browse Organizations
                    </a>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Footer -->
        <div class="mt-8 text-center">
            <a href="/organizations" class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
                ← Back to Organizations
            </a>
        </div>
    </div>
</div> 