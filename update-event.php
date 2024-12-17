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
    $eventbadgepath = $_POST['eventbadgepath'];

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
    /* Custom CSS for layout and table */
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
        <!-- Display profile picture -->
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
            <label for="eventinfopath" class="form-label">Event Info Link</label>
            <input type="url" class="form-control" id="eventinfopath" name="eventinfopath" value="<?php echo htmlspecialchars($eventinfopath); ?>" required>
          </div>
          <div class="mb-3">
            <label for="eventbadgepath" class="form-label">Event Badge (Image URL)</label>
            <input type="text" class="form-control" id="eventbadgepath" name="eventbadgepath" value="<?php echo htmlspecialchars($eventbadgepath); ?>" required>
          </div>
          <button type="submit" name="update_event" class="btn btn-primary btn-custom">Update Event</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS and dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
</body>
</html>

