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
$stmt_user->fetch();  // Fetch the data into the variables

// Use a default image if profile picture is not set
$profilepicture = $profilepicture ? $profilepicture : 'profilePictures/default.png';

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
$result_events = $stmt_events->get_result();

// Fetch the results for attended events
$attendedEvents = [];
while ($row = $result_events->fetch_assoc()) {
    $attendedEvents[] = $row;
}

// Close the events statement and DB connection
$stmt_events->close();
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
          <a class="block px-4 py-2 rounded-lg bg-primary-100 text-primary-700 font-semibold" href="#">Dashboard</a>
        </li>
        <li>
          <a class="block px-4 py-2 rounded-lg hover:bg-primary-50 text-gray-700" href="#">Settings</a>
        </li>
        <li>
          <a class="block px-4 py-2 rounded-lg hover:bg-primary-50 text-gray-700" href="logout.php">Logout</a>
        </li>
      </ul>
    </div>

    <!-- Main content -->
    <div class="flex-1 p-6">
      <h2 class="text-2xl font-bold mb-6 text-primary-700">Event Creator Dashboard</h2>

      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-xl font-semibold mb-4 text-primary-700">Attended Events</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left border border-gray-200 rounded-lg">
            <thead class="bg-primary-50">
              <tr>
                <th class="px-4 py-2 font-semibold">Event Name</th>
                <th class="px-4 py-2 font-semibold">Start Date</th>
                <th class="px-4 py-2 font-semibold">End Date</th>
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
                // Loop through the attended events array and display them
                foreach ($attendedEvents as $event) {
                    echo "<tr class='border-b'>";
                    echo "<td class='px-4 py-2'>" . htmlspecialchars($event['eventname']) . "</td>";
                    echo "<td class='px-4 py-2'>" . htmlspecialchars($event['startdate']) . "</td>";
                    echo "<td class='px-4 py-2'>" . htmlspecialchars($event['enddate']) . "</td>";
                    echo "<td class='px-4 py-2'>" . htmlspecialchars($event['location']) . "</td>";
                    echo "<td class='px-4 py-2'>";
                    if (!empty($event['eventinfopath'])) {
                        echo "<a href='" . htmlspecialchars($event['eventinfopath']) . "' target='_blank' class='text-primary-600 hover:underline'>View Details</a>";
                    } else {
                        echo "<span class='text-gray-500'>No details available</span>";
                    }
                    echo "</td>";
                    echo "</tr>";
                }
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</body>
</html>

