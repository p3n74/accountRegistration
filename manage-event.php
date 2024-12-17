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
$sql_user = "SELECT fname, mname, lname, email, profilepicture FROM user_credentials WHERE uid = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $uid);  // Bind UID to the query
$stmt_user->execute();
$stmt_user->bind_result($fname, $mname, $lname, $email, $profilepicture);  // Bind the result to variables

// Fetch user data first
if ($stmt_user->fetch()) {
    // Use a default image if profile picture is not set
    $profilepicture = $profilepicture ? $profilepicture : 'profilePictures/default.png';
} else {
    // Handle error if no data is found (this block might not be needed in your case)
    die("User not found.");
}

// Close the user details statement after fetching the results
$stmt_user->close();

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

// If the form is submitted to delete the event
if (isset($_POST['delete_event'])) {
    $sql_delete = "DELETE FROM events WHERE eventid = ?";
    if ($stmt_delete = $conn->prepare($sql_delete)) {
        $stmt_delete->bind_param("i", $eventid);
        if ($stmt_delete->execute()) {
            $success_message = "Event deleted successfully.";
        } else {
            $error_message = "Error deleting the event.";
        }
        $stmt_delete->close();
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
  <title>Manage Event</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* Custom CSS for the layout */
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
      gap: 20px;  /* Adds space between the tables */
    }
    .table-container-left,
    .table-container-right {
      width: 48%;  /* Equal width for both tables */
      padding: 20px;
      background-color: #f8f9fa;
      border-radius: 5px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .create-event-btn {
      margin-left: 10px;
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

    <!-- Main content for Manage Event -->
    <div class="col-md-9 col-lg-10 p-3">
      <h2>Manage Event: <?php echo htmlspecialchars($eventname); ?></h2>

      <?php
      if (isset($success_message)) {
          echo "<div class='alert alert-success'>" . htmlspecialchars($success_message) . "</div>";
      }
      if (isset($error_message)) {
          echo "<div class='alert alert-danger'>" . htmlspecialchars($error_message) . "</div>";
      }
      ?>

      <!-- Event Details -->
      <table class="table table-bordered">
        <tr>
          <th>Event Name</th>
          <td><?php echo htmlspecialchars($eventname); ?></td>
        </tr>
        <tr>
          <th>Start Date</th>
          <td><?php echo htmlspecialchars($startdate); ?></td>
        </tr>
        <tr>
          <th>End Date</th>
          <td><?php echo htmlspecialchars($enddate); ?></td>
        </tr>
        <tr>
          <th>Location</th>
          <td><?php echo htmlspecialchars($location); ?></td>
        </tr>
        <tr>
          <th>Event Key</th>
          <td><?php echo htmlspecialchars($eventkey); ?></td>
        </tr>
        <tr>
          <th>Event Link</th>
          <td><a href="<?php echo htmlspecialchars($eventshortinfo); ?>" target="_blank"><?php echo htmlspecialchars($eventshortinfo); ?></a></td>
        </tr>
        <tr>
          <th>Event Badge</th>
          <td><img src="<?php echo htmlspecialchars($eventbadgepath); ?>" alt="Event Badge" class="img-fluid" style="max-width: 200px;"></td>
        </tr>
        <tr>
          <th>Event Info</th>
          <td><a href="<?php echo htmlspecialchars($eventinfopath); ?>" target="_blank">Download Event Info</a></td>
        </tr>
      </table>

      <!-- Button to Edit Event (Redirect to a page to update the event) -->
      <a href="update-event.php?eventid=<?php echo $eventid; ?>" class="btn btn-warning">Edit Event</a>

      <!-- Form to Delete Event -->
      <form method="POST" class="mt-3">
        <button type="submit" name="delete_event" class="btn btn-danger">Delete Event</button>
      </form>
    </div>

  </div>

  <!-- Bootstrap JS and dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
</body>
</html>

