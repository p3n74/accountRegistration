<?php $title = 'Organizations - ' . APP_NAME; ?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="space-y-8">
            <!-- Page Header -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Organizations</h1>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Discover and join student organizations, clubs, and groups on campus. Create your own organization to build community around your interests.
                </p>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8">
                <div class="flex items-center mb-6">
                    <div class="h-8 w-8 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center mr-3">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Quick Actions</h2>
                </div>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <a href="/organizations/create" class="group relative bg-gradient-to-br from-purple-50 to-purple-100 p-6 rounded-2xl border border-purple-200 hover:shadow-lg hover:scale-105 transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div class="h-12 w-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                            <svg class="h-5 w-5 text-purple-400 group-hover:text-purple-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-purple-700 transition-colors">Create Organization</h3>
                            <p class="mt-2 text-sm text-gray-600">Start a new student organization and invite members to join</p>
                        </div>
                    </a>

                    <a href="#browse-organizations" class="group relative bg-gradient-to-br from-indigo-50 to-indigo-100 p-6 rounded-2xl border border-indigo-200 hover:shadow-lg hover:scale-105 transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div class="h-12 w-12 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <svg class="h-5 w-5 text-indigo-400 group-hover:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-indigo-700 transition-colors">Browse Organizations</h3>
                            <p class="mt-2 text-sm text-gray-600">Discover existing organizations and request to join</p>
                        </div>
                    </a>

                    <div class="group relative bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-2xl border border-blue-200">
                        <div class="flex items-center justify-between">
                            <div class="h-12 w-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-lg font-semibold text-gray-900">My Stats</h3>
                            <div class="mt-3 space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Organizations Joined:</span>
                                    <span class="font-semibold text-blue-600"><?= count($userMemberships ?? []) ?></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Organizations Owned:</span>
                                    <span class="font-semibold text-purple-600"><?= count($ownedOrganizations ?? []) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Your Organizations -->
            <?php if (!empty($userMemberships)): ?>
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-50 to-indigo-50 px-8 py-6 border-b border-gray-200">
                    <div class="flex items-center">
                        <div class="h-8 w-8 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center mr-3">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Your Organizations</h2>
                    </div>
                </div>
                <div class="p-8">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <?php foreach ($userMemberships as $membership): ?>
                        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-200 hover:scale-105">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="h-12 w-12 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center">
                                        <span class="text-white font-bold text-lg">
                                            <?= strtoupper(substr($membership['org_name'], 0, 2)) ?>
                                        </span>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= 
                                        $membership['role'] === 'owner' ? 'bg-red-100 text-red-800' : 
                                        ($membership['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 
                                        ($membership['role'] === 'executive' ? 'bg-blue-100 text-blue-800' : 
                                        'bg-gray-100 text-gray-800')) ?>">
                                        <?= ucfirst($membership['role']) ?>
                                    </span>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2"><?= htmlspecialchars($membership['org_name']) ?></h3>
                                <p class="text-sm text-gray-600 mb-4"><?= htmlspecialchars($membership['org_type']) ?> • <?= $membership['member_count'] ?> members</p>
                                <?php if (!empty($membership['org_description'])): ?>
                                    <p class="text-sm text-gray-500 mb-4 line-clamp-2"><?= htmlspecialchars(substr($membership['org_description'], 0, 100)) ?>...</p>
                                <?php endif; ?>
                                <div class="flex space-x-2">
                                    <a href="/organizations/show/<?= $membership['org_slug'] ?>" class="flex-1 inline-flex justify-center items-center py-2 px-3 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                        View
                                    </a>
                                    <?php if (in_array($membership['role'], ['owner', 'admin'])): ?>
                                    <a href="/organizations/manage/<?= $membership['org_slug'] ?>" class="flex-1 inline-flex justify-center items-center py-2 px-3 border border-transparent rounded-lg text-sm font-medium text-white bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 transition-colors">
                                        Manage
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Browse Organizations -->
            <div id="browse-organizations" class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-50 to-blue-50 px-8 py-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="h-8 w-8 bg-gradient-to-r from-indigo-500 to-blue-600 rounded-lg flex items-center justify-center mr-3">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900">Discover Organizations</h2>
                        </div>
                        <div class="flex items-center space-x-4">
                            <!-- Search -->
                            <div class="relative">
                                <input type="text" id="organization-search" placeholder="Search organizations..." 
                                       class="block w-64 px-4 py-2 pl-10 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <!-- Filter -->
                            <select id="organization-filter" class="block px-4 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white">
                                <option value="all">All Types</option>
                                <option value="student">Student Organizations</option>
                                <option value="club">Clubs</option>
                                <option value="department">Departments</option>
                                <option value="external">External</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="p-8">
                    <?php if (!empty($allOrganizations)): ?>
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3" id="organizations-grid">
                            <?php foreach ($allOrganizations as $org): ?>
                            <div class="organization-card bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-200 hover:scale-105" 
                                 data-type="<?= $org['org_type'] ?>" data-name="<?= strtolower($org['org_name']) ?>">
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="h-12 w-12 bg-gradient-to-r from-indigo-500 to-blue-600 rounded-xl flex items-center justify-center">
                                            <span class="text-white font-bold text-lg">
                                                <?= strtoupper(substr($org['org_name'], 0, 2)) ?>
                                            </span>
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <?= ucfirst($org['org_type']) ?>
                                        </span>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2"><?= htmlspecialchars($org['org_name']) ?></h3>
                                    <p class="text-sm text-gray-600 mb-4"><?= $org['member_count'] ?> members • Active</p>
                                    <?php if (!empty($org['org_description'])): ?>
                                        <p class="text-sm text-gray-500 mb-4 line-clamp-2"><?= htmlspecialchars(substr($org['org_description'], 0, 100)) ?>...</p>
                                    <?php endif; ?>
                                    <div class="flex space-x-2">
                                        <a href="/organizations/show/<?= $org['org_slug'] ?>" class="flex-1 inline-flex justify-center items-center py-2 px-3 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                            View Details
                                        </a>
                                        <?php if (!$org['is_member']): ?>
                                        <button onclick="requestToJoin('<?= $org['org_id'] ?>')" class="flex-1 inline-flex justify-center items-center py-2 px-3 border border-transparent rounded-lg text-sm font-medium text-white bg-gradient-to-r from-indigo-500 to-blue-600 hover:from-indigo-600 hover:to-blue-700 transition-colors">
                                            Request to Join
                                        </button>
                                        <?php else: ?>
                                        <span class="flex-1 inline-flex justify-center items-center py-2 px-3 rounded-lg text-sm font-medium text-emerald-700 bg-emerald-100">
                                            Member
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <div class="mx-auto h-16 w-16 bg-gradient-to-r from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center mb-6">
                                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Organizations Yet</h3>
                            <p class="text-gray-600 mb-6">Be the first to create an organization on My Carolinian!</p>
                            <a href="/organizations/create" class="inline-flex items-center px-6 py-3 border border-transparent rounded-xl text-sm font-medium text-white bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 transition-colors shadow-lg hover:shadow-xl">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Create Organization
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('organization-search').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const filter = document.getElementById('organization-filter').value;
    filterOrganizations(searchTerm, filter);
});

document.getElementById('organization-filter').addEventListener('change', function(e) {
    const filter = e.target.value;
    const searchTerm = document.getElementById('organization-search').value.toLowerCase();
    filterOrganizations(searchTerm, filter);
});

function filterOrganizations(searchTerm, filter) {
    const cards = document.querySelectorAll('.organization-card');
    
    cards.forEach(card => {
        const name = card.dataset.name;
        const type = card.dataset.type;
        
        const matchesSearch = name.includes(searchTerm);
        const matchesFilter = filter === 'all' || type === filter;
        
        if (matchesSearch && matchesFilter) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Request to join functionality
function requestToJoin(organizationId) {
    // This would be implemented with AJAX to send a join request
    fetch('/organizations/request-join', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            organization_id: organizationId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update button to show request sent
            event.target.textContent = 'Request Sent';
            event.target.classList.remove('bg-gradient-to-r', 'from-indigo-500', 'to-blue-600', 'hover:from-indigo-600', 'hover:to-blue-700');
            event.target.classList.add('bg-gray-300', 'text-gray-700');
            event.target.disabled = true;
        } else {
            alert('Failed to send request: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to send request');
    });
}
</script> 