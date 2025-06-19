<?php $title = 'Manage Event - ' . APP_NAME; ?>

<div class="max-w-6xl mx-auto">
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <?php if (!isset($event) || empty($event)): ?>
                <p class="text-red-600">Unable to load event details.</p>
            <?php else: ?>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Manage Event: <?= htmlspecialchars($event['eventname']) ?></h2>
                <p class="mb-4 text-gray-600"><strong>Location:</strong> <?= htmlspecialchars($event['location']) ?> | <strong>Date:</strong> <?= date('M d, Y H:i', strtotime($event['startdate'])) ?> - <?= date('M d, Y H:i', strtotime($event['enddate'])) ?></p>
                <p class="mb-6 text-gray-700"><?= htmlspecialchars($event['eventshortinfo']) ?></p>

                <h3 class="text-xl font-semibold mb-3">Participants (<?= $event['participant_count_file'] ?? 0 ?>)</h3>
                <div class="overflow-x-auto bg-gray-50 border rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">User</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Email</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Joined At</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (!empty($event['participants'])): ?>
                                <?php foreach ($event['participants'] as $p): ?>
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($p['name'] ?? '-') ?></td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($p['email'] ?? '-') ?></td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($p['joined_at'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="px-4 py-4 text-center text-gray-500">No participants yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex space-x-4">
                    <a href="<?= url('/events/edit/<?= $event['eventid'] ?>') ?>" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700">Edit Event</a>
                    <a href="<?= url('/events/delete/<?= $event['eventid'] ?>') ?>" onclick="return confirm('Are you sure you want to delete this event?');" class="bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-red-700">Delete Event</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div> 