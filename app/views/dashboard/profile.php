<?php $title = 'Edit Profile - ' . APP_NAME; ?>

<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Edit Profile</h1>
    <form action="/dashboard/profile" method="POST" class="space-y-4">
        <div>
            <label for="fname" class="block text-sm font-medium text-gray-700">First Name</label>
            <input type="text" name="fname" id="fname" value="<?= htmlspecialchars($user['fname']) ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>
        <div>
            <label for="mname" class="block text-sm font-medium text-gray-700">Middle Name</label>
            <input type="text" name="mname" id="mname" value="<?= htmlspecialchars($user['mname']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>
        <div>
            <label for="lname" class="block text-sm font-medium text-gray-700">Last Name</label>
            <input type="text" name="lname" id="lname" value="<?= htmlspecialchars($user['lname']) ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>
        <div>
            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Update Profile</button>
        </div>
    </form>

    <h2 class="text-xl font-bold text-gray-900 mt-8">Change Password</h2>
    <form action="/dashboard/changePassword" method="POST" class="space-y-4">
        <div>
            <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
            <input type="password" name="current_password" id="current_password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>
        <div>
            <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
            <input type="password" name="new_password" id="new_password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>
        <div>
            <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
            <input type="password" name="confirm_password" id="confirm_password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>
        <div>
            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Change Password</button>
        </div>
    </form>

    <h2 class="text-xl font-bold text-gray-900 mt-8">Upload Profile Picture</h2>
    <form action="/dashboard/uploadProfilePicture" method="POST" enctype="multipart/form-data" class="space-y-4">
        <div>
            <label for="profile_picture" class="block text-sm font-medium text-gray-700">Profile Picture</label>
            <input type="file" name="profile_picture" id="profile_picture" accept="image/jpeg,image/png,image/gif" required class="mt-1 block w-full">
        </div>
        <div>
            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Upload Picture</button>
        </div>
    </form>
</div> 