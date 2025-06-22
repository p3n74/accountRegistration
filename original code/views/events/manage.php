<?php $title = 'Manage Event - ' . APP_NAME; ?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="space-y-8">
            <?php if (!isset($event) || empty($event)): ?>
                <!-- Enhanced Error State -->
                <div class="text-center">
                    <div class="mx-auto h-20 w-20 bg-gradient-to-r from-red-500 to-red-600 rounded-3xl flex items-center justify-center mb-8 shadow-xl animate-pulse">
                        <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h1 class="text-4xl font-bold text-gray-900 mb-4">Event Not Found</h1>
                    <p class="text-red-600 max-w-2xl mx-auto mb-10 text-lg">
                        Unable to load event details. The event may have been deleted or you may not have permission to manage it.
                    </p>
                </div>

                <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-2xl border border-gray-100 p-12 text-center">
                    <div class="flex flex-col sm:flex-row gap-6 justify-center">
                        <a href="/dashboard" class="inline-flex items-center px-8 py-4 border border-transparent rounded-2xl text-base font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 transition-all duration-300 shadow-xl hover:shadow-2xl transform hover:-translate-y-1">
                            <svg class="h-6 w-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Dashboard
                        </a>
                        <a href="/events/create" class="inline-flex items-center px-8 py-4 border border-emerald-200 rounded-2xl text-base font-semibold text-emerald-600 bg-emerald-50 hover:bg-emerald-100 transition-all duration-300 shadow-lg hover:shadow-xl">
                            <svg class="h-6 w-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Create New Event
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Enhanced Page Header -->
                <div class="text-center">
                    <div class="mx-auto h-20 w-20 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-3xl flex items-center justify-center mb-8 shadow-xl">
                        <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent mb-4">Event Management</h1>
                    <p class="text-gray-600 max-w-3xl mx-auto text-lg leading-relaxed">
                        Monitor your event's performance, manage participants, and make updates as needed with our comprehensive management tools.
                    </p>
                </div>

                <!-- Enhanced Event Overview Card -->
                <div class="bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-blue-50 px-10 py-8 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="h-12 w-12 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center mr-4 shadow-lg">
                                    <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                </div>
                                <h2 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($event['eventname']) ?></h2>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-emerald-100 text-emerald-800 shadow-sm">
                                    <svg class="h-4 w-4 mr-2" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3"/>
                                    </svg>
                                    Active Event
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-10">
                        <!-- Enhanced Event Details Grid -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
                            <div class="flex items-start space-x-5">
                                <div class="flex-shrink-0">
                                    <div class="h-14 w-14 bg-gradient-to-r from-blue-100 to-blue-200 rounded-2xl flex items-center justify-center shadow-sm">
                                        <svg class="h-7 w-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-2">Location</h3>
                                    <p class="text-gray-600 text-base"><?= htmlspecialchars($event['location']) ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-start space-x-5">
                                <div class="flex-shrink-0">
                                    <div class="h-14 w-14 bg-gradient-to-r from-purple-100 to-purple-200 rounded-2xl flex items-center justify-center shadow-sm">
                                        <svg class="h-7 w-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-2">Event Duration</h3>
                                    <p class="text-gray-600 text-base leading-relaxed">
                                        <span class="font-semibold"><?= date('M d, Y H:i', strtotime($event['startdate'])) ?></span><br>
                                        <span class="text-sm text-gray-500">to</span><br>
                                        <span class="font-semibold"><?= date('M d, Y H:i', strtotime($event['enddate'])) ?></span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Enhanced Event Description -->
                        <?php if (!empty($event['eventshortinfo'])): ?>
                        <div class="bg-gradient-to-r from-gray-50 to-slate-50 rounded-2xl p-8 mb-10 border border-gray-100">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                <svg class="h-5 w-5 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                                </svg>
                                Event Description
                            </h3>
                            <p class="text-gray-700 leading-relaxed text-base"><?= htmlspecialchars($event['eventshortinfo']) ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Enhanced Stats Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-2xl p-8 border border-emerald-200 shadow-lg hover:shadow-xl transition-all duration-300">
                                <div class="flex items-center">
                                    <div class="h-16 w-16 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-2xl flex items-center justify-center shadow-lg">
                                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-6">
                                        <p class="text-3xl font-bold text-emerald-700"><?= $event['participant_count_file'] ?? 0 ?></p>
                                        <p class="text-emerald-600 font-semibold">Total Participants</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-8 border border-blue-200 shadow-lg hover:shadow-xl transition-all duration-300">
                                <div class="flex items-center">
                                    <div class="h-16 w-16 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg">
                                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-6">
                                        <p class="text-3xl font-bold text-blue-700"><?= $event['views'] ?? 0 ?></p>
                                        <p class="text-blue-600 font-semibold">Event Views</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-8 border border-purple-200 shadow-lg hover:shadow-xl transition-all duration-300">
                                <div class="flex items-center">
                                    <div class="h-16 w-16 bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-6">
                                        <?php
                                            $awaitingVerificationCount = 0;
                                            if (!empty($event['participants'])) {
                                                foreach ($event['participants'] as $pp) {
                                                    if (isset($pp['attendance_status']) && (int)$pp['attendance_status'] === 5) {
                                                        $awaitingVerificationCount++;
                                                    }
                                                }
                                            }
                                        ?>
                                        <p class="text-3xl font-bold text-purple-700"><?= $awaitingVerificationCount ?></p>
                                        <p class="text-purple-600 font-semibold">Awaiting Verification</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Add New Participant Section -->
                <div class="bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-50 via-purple-50 to-pink-50 px-10 py-8 border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="h-12 w-12 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center mr-4 shadow-lg">
                                <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-3xl font-bold text-gray-900">Add New Participant</h3>
                                <p class="text-gray-600 mt-1">Search existing users or invite new participants</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-10">
                        <div class="max-w-3xl">
                            <label for="participant-search" class="block text-lg font-bold text-gray-700 mb-4">
                                Search and Add Participants
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <input type="text" id="participant-search" 
                                       placeholder="Type to search users by name or email..." 
                                       class="block w-full pl-12 pr-6 py-4 border border-gray-200 rounded-2xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white text-lg shadow-sm">
                                <ul id="participant-results" class="absolute left-0 right-0 bg-white border border-gray-200 rounded-2xl shadow-2xl mt-2 max-h-60 overflow-auto hidden z-50"></ul>
                            </div>
                            <div class="mt-6 flex flex-col sm:flex-row gap-4">
                                <button id="invite-btn" disabled 
                                        class="inline-flex items-center justify-center px-8 py-4 border border-transparent rounded-2xl text-base font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 shadow-xl hover:shadow-2xl transform hover:-translate-y-1 disabled:transform-none">
                                    <svg class="h-6 w-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    Send Invitation
                                </button>
                                <button id="confirm-btn" disabled 
                                    class="inline-flex items-center justify-center px-8 py-4 border border-transparent rounded-2xl text-base font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 shadow-xl hover:shadow-2xl transform hover:-translate-y-1 disabled:transform-none">
                                <svg class="h-6 w-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Confirm Attendance
                                </button>
                            </div>
                            <p class="mt-4 text-gray-500 text-base">
                                Use "Send Invitation" for future participants or "Confirm Attendance" for those who have already attended.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Participants Section -->
                <div class="bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-50 via-indigo-50 to-purple-50 px-10 py-8 border-b border-gray-200">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-6 lg:space-y-0">
                            <div class="flex items-center">
                                <div class="h-12 w-12 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mr-4 shadow-lg">
                                    <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-3xl font-bold text-gray-900">Event Participants</h3>
                                    <p class="text-gray-600 mt-1">Manage and track participant status</p>
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                <span data-participant-count class="inline-flex items-center px-4 py-2 rounded-full text-base font-semibold bg-blue-100 text-blue-800 shadow-sm">
                                    <?= $event['participant_count_file'] ?? 0 ?> registered
                                </span>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                    <input type="text" id="attendee-filter" 
                                           placeholder="Filter participants..." 
                                           class="block w-full sm:w-72 pl-10 pr-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm" />
                                </div>
                                <select id="status-filter" class="block w-full sm:w-56 pl-4 pr-8 py-3 border border-gray-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm">
                                    <option value="">All Statuses</option>
                                    <option value="0">Invited</option>
                                    <option value="1">Pending</option>
                                    <option value="5">Awaiting Verification</option>
                                    <option value="2">Paid</option>
                                    <option value="3">Attended</option>
                                    <option value="4">Absent</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-10">
                        <?php if (!empty($event['participants'])): ?>
                            <!-- Replace the table wrapper div -->
                            <div class="overflow-hidden shadow-lg border border-gray-200 rounded-2xl">
                                <!-- Replace the entire table with this responsive card-based layout for mobile and optimized table for desktop: -->
                                <!-- Desktop Table View -->
                                <div class="hidden lg:block">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gradient-to-r from-gray-50 to-slate-50">
                                            <tr>
                                                <th data-sort="name" class="px-4 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider cursor-pointer hover:text-emerald-600 transition-colors duration-200 w-1/4">
                                                    <div class="flex items-center space-x-2">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                        </svg>
                                                        <span>Participant</span>
                                                        <svg class="h-3 w-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                                        </svg>
                                                    </div>
                                                </th>
                                                <th data-sort="email" class="px-4 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider cursor-pointer hover:text-emerald-600 transition-colors duration-200 w-1/4">
                                                    <div class="flex items-center space-x-2">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                        </svg>
                                                        <span>Email</span>
                                                        <svg class="h-3 w-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                                        </svg>
                                                    </div>
                                                </th>
                                                <th data-sort="status" class="px-4 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider cursor-pointer hover:text-emerald-600 transition-colors duration-200 w-1/6">
                                                    <div class="flex items-center space-x-2">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path>
                                                        </svg>
                                                        <span>Status</span>
                                                        <svg class="h-3 w-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                                        </svg>
                                                    </div>
                                                </th>
                                                <th data-sort="joined" class="px-4 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider cursor-pointer hover:text-emerald-600 transition-colors duration-200 w-1/6">
                                                    <div class="flex items-center space-x-2">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <span>Joined</span>
                                                        <svg class="h-3 w-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                                        </svg>
                                                    </div>
                                                </th>
                                                <th class="px-4 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-1/6">
                                                    <span>Actions</span>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody id="participant-tbody" class="bg-white divide-y divide-gray-200">
                                            <?php foreach ($event['participants'] as $uidKey => $p): ?>
                                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                                    <td class="px-4 py-4" data-name="<?= htmlspecialchars($p['name'] ?? 'Unknown User') ?>">
                                                        <div class="flex items-center">
                                                            <div class="h-10 w-10 bg-gradient-to-r from-emerald-400 to-teal-500 rounded-full flex items-center justify-center shadow-sm flex-shrink-0">
                                                                <span class="text-sm font-bold text-white">
                                                                    <?= strtoupper(substr($p['name'] ?? 'U', 0, 1)) ?>
                                                                </span>
                                                            </div>
                                                            <div class="ml-3 min-w-0 flex-1">
                                                                <div class="text-sm font-bold text-gray-900 truncate">
                                                                    <?= htmlspecialchars($p['name'] ?? 'Unknown User') ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-4" data-email="<?= htmlspecialchars($p['email'] ?? 'N/A') ?>">
                                                        <div class="text-sm text-gray-700 truncate"><?= htmlspecialchars($p['email'] ?? 'N/A') ?></div>
                                                    </td>
                                                    <?php
                                                        $statusMap = [
                                                            0 => ['Invited','bg-yellow-100 text-yellow-800 border-yellow-200'],
                                                            1 => ['Pending','bg-orange-100 text-orange-800 border-orange-200'],
                                                            2 => ['Paid','bg-green-100 text-green-800 border-green-200'],
                                                            3 => ['Attended','bg-emerald-100 text-emerald-800 border-emerald-200'],
                                                            4 => ['Absent','bg-red-100 text-red-800 border-red-200'],
                                                            5 => ['Awaiting Verification','bg-purple-100 text-purple-800 border-purple-200']
                                                        ];
                                                        $st = $p['attendance_status'] ?? 0;
                                                        [$label,$cls] = $statusMap[$st] ?? ['Unknown','bg-gray-100 text-gray-600 border-gray-200'];
                                                    ?>
                                                    <td class="px-4 py-4" data-status="<?= $st ?>">
                                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full border <?= $cls ?>"><?= $label ?></span>
                                                    </td>
                                                    <td class="px-4 py-4" data-joined="<?= $p['joined_at'] ?? '' ?>">
                                                        <div class="text-sm text-gray-700">
                                                            <?= $p['joined_at'] ? date('M d, Y', strtotime($p['joined_at'])) : 'N/A' ?>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-4">
                                                        <div class="flex space-x-1">
                                                            <button data-pid="<?= htmlspecialchars($p['participant_id']) ?>" data-email="<?= htmlspecialchars($p['email']) ?>" class="update-status px-2 py-1 text-xs font-semibold text-indigo-600 hover:text-white hover:bg-indigo-600 border border-indigo-200 hover:border-indigo-600 rounded-md transition-all duration-200">
                                                                Update
                                                            </button>
                                                            <button data-pid="<?= htmlspecialchars($p['participant_id']) ?>" data-email="<?= htmlspecialchars($p['email']) ?>" class="remove-participant px-2 py-1 text-xs font-semibold text-red-600 hover:text-white hover:bg-red-600 border border-red-200 hover:border-red-600 rounded-md transition-all duration-200">
                                                                Remove
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Mobile Card View -->
                                <div class="lg:hidden space-y-4" id="participant-cards">
                                    <?php foreach ($event['participants'] as $uidKey => $p): ?>
                                        <?php
                                            $statusMap = [
                                                0 => ['Invited','bg-yellow-100 text-yellow-800 border-yellow-200'],
                                                1 => ['Pending','bg-orange-100 text-orange-800 border-orange-200'],
                                                2 => ['Paid','bg-green-100 text-green-800 border-green-200'],
                                                3 => ['Attended','bg-emerald-100 text-emerald-800 border-emerald-200'],
                                                4 => ['Absent','bg-red-100 text-red-800 border-red-200'],
                                                5 => ['Awaiting Verification','bg-purple-100 text-purple-800 border-purple-200']
                                            ];
                                            $st = $p['attendance_status'] ?? 0;
                                            [$label,$cls] = $statusMap[$st] ?? ['Unknown','bg-gray-100 text-gray-600 border-gray-200'];
                                        ?>
                                        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm participant-card" data-name="<?= htmlspecialchars($p['name'] ?? 'Unknown User') ?>" data-email="<?= htmlspecialchars($p['email'] ?? 'N/A') ?>" data-status="<?= $st ?>" data-joined="<?= $p['joined_at'] ?? '' ?>">
                                            <div class="flex items-start justify-between">
                                                <div class="flex items-center flex-1 min-w-0">
                                                    <div class="h-12 w-12 bg-gradient-to-r from-emerald-400 to-teal-500 rounded-full flex items-center justify-center shadow-sm flex-shrink-0">
                                                        <span class="text-base font-bold text-white">
                                                            <?= strtoupper(substr($p['name'] ?? 'U', 0, 1)) ?>
                                                        </span>
                                                    </div>
                                                    <div class="ml-4 min-w-0 flex-1">
                                                        <div class="text-base font-bold text-gray-900 truncate">
                                                            <?= htmlspecialchars($p['name'] ?? 'Unknown User') ?>
                                                        </div>
                                                        <div class="text-sm text-gray-600 truncate">
                                                            <?= htmlspecialchars($p['email'] ?? 'N/A') ?>
                                                        </div>
                                                        <div class="mt-2">
                                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full border <?= $cls ?>"><?= $label ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-4 flex space-x-2">
                                                <button data-pid="<?= htmlspecialchars($p['participant_id']) ?>" data-email="<?= htmlspecialchars($p['email']) ?>" class="update-status flex-1 px-3 py-2 text-sm font-semibold text-indigo-600 hover:text-white hover:bg-indigo-600 border border-indigo-200 hover:border-indigo-600 rounded-lg transition-all duration-200">
                                                    Update Status
                                                </button>
                                                <button data-pid="<?= htmlspecialchars($p['participant_id']) ?>" data-email="<?= htmlspecialchars($p['email']) ?>" class="remove-participant flex-1 px-3 py-2 text-sm font-semibold text-red-600 hover:text-white hover:bg-red-600 border border-red-200 hover:border-red-600 rounded-lg transition-all duration-200">
                                                    Remove
                                                </button>
                                            </div>
                                            <div class="mt-2 text-xs text-gray-500">
                                                Joined: <?= $p['joined_at'] ? date('M d, Y', strtotime($p['joined_at'])) : 'N/A' ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Enhanced Empty State -->
                            <div class="text-center py-16">
                                <div class="mx-auto h-20 w-20 bg-gradient-to-r from-gray-100 to-gray-200 rounded-3xl flex items-center justify-center mb-8">
                                    <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-4">No Participants Yet</h3>
                                <p class="text-gray-600 mb-8 max-w-md mx-auto text-lg">
                                    Your event is ready to go! Use the search above to add participants or share your event to start getting registrations.
                                </p>
                                <div class="flex flex-col sm:flex-row gap-6 justify-center">
                                    <button class="inline-flex items-center px-8 py-4 border border-transparent rounded-2xl text-base font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 transition-all duration-300 shadow-xl hover:shadow-2xl transform hover:-translate-y-1">
                                        <svg class="h-6 w-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                                        </svg>
                                        Share Event
                                    </button>
                                    <a href="/events/edit/<?= $event['eventid'] ?>" class="inline-flex items-center px-8 py-4 border border-emerald-200 rounded-2xl text-base font-semibold text-emerald-600 bg-emerald-50 hover:bg-emerald-100 transition-all duration-300 shadow-lg hover:shadow-xl">
                                        <svg class="h-6 w-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Edit Event Details
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Enhanced Action Buttons -->
                <div class="bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl border border-gray-100 p-10">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-6 lg:space-y-0">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-3">Event Management Actions</h3>
                            <p class="text-gray-600 text-lg">Make changes to your event or remove it from the platform.</p>
                        </div>
                        <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-6">
                            <a href="/events/edit/<?= $event['eventid'] ?>" 
                               class="inline-flex justify-center items-center py-4 px-8 border border-transparent rounded-2xl text-base font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all duration-300 shadow-xl hover:shadow-2xl transform hover:-translate-y-1">
                                <svg class="h-6 w-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit Event
                            </a>
                            <?php
                                $protected = false;
                                if(!empty($event['participants'])){
                                    foreach($event['participants'] as $pp){
                                        if(in_array($pp['attendance_status'],[2,3,5])){ $protected=true; break; }
                                    }
                                }
                            ?>
                            <a href="<?= $protected ? '#' : '/events/delete/'.$event['eventid'] ?>" 
                               <?= $protected ? 'onclick="return false;"' : 'onclick="return confirm(\'Are you sure you want to delete this event? This action cannot be undone and will remove all participant data.\');"' ?>
                               class="inline-flex justify-center items-center py-4 px-8 border border-transparent rounded-2xl text-base font-semibold transition-all duration-300 shadow-xl <?= $protected ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 hover:shadow-2xl transform hover:-translate-y-1' ?>">
                                <svg class="h-6 w-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                <?= $protected ? 'Cannot Delete (Has Active Participants)' : 'Delete Event' ?>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Back to Dashboard -->
                <div class="text-center">
                    <a href="/dashboard" class="inline-flex items-center text-lg font-semibold text-emerald-600 hover:text-emerald-500 transition-colors duration-200 group">
                        <svg class="h-5 w-5 mr-3 group-hover:-translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Dashboard
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Enhanced JavaScript with all functionality -->
<script>
(function(){
    const searchInput = document.getElementById('participant-search');
    const resultsList = document.getElementById('participant-results');
    const inviteBtn = document.getElementById('invite-btn');
    const confirmBtn = document.getElementById('confirm-btn');
    let debounceId;
    let selectedUser = null;

    /* Enhanced Pagination with better styling */
    const perPage = 10;
    let currentPage = 1;
    const pager = document.createElement('nav');
    pager.id = 'pager';
    pager.className = 'mt-8 flex items-center justify-center space-x-2';
    const tableEl = document.getElementById('participant-tbody')?.closest('table');
    if(tableEl){
        tableEl.insertAdjacentElement('afterend', pager);
    }

    function renderPager(totalPages){
        pager.innerHTML = '';
        if(totalPages <= 1) return;

        const btnHtml = (lbl, page, disabled=false, active=false) => {
            const base = 'px-4 py-2 rounded-xl font-semibold transition-all duration-200';
            const style = disabled ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 
                         (active ? 'bg-emerald-600 text-white shadow-lg' : 'bg-white text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 shadow-sm hover:shadow-md border border-gray-200');
            return `<button data-page="${page}" ${disabled? 'disabled' : ''} class="${base} ${style}">${lbl}</button>`;
        };

        const windowSize = 3;
        let start = Math.max(1, currentPage - Math.floor(windowSize/2));
        let end = start + windowSize - 1;
        if(end > totalPages){
            end = totalPages;
            start = Math.max(1, end - windowSize + 1);
        }

        pager.insertAdjacentHTML('beforeend', btnHtml('‹‹', 1, currentPage===1));
        pager.insertAdjacentHTML('beforeend', btnHtml('‹', Math.max(1,currentPage-1), currentPage===1));

        for(let p=start; p<=end; p++){
            pager.insertAdjacentHTML('beforeend', btnHtml(p, p, false, p===currentPage));
        }

        pager.insertAdjacentHTML('beforeend', btnHtml('›', Math.min(totalPages,currentPage+1), currentPage===totalPages));
        pager.insertAdjacentHTML('beforeend', btnHtml('››', totalPages, currentPage===totalPages));
    }

    // Update the JavaScript pagination function to handle both table and cards:
    function paginate(){
        // Handle desktop table
        const rows = Array.from(document.querySelectorAll('#participant-tbody tr'));
        const visibleRows = rows.filter(r => !r.classList.contains('hidden'));
        
        // Handle mobile cards
        const cards = Array.from(document.querySelectorAll('.participant-card'));
        const visibleCards = cards.filter(c => !c.classList.contains('hidden'));
        
        const totalItems = Math.max(visibleRows.length, visibleCards.length);
        const totalPages = Math.max(1, Math.ceil(totalItems / perPage));
        if(currentPage > totalPages) currentPage = totalPages;

        // Paginate table rows
        let index = 0;
        visibleRows.forEach(r => {
            index++;
            const page = Math.ceil(index / perPage);
            r.style.display = page === currentPage ? '' : 'none';
        });
        rows.filter(r => r.classList.contains('hidden')).forEach(r => r.style.display='');

        // Paginate cards
        index = 0;
        visibleCards.forEach(c => {
            index++;
            const page = Math.ceil(index / perPage);
            c.style.display = page === currentPage ? '' : 'none';
        });
        cards.filter(c => c.classList.contains('hidden')).forEach(c => c.style.display='');

        renderPager(totalPages);
    }

    pager.addEventListener('click', e => {
        const btn = e.target.closest('[data-page]');
        if(!btn || btn.disabled) return;
        const page = parseInt(btn.getAttribute('data-page'));
        if(page && page !== currentPage){
            currentPage = page;
            paginate();
        }
    });

    paginate();

    // Enhanced search with better UX and styling
    searchInput.addEventListener('input', function(){
        clearTimeout(debounceId);
        const query = this.value.trim();
        if (query.length < 2) {
            resultsList.classList.add('hidden');
            return;
        }
        
        resultsList.innerHTML = '<li class="px-6 py-4 text-center text-gray-500"><svg class="animate-spin h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></li>';
        resultsList.classList.remove('hidden');
        
        debounceId = setTimeout(() => {
            fetch(`/users/search?q=${encodeURIComponent(query)}`)
                .then(r => r.json())
                .then(data => {
                    if (!Array.isArray(data) || data.length === 0) {
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (emailRegex.test(query)) {
                            resultsList.innerHTML = `<li data-add-email="${query}" class="px-6 py-4 text-center text-emerald-600 cursor-pointer hover:bg-emerald-50 rounded-xl transition-colors duration-200 font-semibold">✉️ Invite "${query}" as new participant</li>`;
                        } else {
                            resultsList.innerHTML = '<li class="px-6 py-4 text-center text-gray-500">No match found</li>';
                        }
                        return;
                    }
                    
                    const filtered = data.filter(u => {
                        const q = query.toLowerCase();
                        return (`${u.fname} ${u.lname}`.toLowerCase()).includes(q) || u.email.toLowerCase().includes(q);
                    });
                    
                    if (filtered.length === 0) {
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (emailRegex.test(query)) {
                            resultsList.innerHTML = `<li data-add-email="${query}" class="px-6 py-4 text-center text-emerald-600 cursor-pointer hover:bg-emerald-50 rounded-xl transition-colors duration-200 font-semibold">✉️ Invite "${query}" as new participant</li>`;
                        } else {
                            resultsList.innerHTML = '<li class="px-6 py-4 text-center text-gray-500">No match found</li>';
                        }
                        return;
                    }    

                    resultsList.innerHTML = filtered.map(u => `
                        <li data-uid="${u.uid}" data-name="${u.fname} ${u.lname}" data-email="${u.email}" data-pic="${u.profilepicture}" class="flex items-center px-6 py-4 cursor-pointer hover:bg-emerald-50 transition-colors duration-200 border-b border-gray-100 last:border-b-0 rounded-xl">
                            <img src="${u.profilepicture || '/default-avatar.png'}" alt="Profile" class="h-10 w-10 rounded-full mr-4 object-cover border-2 border-gray-200 shadow-sm">
                            <div class="flex-1">
                                <div class="text-base font-bold text-gray-900">${u.fname} ${u.lname}</div>
                                <div class="text-sm text-gray-500">${u.email}</div>
                            </div>
                        </li>`).join('');
                    resultsList.classList.remove('hidden');
                })
                .catch(() => {
                    resultsList.innerHTML = '<li class="px-6 py-4 text-center text-red-500">Error loading users</li>';
                });
        }, 300);
    });

    // Enhanced user selection
    resultsList.addEventListener('click', function(e){
        const liUser = e.target.closest('li[data-uid]');
        const liNewEmail = e.target.closest('li[data-add-email]');
        if (!liUser && !liNewEmail) return;

        if (liUser) {
            const uid = liUser.getAttribute('data-uid');
            const name = liUser.getAttribute('data-name');
            const email = liUser.getAttribute('data-email');
            selectedUser = {uid, name, email};
            searchInput.value = `${name} <${email}>`;
        } else if (liNewEmail) {
            const email = liNewEmail.getAttribute('data-add-email');
            selectedUser = {uid: null, name: email, email};
            searchInput.value = email;
        }

        inviteBtn.disabled = false;
        confirmBtn.disabled = false;
        resultsList.classList.add('hidden');
    });

    // Enhanced participant addition
    function addParticipant(statusVal){
        if (!selectedUser) return;
        
        const isInvite = statusVal === 0;
        const btn = isInvite ? inviteBtn : confirmBtn;
        const originalText = btn.innerHTML;
        
        btn.innerHTML = `<svg class="animate-spin h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processing...`;
        btn.disabled = true;
        confirmBtn.disabled = true;
        inviteBtn.disabled = true;
        
        fetch(`/events/addParticipant/<?= $event['eventid'] ?>`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ uid: selectedUser.uid, email: selectedUser.email, event_id: "<?= $event['eventid'] ?>", status: statusVal })
        }).then(r=>r.json()).then(resp=>{
            if (resp.success) {
                const pid = resp.participant_id;
                const joinedAt = resp.joined_at || new Date().toISOString();
                const tbody = document.getElementById('participant-tbody');
                if(tbody){
                    const initial = (selectedUser.name || selectedUser.email).charAt(0).toUpperCase();
                    const statusMap = {
                        0:['Invited','bg-yellow-100 text-yellow-800 border-yellow-200'],
                        3:['Attended','bg-emerald-100 text-emerald-800 border-emerald-200'],
                        5:['Awaiting Verification','bg-purple-100 text-purple-800 border-purple-200']
                    };
                    const [statusLabel,statusClass] = statusMap[statusVal] || statusMap[0];
                    const rowHtml = `<tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-8 py-6 whitespace-nowrap" data-name="${selectedUser.name}">
                            <div class="flex items-center">
                                <div class="h-12 w-12 bg-gradient-to-r from-emerald-400 to-teal-500 rounded-full flex items-center justify-center shadow-sm">
                                    <span class="text-base font-bold text-white">${initial}</span>
                                </div>
                                <div class="ml-5">
                                    <div class="text-base font-bold text-gray-900">${selectedUser.name}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap" data-email="${selectedUser.email}">
                            <div class="text-base text-gray-700">${selectedUser.email}</div>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap" data-status="${statusVal}">
                            <span class="inline-flex px-3 py-2 text-sm font-semibold rounded-full border ${statusClass}">${statusLabel}</span>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap" data-joined="${joinedAt}">
                            <div class="text-base text-gray-700">just now</div>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap w-40">
                            <div class="flex flex-col space-y-2">
                                <button data-pid="${pid}" data-email="${selectedUser.email}" class="update-status w-full px-3 py-2 text-sm font-semibold text-indigo-600 hover:text-white hover:bg-indigo-600 border border-indigo-200 hover:border-indigo-600 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">Update Status</button>
                                <button data-pid="${pid}" data-email="${selectedUser.email}" class="remove-participant w-full px-3 py-2 text-sm font-semibold text-red-600 hover:text-white hover:bg-red-600 border border-red-200 hover:border-red-600 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">Remove</button>
                            </div>
                        </td>
                    </tr>`;
                    tbody.insertAdjacentHTML('beforeend',rowHtml);
                    const badge=document.querySelector('[data-participant-count]');
                    if(badge){
                        const num=parseInt(badge.textContent.match(/\d+/))||0;
                        badge.textContent=(num+1)+' registered';
                    }
                    paginate();
                }
                selectedUser=null;
                searchInput.value='';
                inviteBtn.disabled=true;
                confirmBtn.disabled=true;
                btn.innerHTML=originalText;
            } else {
                btn.innerHTML = originalText;
                inviteBtn.disabled = false;
                confirmBtn.disabled = false;
                alert(resp.message || 'Could not add participant');
            }
        }).catch(()=>{ 
            btn.innerHTML = originalText;
            inviteBtn.disabled = false; 
            confirmBtn.disabled = false;
            alert('Network error');
        });
    }

    inviteBtn.addEventListener('click', () => addParticipant(0));
    confirmBtn.addEventListener('click', () => addParticipant(3));

    document.addEventListener('click', e => {
        if (!resultsList.contains(e.target) && e.target !== searchInput) {
            resultsList.classList.add('hidden');
        }
    });

    // Enhanced status update modal with modern glass morphism design
    const statusModal = document.createElement('div');
    statusModal.innerHTML = `<div class="fixed inset-0 bg-gradient-to-br from-black/60 via-black/50 to-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden" id="status-modal-overlay">
    <div class="bg-white/95 backdrop-blur-xl w-[32rem] rounded-3xl shadow-2xl border border-white/20 p-10 m-4 transform transition-all duration-300">
        <div class="relative mb-10">
            <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/10 via-blue-500/10 to-purple-500/10 rounded-3xl"></div>
            <div class="relative p-8 text-center">
                <div class="mx-auto w-20 h-20 bg-gradient-to-r from-emerald-500 to-blue-600 rounded-3xl flex items-center justify-center mb-6 shadow-xl">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 713.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 713.138-3.138z"></path>
                    </svg>
                </div>
                <h3 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent mb-3">Update Status</h3>
                <p class="text-gray-600 text-lg">Choose the new status for this participant</p>
            </div>
        </div>

        <input type="hidden" id="modal-pid">
        <input type="hidden" id="modal-email">
        <input type="hidden" id="modal-current">
        
        <div class="grid grid-cols-1 gap-4 mb-10">
            <button data-status="0" class="group relative overflow-hidden px-8 py-5 rounded-2xl bg-gradient-to-r from-amber-50 to-yellow-50 border border-amber-200/50 hover:border-amber-300 text-amber-800 font-bold transition-all duration-300 hover:shadow-xl hover:shadow-amber-500/25 hover:-translate-y-1">
                <div class="absolute inset-0 bg-gradient-to-r from-amber-400/0 to-yellow-400/0 group-hover:from-amber-400/10 group-hover:to-yellow-400/10 transition-all duration-300"></div>
                <div class="relative flex items-center justify-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-amber-500 to-yellow-500 rounded-2xl flex items-center justify-center mr-4 shadow-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <span class="text-lg">Mark as Invited</span>
                </div>
            </button>

            <button data-status="2" class="group relative overflow-hidden px-8 py-5 rounded-2xl bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200/50 hover:border-green-300 text-green-800 font-bold transition-all duration-300 hover:shadow-xl hover:shadow-green-500/25 hover:-translate-y-1">
                <div class="absolute inset-0 bg-gradient-to-r from-green-400/0 to-emerald-400/0 group-hover:from-green-400/10 group-hover:to-emerald-400/10 transition-all duration-300"></div>
                <div class="relative flex items-center justify-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-500 rounded-2xl flex items-center justify-center mr-4 shadow-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <span class="text-lg">Confirm Payment</span>
                </div>
            </button>

            <button data-status="3" class="group relative overflow-hidden px-8 py-5 rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/50 hover:border-emerald-300 text-emerald-800 font-bold transition-all duration-300 hover:shadow-xl hover:shadow-emerald-500/25 hover:-translate-y-1">
                <div class="absolute inset-0 bg-gradient-to-r from-emerald-400/0 to-teal-400/0 group-hover:from-emerald-400/10 group-hover:to-teal-400/10 transition-all duration-300"></div>
                <div class="relative flex items-center justify-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl flex items-center justify-center mr-4 shadow-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <span class="text-lg">Confirm Attendance</span>
                </div>
            </button>

            <button data-status="4" class="group relative overflow-hidden px-8 py-5 rounded-2xl bg-gradient-to-r from-red-50 to-rose-50 border border-red-200/50 hover:border-red-300 text-red-800 font-bold transition-all duration-300 hover:shadow-xl hover:shadow-red-500/25 hover:-translate-y-1">
                <div class="absolute inset-0 bg-gradient-to-r from-red-400/0 to-rose-400/0 group-hover:from-red-400/10 group-hover:to-rose-400/10 transition-all duration-300"></div>
                <div class="relative flex items-center justify-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-rose-500 rounded-2xl flex items-center justify-center mr-4 shadow-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <span class="text-lg">Mark as Absent</span>
                </div>
            </button>

            <button data-status="5" class="group relative overflow-hidden px-8 py-5 rounded-2xl bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200/50 hover:border-purple-300 text-purple-800 font-bold transition-all duration-300 hover:shadow-xl hover:shadow-purple-500/25 hover:-translate-y-1">
                <div class="absolute inset-0 bg-gradient-to-r from-purple-400/0 to-indigo-400/0 group-hover:from-purple-400/10 group-hover:to-indigo-400/10 transition-all duration-300"></div>
                <div class="relative flex items-center justify-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-indigo-500 rounded-2xl flex items-center justify-center mr-4 shadow-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"></path>
                        </svg>
                    </div>
                    <span class="text-lg">Awaiting Verification</span>
                </div>
            </button>
        </div>

        <div class="pt-6 border-t border-gray-200/50">
            <button id="modal-close" class="group w-full px-8 py-5 rounded-2xl bg-gradient-to-r from-gray-50 to-slate-50 border border-gray-200/50 hover:border-gray-300 text-gray-700 font-bold transition-all duration-300 hover:shadow-lg hover:shadow-gray-500/10 hover:-translate-y-1">
                <div class="flex items-center justify-center">
                    <svg class="w-6 h-6 mr-3 text-gray-500 group-hover:text-gray-700 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span class="text-lg">Cancel</span>
                </div>
            </button>
        </div>
    </div>
</div>

<style>
@keyframes animate-in {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.backdrop-blur-xl {
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
}

.backdrop-blur-sm {
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

button[data-status] {
    transform-origin: center;
    will-change: transform, box-shadow;
}

button[data-status]:active {
    transform: translateY(0) scale(0.98);
}

.bg-clip-text {
    -webkit-background-clip: text;
    background-clip: text;
}

.scale-in {
    animation: animate-in 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes fade-in {
    from { opacity: 0; }
    to   { opacity: 1; }
}

.fade-in {
    animation: fade-in 0.2s ease-out;
}
</style>`;
    document.body.appendChild(statusModal);

    const overlay = document.getElementById('status-modal-overlay');
    const modalContent = overlay.querySelector('.bg-white\\/95');

    function showModal(){
        overlay.classList.remove('hidden');
        overlay.classList.add('fade-in');
        modalContent.classList.add('scale-in');
    }

    function hideModal(){
        modalContent.style.animation = 'animate-in 0.2s cubic-bezier(0.16, 1, 0.3, 1) reverse';
        overlay.style.animation = 'fade-in 0.2s ease-out reverse';
        setTimeout(() => {
            overlay.classList.add('hidden');
            overlay.style.animation = '';
            modalContent.style.animation = '';
            modalContent.classList.remove('scale-in');
            overlay.classList.remove('fade-in');
        }, 200);
    }

    document.addEventListener('click', function(e){
        const updateBtn = e.target.closest('.update-status');
        if(updateBtn){
            const pid = updateBtn.getAttribute('data-pid');
            const email = updateBtn.getAttribute('data-email');
            const row = updateBtn.closest('tr');
            const curStatus = row?.querySelector('[data-status]')?.getAttribute('data-status') || '';
            document.getElementById('modal-pid').value = pid || '';
            document.getElementById('modal-email').value = email || '';
            document.getElementById('modal-current').value = curStatus;
            adjustStatusOptions(curStatus);
            showModal();
            return;
        }
        
        if(e.target.closest('#modal-close') || e.target === overlay){
            hideModal();
        }
    });

    document.getElementById('status-modal-overlay').addEventListener('click', function(e){
        const statusBtn = e.target.closest('button[data-status]');
        if(!statusBtn) return;
        
        const statusVal = parseInt(statusBtn.getAttribute('data-status'));
        const uid = document.getElementById('modal-pid').value;
        const email = document.getElementById('modal-email').value;
        const originalText = statusBtn.innerHTML;
        
        statusBtn.innerHTML = '<svg class="animate-spin h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
        statusBtn.disabled = true;
        
        fetch(`/events/updateParticipantStatus/<?= $event['eventid'] ?>`,{
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body:JSON.stringify({participant_id: uid, email, event_id:"<?= $event['eventid'] ?>", status: statusVal})
        }).then(r=>r.json()).then(resp=>{
            if(resp.success){
                const row = document.querySelector(`#participant-tbody button.update-status[data-pid="${uid}"]`)?.closest('tr');
                if(row){
                   const badgeCell = row.querySelector('[data-status]');
                   if(badgeCell){
                       badgeCell.setAttribute('data-status',statusVal);
                       const span = badgeCell.querySelector('span');
                       const map = {
                           0:['Invited','bg-yellow-100 text-yellow-800 border-yellow-200'],
                           1:['Pending','bg-orange-100 text-orange-800 border-orange-200'],
                           2:['Paid','bg-green-100 text-green-800 border-green-200'],
                           3:['Attended','bg-emerald-100 text-emerald-800 border-emerald-200'],
                           4:['Absent','bg-red-100 text-red-800 border-red-200'],
                           5:['Awaiting Verification','bg-purple-100 text-purple-800 border-purple-200']
                       };
                       const [lbl,cls] = map[statusVal] || ['Unknown','bg-gray-100 text-gray-600 border-gray-200'];
                       span.textContent = lbl;
                       span.className = `inline-flex px-3 py-2 text-sm font-semibold rounded-full border ${cls}`;
                   }
                }
                hideModal();
            } else {
                alert('Update failed');
            }
            statusBtn.innerHTML = originalText;
            statusBtn.disabled = false;
        }).catch(() => {
            statusBtn.innerHTML = originalText;
            statusBtn.disabled = false;
            alert('Network error');
        });
    });

    document.addEventListener('click', function(e){
        const btn = e.target.closest('.remove-participant');
        if (!btn) return;
        e.preventDefault();
        
        if (!confirm('Are you sure you want to remove this participant? This action cannot be undone.')) return;
        
        const participantId = btn.getAttribute('data-pid');
        const email = btn.getAttribute('data-email');
        const originalText = btn.textContent;
        btn.textContent = 'Removing...';
        btn.disabled = true;
        
        fetch(`/events/removeParticipant/<?= $event['eventid'] ?>`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ participant_id: participantId, email, event_id: "<?= $event['eventid'] ?>" })
        }).then(r=>r.json()).then(resp=>{
            if (resp.success) {
                const row = btn.closest('tr');
                if (row) {
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(-100%)';
                    setTimeout(() => {
                        row.remove();
                        paginate();
                    }, 300);
                }
                
                const badge = document.querySelector('[data-participant-count]');
                if (badge) {
                    let cnt = parseInt(badge.textContent.match(/\d+/)) || 0;
                    badge.textContent = Math.max(0, cnt - 1) + ' registered';
                }
            } else {
                btn.textContent = originalText;
                btn.disabled = false;
                alert('Could not remove participant');
            }
        }).catch(() => {
            btn.textContent = originalText;
            btn.disabled = false;
            alert('Network error');
        });
    });

    // Update the filtering function to handle both table and cards:
    const filterInput = document.getElementById('attendee-filter');
    if(filterInput){
        filterInput.addEventListener('input', function(){
            const term = this.value.trim().toLowerCase();
            
            // Filter table rows
            document.querySelectorAll('#participant-tbody tr').forEach(tr => {
                const name = tr.querySelector('[data-name]')?.textContent.toLowerCase() || '';
                const email = tr.querySelector('[data-email]')?.textContent.toLowerCase() || '';
                const statusLabel = tr.querySelector('[data-status] span')?.textContent.toLowerCase() || '';
                
                if(!term || name.includes(term) || email.includes(term) || statusLabel.includes(term)){
                    tr.classList.remove('hidden');
                } else {
                    tr.classList.add('hidden');
                }
            });
            
            // Filter cards
            document.querySelectorAll('.participant-card').forEach(card => {
                const name = card.getAttribute('data-name')?.toLowerCase() || '';
                const email = card.getAttribute('data-email')?.toLowerCase() || '';
                const statusLabel = card.querySelector('span')?.textContent.toLowerCase() || '';
                
                if(!term || name.includes(term) || email.includes(term) || statusLabel.includes(term)){
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
            
            paginate();
        });
    }

    // Update the status filter to handle both views:
    const statusSelect = document.getElementById('status-filter');
    if(statusSelect){
        statusSelect.addEventListener('change',function(){
            const val = this.value;
            
            // Filter table rows
            document.querySelectorAll('#participant-tbody tr').forEach(tr => {
                const status = tr.querySelector('[data-status]')?.getAttribute('data-status') || '';
                if(!val || status === val){
                    tr.classList.remove('hidden');
                } else {
                    tr.classList.add('hidden');
                }
            });
            
            // Filter cards
            document.querySelectorAll('.participant-card').forEach(card => {
                const status = card.getAttribute('data-status') || '';
                if(!val || status === val){
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
            
            paginate();
        });
    }

    function adjustStatusOptions(current){
        const buttons = document.querySelectorAll('#status-modal-overlay button[data-status]');
        buttons.forEach(btn=>{
            const val = parseInt(btn.getAttribute('data-status'));
            if(current==='2' && val!==3){
                btn.disabled = true;
                btn.classList.add('opacity-50','cursor-not-allowed');
            } else {
                btn.disabled = false;
                btn.classList.remove('opacity-50','cursor-not-allowed');
            }
        });
    }
})();
</script>