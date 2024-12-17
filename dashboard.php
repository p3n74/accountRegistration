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

// Close the user details statement to avoid issues with subsequent queries
$stmt_user->close();

// Prepare the SQL query to fetch attended events
$sql_events = "
    SELECT e.eventid, e.eventname, e.startdate, e.enddate, e.location, e.eventinfopath
    FROM user_credentials u
    JOIN events e ON JSON_CONTAINS(u.attendedevents, JSON_ARRAY(e.eventid))
    WHERE u.uid = ?
";

$stmt_events = $conn->prepare($sql_events);
$stmt_events->bind_param("i", $uid);  // Bind UID to the query
$stmt_events->execute();
$result_events = $stmt_events->get_result(); // This should come after execute()

// Fetch the results for attended events
$attendedEvents = [];
while ($row = $result_events->fetch_assoc()) {
    $attendedEvents[] = $row;
}

// **Do not close the statement yet** as we need the result set

// Prepare the SQL query to fetch My Events (events created by the user)
$sql_my_events = "
    SELECT eventid, eventname, startdate, enddate, location
    FROM events
    WHERE eventcreator = ?
";

$stmt_my_events = $conn->prepare($sql_my_events);
$stmt_my_events->bind_param("i", $uid);  // Bind UID to the query
$stmt_my_events->execute();
$result_my_events = $stmt_my_events->get_result(); // Get the result after execution

// Fetch the results for My Events
$myEvents = [];
while ($row = $result_my_events->fetch_assoc()) {
    $myEvents[] = $row;
}

// Close the event statements and DB connection
$stmt_events->close();
$stmt_my_events->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
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
    .table-container {
      padding: 20px;
    }
    .two-columns {
      display: flex;
      justify-content: space-between;
    }
    .table-container-left,
    .table-container-right {
      width: 48%;
    }
    /* Optional styling for the button */
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
          <a class="nav-link active" href="#">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="settings.php">Settings</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="logout.php">Logout</a>
        </li>
      </ul>
    </div>

    <!-- Main content (two columns layout) -->
    <div class="col-md-9 col-lg-10 p-3">
      <h2>Dashboard</h2>

      <!-- Two-column layout for attended events and my events -->
      <div class="two-columns">
        
        <!-- Left Column (Attended Events) -->
        <div class="table-container-left">
          <h3>Attended Events</h3>
          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>#</th>
                <th>Event Name</th>
                <th>Date</th>
                <th>Location</th>
                <th>Details</th>
              </tr>
            </thead>
            <tbody>
              <?php
              // Check if there are any attended events
              if (empty($attendedEvents)) {
                echo "<tr><td colspan='5'>No attended events found.</td></tr>";
              } else {
                // Loop through the attended events array and display them
                $count = 1;
                foreach ($attendedEvents as $event) {
                    echo "<tr>";
                    echo "<td>" . $count++ . "</td>";
                    echo "<td>" . htmlspecialchars($event['eventname']) . "</td>";
                    echo "<td>" . htmlspecialchars($event['startdate']) . "</td>";
                    echo "<td>" . htmlspecialchars($event['location']) . "</td>";
                    echo "<td><a href='" . htmlspecialchars($event['eventinfopath']) . "' target='_blank'>Details</a></td>";
                    echo "</tr>";
                }
              }
              ?>
            </tbody>
          </table>
        </div>

        <!-- Right Column (My Events) -->
        <div class="table-container-right">
          <div class="d-flex justify-content-between align-items-center">
            <h3>My Events</h3>
            <a href="create-event.php" class="btn btn-primary">Create Event</a>
          </div>
          <!-- Table of Events -->
			<table class="table table-striped">
			  <thead>
				<tr>
				  <th scope="col">Event Name</th>
				  <th scope="col">Start Date</th>
				  <th scope="col">End Date</th>
				  <th scope="col">Location</th>
				  <th scope="col">Actions</th>
				</tr>
			  </thead>
			  <tbody>
				<?php while ($stmt_events->fetch()): ?>
				  <tr>
					<td><?php echo htmlspecialchars($eventname); ?></td>
					<td><?php echo htmlspecialchars($startdate); ?></td>
					<td><?php echo htmlspecialchars($enddate); ?></td>
					<td><?php echo htmlspecialchars($location); ?></td>
					<td>
					  <a href="manage_event.php?eventid=<?php echo $eventid; ?>" class="btn btn-primary btn-sm">Manage</a>
					</td>
				  </tr>
				<?php endwhile; ?>
			  </tbody>
			</table>
		</div>

      </div>

    </div>
  </div>

  <!-- Bootstrap JS and dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
</body>
</html>

