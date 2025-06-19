<?php $title = 'Edit Event - ' . APP_NAME; ?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Edit Event</h2>
            
            <?php if (!isset($event) || empty($event)): ?>
                <p class="text-red-600">Unable to load event details.</p>
            <?php else: ?>
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <!-- Event Name -->
                    <div>
                        <label for="eventname" class="block text-sm font-medium text-gray-700">Event Name *</label>
                        <input type="text" id="eventname" name="eventname" required
                               value="<?= htmlspecialchars($event['eventname']) ?>"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <!-- Date Range -->
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="startdate" class="block text-sm font-medium text-gray-700">Start Date *</label>
                            <input type="datetime-local" id="startdate" name="startdate" required
                                   value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime($event['startdate']))) ?>"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="enddate" class="block text-sm font-medium text-gray-700">End Date *</label>
                            <input type="datetime-local" id="enddate" name="enddate" required
                                   value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime($event['enddate']))) ?>"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    <!-- Location -->
                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700">Location *</label>
                        <input type="text" id="location" name="location" required
                               value="<?= htmlspecialchars($event['location']) ?>"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <!-- Short Info -->
                    <div>
                        <label for="eventshortinfo" class="block text-sm font-medium text-gray-700">Short Description</label>
                        <textarea id="eventshortinfo" name="eventshortinfo" rows="3"
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                  placeholder="Brief description of the event"><?= htmlspecialchars($event['eventshortinfo']) ?></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-3">
                        <a href="<?= url('/dashboard') ?>" 
                           class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Save Changes
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div> 