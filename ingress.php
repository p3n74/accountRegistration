<?php
// Include the database connection file
require 'includes/db.php'; // This will include the db.php file with the $conn variable

// Add Bootstrap CSS (if not already included)
echo '<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">';

// Viewport meta tag for responsive scaling on mobile
echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';

// Check if 'token' and 'event' are passed in the URL query string
if (isset($_GET['token']) && isset($_GET['event'])) {
    // Get the token and event ID from the URL
    $token = $_GET['token'];
    $eventid = $_GET['event'];

    // Prepare the SQL query to get user details based on the token
    $sql = "SELECT * FROM user_credentials WHERE currboundtoken = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token); // Binding the token to the query
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the user is found
    if ($result->num_rows > 0) {
        // Fetch the user details
        $row = $result->fetch_assoc();

        // Get the creationtime and convert it to a timestamp
        $creationtime = strtotime($row['creationtime']);
        $current_time = time(); // Get the current time

        // Check if the token is older than 10 minutes
        if (($current_time - $creationtime) > 600) { // 600 seconds = 10 minutes
            echo "<div class='alert alert-danger mt-4'>The token has expired. Please try again.</div>";
            exit(); // Stop further script execution
        }

        // At this point, the token is valid. Now, we need to check the user's password.
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['password'])) {
            $password = $_POST['password'];

            // Verify the password with the hashed password stored in the user_credentials table
            if (password_verify($password, $row['password'])) {
                // Password is correct, now check the event_participants table
                $uid = $row['uid']; // Get the user's ID
                $event_sql = "SELECT * FROM event_participants WHERE eventid = ? AND uid = ? LIMIT 1";
                $event_stmt = $conn->prepare($event_sql);
                $event_stmt->bind_param("ii", $eventid, $uid);
                $event_stmt->execute();
                $event_result = $event_stmt->get_result();

                // Check if the user is already in the event
                if ($event_result->num_rows > 0) {
                    // The user is already in the event, check if join_time and leave_time are NULL
                    $event_row = $event_result->fetch_assoc();

                    if (is_null($event_row['join_time']) && is_null($event_row['leave_time'])) {
                        // User has no join_time, hence they are joining
                        $update_sql = "UPDATE event_participants SET join_time = NOW() WHERE eventid = ? AND uid = ?";
                        $update_stmt = $conn->prepare($update_sql);
                        $update_stmt->bind_param("ii", $eventid, $uid);
                        $update_stmt->execute();
                        echo "<div class='alert alert-success mt-4'>You have successfully joined the event.</div>";
                    } elseif (!is_null($event_row['join_time']) && is_null($event_row['leave_time'])) {
                        // User has a join_time but no leave_time, hence they are leaving
                        $update_sql = "UPDATE event_participants SET leave_time = NOW() WHERE eventid = ? AND uid = ?";
                        $update_stmt = $conn->prepare($update_sql);
                        $update_stmt->bind_param("ii", $eventid, $uid);
                        $update_stmt->execute();

                        // Append eventid to attended events
                        $attendedEvents = json_decode($row['attendedevents'], true); // Get the attended events as an array
                        if (!in_array($eventid, $attendedEvents)) {
                            $attendedEvents[] = $eventid; // Add the event ID if it's not already in the list
                            $updatedAttendedevents = json_encode($attendedEvents); // Convert it back to JSON

                            // Update the attendedevents field
                            $update_user_sql = "UPDATE user_credentials SET attendedevents = ? WHERE uid = ?";
                            $update_user_stmt = $conn->prepare($update_user_sql);
                            $update_user_stmt->bind_param("si", $updatedAttendedevents, $uid);
                            $update_user_stmt->execute();
                        }

                        echo "<div class='alert alert-success mt-4'>You have successfully left the event.</div>";
                    } else {
                        echo "<div class='alert alert-info mt-4'>You have already recorded both your join and leave times.</div>";
                    }
                } else {
                    // User is not in the event, insert a new record with join_time
                    $insert_sql = "INSERT INTO event_participants (eventid, uid, join_time, token) VALUES (?, ?, NOW(), ?)";
                    $insert_stmt = $conn->prepare($insert_sql);
                    $insert_stmt->bind_param("iis", $eventid, $uid, $token);
                    $insert_stmt->execute();
                    echo "<div class='alert alert-success mt-4'>You have successfully joined the event.</div>";
                }

                // Optionally, generate a new token and update currboundtoken
                $localToken = bin2hex(random_bytes(32)); // Generate new token
                $newCreationTime = date('Y-m-d H:i:s'); // Get current timestamp for creation time

                // Update the user's currboundtoken and creationtime
                $update_user_sql = "UPDATE user_credentials SET currboundtoken = ?, creationtime = ? WHERE uid = ?";
                $update_user_stmt = $conn->prepare($update_user_sql);
                $update_user_stmt->bind_param("ssi", $localToken, $newCreationTime, $uid);
                $update_user_stmt->execute();

                // Optionally, return the new token to the user
                echo "<div class='alert alert-info mt-4'>A new token has been generated: Return to the Registration desk to sign out</div>";
            } else {
                // Incorrect password
                echo "<div class='alert alert-danger mt-4'>Incorrect password. Please try again.</div>";
            }
        } else {
            // Check if the user is joining or leaving the event
            $action = "Join Event";
            $heading = "Enter Password to Join Event";
            $buttonText = "Join Event";

            // Check if the user is already in the event (i.e., joining or leaving)
            $uid = $row['uid']; // Get the user's ID
            $event_sql = "SELECT * FROM event_participants WHERE eventid = ? AND uid = ? LIMIT 1";
            $event_stmt = $conn->prepare($event_sql);
            $event_stmt->bind_param("ii", $eventid, $uid);
            $event_stmt->execute();
            $event_result = $event_stmt->get_result();

            if ($event_result->num_rows > 0) {
                // User is in the event, check if they are joining or leaving
                $event_row = $event_result->fetch_assoc();

                if (!is_null($event_row['join_time']) && is_null($event_row['leave_time'])) {
                    // User is already joined and hasn't left yet, they can leave the event
                    $action = "Leave Event";
                    $heading = "Enter Password to Leave Event";
                    $buttonText = "Leave Event";
                }
            }

            // Display the form with the dynamic heading and button
            echo '
            <div class="container mt-4">
                <div class="row justify-content-center">
                    <div class="col-12 col-sm-8 col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h3>' . $heading . '</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="form-group">
                                        <label for="password">Password</label>
                                        <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block">' . $buttonText . '</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
        }
    } else {
        echo "<div class='alert alert-danger mt-4'>The token is invalid or has expired. Please try again.</div>";
    }
} else {
    echo "<div class='alert alert-danger mt-4'>Token or Event ID is missing from the URL. Please provide the required information.</div>";
}
?>

<!-- Add Bootstrap JS (required for some functionality) -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

