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

// Query to fetch attended events
$sql_events = "
    SELECT e.eventid, e.eventname, e.startdate, e.enddate, e.location, e.eventshortinfo
    FROM events e 
    WHERE e.eventid IN (
        SELECT JSON_UNQUOTE(JSON_EXTRACT(attendedevents, CONCAT('$[', n.n, ']')))
        FROM user_credentials, 
             (SELECT 0 AS n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 
              UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) AS n
        WHERE uid = ?
    );
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

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Event Badges</title>
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
    .badge-container {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      margin-top: 30px;
    }
    .badge {
      font-size: 18px;
      padding: 10px 20px;
      background-color: #007bff;
      color: white;
      border-radius: 25px;
      text-transform: capitalize;
      font-weight: bold;
    }
    .badge:hover {
      background-color: #0056b3;
      cursor: pointer;
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

    <!-- Main content (badge layout) -->
    <div class="col-md-9 col-lg-10 p-3">
      <h2>My Event Badges</h2>

      <!-- Event Badges Section -->
      <div class="badge-container">
        <?php
        // Check if there are any attended events
        if (empty($attendedEvents)) {
            echo "<p>No attended events found.</p>";
        } else {
            // Loop through the attended events array and display each as a badge
            foreach ($attendedEvents as $event) {
                echo "<div class='badge'>";
                echo htmlspecialchars($event['eventname']);
                echo "</div>";
            }
        }
        ?>
      </div>

	</div>
	</body>
</html>

