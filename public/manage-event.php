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
$sql_event = "SELECT eventid, eventname, startdate, enddate, location, eventkey, eventshortinfo, eventinfopath, eventbadgepath 
              FROM events WHERE eventid = ? AND eventcreator = ?";
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

// Count the total number of participants
$participant_count = $result_participants->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Event</title>
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
      <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold text-primary-700">Manage Event: <?php echo htmlspecialchars($eventname); ?></h2>
          <div class="space-x-3">
            <a href="update-event.php?eventid=<?php echo $eventid; ?>" class="btn-secondary">Edit Event</a>
            <form method="POST" action="manage-event.php?eventid=<?php echo $eventid; ?>" class="inline">
              <button type="submit" name="delete_event" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200" onclick="return confirm('Are you sure you want to delete this event?')">Delete Event</button>
            </form>
          </div>
        </div>

        <!-- Event Details -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
          <h3 class="text-xl font-semibold mb-4 text-primary-700">Event Details</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <p class="text-sm font-medium text-gray-600">Event Name</p>
              <p class="text-lg"><?php echo htmlspecialchars($eventname); ?></p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-600">Location</p>
              <p class="text-lg"><?php echo htmlspecialchars($location); ?></p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-600">Start Date</p>
              <p class="text-lg"><?php echo htmlspecialchars($startdate); ?></p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-600">End Date</p>
              <p class="text-lg"><?php echo htmlspecialchars($enddate); ?></p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-600">Event Key</p>
              <p class="text-lg"><?php echo htmlspecialchars($eventkey); ?></p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-600">Event Link</p>
              <p class="text-lg">
                <?php if (!empty($eventshortinfo)): ?>
                  <a href="<?php echo htmlspecialchars($eventshortinfo); ?>" target="_blank" class="text-primary-600 hover:underline">View Event Link</a>
                <?php else: ?>
                  <span class="text-gray-500">No link provided</span>
                <?php endif; ?>
              </p>
            </div>
          </div>
        </div>

        <!-- Participants -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-xl font-semibold mb-4 text-primary-700">Event Participants</h3>
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left border border-gray-200 rounded-lg">
              <thead class="bg-primary-50">
                <tr>
                  <th class="px-4 py-2 font-semibold">Name</th>
                  <th class="px-4 py-2 font-semibold">Email</th>
                  <th class="px-4 py-2 font-semibold">Join Time</th>
                  <th class="px-4 py-2 font-semibold">Leave Time</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($participants)): ?>
                  <tr>
                    <td colspan="4" class="px-4 py-2 text-center text-gray-500">No participants yet.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($participants as $participant): ?>
                    <tr class="border-b">
                      <td class="px-4 py-2"><?php echo htmlspecialchars($participant['fname'] . ' ' . $participant['lname']); ?></td>
                      <td class="px-4 py-2"><?php echo htmlspecialchars($participant['email']); ?></td>
                      <td class="px-4 py-2"><?php echo $participant['join_time'] ? htmlspecialchars($participant['join_time']) : 'Not joined'; ?></td>
                      <td class="px-4 py-2"><?php echo $participant['leave_time'] ? htmlspecialchars($participant['leave_time']) : 'Not left'; ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>

