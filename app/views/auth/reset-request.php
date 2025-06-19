<?php $title = 'Reset Password Request - ' . APP_NAME; ?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Forgot your password?
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Enter your email address and we'll send you a link to reset your password.
            </p>
        </div>

        <?php if (isset($flash) && $flash['type'] === 'error'): ?>
            <div class="bg-red-100 text-red-700 px-4 py-2 rounded">
                <?= htmlspecialchars($flash['message']); ?>
            </div>
        <?php elseif (isset($flash) && $flash['type'] === 'success'): ?>
            <div class="bg-green-100 text-green-700 px-4 py-2 rounded">
                <?= htmlspecialchars($flash['message']); ?>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" method="POST" action="<?= url('/auth/resetRequest') ?>">
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="email" class="sr-only">Email address</label>
                    <input id="email" name="email" type="email" required
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                           placeholder="Email address">
                </div>
            </div>

            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Send Reset Link
                </button>
            </div>

            <div class="text-sm text-center">
                <a href="<?= url('/auth/login') ?>" class="font-medium text-indigo-600 hover:text-indigo-500">
                    Back to login
                </a>
            </div>
        </form>
    </div>
</div> 