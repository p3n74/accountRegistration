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
  <!-- Tailwind CSS -->
  <link href="dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
  <div class="flex">
    <!-- Sidebar with profile information -->
    <div class="sidebar flex flex-col min-h-screen bg-gray-100 p-6 w-64">
      <div class="text-center mb-6">
        <!-- Display profile picture -->
        <img src="<?php echo htmlspecialchars($profilepicture); ?>" alt="User Profile" class="mx-auto w-16 h-16 rounded-full mb-4 object-cover">
        <h4 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($fname . ' ' . $lname); ?></h4>
        <p class="text-gray-500 text-sm"><?php echo htmlspecialchars($email); ?></p>
      </div>
      <hr class="my-4">
      <ul class="space-y-2">
        <li>
          <a class="block px-4 py-2 rounded-lg bg-primary-100 text-primary-700 font-semibold" href="#">Dashboard</a>
        </li>
        <li>
          <a class="block px-4 py-2 rounded-lg hover:bg-primary-50 text-gray-700" href="settings.php">Settings</a>
        </li>
        <li>
          <a class="block px-4 py-2 rounded-lg hover:bg-primary-50 text-gray-700" href="logout.php">Logout</a>
        </li>
      </ul>
    </div>

    <!-- Main content (two columns layout) -->
    <div class="flex-1 p-6">
      <h2 class="text-2xl font-bold mb-6 text-primary-700">Dashboard</h2>

      <!-- Two-column layout for attended events and my events -->
      <div class="flex flex-col md:flex-row gap-6">
        <!-- Left Column (Attended Events) -->
        <div class="flex-1 bg-white rounded-lg shadow p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-primary-700">Attended Events</h3>
            <a href="badges.php" class="btn-primary">View Collected Badges</a>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left border border-gray-200 rounded-lg">
              <thead class="bg-primary-50">
                <tr>
                  <th class="px-4 py-2 font-semibold">#</th>
                  <th class="px-4 py-2 font-semibold">Event Name</th>
                  <th class="px-4 py-2 font-semibold">Date</th>
                  <th class="px-4 py-2 font-semibold">Location</th>
                  <th class="px-4 py-2 font-semibold">Details</th>
                </tr>
              </thead>
              <tbody>
                <?php
                // Check if there are any attended events
                if (empty($attendedEvents)) {
                  echo "<tr><td colspan='5' class='px-4 py-2 text-center text-gray-500'>No attended events found.</td></tr>";
                } else {
                  $count = 1;
                  foreach ($attendedEvents as $event) {
                      echo "<tr class='border-b'>";
                      echo "<td class='px-4 py-2'>" . $count++ . "</td>";
                      echo "<td class='px-4 py-2'>" . htmlspecialchars($event['eventname']) . "</td>";
                      echo "<td class='px-4 py-2'>" . htmlspecialchars($event['startdate']) . "</td>";
                      echo "<td class='px-4 py-2'>" . htmlspecialchars($event['location']) . "</td>";
                      echo "<td class='px-4 py-2'><a href='" . htmlspecialchars($event['eventshortinfo']) . "' target='_blank' class='text-primary-600 hover:underline'>Details</a></td>";
                      echo "</tr>";
                  }
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Right Column (My Events) -->
        <div class="flex-1 bg-white rounded-lg shadow p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-primary-700">My Events</h3>
            <a href="create-event.php" class="btn-primary">Create Event</a>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left border border-gray-200 rounded-lg">
              <thead class="bg-primary-50">
                <tr>
                  <th class="px-4 py-2 font-semibold">Event Name</th>
                  <th class="px-4 py-2 font-semibold">Start Date</th>
                  <th class="px-4 py-2 font-semibold">End Date</th>
                  <th class="px-4 py-2 font-semibold">Location</th>
                  <th class="px-4 py-2 font-semibold">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($myEvents as $event): ?>
                  <tr class="border-b">
                    <td class="px-4 py-2"><?php echo htmlspecialchars($event['eventname']); ?></td>
                    <td class="px-4 py-2"><?php echo htmlspecialchars($event['startdate']); ?></td>
                    <td class="px-4 py-2"><?php echo htmlspecialchars($event['enddate']); ?></td>
                    <td class="px-4 py-2"><?php echo htmlspecialchars($event['location']); ?></td>
                    <td class="px-4 py-2">
                      <a href="manage-event.php?eventid=<?php echo $event['eventid']; ?>" class="btn-primary btn-sm">Manage</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>

