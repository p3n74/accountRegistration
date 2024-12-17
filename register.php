<?php
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'includes/db.php'; // Include the database connection
require 'includes/apikey.php'; // Include the API key

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] == "register") {
    // Sanitize and validate inputs
    $fname = htmlspecialchars($_POST['fname']);
    $mname = htmlspecialchars($_POST['mname']);
    $lname = htmlspecialchars($_POST['lname']);

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    if (strlen($password) < 8) {
        die("Password must be at least 8 characters long.");
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Check if the email already exists in the database
    $checkEmailSql = "SELECT * FROM user_credentials WHERE email = ?";
    $stmt = $conn->prepare($checkEmailSql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        die("Error: This email address is already registered.");
    }

    // Generate a unique token for email confirmation
    $token = bin2hex(random_bytes(32));

    // Insert user into the database
    $insertSql = "INSERT INTO user_credentials (fname, mname, lname, email, password, currboundtoken, emailverified) VALUES (?, ?, ?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($insertSql);
    $stmt->bind_param("ssssss", $fname, $mname, $lname, $email, $hashedPassword, $token);

    if ($stmt->execute()) {
        // Send confirmation email
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = '21102134@usc.edu.ph'; // Gmail used to send email 
            $mail->Password = $apikey; // API KEY 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('211021342@usc.edu.ph', 'Nikolai');
            $mail->addAddress($email, $fname);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Confirm Your DCISM Account';
            $confirmationLink = "http://accounts.dcism.org/accountRegistration/confirm.php?token=$token";
            $mail->Body = "<p>Hi $fname,</p>
                           <p>Thank you for registering. Please click the link below to confirm your email:</p>
                           <p><a href='$confirmationLink'>Confirm My Account</a></p>";

            $mail->send();

            // Redirect to avoid form resubmission
            header("Location: register.php?status=success");
            exit; // Make sure to exit after the redirect
        } catch (Exception $e) {
            echo "Error sending email: " . $mail->ErrorInfo;
        }
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom Styling for the Registration Page */
        .container {
            max-width: 500px;
            margin-top: 100px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color: #007BFF;
            border: none;
        }
        h2 {
            color: #007BFF;
        }
        label {
            font-weight: bold;
        }
        .btn-link {
            color: #007BFF;
        }
        /* Success Notification Styling */
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Register</h2>
        
        <!-- Success message -->
        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="alert alert-success text-center">
                Registration successful! A confirmation email has been sent to your email address.
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" class="mt-4">
            <input type="hidden" id="action" name="action" value="register">  
            <div class="mb-3">
                <label for="fname" class="form-label">First Name</label>
                <input type="text" class="form-control" id="fname" name="fname" required>
            </div>
            <div class="mb-3">
                <label for="mname" class="form-label">Middle Name</label>
                <input type="text" class="form-control" id="mname" name="mname" required>
            </div>
            <div class="mb-3">
                <label for="lname" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="lname" name="lname" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>

        <!-- Link to Login page -->
        <div class="mt-3 text-center">
            <a href="login.php" class="btn btn-link">Already Registered? Click here to Log-in</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

