<?php $title = 'My Badges - ' . APP_NAME; ?>

<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">My Badges</h1>
    <?php if (!empty($attendedEvents)): ?>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($attendedEvents as $event): ?>
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                    <?php if (!empty($event['eventbadgepath'])): ?>
                        <img class="w-full h-48 object-cover" src="<?= htmlspecialchars($event['eventbadgepath']) ?>" alt="Event Badge">
                    <?php else: ?>
                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                            <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                            </svg>
                        </div>
                    <?php endif; ?>
                    <div class="p-4">
                        <h4 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($event['eventname']) ?></h4>
                        <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($event['eventshortinfo']) ?></p>
                        <div class="mt-2 text-xs text-gray-500">
                            <p><?= date('M j, Y', strtotime($event['startdate'])) ?> - <?= date('M j, Y', strtotime($event['enddate'])) ?></p>
                            <p><?= htmlspecialchars($event['location']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-gray-600">You haven't attended any events yet.</p>
    <?php endif; ?>
</div> 