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
$stmt_user->bind_param("i", $uid);
$stmt_user->execute();
$stmt_user->bind_result($fname, $mname, $lname, $email, $profilepicture);

if ($stmt_user->fetch()) {
    $profilepicture = $profilepicture ? $profilepicture : 'profilePictures/default.png';
} else {
    die("User not found.");
}

$stmt_user->close();

// Get eventid from URL
if (isset($_GET['eventid'])) {
    $eventid = $_GET['eventid'];
} else {
    die("Event ID is missing.");
}

// Query to fetch event details
$sql_event = "SELECT eventid, eventname, startdate, enddate, location, eventkey, eventshortinfo, eventinfopath, eventbadgepath FROM events WHERE eventid = ? AND eventcreator = ?";
$stmt_event = $conn->prepare($sql_event);
$stmt_event->bind_param("ii", $eventid, $uid);
$stmt_event->execute();
$stmt_event->store_result();
$stmt_event->bind_result($eventid, $eventname, $startdate, $enddate, $location, $eventkey, $eventshortinfo, $eventinfopath, $eventbadgepath);

if ($stmt_event->num_rows == 0) {
    die("Event not found or you do not have permission to manage it.");
}

$stmt_event->fetch();

// Query to fetch participants
$sql_participants = "SELECT u.fname, u.lname, u.email, e.join_time, e.leave_time 
                     FROM event_participants e 
                     JOIN user_credentials u ON e.uid = u.uid 
                     WHERE e.eventid = ?";
$stmt_participants = $conn->prepare($sql_participants);
$stmt_participants->bind_param("i", $eventid);
$stmt_participants->execute();
$result_participants = $stmt_participants->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Event</title>
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
      gap: 20px;
    }
    .table-container {
      width: 48%;
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
    <!-- Sidebar -->
    <div class="sidebar col-md-3 col-lg-2 p-3">
      <div class="text-center">
        <img src="<?php echo htmlspecialchars($profilepicture); ?>" alt="User Profile" class="img-fluid">
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
	  <h2>Manage Event: <?php echo htmlspecialchars($eventname); ?></h2>

	  <div class="two-columns d-flex gap-3">
		<!-- Event Details -->
		<div class="table-container">
		  <h4>Event Details</h4>
		  <table class="table table-bordered">
			<tr><th>Name</th><td><?php echo htmlspecialchars($eventname); ?></td></tr>
			<tr><th>Start Date</th><td><?php echo htmlspecialchars($startdate); ?></td></tr>
			<tr><th>End Date</th><td><?php echo htmlspecialchars($enddate); ?></td></tr>
			<tr><th>Location</th><td><?php echo htmlspecialchars($location); ?></td></tr>
			<tr><th>Event Key</th><td><?php echo htmlspecialchars($eventkey); ?></td></tr>
			<tr><th>Event Link</th><td><a href="<?php echo htmlspecialchars($eventshortinfo); ?>" target="_blank">Visit Link</a></td></tr>
		  </table>
		</div>

		<!-- Participants List -->
		<div class="table-container w-100">
		  <h4>Participants</h4>
		  <table class="table table-striped">
			<thead>
			  <tr>
				<th>Name</th>
				<th>Email</th>
				<th>Join Time</th>
				<th>Leave Time</th>
			  </tr>
			</thead>
			<tbody>
			  <?php while ($row = $result_participants->fetch_assoc()): ?>
				<tr>
				  <td><?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?></td>
				  <td><?php echo htmlspecialchars($row['email']); ?></td>
				  <td><?php echo htmlspecialchars($row['join_time']); ?></td>
				  <td><?php echo htmlspecialchars($row['leave_time'] ?? 'N/A'); ?></td>
				</tr>
			  <?php endwhile; ?>
			</tbody>
		  </table>

		  <!-- Download Button Under the Table -->
		</div>
		 <div class="mt-3 text-center">
			<form action="generate-participants-pdf.php" method="GET" target="_blank">
			  <input type="hidden" name="eventid" value="<?php echo htmlspecialchars($eventid); ?>">
			  <button type="submit" class="btn btn-primary">
				Download Participants List as PDF
			  </button>
			</form>
		  </div>
		</div>


	  <!-- Action Buttons (Edit and Delete) -->
	  <div class="mt-4">
		<a href="update-event.php?eventid=<?php echo $eventid; ?>" class="btn btn-warning btn-custom">Edit Event</a>
		<form method="POST" class="d-inline">
		  <button type="submit" name="delete_event" class="btn btn-danger btn-custom">Delete Event</button>
		</form>
	  </div>
	</div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
</body>
</html>

