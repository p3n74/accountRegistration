<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? APP_NAME ?></title>
    <link href="dist/output.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom gradient animations */
        @keyframes gradient-shift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        .gradient-animate {
            background-size: 200% 200%;
            animation: gradient-shift 8s ease infinite;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-white to-slate-100 min-h-screen flex flex-col">
    <?php if (isset($_SESSION['uid'])): ?>
        <!-- Modern Navigation for authenticated users -->
        <nav class="bg-white/80 backdrop-blur-lg shadow-xl border-b border-gray-100 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="<?= url('/dashboard') ?>" class="flex items-center space-x-3 group">
                            <div class="h-10 w-10 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all duration-200">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                            <span class="text-xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">
                                My Carolinian
                            </span>
                        </a>
                    </div>
                    
                    <!-- Desktop Navigation -->
                    <div class="hidden md:flex items-center space-x-1">
                        <a href="<?= url('/dashboard') ?>" class="px-4 py-2 rounded-xl text-sm font-medium text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 transition-all duration-200 flex items-center space-x-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v3H8V5z"></path>
                            </svg>
                            <span>Dashboard</span>
                        </a>
                        <a href="<?= url('/events/create') ?>" class="px-4 py-2 rounded-xl text-sm font-medium text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 transition-all duration-200 flex items-center space-x-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span>Create Event</span>
                        </a>
                        <a href="<?= url('/dashboard/badges') ?>" class="px-4 py-2 rounded-xl text-sm font-medium text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 transition-all duration-200 flex items-center space-x-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                            </svg>
                            <span>My Badges</span>
                        </a>
                        
                        <!-- Profile Dropdown -->
                        <div class="relative">
                            <button class="flex items-center space-x-2 px-4 py-2 rounded-xl text-sm font-medium text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 transition-all duration-200" onclick="toggleDropdown(event)">
                                <div class="h-8 w-8 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-lg flex items-center justify-center">
                                    <span class="text-xs font-semibold text-white">
                                        <?= strtoupper(substr($_SESSION['fname'], 0, 1)) ?>
                                    </span>
                                </div>
                                <span><?= $_SESSION['fname'] ?></span>
                                <svg class="h-4 w-4 transition-transform duration-200" id="dropdownArrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div id="profileDropdown" class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50 hidden transform opacity-0 scale-95 transition-all duration-200">
                                <div class="px-4 py-3 border-b border-gray-100">
                                    <p class="text-sm font-medium text-gray-900"><?= $_SESSION['fname'] ?></p>
                                    <p class="text-xs text-gray-500"><?= $_SESSION['email'] ?? 'Student' ?></p>
                                </div>
                                <a href="<?= url('/dashboard/profile') ?>" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 transition-colors duration-200">
                                    <svg class="h-4 w-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Profile Settings
                                </a>
                                <a href="<?= url('/auth/logout') ?>" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-200">
                                    <svg class="h-4 w-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    Sign Out
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile menu button -->
                    <div class="md:hidden flex items-center">
                        <button class="p-2 rounded-xl text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 transition-all duration-200" onclick="toggleMobileMenu()">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Navigation Menu -->
            <div id="mobileMenu" class="md:hidden hidden bg-white border-t border-gray-100">
                <div class="px-4 py-2 space-y-1">
                    <a href="<?= url('/dashboard') ?>" class="block px-4 py-3 rounded-xl text-sm font-medium text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 transition-all duration-200">Dashboard</a>
                    <a href="<?= url('/events/create') ?>" class="block px-4 py-3 rounded-xl text-sm font-medium text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 transition-all duration-200">Create Event</a>
                    <a href="<?= url('/dashboard/badges') ?>" class="block px-4 py-3 rounded-xl text-sm font-medium text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 transition-all duration-200">My Badges</a>
                    <a href="<?= url('/dashboard/profile') ?>" class="block px-4 py-3 rounded-xl text-sm font-medium text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 transition-all duration-200">Profile</a>
                    <a href="<?= url('/auth/logout') ?>" class="block px-4 py-3 rounded-xl text-sm font-medium text-red-600 hover:bg-red-50 transition-all duration-200">Sign Out</a>
                </div>
            </div>
        </nav>
    <?php endif; ?>

    <!-- Modern Flash Messages -->
    <?php if (isset($flash) && $flash): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="rounded-2xl p-4 shadow-lg border <?= $flash['type'] === 'success' ? 'bg-emerald-50 border-emerald-200' : 'bg-red-50 border-red-200' ?> transform transition-all duration-300 hover:scale-105">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <?php if ($flash['type'] === 'success'): ?>
                            <div class="h-8 w-8 bg-emerald-500 rounded-xl flex items-center justify-center">
                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        <?php else: ?>
                            <div class="h-8 w-8 bg-red-500 rounded-xl flex items-center justify-center">
                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium <?= $flash['type'] === 'success' ? 'text-emerald-800' : 'text-red-800' ?>">
                            <?= htmlspecialchars($flash['message']) ?>
                        </p>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 flex-shrink-0 <?= $flash['type'] === 'success' ? 'text-emerald-400 hover:text-emerald-600' : 'text-red-400 hover:text-red-600' ?> transition-colors duration-200">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="flex-1">
        <?= $content ?>
    </main>

    <!-- Modern Footer -->
    <footer class="bg-white/80 backdrop-blur-lg border-t border-gray-100 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center space-x-3 mb-4 md:mb-0">
                    <div class="h-8 w-8 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-lg flex items-center justify-center">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <span class="font-semibold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">
                        My Carolinian
                    </span>
                </div>
                <div class="flex items-center space-x-6 text-sm text-gray-500">
                    <span>&copy; <?= date('Y') ?> Computer Information Sciences Council. All rights reserved.</span>
                    <div class="flex items-center space-x-4">
                        <a href="#" class="hover:text-emerald-600 transition-colors duration-200">Privacy</a>
                        <a href="#" class="hover:text-emerald-600 transition-colors duration-200">Terms</a>
                        <a href="#" class="hover:text-emerald-600 transition-colors duration-200">Support</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        function toggleDropdown(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('profileDropdown');
            const arrow = document.getElementById('dropdownArrow');
            
            if (dropdown.classList.contains('hidden')) {
                dropdown.classList.remove('hidden', 'opacity-0', 'scale-95');
                dropdown.classList.add('opacity-100', 'scale-100');
                arrow.style.transform = 'rotate(180deg)';
            } else {
                dropdown.classList.add('opacity-0', 'scale-95');
                dropdown.classList.remove('opacity-100', 'scale-100');
                arrow.style.transform = 'rotate(0deg)';
                setTimeout(() => {
                    dropdown.classList.add('hidden');
                }, 200);
            }
        }

        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('hidden');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            const dropdown = document.getElementById('profileDropdown');
            const arrow = document.getElementById('dropdownArrow');
            if (!dropdown.classList.contains('hidden')) {
                dropdown.classList.add('opacity-0', 'scale-95');
                dropdown.classList.remove('opacity-100', 'scale-100');
                arrow.style.transform = 'rotate(0deg)';
                setTimeout(() => {
                    dropdown.classList.add('hidden');
                }, 200);
            }
        });

        // Auto-hide flash messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessages = document.querySelectorAll('[class*="bg-emerald-50"], [class*="bg-red-50"]');
            flashMessages.forEach(function(message) {
                setTimeout(function() {
                    message.style.transition = 'all 0.5s ease-out';
                    message.style.transform = 'translateX(100%)';
                    message.style.opacity = '0';
                    setTimeout(function() {
                        message.remove();
                    }, 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>