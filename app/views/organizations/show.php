<?php $title = ($organization['org_name'] ?? 'Organization') . ' - ' . APP_NAME; ?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="space-y-8">
            <!-- Organization Header -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-500 to-indigo-600 px-6 py-8">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="h-20 w-20 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mr-6 border-4 border-white/30">
                                <span class="text-white font-bold text-2xl">
                                    <?= strtoupper(substr($organization['org_name'], 0, 2)) ?>
                                </span>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-white mb-2"><?= htmlspecialchars($organization['org_name']) ?></h1>
                                <div class="flex items-center space-x-4 text-purple-100">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white/20 text-white">
                                        <?= ucfirst($organization['org_type']) ?>
                                    </span>
                                    <span class="flex items-center">
                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <?= $organization['member_count'] ?> members
                                    </span>
                                    <span class="flex items-center">
                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Founded <?= date('M Y', strtotime($organization['created_at'])) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex items-center space-x-3">
                            <?php if ($userRole): ?>
                                <!-- User is a member -->
                                <?php if (in_array($userRole, ['owner', 'admin'])): ?>
                                    <a href="/organizations/manage/<?= $organization['org_slug'] ?>" class="inline-flex items-center px-4 py-2 border border-white/30 rounded-xl text-sm font-medium text-white hover:bg-white/20 transition-colors duration-200">
                                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        Manage
                                    </a>
                                <?php endif; ?>
                                <span class="inline-flex items-center px-4 py-2 bg-white/20 rounded-xl text-sm font-medium text-white">
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Member (<?= ucfirst($userRole) ?>)
                                </span>
                            <?php elseif ($hasPendingRequest): ?>
                                <span class="inline-flex items-center px-4 py-2 bg-white/20 rounded-xl text-sm font-medium text-white">
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Request Pending
                                </span>
                            <?php else: ?>
                                <button onclick="requestToJoin('<?= $organization['org_id'] ?>')" class="inline-flex items-center px-4 py-2 bg-white text-purple-600 rounded-xl text-sm font-medium hover:bg-purple-50 transition-colors duration-200 shadow-lg">
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                    </svg>
                                    Request to Join
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Organization Stats -->
                <div class="bg-gradient-to-r from-purple-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600"><?= $organization['member_count'] ?></div>
                            <div class="text-sm text-gray-600">Members</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-indigo-600"><?= $organization['event_count'] ?? 0 ?></div>
                            <div class="text-sm text-gray-600">Events Hosted</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600"><?= count($recentActivity ?? []) ?></div>
                            <div class="text-sm text-gray-600">Recent Activities</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-indigo-600">
                                <?= $organization['status'] === 'active' ? 'Active' : ucfirst($organization['status']) ?>
                            </div>
                            <div class="text-sm text-gray-600">Status</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- About Section -->
                    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8">
                        <div class="flex items-center mb-6">
                            <div class="h-8 w-8 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center mr-3">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900">About</h2>
                        </div>
                        
                        <?php if (!empty($organization['short_description'])): ?>
                            <div class="mb-6">
                                <p class="text-lg text-gray-600 leading-relaxed"><?= htmlspecialchars($organization['short_description']) ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($organization['org_description'])): ?>
                            <div class="prose prose-gray max-w-none">
                                <?= nl2br(htmlspecialchars($organization['org_description'])) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Events Section -->
                    <div class="grid grid-cols-1 gap-8">
                        <!-- Upcoming Events -->
                        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                            <div class="bg-gradient-to-r from-purple-50 to-indigo-50 px-8 py-6 border-b border-gray-200 flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <h2 class="text-2xl font-bold text-gray-900">Upcoming Events</h2>
                                </div>
                                <span class="text-sm text-gray-500 hidden sm:inline">next 5</span>
                            </div>
                            <div class="p-8">
                                <?php if (!empty($upcomingEvents)): ?>
                                    <div class="space-y-4">
                                        <?php foreach ($upcomingEvents as $ev): ?>
                                            <a href="/events/manage/<?= $ev['eventid'] ?>" class="flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors group">
                                                <div>
                                                    <h3 class="text-sm font-semibold text-gray-900 group-hover:text-purple-600 transition-colors">
                                                        <?= htmlspecialchars($ev['eventname']) ?>
                                                    </h3>
                                                    <p class="text-xs text-gray-500"><?= date('M d, Y g:ia', strtotime($ev['startdate'])) ?></p>
                                                </div>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">
                                                    <?= $ev['participantcount'] ?>
                                                </span>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-sm text-gray-500">No upcoming events.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Recent Events -->
                        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                            <div class="bg-gradient-to-r from-purple-50 to-indigo-50 px-8 py-6 border-b border-gray-200 flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h1m2 0h1m2 0h13M3 6h13M3 14h13M3 18h13" />
                                        </svg>
                                    </div>
                                    <h2 class="text-2xl font-bold text-gray-900">Recent Events</h2>
                                </div>
                                <span class="text-sm text-gray-500 hidden sm:inline">last 5</span>
                            </div>
                            <div class="p-8">
                                <?php if (!empty($recentPastEvents)): ?>
                                    <div class="space-y-4">
                                        <?php foreach ($recentPastEvents as $ev): ?>
                                            <a href="/events/manage/<?= $ev['eventid'] ?>" class="flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors group">
                                                <div>
                                                    <h3 class="text-sm font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">
                                                        <?= htmlspecialchars($ev['eventname']) ?>
                                                    </h3>
                                                    <p class="text-xs text-gray-500">Ended <?= date('M d, Y g:ia', strtotime($ev['enddate'])) ?></p>
                                                </div>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                                    <?= $ev['participantcount'] ?>
                                                </span>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-sm text-gray-500">No past events.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Members Section -->
                    <?php if (!empty($members)): ?>
                    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-8 py-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                    </div>
                                    <h2 class="text-2xl font-bold text-gray-900">Members</h2>
                                </div>
                                <span class="text-sm text-gray-500"><?= count($members) ?> total</span>
                            </div>
                        </div>
                        <div class="p-8">
                            <div class="grid grid-cols-1 gap-4">
                                <?php foreach ($members as $member): ?>
                                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center">
                                        <div class="h-12 w-12 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center mr-4">
                                            <span class="text-white font-semibold">
                                                <?= strtoupper(substr($member['fname'], 0, 1) . substr($member['lname'], 0, 1)) ?>
                                            </span>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-semibold text-gray-900">
                                                <?= htmlspecialchars($member['fname'] . ' ' . $member['lname']) ?>
                                            </h3>
                                            <p class="text-xs text-gray-500">
                                                Joined <?= date('M Y', strtotime($member['joined_at'])) ?>
                                            </p>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= 
                                        $member['role'] === 'owner' ? 'bg-red-100 text-red-800' : 
                                        ($member['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 
                                        ($member['role'] === 'executive' ? 'bg-blue-100 text-blue-800' : 
                                        ($member['role'] === 'treasurer' ? 'bg-green-100 text-green-800' :
                                        'bg-gray-100 text-gray-800'))) ?>">
                                        <?= ucfirst($member['role']) ?>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="space-y-8">
                    <!-- Contact Information -->
                    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6">
                        <div class="flex items-center mb-4">
                            <div class="h-6 w-6 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center mr-2">
                                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Contact</h3>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="h-4 w-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <a href="mailto:<?= htmlspecialchars($organization['contact_email']) ?>" class="hover:text-purple-600 transition-colors">
                                    <?= htmlspecialchars($organization['contact_email']) ?>
                                </a>
                            </div>
                            <?php if (!empty($organization['contact_phone'])): ?>
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="h-4 w-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                <a href="tel:<?= htmlspecialchars($organization['contact_phone']) ?>" class="hover:text-purple-600 transition-colors">
                                    <?= htmlspecialchars($organization['contact_phone']) ?>
                                </a>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($organization['website_url'])): ?>
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="h-4 w-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"></path>
                                </svg>
                                <a href="<?= htmlspecialchars($organization['website_url']) ?>" target="_blank" class="hover:text-purple-600 transition-colors">
                                    Visit Website
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <?php if ($userRole && in_array($userRole, ['owner', 'admin'])): ?>
                    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6">
                        <div class="flex items-center mb-4">
                            <div class="h-6 w-6 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center mr-2">
                                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                        </div>
                        <div class="space-y-3">
                            <a href="/organizations/manage/<?= $organization['org_slug'] ?>" class="block w-full text-center py-2 px-4 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                Manage Organization
                            </a>
                            <a href="/organizations/edit/<?= $organization['org_id'] ?>" class="block w-full text-center py-2 px-4 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                Edit Organization
                            </a>
                            <a href="/events/create?organization=<?= $organization['org_slug'] ?>" class="block w-full text-center py-2 px-4 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-lg text-sm font-medium text-white hover:from-purple-600 hover:to-indigo-700 transition-colors">
                                Create Event
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Organization Details -->
                    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6">
                        <div class="flex items-center mb-4">
                            <div class="h-6 w-6 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center mr-2">
                                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Details</h3>
                        </div>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Type:</span>
                                <span class="font-medium text-gray-900"><?= ucfirst($organization['org_type']) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Founded:</span>
                                <span class="font-medium text-gray-900"><?= date('M j, Y', strtotime($organization['created_at'])) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Status:</span>
                                <span class="font-medium <?= $organization['status'] === 'active' ? 'text-green-600' : 'text-gray-600' ?>">
                                    <?= ucfirst($organization['status']) ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Visibility:</span>
                                <span class="font-medium text-gray-900">
                                    <?= $organization['is_public'] ? 'Public' : 'Private' ?>
                                </span>
                            </div>
                            <?php if(!empty($organization['school_name'])): ?>
                            <div class="flex justify-between">
                                <span class="text-gray-500">School:</span>
                                <span class="font-medium text-gray-900">
                                    <?= htmlspecialchars($organization['school_name']) ?>
                                </span>
                            </div>
                            <?php endif; ?>
                            <?php if(!empty($organization['department_name'])): ?>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Department:</span>
                                <span class="font-medium text-gray-900">
                                    <?= htmlspecialchars($organization['department_name']) ?>
                                </span>
                            </div>
                            <?php endif; ?>
                            <?php if(!empty($organization['program_name'])): ?>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Program:</span>
                                <span class="font-medium text-gray-900">
                                    <?= htmlspecialchars($organization['program_name']) ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function requestToJoin(organizationId) {
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
            // Reload page to show updated state
            location.reload();
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