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
    die("User not found.");
}

// Close the user details statement after fetching the results
$stmt_user->close();

// Prepare the SQL query to fetch attended events with badge image URL
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
SELECT e.eventid, e.eventname, e.startdate, e.enddate, e.location, e.eventshortinfo, e.eventbadgepath
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

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Badges</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container">
    <h2>Badges</h2>

    <!-- Display user information -->
    <div class="user-info">
      <p>Name: <?php echo htmlspecialchars($fname . ' ' . $lname); ?></p>
      <p>Email: <?php echo htmlspecialchars($email); ?></p>
    </div>

    <!-- Display attended events and their badges -->
    <h3>Attended Events</h3>
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>#</th>
          <th>Event Name</th>
          <th>Date</th>
          <th>Location</th>
          <th>Badge</th>
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
                echo "<td>";
                // Check if event badge exists and display the image
                if (!empty($event['eventbadgepath'])) {
                    echo "<img src='" . htmlspecialchars($event['eventbadgepath']) . "' alt='Event Badge' width='50' height='50'>";
                } else {
                    echo "No Badge Available";
                }
                echo "</td>";
                echo "</tr>";
            }
        }
        ?>
      </tbody>
    </table>
  </div>

  <!-- Bootstrap JS and dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
</body>
</html>

