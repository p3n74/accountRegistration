<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    die("You must log in first.");
}

// Include database connection
require_once '../includes/db.php';

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
          <a class="block px-4 py-2 rounded-lg hover:bg-primary-50 text-gray-700" href="settings.php">Settings</a>
        </li>
        <li>
          <a class="block px-4 py-2 rounded-lg hover:bg-primary-50 text-gray-700" href="logout.php">Logout</a>
        </li>
      </ul>
    </div>

    <!-- Main content for Update Event form -->
    <div class="flex-1 p-6">
      <div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-8">
        <h2 class="text-2xl font-bold mb-6 text-primary-700">Update Event</h2>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
          <div class="alert alert-success mb-6">Event updated successfully!</div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
          <div class="alert alert-danger mb-6"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form method="POST" action="update-event.php?eventid=<?php echo $eventid; ?>" enctype="multipart/form-data">
          <div class="mb-6">
            <label for="eventname" class="form-label">Event Name</label>
            <input type="text" class="form-control" name="eventname" id="eventname" value="<?php echo htmlspecialchars($eventname); ?>" required>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
              <label for="startdate" class="form-label">Start Date</label>
              <input type="datetime-local" class="form-control" name="startdate" id="startdate" value="<?php echo $startdate; ?>" required>
            </div>
            <div>
              <label for="enddate" class="form-label">End Date</label>
              <input type="datetime-local" class="form-control" name="enddate" id="enddate" value="<?php echo $enddate; ?>" required>
            </div>
          </div>

          <div class="mb-6">
            <label for="location" class="form-label">Location</label>
            <input type="text" class="form-control" name="location" id="location" value="<?php echo htmlspecialchars($location); ?>" required>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
              <label for="eventkey" class="form-label">Event Key</label>
              <input type="text" class="form-control" name="eventkey" id="eventkey" value="<?php echo htmlspecialchars($eventkey); ?>" required>
            </div>
            <div>
              <label for="eventshortinfo" class="form-label">Event Link</label>
              <input type="text" class="form-control" name="eventshortinfo" id="eventshortinfo" value="<?php echo htmlspecialchars($eventshortinfo); ?>">
            </div>
          </div>

          <div class="mb-6">
            <label for="eventbadge" class="form-label">Event Badge (Optional)</label>
            <input type="file" class="form-control" name="eventbadge" id="eventbadge">
          </div>

          <div class="mb-6">
            <label for="eventinfo" class="form-label">Event Certificate (PDF or file)</label>
            <input type="file" class="form-control" name="eventinfo" id="eventinfo">
          </div>

          <button type="submit" class="btn-primary">Update Event</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>

