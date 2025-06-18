<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Log-in</title>
	<?php require_once '../includes/config.php'; ?>
	 <!-- Favicon -->
 <link rel="icon" href="icon.png" type="image/png">
    <!-- Tailwind CSS -->
    <link href="dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-bold text-center mb-6 text-primary-700">Log-in</h2>
            <form action="auth.php" method="POST">
                <input type="hidden" id="action" name="action" value="login">
                <div class="mb-6">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-6">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn-primary w-full">Login</button>
            </form>

            <a href="regpage.php" class="btn-primary mt-4 w-full block text-center">No account? Register here</a>

            <!-- Forgot Password Button -->
            <div class="mt-6 text-center">
                <a href="reset-request.php" class="text-primary-600 hover:text-primary-700 hover:underline">Forgot Password?</a>
            </div>
        </div>
    </div>

    <!-- Modal for Error Message -->
    <div class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center" id="errorModal">
        <div class="bg-white rounded-lg p-6 max-w-sm w-full mx-4">
            <div class="flex justify-between items-center mb-4">
                <h5 class="text-lg font-semibold">Login Error</h5>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="mb-4" id="errorMessage">
                <!-- Error message will be injected here -->
            </div>
            <div class="flex justify-end">
                <button type="button" class="btn-secondary" onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>

    <script>
        // Check if there is an error message passed via URL
        const urlParams = new URLSearchParams(window.location.search);
        const errorMessage = urlParams.get('error');
        if (errorMessage) {
            // Show modal with error message
            document.getElementById('errorMessage').innerText = errorMessage;
            document.getElementById('errorModal').classList.remove('hidden');
            document.getElementById('errorModal').classList.add('flex');
        }

        function closeModal() {
            document.getElementById('errorModal').classList.add('hidden');
            document.getElementById('errorModal').classList.remove('flex');
        }
    </script>
</body>
</html>

