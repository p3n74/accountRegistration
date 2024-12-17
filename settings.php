<?php
// Start the session
session_start();

// Check if user is logged in by checking session for UID
if (!isset($_SESSION['uid'])) {
    die("You must log in first.");
}

// Include database connection
require_once 'includes/db.php';

// Retrieve UID from session
$uid = $_SESSION['uid'];

// Query to fetch user details (name, email, profile picture) based on UID
$sql_user = "SELECT fname, mname, lname, email, profilepicture, verification_code FROM user_credentials WHERE uid = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $uid);  // Bind UID to the query
$stmt_user->execute();
$stmt_user->store_result();  // Store the result set to avoid "Commands out of sync" error
$stmt_user->bind_result($fname, $mname, $lname, $email, $profilepicture, $verification_code);  // Bind the result to variables
$stmt_user->fetch();  // Fetch the data into the variables

// Use a default image if profile picture is not set
$profilepicture = $profilepicture ? $profilepicture : 'profilePictures/default.png';

// Handle the form submission for profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] == 'update_settings') {
    $new_fname = $_POST['fname'];
    $new_mname = $_POST['mname'];
    $new_lname = $_POST['lname'];
    $new_email = $_POST['email'];
    $new_password = $_POST['password'];

    // Validate email
    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        // Check if new email is different from the current one
        if ($new_email != $email) {
            // Generate a new verification code
            $verification_code = bin2hex(random_bytes(16)); // Generate a 32-character random code
            
            // Update the database with the new email and verification code
            $sql_update = "UPDATE user_credentials SET fname = ?, mname = ?, lname = ?, new_email = ?, verification_code = ? WHERE uid = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("sssssi", $new_fname, $new_mname, $new_lname, $new_email, $verification_code, $uid);
            $stmt_update->execute();

            // Check if the update was successful
            if ($stmt_update->affected_rows > 0) {
                // Send verification email (this part requires a function for email sending, e.g., using PHP mail or PHPMailer)
                $verification_link = "http://yourdomain.com/verify-email.php?code=" . urlencode($verification_code);
                $subject = "Email Verification";
                $message = "Please click the following link to verify your new email address: $verification_link";
                $headers = "From: no-reply@yourdomain.com";
                
                // Send the email (ensure your mail configuration is correct)
                if (mail($new_email, $subject, $message, $headers)) {
                    echo "<p>A verification email has been sent to your new email address. Please check your inbox.</p>";
                } else {
                    $error_message = "Failed to send verification email.";
                }
            } else {
                $error_message = "Failed to update your details.";
            }
        } else {
            // If the new email is the same as the old one, just update the other fields
            $sql_update = "UPDATE user_credentials SET fname = ?, mname = ?, lname = ? WHERE uid = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("sssi", $new_fname, $new_mname, $new_lname, $uid);
            $stmt_update->execute();
            if ($stmt_update->affected_rows > 0) {
                echo "<p>Your details have been updated successfully!</p>";
            } else {
                $error_message = "No changes were made or an error occurred.";
            }
        }
    }

    $stmt_update->close();
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
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .sidebar {
      min-height: 100vh;
      background-color: #f8f9fa;
      padding-top: 20px;
      position: sticky;
      top: 0;
    }
    .sidebar img {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      margin-bottom: 20px;
    }
    .form-container {
      padding: 30px;
      margin: 30px auto;
      max-width: 800px;
      background-color: #f8f9fa;
      border-radius: 8px;
    }
    .main-content {
      padding: 30px;
      background-color: #f1f1f1;
      width: 100%;
    }
    .d-flex {
      display: flex;
    }
    .sidebar-col {
      flex: 0 0 250px;
    }
    .main-col {
      flex: 1;
      padding-left: 30px;
    }
  </style>
</head>
<body>
  <div class="d-flex">
    <div class="sidebar sidebar-col col-md-3 col-lg-2 p-3">
      <div class="text-center">
        <img src="<?php echo htmlspecialchars($profilepicture); ?>" alt="User Profile" class="img-fluid">
        <h4><?php echo htmlspecialchars($fname . ' ' . $lname); ?></h4>
        <p><?php echo htmlspecialchars($email); ?></p>
      </div>
      <hr>
      <ul class="nav flex-column">
        <li class="nav-item">
          <a class="nav-link" href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="settings.php">Settings</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="logout.php">Logout</a>
        </li>
      </ul>
    </div>

    <div class="main-col col-md-9 col-lg-10">
      <h2>Settings</h2>

      <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        <div class="alert alert-success">Your details have been updated successfully!</div>
      <?php elseif (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
      <?php endif; ?>

      <div class="form-container">
        <form action="settings.php" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="action" value="update_settings">
          <div class="mb-3">
            <label for="fname" class="form-label">First Name</label>
            <input type="text" class="form-control" id="fname" name="fname" value="<?php echo htmlspecialchars($fname); ?>" required>
          </div>

          <div class="mb-3">
            <label for="mname" class="form-label">Middle Name</label>
            <input type="text" class="form-control" id="mname" name="mname" value="<?php echo htmlspecialchars($mname); ?>">
          </div>

          <div class="mb-3">
            <label for="lname" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="lname" name="lname" value="<?php echo htmlspecialchars($lname); ?>" required>
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">New Password (Leave blank to keep current password)</label>
            <input type="password" class="form-control" id="password" name="password">
          </div>

          <div class="mb-3">
            <label for="profilepicture" class="form-label">Profile Picture</label>
            <input type="file" class="form-control" id="profilepicture" name="profilepicture">
          </div>

          <button type="submit" class="btn btn-primary w-100">Update</button>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
</body>
</html>

