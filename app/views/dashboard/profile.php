<?php $title = 'Edit Profile - ' . APP_NAME; ?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="space-y-8">
            <!-- Page Header -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Edit Profile</h1>
                <p class="text-gray-600">Manage your My Carolinian account settings and preferences</p>
            </div>

            <!-- Profile Information Card -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-emerald-50 to-teal-50 px-8 py-6 border-b border-gray-200">
                    <div class="flex items-center">
                        <div class="h-8 w-8 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-lg flex items-center justify-center mr-3">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Personal Information</h2>
                    </div>
                </div>
                <div class="p-8">
                    <?php $isStudent = (int)($user['is_student'] ?? 0) === 1; ?>
                    
                    <?php if ($isStudent): ?>
                        <!-- Student Notice -->
                        <div class="mb-6 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-xl">
                            <div class="flex items-start">
                                <svg class="h-5 w-5 text-blue-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium">University Student Account</p>
                                    <p class="text-sm mt-1">Your name information is managed by the university system and cannot be modified. If you need to update your name, please contact the registrar's office.</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <form action="/dashboard/profile" method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="fname" class="block text-sm font-semibold text-gray-700 mb-2">
                                    First Name
                                    <?php if ($isStudent): ?>
                                        <span class="text-blue-600 text-xs font-normal">(University Managed)</span>
                                    <?php endif; ?>
                                </label>
                                <div class="relative">
                                    <input type="text" name="fname" id="fname" value="<?= htmlspecialchars($user['fname']) ?>" required 
                                           <?= $isStudent ? 'disabled readonly' : '' ?>
                                           class="block w-full px-4 py-3 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 <?= $isStudent ? 'bg-gray-100 cursor-not-allowed' : 'bg-gray-50 focus:bg-white' ?>">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <?php if ($isStudent): ?>
                                            <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        <?php else: ?>
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label for="lname" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Last Name
                                    <?php if ($isStudent): ?>
                                        <span class="text-blue-600 text-xs font-normal">(University Managed)</span>
                                    <?php endif; ?>
                                </label>
                                <div class="relative">
                                    <input type="text" name="lname" id="lname" value="<?= htmlspecialchars($user['lname']) ?>" required 
                                           <?= $isStudent ? 'disabled readonly' : '' ?>
                                           class="block w-full px-4 py-3 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 <?= $isStudent ? 'bg-gray-100 cursor-not-allowed' : 'bg-gray-50 focus:bg-white' ?>">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <?php if ($isStudent): ?>
                                            <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        <?php else: ?>
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="mname" class="block text-sm font-semibold text-gray-700 mb-2">
                                Middle Name <span class="text-gray-400 font-normal">(optional)</span>
                                <?php if ($isStudent): ?>
                                    <span class="text-blue-600 text-xs font-normal">(University Managed)</span>
                                <?php endif; ?>
                            </label>
                            <div class="relative">
                                <input type="text" name="mname" id="mname" value="<?= htmlspecialchars($user['mname']) ?>" 
                                       <?= $isStudent ? 'disabled readonly' : '' ?>
                                       class="block w-full px-4 py-3 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 <?= $isStudent ? 'bg-gray-100 cursor-not-allowed' : 'bg-gray-50 focus:bg-white' ?>"
                                       placeholder="Enter middle name (optional)">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <?php if ($isStudent): ?>
                                        <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    <?php else: ?>
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!$isStudent): ?>
                            <div class="flex justify-end">
                                <button type="submit" 
                                        class="flex justify-center items-center py-3 px-6 border border-transparent rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Update Profile
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="flex justify-center">
                                <div class="text-sm text-gray-500 bg-gray-50 px-4 py-2 rounded-lg">
                                    <svg class="h-4 w-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Name fields are locked for university students
                                </div>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Change Password Card -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-orange-50 to-red-50 px-8 py-6 border-b border-gray-200">
                    <div class="flex items-center">
                        <div class="h-8 w-8 bg-gradient-to-r from-orange-500 to-red-500 rounded-lg flex items-center justify-center mr-3">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Change Password</h2>
                    </div>
                </div>
                <div class="p-8">
                    <form action="/dashboard/changePassword" method="POST" class="space-y-6">
                        <div>
                            <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-2">Current Password</label>
                            <div class="relative">
                                <input type="password" name="current_password" id="current_password" required 
                                       class="block w-full px-4 py-3 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white"
                                       placeholder="Enter your current password">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="new_password" class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
                                <div class="relative">
                                    <input type="password" name="new_password" id="new_password" required 
                                           class="block w-full px-4 py-3 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white"
                                           placeholder="Enter new password">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">Use at least 8 characters with a mix of letters, numbers, and symbols</p>
                            </div>
                            <div>
                                <label for="confirm_password" class="block text-sm font-semibold text-gray-700 mb-2">Confirm New Password</label>
                                <div class="relative">
                                    <input type="password" name="confirm_password" id="confirm_password" required 
                                           class="block w-full px-4 py-3 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white"
                                           placeholder="Confirm new password">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="flex justify-center items-center py-3 px-6 border border-transparent rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                </svg>
                                Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Profile Picture Upload Card -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-8 py-6 border-b border-gray-200">
                    <div class="flex items-center">
                        <div class="h-8 w-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center mr-3">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Profile Picture</h2>
                    </div>
                </div>
                <div class="p-8">
                    <!-- Current Profile Picture Display -->
                    <div class="flex items-center space-x-6 mb-6">
                        <div class="flex-shrink-0">
                            <?php if (!empty($user['profilepicture'])): ?>
                                <img class="h-20 w-20 rounded-2xl border-4 border-gray-200 shadow-lg object-cover" src="profilePictures/<?= htmlspecialchars($user['profilepicture']) ?>" alt="Current Profile Picture">
                            <?php else: ?>
                                <div class="h-20 w-20 rounded-2xl bg-gradient-to-r from-purple-100 to-pink-100 border-4 border-gray-200 shadow-lg flex items-center justify-center">
                                    <svg class="h-10 w-10 text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Current Picture</h3>
                            <p class="text-sm text-gray-600">Upload a new image to update your profile picture</p>
                        </div>
                    </div>

                    <form action="/dashboard/uploadProfilePicture" method="POST" enctype="multipart/form-data" class="space-y-6">
                        <div>
                            <label for="profile_picture" class="block text-sm font-semibold text-gray-700 mb-2">Choose New Profile Picture</label>
                            <div class="relative">
                                <input type="file" name="profile_picture" id="profile_picture" accept="image/jpeg,image/png,image/gif" required 
                                       class="block w-full px-4 py-3 border border-gray-200 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                            </div>
                            <p class="mt-2 text-xs text-gray-500">Supported formats: JPEG, PNG, GIF. Maximum file size: 5MB</p>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="flex justify-center items-center py-3 px-6 border border-transparent rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                Upload Picture
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Back to Dashboard -->
            <div class="text-center">
                <a href="/dashboard" class="inline-flex items-center text-sm font-semibold text-emerald-600 hover:text-emerald-500 transition-colors duration-200">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>