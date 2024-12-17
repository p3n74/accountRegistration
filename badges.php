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
    .badge-card {
      margin-bottom: 20px;
      margin-right: 20px;
    }
    .badge-img {
      width: 100%;
      height: 500px; /* Set the height to 500px */
      object-fit: contain; /* Ensure the image fits within the 500x500 space */
      border-bottom: 1px solid #ddd;
    }
    .badge-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
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
          <a class="nav-link active" href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="settings.php">Settings</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="logout.php">Logout</a>
        </li>
      </ul>
    </div>

    <!-- Main content (Badges) -->
    <div class="col-md-9 col-lg-10 p-3">
      <h2>Badges</h2>

      <!-- Display attended events as cards in two columns -->
      <div class="badge-container">
        <?php
        // Check if there are any attended events
        if (empty($attendedEvents)) {
            echo "<p>No attended events found.</p>";
        } else {
            // Loop through the attended events array and display them as cards
            foreach ($attendedEvents as $event) {
                echo '<div class="col-md-6 col-lg-4 badge-card">';
                echo '<div class="card">';

                // Display badge image (if available)
                if (!empty($event['eventbadgepath'])) {
                    echo '<img src="' . htmlspecialchars($event['eventbadgepath']) . '" class="card-img-top badge-img" alt="Event Badge">';
                } else {
                    echo '<div class="card-img-top badge-img bg-light d-flex justify-content-center align-items-center">
                            <span>No Badge</span>
                          </div>';
                }

                // Display event name in the card body
                echo '<div class="card-body">';
                echo '<h5 class="card-title">' . htmlspecialchars($event['eventname']) . '</h5>';
                echo '</div>';

                echo '</div>';
                echo '</div>';
            }
        }
        ?>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS and dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
</body>
</html>

