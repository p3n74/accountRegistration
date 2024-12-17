<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    die("You must log in first.");
}

// Include the database connection
require_once 'includes/db.php';

$uid = $_SESSION['uid'];

// Fetch user information
$sql_user = "SELECT fname, lname, email, profilepicture FROM user_credentials WHERE uid = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $uid);
$stmt_user->execute();
$stmt_user->bind_result($fname, $lname, $email, $profilepicture);
$stmt_user->fetch();
$stmt_user->close();

// Check for Event ID
if (isset($_GET['eventid'])) {
    $eventid = $_GET['eventid'];
} else {
    die("Event ID is missing.");
}

// Fetch event details
$sql_event = "SELECT eventname, startdate, enddate, location, eventkey, eventshortinfo, eventbadgepath, eventinfopath 
              FROM events WHERE eventid = ? AND eventcreator = ?";
$stmt_event = $conn->prepare($sql_event);
$stmt_event->bind_param("ii", $eventid, $uid);
$stmt_event->execute();
$stmt_event->bind_result($eventname, $startdate, $enddate, $location, $eventkey, $eventshortinfo, $eventbadgepath, $eventinfopath);
$stmt_event->fetch();
$stmt_event->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventname = $_POST['eventname'];
    $startdate = $_POST['startdate'];
    $enddate = $_POST['enddate'];
    $location = $_POST['location'];
    $eventkey = $_POST['eventkey'];
    $eventshortinfo = $_POST['eventshortinfo'];

    // File uploads
    if (isset($_FILES['eventbadge']) && $_FILES['eventbadge']['error'] === 0) {
        $badge_path = "uploads/badge_" . time() . "_" . basename($_FILES['eventbadge']['name']);
        move_uploaded_file($_FILES['eventbadge']['tmp_name'], $badge_path);
        $eventbadgepath = $badge_path;
    }

    if (isset($_FILES['eventinfo']) && $_FILES['eventinfo']['error'] === 0) {
        $info_path = "uploads/certificate_" . time() . "_" . basename($_FILES['eventinfo']['name']);
        move_uploaded_file($_FILES['eventinfo']['tmp_name'], $info_path);
        $eventinfopath = $info_path;
    }

    // Update database
    $sql_update = "UPDATE events SET eventname = ?, startdate = ?, enddate = ?, location = ?, eventkey = ?, eventshortinfo = ?, eventbadgepath = ?, eventinfopath = ? WHERE eventid = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ssssssssi", $eventname, $startdate, $enddate, $location, $eventkey, $eventshortinfo, $eventbadgepath, $eventinfopath, $eventid);

    if ($stmt_update->execute()) {
        // After a successful update, redirect to avoid resubmitting
        header("Location: update-event.php?eventid=$eventid&status=success");
        exit();
    } else {
        $error_message = "Failed to update event.";
    }
    $stmt_update->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Update Event</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .sidebar {
      min-height: 100vh;
      background-color: #f8f9fa;
      padding-top: 20px;
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
  </style>
</head>
<body>
<div class="d-flex">
  <!-- Sidebar -->
  <div class="sidebar col-md-3 col-lg-2 p-3">
    <div class="text-center">
      <img src="<?php echo htmlspecialchars($profilepicture); ?>" alt="User Profile">
      <h4><?php echo htmlspecialchars($fname . ' ' . $lname); ?></h4>
      <p><?php echo htmlspecialchars($email); ?></p>
    </div>
    <hr>
    <ul class="nav flex-column">
      <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
      <li class="nav-item"><a class="nav-link" href="settings.php">Settings</a></li>
      <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
    </ul>
  </div>

  <!-- Main Content -->
  <div class="col-md-9 col-lg-10 p-3">
    <div class="form-container">
      <h2>Update Event</h2>
      <?php if (isset($_GET['status']) && $_GET['status'] == 'success') {
        echo "<div class='alert alert-success'>Event updated successfully!</div>";
      } ?>
      <?php if (isset($error_message)) echo "<div class='alert alert-danger'>$error_message</div>"; ?>
      
      <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
          <label class="form-label">Event Name</label>
          <input type="text" class="form-control" name="eventname" value="<?php echo htmlspecialchars($eventname); ?>" required>
        </div>
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Start Date</label>
            <input type="datetime-local" class="form-control" name="startdate" value="<?php echo $startdate; ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">End Date</label>
            <input type="datetime-local" class="form-control" name="enddate" value="<?php echo $enddate; ?>" required>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Location</label>
          <input type="text" class="form-control" name="location" value="<?php echo htmlspecialchars($location); ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Event Key</label>
          <input type="text" class="form-control" name="eventkey" value="<?php echo htmlspecialchars($eventkey); ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Event Link</label>
          <input type="text" class="form-control" name="eventshortinfo" value="<?php echo htmlspecialchars($eventshortinfo); ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Event Badge</label>
          <input type="file" class="form-control" name="eventbadge">
        </div>
        <div class="mb-3">
          <label class="form-label">Event Certificate</label>
          <input type="file" class="form-control" name="eventinfo">
        </div>
        <button type="submit" class="btn btn-primary">Update Event</button>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
</body>
</html>

