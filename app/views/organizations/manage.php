<?php $title = 'Manage ' . ($organization['org_name'] ?? 'Organization') . ' - ' . APP_NAME; ?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="space-y-8">
            <!-- Page Header -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-500 to-indigo-600 px-6 py-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="h-16 w-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mr-4 border-4 border-white/30">
                                <span class="text-white font-bold text-xl">
                                    <?= strtoupper(substr($organization['org_name'], 0, 2)) ?>
                                </span>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-white"><?= htmlspecialchars($organization['org_name']) ?></h1>
                                <p class="text-purple-100">Organization Management</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <a href="/organizations/show/<?= $organization['org_slug'] ?>" class="inline-flex items-center px-4 py-2 border border-white/30 rounded-xl text-sm font-medium text-white hover:bg-white/20 transition-colors duration-200">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View Organization
                            </a>
                            <a href="/organizations/edit/<?= $organization['org_id'] ?>" class="inline-flex items-center px-4 py-2 bg-white text-purple-600 rounded-xl text-sm font-medium hover:bg-purple-50 transition-colors duration-200 shadow-lg">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Management Tabs -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="border-b border-gray-200">
                    <nav class="flex space-x-8 px-8" aria-label="Tabs">
                        <button onclick="showTab('members')" id="tab-members" class="tab-button active border-transparent text-purple-600 border-b-2 border-purple-500 py-4 px-1 text-sm font-medium">
                            Members
                        </button>
                        <button onclick="showTab('invitations')" id="tab-invitations" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-4 px-1 text-sm font-medium">
                            Invitations
                        </button>
                        <button onclick="showTab('requests')" id="tab-requests" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-4 px-1 text-sm font-medium">
                            Join Requests
                            <?php if (!empty($pendingRequests) && count($pendingRequests) > 0): ?>
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <?= count($pendingRequests) ?>
                                </span>
                            <?php endif; ?>
                        </button>
                    </nav>
                </div>

                <!-- Members Tab -->
                <div id="content-members" class="tab-content p-8">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <div class="h-8 w-8 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center mr-3">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 515.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900">Organization Members</h2>
                        </div>
                        <button onclick="showInviteModal()" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-xl text-sm font-medium text-white hover:from-purple-600 hover:to-indigo-700 transition-colors shadow-lg">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            Invite Members
                        </button>
                    </div>

                    <?php if (!empty($members)): ?>
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-2xl">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($members as $member): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center mr-4">
                                                    <span class="text-white font-semibold text-sm">
                                                        <?= strtoupper(substr($member['fname'], 0, 1) . substr($member['lname'], 0, 1)) ?>
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?= htmlspecialchars($member['fname'] . ' ' . $member['lname']) ?>
                                                    </div>
                                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($member['email']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= 
                                                $member['role'] === 'owner' ? 'bg-red-100 text-red-800' : 
                                                ($member['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 
                                                ($member['role'] === 'executive' ? 'bg-blue-100 text-blue-800' : 
                                                ($member['role'] === 'treasurer' ? 'bg-green-100 text-green-800' :
                                                'bg-gray-100 text-gray-800'))) ?>">
                                                <?= ucfirst($member['role']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= date('M j, Y', strtotime($member['joined_at'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= 
                                                $member['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                                <?= ucfirst($member['status']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <?php if ($member['role'] !== 'owner' && $userRole === 'owner'): ?>
                                                <div class="flex items-center justify-end space-x-2">
                                                    <button onclick="updateMemberRole('<?= $member['user_id'] ?>', '<?= $member['role'] ?>')" class="text-indigo-600 hover:text-indigo-900 text-sm">
                                                        Change Role
                                                    </button>
                                                    <button onclick="removeMember('<?= $member['user_id'] ?>')" class="text-red-600 hover:text-red-900 text-sm">
                                                        Remove
                                                    </button>
                                                </div>
                                            <?php elseif ($member['role'] === 'member' && in_array($userRole, ['owner', 'admin'])): ?>
                                                <button onclick="updateMemberRole('<?= $member['user_id'] ?>', '<?= $member['role'] ?>')" class="text-indigo-600 hover:text-indigo-900 text-sm">
                                                    Promote
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 515.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No members yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by inviting people to join your organization.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Invitations Tab -->
                <div id="content-invitations" class="tab-content hidden p-8">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <div class="h-8 w-8 bg-gradient-to-r from-indigo-500 to-blue-600 rounded-lg flex items-center justify-center mr-3">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900">Pending Invitations</h2>
                        </div>
                    </div>

                    <?php if (!empty($pendingInvitations)): ?>
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-2xl">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sent</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires</th>
                                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($pendingInvitations as $invitation): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($invitation['email']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <?= ucfirst($invitation['role']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= date('M j, Y', strtotime($invitation['created_at'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= date('M j, Y', strtotime($invitation['expires_at'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                <button onclick="resendInvitation('<?= $invitation['id'] ?>')" class="text-indigo-600 hover:text-indigo-900 text-sm">
                                                    Resend
                                                </button>
                                                <button onclick="cancelInvitation('<?= $invitation['id'] ?>')" class="text-red-600 hover:text-red-900 text-sm">
                                                    Cancel
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No pending invitations</h3>
                            <p class="mt-1 text-sm text-gray-500">All invitations have been accepted or have expired.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Join Requests Tab -->
                <div id="content-requests" class="tab-content hidden p-8">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <div class="h-8 w-8 bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg flex items-center justify-center mr-3">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900">Join Requests</h2>
                        </div>
                    </div>

                    <?php if (!empty($pendingRequests)): ?>
                        <div class="grid grid-cols-1 gap-6">
                            <?php foreach ($pendingRequests as $request): ?>
                            <div class="bg-gray-50 rounded-2xl p-6 border border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="h-12 w-12 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mr-4">
                                            <span class="text-white font-semibold">
                                                <?= strtoupper(substr($request['fname'], 0, 1) . substr($request['lname'], 0, 1)) ?>
                                            </span>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                <?= htmlspecialchars($request['fname'] . ' ' . $request['lname']) ?>
                                            </h3>
                                            <p class="text-sm text-gray-500"><?= htmlspecialchars($request['email']) ?></p>
                                            <p class="text-xs text-gray-400">Requested <?= date('M j, Y', strtotime($request['requested_at'])) ?></p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <button onclick="handleJoinRequest('<?= $request['user_id'] ?>', 'accept')" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl text-sm font-medium text-white hover:from-green-600 hover:to-emerald-700 transition-colors">
                                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Accept
                                        </button>
                                        <button onclick="handleJoinRequest('<?= $request['user_id'] ?>', 'decline')" class="inline-flex items-center px-4 py-2 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Decline
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No pending requests</h3>
                            <p class="mt-1 text-sm text-gray-500">There are currently no requests to join this organization.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Invite Modal -->
<div id="inviteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/5 lg:w-1/2 shadow-lg rounded-2xl bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-gray-900">Invite Members</h3>
                <button onclick="hideInviteModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="inviteForm" class="space-y-6">
                <div>
                    <label for="invite_emails" class="block text-sm font-medium text-gray-700 mb-2">Email Addresses</label>
                    <textarea id="invite_emails" name="invite_emails" rows="4" required
                              class="block w-full px-4 py-3 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                              placeholder="Enter email addresses separated by commas or new lines..."></textarea>
                    <p class="mt-2 text-sm text-gray-500">You can enter multiple email addresses separated by commas or on separate lines.</p>
                </div>
                
                <div>
                    <label for="invite_role" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <select id="invite_role" name="invite_role" required
                            class="block w-full px-4 py-3 border border-gray-200 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200">
                        <option value="member">Member</option>
                        <option value="executive">Executive</option>
                        <?php if ($userRole === 'owner'): ?>
                            <option value="admin">Administrator</option>
                            <option value="treasurer">Treasurer</option>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div>
                    <label for="invite_message" class="block text-sm font-medium text-gray-700 mb-2">Personal Message (Optional)</label>
                    <textarea id="invite_message" name="invite_message" rows="3"
                              class="block w-full px-4 py-3 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                              placeholder="Add a personal message to the invitation..."></textarea>
                </div>
                
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="hideInviteModal()" class="px-4 py-2 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-xl text-sm font-medium text-white hover:from-purple-600 hover:to-indigo-700 transition-colors">
                        Send Invitations
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Tab Management
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active', 'border-purple-500', 'text-purple-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Add active class to selected tab button
    const activeButton = document.getElementById('tab-' + tabName);
    activeButton.classList.add('active', 'border-purple-500', 'text-purple-600');
    activeButton.classList.remove('border-transparent', 'text-gray-500');
}

// Modal Management
function showInviteModal() {
    document.getElementById('inviteModal').classList.remove('hidden');
}

function hideInviteModal() {
    document.getElementById('inviteModal').classList.add('hidden');
    document.getElementById('inviteForm').reset();
}

// Invite Form Submission
document.getElementById('inviteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const emails = document.getElementById('invite_emails').value;
    const role = document.getElementById('invite_role').value;
    const message = document.getElementById('invite_message').value;
    
    fetch('/organizations/inviteMembers/<?= $organization['org_slug'] ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            emails: emails,
            role: role,
            message: message
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideInviteModal();
            location.reload();
        } else {
            alert('Failed to send invitations: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to send invitations');
    });
});

// Member Management Functions
function updateMemberRole(userId, currentRole) {
    const roles = ['member', 'executive', 'treasurer', 'admin'];
    const allowedRoles = <?= $userRole === 'owner' ? json_encode(['member', 'executive', 'treasurer', 'admin']) : json_encode(['member', 'executive']) ?>;
    
    const roleOptions = allowedRoles.map(role => 
        `<option value="${role}" ${role === currentRole ? 'selected' : ''}>${role.charAt(0).toUpperCase() + role.slice(1)}</option>`
    ).join('');
    
    const newRole = prompt(`Select new role for member:\n${roleOptions.replace(/<[^>]*>/g, ' ')}`);
    
    if (newRole && allowedRoles.includes(newRole) && newRole !== currentRole) {
        fetch('/organizations/updateMemberRole/<?= $organization['org_id'] ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userId,
                role: newRole
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to update member role: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update member role');
        });
    }
}

function removeMember(userId) {
    if (confirm('Are you sure you want to remove this member from the organization?')) {
        fetch('/organizations/removeMember/<?= $organization['org_id'] ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to remove member: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to remove member');
        });
    }
}

function handleJoinRequest(userId, action) {
    const actionText = action === 'accept' ? 'accept' : 'decline';
    if (confirm(`Are you sure you want to ${actionText} this join request?`)) {
        fetch('/organizations/handleJoinRequest/<?= $organization['org_slug'] ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userId,
                action: action
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(`Failed to ${actionText} request: ` + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(`Failed to ${actionText} request`);
        });
    }
}

function resendInvitation(invitationId) {
    fetch('/organizations/<?= $organization['slug'] ?>/invitations/resend', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            invitation_id: invitationId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Invitation resent successfully');
        } else {
            alert('Failed to resend invitation: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to resend invitation');
    });
}

function cancelInvitation(invitationId) {
    if (confirm('Are you sure you want to cancel this invitation?')) {
        fetch('/organizations/<?= $organization['slug'] ?>/invitations/cancel', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                invitation_id: invitationId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to cancel invitation: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to cancel invitation');
        });
    }
}
</script> 