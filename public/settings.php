<?php
// Start the session
session_start();

// Check if user is logged in by checking session for UID
if (!isset($_SESSION['uid'])) {
    die("You must log in first.");
}

// Include database connection
require_once '../includes/db.php';

// Include PHPMailer
require '../phpmailer/Exception.php';
require '../phpmailer/PHPMailer.php';
require '../phpmailer/SMTP.php';
require '../includes/apikey.php'; // Include the API key
require_once '../includes/config.php'; // Include configuration (BASE_URL)

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Retrieve UID from session
$uid = $_SESSION['uid'];

// Query to fetch user details (name, email, profile picture) based on UID
$sql_user = "SELECT fname, mname, lname, email, profilepicture, verification_code, password FROM user_credentials WHERE uid = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $uid);  // Bind UID to the query
$stmt_user->execute();
$stmt_user->store_result();  // Store the result set to avoid "Commands out of sync" error
$stmt_user->bind_result($fname, $mname, $lname, $email, $profilepicture, $verification_code, $current_password);  // Bind the result to variables
$stmt_user->fetch();  // Fetch the data into the variables

// Use a default image if profile picture is not set
$profilepicture = $profilepicture ? $profilepicture : 'profilePictures/default.png';

// Handle the form submission for profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle personal info update
    if (isset($_POST['action']) && $_POST['action'] == 'update_settings') {
        $new_fname = $_POST['fname'];
        $new_mname = $_POST['mname'];
        $new_lname = $_POST['lname'];
        $new_password = $_POST['password'];

        // If the password field is not empty, hash the new password
        if (!empty($new_password)) {
            // Hash the new password using bcrypt
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            // Update password along with other details
            $sql_update = "UPDATE user_credentials SET fname = ?, mname = ?, lname = ?, password = ? WHERE uid = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ssssi", $new_fname, $new_mname, $new_lname, $hashed_password, $uid);
        } else {
            // Update only the personal info without changing the password
            $sql_update = "UPDATE user_credentials SET fname = ?, mname = ?, lname = ? WHERE uid = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("sssi", $new_fname, $new_mname, $new_lname, $uid);
        }

        $stmt_update->execute();
        if ($stmt_update->affected_rows > 0) {
            // Redirect after successful update
            header("Location: settings.php?status=success");
            exit();
        } else {
            $error_message = "No changes were made or an error occurred.";
        }
        $stmt_update->close();
    }

    // Handle email update request
    if (isset($_POST['action']) && $_POST['action'] == 'update_email') {
        $new_email = $_POST['email'];

        // Validate the new email
        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Invalid email format.";
        } else {
            // Generate a new verification code
            $verification_code = bin2hex(random_bytes(16)); // Generate a 32-character random code
            
            // Update the database with the new email and verification code
            $sql_update_email = "UPDATE user_credentials SET new_email = ?, verification_code = ?, emailverified = 0 WHERE uid = ?";
            $stmt_update_email = $conn->prepare($sql_update_email);
            $stmt_update_email->bind_param("ssi", $new_email, $verification_code, $uid);
            $stmt_update_email->execute();

            // Check if the update was successful
            if ($stmt_update_email->affected_rows > 0) {
                // Send verification email using PHPMailer
                $verification_link = BASE_URL . "verify_email.php?code=" . urlencode($verification_code);

                // Set up PHPMailer
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';  // Set your SMTP server (replace with actual server)
                    $mail->SMTPAuth = true;
                    $mail->Username = '21102134@usc.edu.ph';  // Replace with Mailtrap SMTP username
                    $mail->Password = $apikey;  // Replace with Mailtrap SMTP password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    // Recipients
                    $mail->setFrom('21102134@usc.edu.ph', 'Nikolai');
                    $mail->addAddress($new_email);  // Recipient's email address

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'DCISM Accounts';
                    $mail->Body    = "Please verify your email by clicking the following link:<br><br><a href='$verification_link'>Verify Email</a>";

                    // Send the email
                    $mail->send();
                    //echo "<p>A verification email has been sent to your new email address. Please check your inbox.</p>";
                } catch (Exception $e) {
                    $error_message = "Failed to send verification email. Error: " . $mail->ErrorInfo;
                }
            } else {
                $error_message = "Failed to update your email request.";
            }
            $stmt_update_email->close();
        }
    }
}

