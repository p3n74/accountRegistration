<?php $title = 'Register - ' . APP_NAME; ?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 via-white to-slate-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <!-- Header Section -->
        <div class="text-center mb-8">
            <div class="mx-auto h-16 w-16 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">
                Create your account
            </h2>
            <p class="text-gray-600">
                Already have an account?
                <a href="/auth/login" class="font-semibold text-emerald-600 hover:text-emerald-500 transition-colors duration-200">
                    Sign in here
                </a>
            </p>
        </div>
        
        <!-- Main Card -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8">
            <!-- Error/Success Messages -->
            <?php if (isset($error)): ?>
                <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl flex items-center">
                    <svg class="h-5 w-5 text-red-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm font-medium"><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center">
                    <svg class="h-5 w-5 text-emerald-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm font-medium"><?= htmlspecialchars($success) ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Existing Student Notice -->
            <div id="existing-student-notice" class="hidden mb-6 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-xl">
                <div class="flex items-start">
                    <svg class="h-5 w-5 text-blue-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <p class="text-sm font-medium">Student record found!</p>
                        <p class="text-sm mt-1">Your name and program information have been pre-filled and locked because you are already in our system.</p>
                    </div>
                </div>
            </div>

            <!-- Registration Form -->
            <form class="space-y-5" method="POST">
                <!-- Email Field (moved to first) -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email address</label>
                    <div class="relative">
                        <input id="email" name="email" type="email" required 
                               value="<?= htmlspecialchars($email ?? '') ?>"
                               class="block w-full px-4 py-3 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white" 
                               placeholder="Enter your email address">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Name Fields -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="fname" class="block text-sm font-semibold text-gray-700 mb-2">First Name</label>
                        <div class="relative">
                            <input id="fname" name="fname" type="text" required 
                                   value="<?= htmlspecialchars($fname ?? '') ?>"
                                   class="block w-full px-4 py-3 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white" 
                                   placeholder="First name">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="lname" class="block text-sm font-semibold text-gray-700 mb-2">Last Name</label>
                        <div class="relative">
                            <input id="lname" name="lname" type="text" required 
                                   value="<?= htmlspecialchars($lname ?? '') ?>"
                                   class="block w-full px-4 py-3 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white" 
                                   placeholder="Last name">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="mname" class="block text-sm font-semibold text-gray-700 mb-2">Middle Name <span class="text-gray-400 font-normal">(optional)</span></label>
                    <div class="relative">
                        <input id="mname" name="mname" type="text" 
                               value="<?= htmlspecialchars($mname ?? '') ?>"
                               class="block w-full px-4 py-3 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white" 
                               placeholder="Middle name (optional)">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Program Selection -->
                <div>
                    <label for="program_id" class="block text-sm font-semibold text-gray-700 mb-2">Program <span class="text-gray-400 font-normal">(optional)</span></label>
                    <p class="text-xs text-gray-500 -mt-1 mb-2">Selecting your program will tailor your content to show events and activities from your department first.</p>
                    <div class="relative">
                        <select id="program_id" name="program_id" 
                                class="block w-full px-4 py-3 border border-gray-200 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white">
                            <option value="">Select a program (optional)</option>
                            <?php if (isset($programs) && is_array($programs)): ?>
                                <?php 
                                $currentSchool = '';
                                $currentDepartment = '';
                                foreach ($programs as $program): 
                                    if ($currentSchool !== $program['school_name']):
                                        if ($currentSchool !== ''): echo '</optgroup>'; endif;
                                        $currentSchool = $program['school_name'];
                                        echo '<optgroup label="' . htmlspecialchars($currentSchool) . '">';
                                        $currentDepartment = '';
                                    endif;
                                    
                                    if ($currentDepartment !== $program['department_name']):
                                        $currentDepartment = $program['department_name'];
                                        echo '<option disabled>── ' . htmlspecialchars($currentDepartment) . ' ──</option>';
                                    endif;
                                    
                                    $selected = (isset($program_id) && $program_id == $program['program_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $program['program_id'] ?>" <?= $selected ?>>
                                        <?= htmlspecialchars($program['program_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                                <?php if ($currentSchool !== ''): echo '</optgroup>'; endif; ?>
                            <?php endif; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Password Fields -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <input id="password" name="password" type="password" required 
                               class="block w-full px-4 py-3 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white" 
                               placeholder="Create a strong password">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-semibold text-gray-700 mb-2">Confirm Password</label>
                    <div class="relative">
                        <input id="confirm_password" name="confirm_password" type="password" required 
                               class="block w-full px-4 py-3 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white" 
                               placeholder="Confirm your password">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <button type="submit" 
                        class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                    Create Your Account
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center">
            <p class="text-xs text-gray-500">
                By creating an account, you agree to our terms of service and privacy policy.
            </p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const emailField = document.getElementById('email');
    const fnameField = document.getElementById('fname');
    const mnameField = document.getElementById('mname');
    const lnameField = document.getElementById('lname');
    const programField = document.getElementById('program_id');
    const existingStudentNotice = document.getElementById('existing-student-notice');
    
    // Null check for all elements
    if (!emailField || !fnameField || !mnameField || !lnameField || !programField || !existingStudentNotice) {
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
                    
                    // Pre-fill and lock program field if available
                    if (data.data.program_id) {
                        programField.value = data.data.program_id;
                        programField.disabled = true;
                        programField.classList.add('bg-gray-100', 'cursor-not-allowed');
                    }
                    
                    // Disable the name fields
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
        if (programField) {
            programField.disabled = false;
            programField.classList.remove('bg-gray-100', 'cursor-not-allowed');
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
            if (programField) programField.value = '';
        }
    }
});
</script>