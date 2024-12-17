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

// Fetch user profile details (fname, lname, email)
$sql_user = "SELECT fname, lname, email FROM user_credentials WHERE uid = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $uid);
$stmt_user->execute();
$stmt_user->store_result();
$stmt_user->bind_result($fname, $lname, $email);
$stmt_user->fetch();

// Get eventid from URL
if (isset($_GET['eventid'])) {
    $eventid = $_GET['eventid'];
} else {
    die("Event ID is missing.");
}

// Query to fetch event details for the specified eventid
$sql_event = "SELECT eventid, eventname, startdate, enddate, location, eventkey, eventshortinfo, eventinfopath, eventbadgepath FROM events WHERE eventid = ? AND eventcreator = ?";
$stmt_event = $conn->prepare($sql_event);
$stmt_event->bind_param("ii", $eventid, $uid);
$stmt_event->execute();
$stmt_event->store_result();
$stmt_event->bind_result($eventid, $eventname, $startdate, $enddate, $location, $eventkey, $eventshortinfo, $eventinfopath, $eventbadgepath);

// Check if event exists
if ($stmt_event->num_rows == 0) {
    die("Event not found or you do not have permission to manage it.");
}

$stmt_event->fetch();  // Fetch event details

// Handle form submission to update event
if (isset($_POST['update_event'])) {
    $eventname = $_POST['eventname'];
    $startdate = $_POST['startdate'];
    $enddate = $_POST['enddate'];
    $location = $_POST['location'];
    $eventkey = $_POST['eventkey'];
    $eventshortinfo = $_POST['eventshortinfo'];
    $eventinfopath = $_POST['eventinfopath'];

    // Handle file upload for event badge
    if (isset($_FILES['eventbadgepath']) && $_FILES['eventbadgepath']['error'] == 0) {
        $badge_tmp_name = $_FILES['eventbadgepath']['tmp_name'];
        $badge_name = $_FILES['eventbadgepath']['name'];
        $badge_ext = pathinfo($badge_name, PATHINFO_EXTENSION);
        $badge_new_name = "badge_" . time() . "." . $badge_ext;
        $badge_upload_dir = 'uploads/';
        
        if (move_uploaded_file($badge_tmp_name, $badge_upload_dir . $badge_new_name)) {
            $eventbadgepath = $badge_upload_dir . $badge_new_name;
        } else {
            $error_message = "Error uploading the event badge.";
        }
    }

    // Handle file upload for event certificate
    if (isset($_FILES['eventinfopath']) && $_FILES['eventinfopath']['error'] == 0) {
        $cert_tmp_name = $_FILES['eventinfopath']['tmp_name'];
        $cert_name = $_FILES['eventinfopath']['name'];
        $cert_ext = pathinfo($cert_name, PATHINFO_EXTENSION);
        $cert_new_name = "certificate_" . time() . "." . $cert_ext;
        $cert_upload_dir = 'uploads/';
        
        if (move_uploaded_file($cert_tmp_name, $cert_upload_dir . $cert_new_name)) {
            $eventinfopath = $cert_upload_dir . $cert_new_name;
        } else {
            $error_message = "Error uploading the event certificate.";
        }
    }

    // Prepare update query
    $sql_update = "UPDATE events SET eventname = ?, startdate = ?, enddate = ?, location = ?, eventkey = ?, eventshortinfo = ?, eventinfopath = ?, eventbadgepath = ? WHERE eventid = ?";
    if ($stmt_update = $conn->prepare($sql_update)) {
        $stmt_update->bind_param("ssssssssi", $eventname, $startdate, $enddate, $location, $eventkey, $eventshortinfo, $eventinfopath, $eventbadgepath, $eventid);
        if ($stmt_update->execute()) {
            $success_message = "Event updated successfully.";
        } else {
            $error_message = "Error updating the event.";
        }
        $stmt_update->close();
    }
}

