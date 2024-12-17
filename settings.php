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
            $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = '21102134@usc.edu.ph'; // Replace with your Gmail email
            $mail->Password = $apikey; // API Key from 'includes/apikey.php'
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('211021342@usc.edu.ph', 'Nikolai'); // Replace with your sender name and email
            $mail->addAddress($email, $fname);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Confirm Your Account';
            $confirmationLink = "http://accounts.dcism.org/accountRegistration/confirm.php?token=$token"; // Replace with your domain URL
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
        <!-- Personal Info Update Form -->
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

      <!-- Email Update Form -->
      <div class="form-container">
        <h3>Change Email</h3>
        <form action="settings.php" method="POST">
          <input type="hidden" name="action" value="update_email">
          
          <div class="mb-3">
            <label for="email" class="form-label">New Email Address</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
          </div>

          <button type="submit" class="btn btn-primary w-100">Update Email</button>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
</body>
</html>

