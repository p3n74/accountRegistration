<?php $title = 'Dashboard - ' . APP_NAME; ?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="space-y-8">
            <!-- Welcome Section -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-emerald-500 to-teal-600 px-6 py-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <?php if (!empty($user['profilepicture'])): ?>
                                <img class="h-16 w-16 rounded-full border-4 border-white shadow-lg" src="profilePictures/<?= htmlspecialchars($user['profilepicture']) ?>" alt="Profile Picture">
                            <?php else: ?>
                                <div class="h-16 w-16 rounded-full bg-white/20 backdrop-blur-sm border-4 border-white shadow-lg flex items-center justify-center">
                                    <svg class="h-8 w-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="ml-6">
                            <h1 class="text-3xl font-bold text-white">Welcome back, <?= htmlspecialchars($user['fname']) ?>!</h1>
                            <p class="text-emerald-100 mt-1">Discover campus events and track your My Carolinian journey</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gradient-to-r from-emerald-50 to-teal-50">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-emerald-600">12</div>
                            <div class="text-sm text-gray-600">Events Attended</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-teal-600">8</div>
                            <div class="text-sm text-gray-600">Badges Earned</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-emerald-600">3</div>
                            <div class="text-sm text-gray-600">Events Created</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8">
                <div class="flex items-center mb-6">
                    <div class="h-8 w-8 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-lg flex items-center justify-center mr-3">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Quick Actions</h2>
                </div>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <a href="/events/create" class="group relative bg-gradient-to-br from-emerald-50 to-emerald-100 p-6 rounded-2xl border border-emerald-200 hover:shadow-lg hover:scale-105 transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div class="h-12 w-12 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                            <svg class="h-5 w-5 text-emerald-400 group-hover:text-emerald-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-emerald-700 transition-colors">Create New Event</h3>
                            <p class="mt-2 text-sm text-gray-600">Set up a new campus event and start collecting registrations</p>
                        </div>
                    </a>

                    <a href="/dashboard/badges" class="group relative bg-gradient-to-br from-teal-50 to-teal-100 p-6 rounded-2xl border border-teal-200 hover:shadow-lg hover:scale-105 transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div class="h-12 w-12 bg-gradient-to-r from-teal-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                                </svg>
                            </div>
                            <svg class="h-5 w-5 text-teal-400 group-hover:text-teal-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-teal-700 transition-colors">View My Badges</h3>
                            <p class="mt-2 text-sm text-gray-600">See all your My Carolinian achievements and earned badges</p>
                        </div>
                    </a>

                    <a href="/dashboard/profile" class="group relative bg-gradient-to-br from-purple-50 to-purple-100 p-6 rounded-2xl border border-purple-200 hover:shadow-lg hover:scale-105 transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div class="h-12 w-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <svg class="h-5 w-5 text-purple-400 group-hover:text-purple-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-purple-700 transition-colors">Edit Profile</h3>
                            <p class="mt-2 text-sm text-gray-600">Update your personal information and My Carolinian preferences</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Created Events -->
            <?php if (!empty($createdEvents)): ?>
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-8 py-6 border-b border-gray-200">
                    <div class="flex items-center">
                        <div class="h-8 w-8 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-lg flex items-center justify-center mr-3">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Events You've Created</h2>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Event Name</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Location</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <?php foreach ($createdEvents as $event): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($event['eventname']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-600"><?= date('M j, Y', strtotime($event['startdate'])) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-600"><?= htmlspecialchars($event['location']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                                    <a href="/events/edit/<?= $event['eventid'] ?>" class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-medium bg-emerald-100 text-emerald-700 hover:bg-emerald-200 transition-colors">
                                        <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Edit
                                    </a>
                                    <a href="/events/manage/<?= $event['eventid'] ?>" class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-medium bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors">
                                        <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        Manage
                                    </a>
                                    <a href="/events/delete/<?= $event['eventid'] ?>" class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-medium bg-red-100 text-red-700 hover:bg-red-200 transition-colors" onclick="return confirm('Are you sure you want to delete this event?')">
                                        <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Attended Events -->
            <?php if (!empty($attendedEvents)): ?>
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8">
                <div class="flex items-center mb-6">
                    <div class="h-8 w-8 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-lg flex items-center justify-center mr-3">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Your My Carolinian Journey</h2>
                </div>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($attendedEvents as $event): ?>
                    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-200 hover:scale-105">
                        <?php if (!empty($event['eventbadgepath'])): ?>
                            <div class="relative">
                                <img class="w-full h-48 object-cover" src="<?= htmlspecialchars($event['eventbadgepath']) ?>" alt="Event Badge">
                                <div class="absolute top-4 right-4 bg-emerald-500 text-white px-2 py-1 rounded-lg text-xs font-semibold">
                                    Badge Earned
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="w-full h-48 bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center">
                                <svg class="h-16 w-16 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                                </svg>
                            </div>
                        <?php endif; ?>
                        <div class="p-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-2"><?= htmlspecialchars($event['eventname']) ?></h4>
                            <p class="text-sm text-gray-600 mb-4"><?= htmlspecialchars($event['eventshortinfo']) ?></p>
                            <div class="space-y-2">
                                <div class="flex items-center text-xs text-gray-500">
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <?= date('M j, Y', strtotime($event['startdate'])) ?> - <?= date('M j, Y', strtotime($event['enddate'])) ?>
                                </div>
                                <div class="flex items-center text-xs text-gray-500">
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <?= htmlspecialchars($event['location']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>