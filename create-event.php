<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Event</title>
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
    .form-container {
      padding: 30px;
      margin: 30px auto; /* Add margin around the form container */
      max-width: 800px; /* Set max-width to limit the form's width */
      background-color: #f8f9fa;
      border-radius: 8px;
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
          <a class="nav-link" href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="settings.php">Settings</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="logout.php">Logout</a>
        </li>
      </ul>
    </div>

    <!-- Main content for Create Event form -->
    <div class="col-md-9 col-lg-10 p-3">
      <div class="form-container">
        <h2>Create New Event</h2>

        <?php
        if (isset($error_message)) {
            echo "<div class='alert alert-danger'>" . htmlspecialchars($error_message) . "</div>";
        }
        ?>

        <form method="POST" action="create-event.php" enctype="multipart/form-data">
          <div class="mb-3">
            <label for="eventname" class="form-label">Event Name</label>
            <input type="text" class="form-control" id="eventname" name="eventname" required>
          </div>

          <div class="mb-3">
            <label for="startdate" class="form-label">Start Date</label>
            <input type="datetime-local" class="form-control" id="startdate" name="startdate" required>
          </div>

          <div class="mb-3">
            <label for="enddate" class="form-label">End Date</label>
            <input type="datetime-local" class="form-control" id="enddate" name="enddate" required>
          </div>

          <div class="mb-3">
            <label for="location" class="form-label">Location</label>
            <input type="text" class="form-control" id="location" name="location" required>
          </div>

          <div class="mb-3">
            <label for="eventdetails" class="form-label">Event Details</label>
            <textarea class="form-control" id="eventdetails" name="eventdetails" rows="4" required></textarea>
          </div>

          <div class="mb-3">
            <label for="eventkey" class="form-label">Event Key</label>
            <input type="text" class="form-control" id="eventkey" name="eventkey" required>
          </div>

          <div class="mb-3">
            <label for="eventshortinfo" class="form-label">Event Short Info</label>
            <input type="text" class="form-control" id="eventshortinfo" name="eventshortinfo" required>
          </div>

          <div class="mb-3">
            <label for="eventbadge" class="form-label">Event Badge (Optional)</label>
            <input type="file" class="form-control" id="eventbadge" name="eventbadge">
          </div>

          <div class="mb-3">
            <label for="eventinfo" class="form-label">Event Info (PDF or file)</label>
            <input type="file" class="form-control" id="eventinfo" name="eventinfo">
          </div>

          <button type="submit" class="btn btn-primary">Create Event</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS and dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
</body>
</html>

