<?php
// Start the session
session_start();

// Check if user is logged in by checking session for UID
if (!isset($_SESSION['uid'])) {
    die("You must log in first.");
}

// Include database connection
require_once '../includes/db.php';

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

    <!-- Main content -->
    <div class="flex-1 p-6">
      <h2 class="text-2xl font-bold mb-6 text-primary-700">Badges</h2>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php
        // Check if there are any attended events
        if (empty($attendedEvents)) {
            echo "<div class='col-span-full text-center text-gray-500 py-8'>No badges found. Attend events to collect badges!</div>";
        } else {
            // Loop through the attended events array and display them
            foreach ($attendedEvents as $event) {
                echo '<div class="bg-white rounded-lg shadow-md overflow-hidden">';
                
                // Display badge image if available
                if (!empty($event['eventbadgepath']) && $event['eventbadgepath'] != 'eventbadges/default.png') {
                    echo '<img src="' . htmlspecialchars($event['eventbadgepath']) . '" class="w-full h-48 object-cover" alt="Event Badge">';
                } else {
                    echo '<div class="w-full h-48 bg-gray-200 flex justify-center items-center">
                            <span class="text-gray-500 text-lg">No Badge Available</span>
                          </div>';
                }
                
                echo '<div class="p-4">';
                echo '<h5 class="text-lg font-semibold mb-2">' . htmlspecialchars($event['eventname']) . '</h5>';
                echo '<p class="text-sm text-gray-600 mb-2">' . htmlspecialchars($event['startdate']) . '</p>';
                echo '<p class="text-sm text-gray-600 mb-3">' . htmlspecialchars($event['location']) . '</p>';
                
                if (!empty($event['eventshortinfo'])) {
                    echo '<a href="' . htmlspecialchars($event['eventshortinfo']) . '" target="_blank" class="text-primary-600 hover:text-primary-700 hover:underline text-sm">View Event Details</a>';
                }
                
                echo '</div>';
                echo '</div>';
            }
        }
        ?>
      </div>
    </div>
  </div>
</body>
</html>

