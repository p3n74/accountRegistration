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

// Prepare the SQL query to fetch attended events
$sql = "
    SELECT e.eventid, e.eventname, e.startdate, e.enddate, e.location, e.eventinfopath
    FROM user_credentials u
    JOIN events e ON JSON_CONTAINS(u.attendedevents, JSON_ARRAY(e.eventid))
    WHERE u.uid = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $uid);  // Bind UID to the query
$stmt->execute();
$result = $stmt->get_result();

// Fetch the results
$attendedEvents = [];
while ($row = $result->fetch_assoc()) {
    $attendedEvents[] = $row;
}

// Close the statement and DB connection
$stmt->close();
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
          <a class="nav-link" href="#">Settings</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Logout</a>
        </li>
      </ul>
    </div>

    <!-- Main content (table of attended events) -->
    <div class="col-md-9 col-lg-10 p-3 table-container">
      <h2>Attended Events</h2>
      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>Event Name</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Location</th>
            <th>Details</th>
          </tr>
        </thead>
        <tbody>
          <?php
          // Check if there are any attended events
          if (empty($attendedEvents)) {
            echo "<tr><td colspan='6'>No attended events found.</td></tr>";
          } else {
            // Loop through the attended events array and display them
            $count = 1;
            foreach ($attendedEvents as $event) {
                echo "<tr>";
                echo "<td>" . $count++ . "</td>";
                echo "<td>" . htmlspecialchars($event['eventname']) . "</td>";
                echo "<td>" . htmlspecialchars($event['startdate']) . "</td>";
                echo "<td>" . htmlspecialchars($event['enddate']) . "</td>";
                echo "<td>" . htmlspecialchars($event['location']) . "</td>";
                echo "<td><a href='" . htmlspecialchars($event['eventinfopath']) . "' target='_blank'>Details</a></td>";
                echo "</tr>";
            }
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Bootstrap JS and dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
</body>
</html>

