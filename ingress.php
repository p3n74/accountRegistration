<?php

// Include the database connection file
require 'includes/db.php'; // This will include the db.php file with the $conn variable

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
            echo "Token Is Expired";
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

                if ($event_result->num_rows > 0) {
                    // The user is already in the event, check if join_time and leave_time are NULL
                    $event_row = $event_result->fetch_assoc();

                    if (is_null($event_row['join_time']) && is_null($event_row['leave_time'])) {
                        // If both join_time and leave_time are NULL, insert the current timestamp into join_time
                        $update_sql = "UPDATE event_participants SET join_time = NOW() WHERE eventid = ? AND uid = ?";
                        $update_stmt = $conn->prepare($update_sql);
                        $update_stmt->bind_param("ii", $eventid, $uid);
                        $update_stmt->execute();
                        echo "Join time recorded successfully.";
                    } elseif (!is_null($event_row['join_time']) && is_null($event_row['leave_time'])) {
                        // If join_time exists and leave_time is NULL, insert the current timestamp into leave_time
                        $update_sql = "UPDATE event_participants SET leave_time = NOW() WHERE eventid = ? AND uid = ?";
                        $update_stmt = $conn->prepare($update_sql);
                        $update_stmt->bind_param("ii", $eventid, $uid);
                        $update_stmt->execute();
                        echo "Leave time recorded successfully.";
                    } else {
                        echo "Event participant already updated.";
                    }
                } else {
                    // User is not in the event, insert a new record with join_time
                    $insert_sql = "INSERT INTO event_participants (eventid, uid, join_time, token) VALUES (?, ?, NOW(), ?)";
                    $insert_stmt = $conn->prepare($insert_sql);
                    $insert_stmt->bind_param("iis", $eventid, $uid, $token);
                    $insert_stmt->execute();
                    echo "User added to the event with join time.";
                }

                // Generate a new token and update currboundtoken and creationtime
                $localToken = bin2hex(random_bytes(32)); // Generate new token
                $newCreationTime = date('Y-m-d H:i:s'); // Get current timestamp for creation time

                // Update the user's currboundtoken and creationtime
                $update_user_sql = "UPDATE user_credentials SET currboundtoken = ?, creationtime = ? WHERE uid = ?";
                $update_user_stmt = $conn->prepare($update_user_sql);
                $update_user_stmt->bind_param("ssi", $localToken, $newCreationTime, $uid);
                $update_user_stmt->execute();

                // Optionally, you can return the new token to the user
                echo "<br>New Token: " . $localToken;
            } else {
                // Incorrect password
                echo "Incorrect password.";
            }
        } else {
            // Display the password form
            echo '<form method="POST">
                    <label for="password">Enter your password:</label>
                    <input type="password" name="password" id="password" required>
                    <input type="submit" value="Submit">
                  </form>';
        }
    } else {
        echo "Token Invalid Or Expired";
    }
} else {
    echo "Token or Event ID is missing";
}

?>