// Handle the file upload for profile picture
if (isset($_FILES['profilepicture']) && $_FILES['profilepicture']['error'] == 0) {
    $uploadDir = 'profilePictures/';
    $fileName = $_FILES['profilepicture']['name'];
    $fileTmpName = $_FILES['profilepicture']['tmp_name'];
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

    // Create a new file name: uid_fname_mname_lname.extension
    $newFileName = $uid . "_" . $fname . "_" . $mname . "_" . $lname . "." . $fileExtension;
    $newFilePath = $uploadDir . $newFileName;

    // Validate the file type (optional)
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($fileExtension, $allowedExtensions)) {
        $error_message = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
    } else {
        // Move the uploaded file to the profilePictures folder
        if (move_uploaded_file($fileTmpName, $newFilePath)) {
            // Update the profile picture in the database
            $sql_update_pic = "UPDATE user_credentials SET profilepicture = ? WHERE uid = ?";
            $stmt_update_pic = $conn->prepare($sql_update_pic);
            $stmt_update_pic->bind_param("si", $newFilePath, $uid);
            $stmt_update_pic->execute();

            if ($stmt_update_pic->affected_rows > 0) {
                // Redirect after successful profile picture update
                header("Location: settings.php?status=success");
                exit();
            } else {
                $error_message = "Error updating profile picture.";
            }
            $stmt_update_pic->close();
        } else {
            $error_message = "Error uploading the profile picture.";
        }
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings</title>
 <!-- Favicon -->
 <link rel="icon" href="icon.png" type="image/png">
  <!-- Tailwind CSS -->
  <link href="dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
  <div class="flex">
    <!-- Sidebar with profile information -->
    <div class="flex flex-col min-h-screen bg-gray-100 p-6 w-64">
      <div class="text-center mb-6">
        <!-- Display profile picture -->
        <img src="<?php echo htmlspecialchars($profilepicture); ?>" alt="User Profile" class="mx-auto w-16 h-16 rounded-full mb-4 object-cover">
        <h4 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($fname . ' ' . $lname); ?></h4>
        <p class="text-gray-500 text-sm"><?php echo htmlspecialchars($email); ?></p>
      </div>
      <hr class="my-4">
      <ul class="space-y-2">
        <li>
          <a class="block px-4 py-2 rounded-lg hover:bg-primary-50 text-gray-700" href="dashboard.php">Dashboard</a>
        </li>
        <li>
          <a class="block px-4 py-2 rounded-lg bg-primary-100 text-primary-700 font-semibold" href="settings.php">Settings</a>
        </li>
        <li>
          <a class="block px-4 py-2 rounded-lg hover:bg-primary-50 text-gray-700" href="logout.php">Logout</a>
        </li>
      </ul>
    </div>

    <!-- Main content for Settings -->
    <div class="flex-1 p-6">
      <div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-8">
        <h2 class="text-2xl font-bold mb-6 text-primary-700">Update Profile</h2>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
          <div class="alert alert-success">Your details have been updated successfully!</div>
        <?php elseif (isset($error_message)): ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- Personal Info Update Form -->
        <form action="settings.php" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="action" value="update_settings">

          <div class="mb-6">
            <label for="fname" class="form-label">First Name</label>
            <input type="text" class="form-control" id="fname" name="fname" value="<?php echo htmlspecialchars($fname); ?>" required>
          </div>

          <div class="mb-6">
            <label for="mname" class="form-label">Middle Name</label>
            <input type="text" class="form-control" id="mname" name="mname" value="<?php echo htmlspecialchars($mname); ?>">
          </div>

          <div class="mb-6">
            <label for="lname" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="lname" name="lname" value="<?php echo htmlspecialchars($lname); ?>" required>
          </div>

          <div class="mb-6">
            <label for="password" class="form-label">New Password (Leave blank to keep current password)</label>
            <input type="password" class="form-control" id="password" name="password">
          </div>

          <div class="mb-6">
            <label for="profilepicture" class="form-label">Profile Picture</label>
            <input type="file" class="form-control" id="profilepicture" name="profilepicture">
          </div>

          <button type="submit" class="btn-primary">Update</button>
        </form>

        <!-- Email Update Form -->
        <div class="mt-12 pt-8 border-t border-gray-200">
          <h3 class="text-xl font-semibold mb-6 text-primary-700">Change Email</h3>
          <form action="settings.php" method="POST">
            <input type="hidden" name="action" value="update_email">
            
            <div class="mb-6">
              <label for="email" class="form-label">New Email Address</label>
              <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>

            <button type="submit" class="btn-primary">Update Email</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>
</html>

