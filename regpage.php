<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Register</h2>
        <form action="register.php" method="POST" class="mt-4">
			<input type="hidden" id="action" name="action" value="register">	
			<div class="mb-3">
                <label for="firstname" class="form-label">First Name</label>
                <input type="text" class="form-control" id="fname" name="fname" required>
			</div>
            <div class="mb-3">
                <label for="firstname" class="form-label">Middle Name</label>
                <input type="text" class="form-control" id="mname" name="mname" required>
			</div>
            <div class="mb-3">
                <label for="firstname" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="lname" name="lname" required>
			</div>


            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Register</button>
		</form>
	
		<a href="login.php" class="btn btn-primary">Already Registered? Click here to Log-in</a>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

