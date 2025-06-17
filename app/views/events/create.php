<?php $title = 'Create Event - ' . APP_NAME; ?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Create New Event</h2>
            
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <!-- Event Name -->
                <div>
                    <label for="eventname" class="block text-sm font-medium text-gray-700">Event Name *</label>
                    <input type="text" id="eventname" name="eventname" required
                           value="<?= htmlspecialchars($eventname ?? '') ?>"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>

                <!-- Date Range -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="startdate" class="block text-sm font-medium text-gray-700">Start Date *</label>
                        <input type="datetime-local" id="startdate" name="startdate" required
                               value="<?= htmlspecialchars($startdate ?? '') ?>"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="enddate" class="block text-sm font-medium text-gray-700">End Date *</label>
                        <input type="datetime-local" id="enddate" name="enddate" required
                               value="<?= htmlspecialchars($enddate ?? '') ?>"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>

                <!-- Location -->
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700">Location *</label>
                    <input type="text" id="location" name="location" required
                           value="<?= htmlspecialchars($location ?? '') ?>"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>

                <!-- Short Info -->
                <div>
                    <label for="eventshortinfo" class="block text-sm font-medium text-gray-700">Short Description</label>
                    <textarea id="eventshortinfo" name="eventshortinfo" rows="3"
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                              placeholder="Brief description of the event"><?= htmlspecialchars($eventshortinfo ?? '') ?></textarea>
                </div>

                <!-- Event Badge -->
                <div>
                    <label for="eventbadge" class="block text-sm font-medium text-gray-700">Event Badge</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="eventbadge" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                    <span>Upload a file</span>
                                    <input id="eventbadge" name="eventbadge" type="file" class="sr-only" accept="image/*">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, GIF up to 5MB</p>
                        </div>
                    </div>
                </div>

                <!-- Event Info -->
                <div>
                    <label for="eventinfo" class="block text-sm font-medium text-gray-700">Event Information</label>
                    <textarea id="eventinfo" name="eventinfo" rows="10"
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                              placeholder="Detailed information about the event..."><?= htmlspecialchars($eventinfo ?? '') ?></textarea>
                    <p class="mt-2 text-sm text-gray-500">You can use HTML formatting for rich content.</p>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3">
                    <a href="/dashboard" 
                       class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Create Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> 