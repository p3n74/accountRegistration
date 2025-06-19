<?php $title = 'Register - ' . APP_NAME; ?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Create your account
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Or
                <a href="/auth/login" class="font-medium text-indigo-600 hover:text-indigo-500">
                    sign in to your existing account
                </a>
            </p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <form class="mt-8 space-y-6" method="POST">
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="fname" class="sr-only">First Name</label>
                    <input id="fname" name="fname" type="text" required 
                           value="<?= htmlspecialchars($fname ?? '') ?>"
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                           placeholder="First Name">
                </div>
                <div>
                    <label for="mname" class="sr-only">Middle Name</label>
                    <input id="mname" name="mname" type="text" 
                           value="<?= htmlspecialchars($mname ?? '') ?>"
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                           placeholder="Middle Name (optional)">
                </div>
                <div>
                    <label for="lname" class="sr-only">Last Name</label>
                    <input id="lname" name="lname" type="text" required 
                           value="<?= htmlspecialchars($lname ?? '') ?>"
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                           placeholder="Last Name">
                </div>
                <div>
                    <label for="email" class="sr-only">Email address</label>
                    <input id="email" name="email" type="email" required 
                           value="<?= htmlspecialchars($email ?? '') ?>"
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                           placeholder="Email address">
                </div>
                
                <!-- Existing student notification -->
                <div id="existing-student-notice" class="hidden bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
                    <p class="text-sm">
                        <strong>Student record found!</strong> Your name information has been pre-filled and locked because you are already in our system.
                    </p>
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <input id="password" name="password" type="password" required 
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                           placeholder="Password">
                </div>
                <div>
                    <label for="confirm_password" class="sr-only">Confirm Password</label>
                    <input id="confirm_password" name="confirm_password" type="password" required 
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                           placeholder="Confirm Password">
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                    </span>
                    Create Account
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const emailField = document.getElementById('email');
    const fnameField = document.getElementById('fname');
    const mnameField = document.getElementById('mname');
    const lnameField = document.getElementById('lname');
    const existingStudentNotice = document.getElementById('existing-student-notice');
    
    // Null check for all elements
    if (!emailField || !fnameField || !mnameField || !lnameField || !existingStudentNotice) {
        console.error('Required form elements not found');
        return;
    }
    
    let debounceTimer;
    
    // Check existing student on page load if email is already filled
    if (emailField.value.trim()) {
        checkExistingStudent(emailField.value.trim());
    }
    
    // More dynamic - check on every input with shorter debounce for live feel
    emailField.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        
        const email = this.value.trim();
        
        // If email is empty or invalid, reset immediately
        if (!email || !isValidEmail(email)) {
            resetNameFields();
            return;
        }
        
        // Very short debounce for live feel (200ms instead of 500ms)
        debounceTimer = setTimeout(() => {
            checkExistingStudent(email);
        }, 200);
    });
    
    // Also check on blur for immediate validation
    emailField.addEventListener('blur', function() {
        const email = this.value.trim();
        if (email && isValidEmail(email)) {
            checkExistingStudent(email);
        }
    });
    
    function isValidEmail(email) {
        // More thorough email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    function checkExistingStudent(email) {
        const formData = new FormData();
        formData.append('email', email);
        
        fetch('/auth/checkExistingStudent', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text(); // Get as text first to debug
        })
        .then(text => {
            try {
                const data = JSON.parse(text);
                if (data.exists && data.data) {
                    // Pre-fill and lock name fields
                    fnameField.value = data.data.fname;
                    mnameField.value = data.data.mname || '';
                    lnameField.value = data.data.lname;
                    
                    // Disable the fields
                    fnameField.disabled = true;
                    mnameField.disabled = true;
                    lnameField.disabled = true;
                    
                    // Add visual styling for disabled fields
                    fnameField.classList.add('bg-gray-100', 'cursor-not-allowed');
                    mnameField.classList.add('bg-gray-100', 'cursor-not-allowed');
                    lnameField.classList.add('bg-gray-100', 'cursor-not-allowed');
                    
                    // Show notification
                    if (existingStudentNotice && existingStudentNotice.classList) {
                        existingStudentNotice.classList.remove('hidden');
                    }
                } else {
                    resetNameFields();
                }
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                console.error('Response text:', text);
                resetNameFields();
            }
        })
        .catch(error => {
            console.error('Error checking existing student:', error);
            resetNameFields();
        });
    }
    
    function resetNameFields() {
        // Check if elements exist before manipulating
        if (fnameField) {
            fnameField.disabled = false;
            fnameField.classList.remove('bg-gray-100', 'cursor-not-allowed');
        }
        if (mnameField) {
            mnameField.disabled = false;
            mnameField.classList.remove('bg-gray-100', 'cursor-not-allowed');
        }
        if (lnameField) {
            lnameField.disabled = false;
            lnameField.classList.remove('bg-gray-100', 'cursor-not-allowed');
        }
        
        // Hide notification
        if (existingStudentNotice && existingStudentNotice.classList) {
            existingStudentNotice.classList.add('hidden');
        }
        
        // Don't clear values if they were manually entered
        if (emailField && emailField.value.trim() === '') {
            if (fnameField) fnameField.value = '';
            if (mnameField) mnameField.value = '';
            if (lnameField) lnameField.value = '';
        }
    }
});
</script> 