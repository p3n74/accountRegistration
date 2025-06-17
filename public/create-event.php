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

// If the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and collect form data
    $eventname = $_POST['eventname'];
    $startdate = $_POST['startdate'];
    $enddate = $_POST['enddate'];
    $location = $_POST['location'];
    $eventkey = $_POST['eventkey'];
    $eventshortinfo = $_POST['eventshortinfo'];

    // Event Badge Upload
    $eventbadgepath = 'eventbadges/default.png';  // Default badge if none uploaded
    if (isset($_FILES['eventbadge']) && $_FILES['eventbadge']['error'] == 0) {
        $file_tmp = $_FILES['eventbadge']['tmp_name'];
        $file_name = $_FILES['eventbadge']['name'];
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $file_new_name = uniqid() . '.' . $file_extension;
        $file_path = 'eventbadges/' . $file_new_name;

        // Move the uploaded badge image to the eventbadges folder
        if (move_uploaded_file($file_tmp, $file_path)) {
            $eventbadgepath = $file_path; // Update the path if successfully uploaded
        }
    }

    // Event Info Upload (PDF or file)
    $eventinfopath = NULL;  // Default is null if no file uploaded
    if (isset($_FILES['eventinfo']) && $_FILES['eventinfo']['error'] == 0) {
        $file_tmp = $_FILES['eventinfo']['tmp_name'];
        $file_name = $_FILES['eventinfo']['name'];
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $file_new_name = uniqid() . '.' . $file_extension;
        $file_path = 'eventinfo/' . $file_new_name;

        // Move the uploaded PDF or file to the eventinfo folder
        if (move_uploaded_file($file_tmp, $file_path)) {
            $eventinfopath = $file_path; // Update the path if successfully uploaded
        }
    }

    // Set participantcount to 0 if not set
    $participantcount = 0;

    // Insert the event into the database
    $sql = "INSERT INTO events (eventname, startdate, enddate, location, eventinfopath, eventbadgepath, eventcreator, eventkey, eventshortinfo, participantcount)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssssssissi", $eventname, $startdate, $enddate, $location, $eventinfopath, $eventbadgepath, $uid, $eventkey, $eventshortinfo, $participantcount);
        if ($stmt->execute()) {
            // After successful insertion, redirect to prevent form resubmission
            header("Location: dashboard.php");
            exit(); // Always call exit after header redirection to stop further execution
        } else {
            $error_message = "Error creating the event.";
        }
        $stmt->close();
    }
}

// Close DB connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Event</title>
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

    <!-- Main content for Create Event form -->
    <div class="flex-1 p-6">
      <div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-8">
        <h2 class="text-2xl font-bold mb-6 text-primary-700">Create New Event</h2>

        <?php
        if (isset($error_message)) {
            echo "<div class='alert alert-danger'>" . htmlspecialchars($error_message) . "</div>";
        }
        ?>

        <form method="POST" action="create-event.php" enctype="multipart/form-data">
          <div class="mb-6">
            <label for="eventname" class="form-label">Event Name</label>
            <input type="text" class="form-control" id="eventname" name="eventname" required>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
              <label for="startdate" class="form-label">Start Date</label>
              <input type="datetime-local" class="form-control" id="startdate" name="startdate" required>
            </div>
            <div>
              <label for="enddate" class="form-label">End Date</label>
              <input type="datetime-local" class="form-control" id="enddate" name="enddate" required>
            </div>
          </div>

          <div class="mb-6">
            <label for="location" class="form-label">Location</label>
            <input type="text" class="form-control" id="location" name="location" required>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
              <label for="eventkey" class="form-label">Event Key</label>
              <input type="text" class="form-control" id="eventkey" name="eventkey" required>
            </div>
            <div>
              <label for="eventshortinfo" class="form-label">Event Link</label>
              <input type="text" class="form-control" id="eventshortinfo" name="eventshortinfo">
            </div>
          </div>

          <div class="mb-6">
            <label for="eventbadge" class="form-label">Event Badge (Optional)</label>
            <input type="file" class="form-control" id="eventbadge" name="eventbadge">
          </div>

          <div class="mb-6">
            <label for="eventinfo" class="form-label">Event Certificate (PDF or file)</label>
            <input type="file" class="form-control" id="eventinfo" name="eventinfo">
          </div>

          <button type="submit" class="btn-primary">Create Event</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap Modal for Tutorial -->
  <div class="modal fade" id="tutorialModal" tabindex="-1" aria-labelledby="tutorialModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="tutorialModalLabel">Event Creation Tutorial</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <h6>Welcome to the event creation page!</h6>
          <p>Follow the steps below to create your event:</p>
          <ol>
            <li><strong>Event Name:</strong> Enter the name of your event.</li>
            <li><strong>Start Date & End Date:</strong> Set the start and end date and time for your event.</li>
            <li><strong>Location:</strong> Enter the location of the event.</li>
            <li><strong>Event Key:</strong> Provide a code for the onsite terminals to use for your event. Use <strong>palisade.dcism.org</strong> for onsite registration and attendee tracking</li>
            <li><strong>Event Short Info:</strong> Put a link to your page or event page.</li>
            <li><strong>Event Badge:</strong> Optionally, upload a badge for your attendees to collect.</li>
            <li><strong>Event Certificate (PDF):</strong> Optionally, upload a certificate for your attendees.</li>
          </ol>
          <p>Once you're done, click "Create Event" to finalize.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Got it!</button>
        </div>
      </div>
    </div>
  </div>
</body>
</html>