// Handle file deletion
if (isset($_POST['delete_files'])) {
    // Delete the event badge
    if (!empty($eventbadgepath) && file_exists($eventbadgepath)) {
        unlink($eventbadgepath);
    }

    // Delete the event certificate
    if (!empty($eventinfopath) && file_exists($eventinfopath)) {
        unlink($eventinfopath);
    }

    // Update the database to remove file paths
    $sql_delete_files = "UPDATE events SET eventbadgepath = '', eventinfopath = '' WHERE eventid = ?";
    if ($stmt_delete_files = $conn->prepare($sql_delete_files)) {
        $stmt_delete_files->bind_param("i", $eventid);
        $stmt_delete_files->execute();
        $success_message = "Files deleted successfully.";
    }
}

// Close DB connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Update Event</title>
  <!-- Bootstrap CSS -->
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
    .two-columns {
      display: flex;
      justify-content: space-between;
      gap: 20px;
    }
    .form-container {
      background-color: #f8f9fa;
      padding: 20px;
      border-radius: 5px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .btn-custom {
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <div class="d-flex">
    <!-- Sidebar with profile information -->
    <div class="sidebar col-md-3 col-lg-2 p-3">
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
          <a class="nav-link" href="settings.php">Settings</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="logout.php">Logout</a>
        </li>
      </ul>
    </div>

    <!-- Main content for Update Event -->
    <div class="col-md-9 col-lg-10 p-3">
      <h2 class="mb-4">Update Event: <?php echo htmlspecialchars($eventname); ?></h2>

      <?php
      if (isset($success_message)) {
          echo "<div class='alert alert-success'>" . htmlspecialchars($success_message) . "</div>";
      }
      if (isset($error_message)) {
          echo "<div class='alert alert-danger'>" . htmlspecialchars($error_message) . "</div>";
      }
      ?>

      <!-- Event Update Form -->
      <div class="form-container">
        <form method="POST" enctype="multipart/form-data">
          <div class="mb-3">
            <label for="eventname" class="form-label">Event Name</label>
            <input type="text" class="form-control" id="eventname" name="eventname" value="<?php echo htmlspecialchars($eventname); ?>" required>
          </div>
          <div class="mb-3">
            <label for="startdate" class="form-label">Start Date</label>
            <input type="datetime-local" class="form-control" id="startdate" name="startdate" value="<?php echo htmlspecialchars($startdate); ?>" required>
          </div>
          <div class="mb-3">
            <label for="enddate" class="form-label">End Date</label>
            <input type="datetime-local" class="form-control" id="enddate" name="enddate" value="<?php echo htmlspecialchars($enddate); ?>" required>
          </div>
          <div class="mb-3">
            <label for="location" class="form-label">Location</label>
            <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($location); ?>" required>
          </div>
          <div class="mb-3">
            <label for="eventkey" class="form-label">Event Key</label>
            <input type="text" class="form-control" id="eventkey" name="eventkey" value="<?php echo htmlspecialchars($eventkey); ?>" required>
          </div>
          <div class="mb-3">
            <label for="eventshortinfo" class="form-label">Event Link</label>
            <input type="url" class="form-control" id="eventshortinfo" name="eventshortinfo" value="<?php echo htmlspecialchars($eventshortinfo); ?>" required>
          </div>
          <div class="mb-3">
            <label for="eventinfopath" class="form-label">Event Certificate (Upload)</label>
            <input type="file" class="form-control" id="eventinfopath" name="eventinfopath">
          </div>
          <div class="mb-3">
            <label for="eventbadgepath" class="form-label">Event Badge (Upload)</label>
            <input type="file" class="form-control" id="eventbadgepath" name="eventbadgepath">
          </div>
          
          <button type="submit" name="update_event" class="btn btn-primary btn-custom">Update Event</button>
        </form>
        
        <!-- Delete Files Form -->
        <form method="POST" class="mt-3">
          <button type="submit" name="delete_files" class="btn btn-danger btn-custom">Delete Event Badge and Certificate</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS and dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
</body>
</html>

