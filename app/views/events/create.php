<?php $title = 'Create Event - ' . APP_NAME; ?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="space-y-8">
            <!-- Page Header -->
            <div class="text-center">
                <!-- If creating for an organization, display banner -->
                <?php if(isset($organization) && $organization): ?>
                    <div class="mb-6 inline-flex items-center px-4 py-2 bg-purple-100 text-purple-700 rounded-xl text-sm font-medium">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l6 6-6 6" />
                        </svg>
                        Creating event for <span class="font-semibold ml-1"><?= htmlspecialchars($organization['org_name']) ?></span>
                    </div>
                <?php endif; ?>
                <div class="mx-auto h-16 w-16 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Create New Event</h1>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Share your event with the My Carolinian community and help fellow students discover amazing experiences.
                </p>
            </div>

            <!-- Main Form Card -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-emerald-50 to-teal-50 px-8 py-6 border-b border-gray-200">
                    <div class="flex items-center">
                        <div class="h-8 w-8 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-lg flex items-center justify-center mr-3">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Event Details</h2>
                    </div>
                </div>
                
                <div class="p-8">
                    <form method="POST" enctype="multipart/form-data" class="space-y-8">
                        <?php if(isset($organization) && $organization): ?>
                            <input type="hidden" name="org_id" value="<?= htmlspecialchars($organization['org_id']) ?>">
                        <?php endif; ?>
                        <!-- Event Name -->
                        <div>
                            <label for="eventname" class="block text-sm font-semibold text-gray-700 mb-2">
                                Event Name <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" id="eventname" name="eventname" required
                                       value="<?= htmlspecialchars($eventname ?? '') ?>"
                                       class="block w-full px-4 py-3 pl-12 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white"
                                       placeholder="Enter a compelling event name">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Date Range -->
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="startdate" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Start Date & Time <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="datetime-local" id="startdate" name="startdate" required
                                           value="<?= htmlspecialchars($startdate ?? '') ?>"
                                           class="block w-full px-4 py-3 pl-12 border border-gray-200 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label for="enddate" class="block text-sm font-semibold text-gray-700 mb-2">
                                    End Date & Time <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="datetime-local" id="enddate" name="enddate" required
                                           value="<?= htmlspecialchars($enddate ?? '') ?>"
                                           class="block w-full px-4 py-3 pl-12 border border-gray-200 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Location -->
                        <div>
                            <label for="location" class="block text-sm font-semibold text-gray-700 mb-2">
                                Location <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" id="location" name="location" required
                                       value="<?= htmlspecialchars($location ?? '') ?>"
                                       class="block w-full px-4 py-3 pl-12 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white"
                                       placeholder="Where will this event take place?">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Short Info -->
                        <div>
                            <label for="eventshortinfo" class="block text-sm font-semibold text-gray-700 mb-2">
                                Short Description
                            </label>
                            <div class="relative">
                                <textarea id="eventshortinfo" name="eventshortinfo" rows="3"
                                          class="block w-full px-4 py-3 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white resize-none"
                                          placeholder="Brief, engaging description that will appear in event listings..."><?= htmlspecialchars($eventshortinfo ?? '') ?></textarea>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">Keep it concise - this will be shown in event previews</p>
                        </div>

                        <!-- Registration & Payment -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label for="registration_fee" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Registration Fee (PHP)
                                </label>
                                <div class="relative">
                                    <input type="number" id="registration_fee" name="registration_fee" min="0" step="0.01"
                                           value="<?= htmlspecialchars(isset($registrationFee) ? $registrationFee : '') ?>"
                                           class="block w-full px-4 py-3 pl-12 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white"
                                           placeholder="0.00">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-400">₱</span>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Leave at 0 for free events.</p>
                            </div>
                            <div class="flex items-end">
                                <label class="inline-flex items-center space-x-2">
                                    <input type="checkbox" name="payment_required" value="1" class="h-5 w-5 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded" <?= (isset($paymentRequired) && $paymentRequired) ? 'checked' : '' ?>>
                                    <span class="text-sm text-gray-700">Payment required</span>
                                </label>
                            </div>
                        </div>

                        <!-- Event Badge Upload -->
                        <div>
                            <label for="eventbadge" class="block text-sm font-semibold text-gray-700 mb-2">
                                Event Badge
                            </label>
                            <div class="mt-1 flex justify-center px-6 pt-8 pb-8 border-2 border-gray-200 border-dashed rounded-2xl hover:border-emerald-300 transition-colors duration-200 bg-gray-50 hover:bg-emerald-50">
                                <div class="space-y-2 text-center">
                                    <div class="mx-auto h-16 w-16 bg-gradient-to-r from-emerald-100 to-teal-100 rounded-2xl flex items-center justify-center mb-4">
                                        <svg class="h-8 w-8 text-emerald-500" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="eventbadge" class="relative cursor-pointer bg-white rounded-xl font-semibold text-emerald-600 hover:text-emerald-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-emerald-500 px-3 py-1">
                                            <span>Upload a badge image</span>
                                            <input id="eventbadge" name="eventbadge" type="file" class="sr-only" accept="image/*">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 5MB</p>
                                    <p class="text-xs text-emerald-600 font-medium">This will be the badge attendees earn!</p>
                                </div>
                            </div>
                        </div>

                        <!-- Event Info -->
                        <div>
                            <label for="eventinfo" class="block text-sm font-semibold text-gray-700 mb-2">
                                Detailed Event Information
                            </label>
                            <div class="relative">
                                <textarea id="eventinfo" name="eventinfo" rows="10"
                                          class="block w-full px-4 py-3 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white resize-none"
                                          placeholder="Provide comprehensive details about your event. Include what attendees can expect, any requirements, contact information, and other relevant details..."><?= htmlspecialchars($eventinfo ?? '') ?></textarea>
                            </div>
                            <div class="mt-3 flex items-start space-x-2">
                                <svg class="h-5 w-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-sm text-gray-600">
                                    <span class="font-medium">Pro tip:</span> You can use HTML formatting for rich content like <code class="bg-gray-100 px-1 rounded text-xs">&lt;b&gt;bold&lt;/b&gt;</code>, <code class="bg-gray-100 px-1 rounded text-xs">&lt;i&gt;italic&lt;/i&gt;</code>, and <code class="bg-gray-100 px-1 rounded text-xs">&lt;br&gt;</code> for line breaks.
                                </p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4 pt-6 border-t border-gray-200">
                            <a href="/dashboard" 
                               class="inline-flex justify-center items-center py-3 px-6 border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="inline-flex justify-center items-center py-3 px-6 border border-transparent rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Create Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Help Section -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-100">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Need Help Creating Your Event?</h3>
                        <p class="text-gray-600 mb-4">
                            Make your event stand out! Here are some tips for creating engaging events that students will love to attend.
                        </p>
                        <ul class="text-sm text-gray-600 space-y-2">
                            <li class="flex items-start">
                                <svg class="h-4 w-4 text-emerald-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Use clear, descriptive event names that tell people what to expect
                            </li>
                            <li class="flex items-start">
                                <svg class="h-4 w-4 text-emerald-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Include specific location details and any special instructions
                            </li>
                            <li class="flex items-start">
                                <svg class="h-4 w-4 text-emerald-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Design an attractive badge - it's what attendees will earn and share!
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>