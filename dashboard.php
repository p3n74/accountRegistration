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

// Prepare the SQL query to fetch attended events
$sql_events = "
WITH user_events AS (
    SELECT
        JSON_UNQUOTE(JSON_EXTRACT(attendedevents, CONCAT('$[', n.n, ']'))) AS eventid
    FROM user_credentials u
    JOIN (
        SELECT 0 AS n UNION ALL SELECT 1 UNION ALL SELECT 2 
        UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 
        UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 
        UNION ALL SELECT 9
    ) AS n
    WHERE u.uid = ?
    AND JSON_UNQUOTE(JSON_EXTRACT(u.attendedevents, CONCAT('$[', n.n, ']'))) IS NOT NULL
)
SELECT e.eventid, e.eventname, e.startdate, e.enddate, e.location, e.eventshortinfo
FROM events e
JOIN user_events ue ON e.eventid = ue.eventid;
";

$stmt_events = $conn->prepare($sql_events);
$stmt_events->bind_param("i", $uid);  // Bind UID to the query
$stmt_events->execute();
$result_events = $stmt_events->get_result(); // Get result set

// Fetch the results for attended events
$attendedEvents = [];
while ($row = $result_events->fetch_assoc()) {
    $attendedEvents[] = $row;
}

// Close the result set and statement for attended events
$result_events->free();  // Free the result set
$stmt_events->close();   // Close the statement

// Prepare the SQL query to fetch My Events (events created by the user)
$sql_my_events = "
    SELECT eventid, eventname, startdate, enddate, location
    FROM events
	WHERE eventcreator = ?
	order by eventid desc;
";

$stmt_my_events = $conn->prepare($sql_my_events);
$stmt_my_events->bind_param("i", $uid);  // Bind UID to the query
$stmt_my_events->execute();
$result_my_events = $stmt_my_events->get_result(); // Get result set

// Fetch the results for My Events
$myEvents = [];
while ($row = $result_my_events->fetch_assoc()) {
    $myEvents[] = $row;
}

// Close the result set and statement for my events
$result_my_events->free();  // Free the result set
$stmt_my_events->close();   // Close the statement

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
 <!-- Favicon -->
 <link rel="icon" href="icon.png" type="image/png">
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
    h3 {
      color: #007BFF;
    }
    .btn-primary {
      background-color: #007BFF;
      border-color: #007BFF;
    }
    .btn-primary:hover {
      background-color: #0056b3;
      border-color: #004085;
    }
    .alert {
      padding: 15px;
      border-radius: 5px;
    }
    .alert-info {
      background-color: #d1ecf1;
      border-color: #bee5eb;
      color: #0c5460;
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
          <div class="d-flex justify-content-between align-items-center">
            <h3>Attended Events</h3>
            <a href="badges.php" class="btn btn-primary">View Collected Badges</a>
          </div>
 
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
                    echo "<td><a href='" . htmlspecialchars($event['eventshortinfo']) . "' target='_blank'>Details</a></td>";
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
              <?php foreach ($myEvents as $event): ?>
                <tr>
                  <td><?php echo htmlspecialchars($event['eventname']); ?></td>
                  <td><?php echo htmlspecialchars($event['startdate']); ?></td>
                  <td><?php echo htmlspecialchars($event['enddate']); ?></td>
                  <td><?php echo htmlspecialchars($event['location']); ?></td>
                  <td>
                    <a href="manage-event.php?eventid=<?php echo $event['eventid']; ?>" class="btn btn-primary btn-sm">Manage</a>
                  </td>
                </tr>
              <?php endforeach; ?>
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

