<?php
// Include the database connection file
require 'includes/db.php';

// Initialize variables
$error_message = '';
$success_message = '';
$info_message = '';
$buttonText = 'Join Event';
$eventname = 'Event';

// Check if 'token' and 'event' are passed in the URL query string
if (isset($_GET['token']) && isset($_GET['event'])) {
    $token = $_GET['token'];
    $eventid = $_GET['event'];

    // Get event name for display
    $event_sql = "SELECT eventname FROM events WHERE eventid = ?";
    $event_stmt = $conn->prepare($event_sql);
    $event_stmt->bind_param("i", $eventid);
    $event_stmt->execute();
    $event_result = $event_stmt->get_result();
    if ($event_result->num_rows > 0) {
        $event_row = $event_result->fetch_assoc();
        $eventname = $event_row['eventname'];
    }

    // Prepare the SQL query to get user details based on the token
    $sql = "SELECT * FROM user_credentials WHERE currboundtoken = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the user is found
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Get the creationtime and convert it to a timestamp
        $creationtime = strtotime($row['creationtime']);
        $current_time = time();

        // Check if the token is older than 10 minutes
        if (($current_time - $creationtime) > 600) {
            $error_message = "The token has expired. Please try again.";
        } else {
            // At this point, the token is valid. Now, we need to check the user's password.
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['password'])) {
                $password = $_POST['password'];

                // Verify the password with the hashed password stored in the user_credentials table
                if (password_verify($password, $row['password'])) {
                    $uid = $row['uid'];
                    $event_sql = "SELECT * FROM event_participants WHERE eventid = ? AND uid = ? LIMIT 1";
                    $event_stmt = $conn->prepare($event_sql);
                    $event_stmt->bind_param("ii", $eventid, $uid);
                    $event_stmt->execute();
                    $event_result = $event_stmt->get_result();

                    // Check if the user is already in the event
                    if ($event_result->num_rows > 0) {
                        $event_row = $event_result->fetch_assoc();

                        if (is_null($event_row['join_time']) && is_null($event_row['leave_time'])) {
                            // User has no join_time, hence they are joining
                            $update_sql = "UPDATE event_participants SET join_time = NOW() WHERE eventid = ? AND uid = ?";
                            $update_stmt = $conn->prepare($update_sql);
                            $update_stmt->bind_param("ii", $eventid, $uid);
                            $update_stmt->execute();
                            $success_message = "You have successfully joined the event.";
                        } elseif (!is_null($event_row['join_time']) && is_null($event_row['leave_time'])) {
                            // User has a join_time but no leave_time, hence they are leaving
                            $update_sql = "UPDATE event_participants SET leave_time = NOW() WHERE eventid = ? AND uid = ?";
                            $update_stmt = $conn->prepare($update_sql);
                            $update_stmt->bind_param("ii", $eventid, $uid);
                            $update_stmt->execute();

                            // Append eventid to attended events
                            $attendedEvents = json_decode($row['attendedevents'], true);
                            if (!in_array($eventid, $attendedEvents)) {
                                $attendedEvents[] = $eventid;
                                $updatedAttendedevents = json_encode($attendedEvents);

                                $update_user_sql = "UPDATE user_credentials SET attendedevents = ? WHERE uid = ?";
                                $update_user_stmt = $conn->prepare($update_user_sql);
                                $update_user_stmt->bind_param("si", $updatedAttendedevents, $uid);
                                $update_user_stmt->execute();
                            }

                            $success_message = "You have successfully left the event.";
                        } else {
                            $info_message = "You have already recorded both your join and leave times.";
                        }
                    } else {
                        // User is not in the event, insert a new record with join_time
                        $insert_sql = "INSERT INTO event_participants (eventid, uid, join_time, token) VALUES (?, ?, NOW(), ?)";
                        $insert_stmt = $conn->prepare($insert_sql);
                        $insert_stmt->bind_param("iis", $eventid, $uid, $token);
                        $insert_stmt->execute();
                        $success_message = "You have successfully joined the event.";
                    }

                    // Generate a new token and update currboundtoken
                    $localToken = bin2hex(random_bytes(32));
                    $newCreationTime = date('Y-m-d H:i:s');

                    $update_user_sql = "UPDATE user_credentials SET currboundtoken = ?, creationtime = ? WHERE uid = ?";
                    $update_user_stmt = $conn->prepare($update_user_sql);
                    $update_user_stmt->bind_param("ssi", $localToken, $newCreationTime, $uid);
                    $update_user_stmt->execute();

                    $info_message = "A new token has been generated: Return to the Registration desk to sign out";
                } else {
                    $error_message = "Incorrect password. Please try again.";
                }
            } else {
                // Check if the user is joining or leaving the event
                $uid = $row['uid'];
                $event_sql = "SELECT * FROM event_participants WHERE eventid = ? AND uid = ? LIMIT 1";
                $event_stmt = $conn->prepare($event_sql);
                $event_stmt->bind_param("ii", $eventid, $uid);
                $event_stmt->execute();
                $event_result = $event_stmt->get_result();

                if ($event_result->num_rows > 0) {
                    $event_row = $event_result->fetch_assoc();

                    if (!is_null($event_row['join_time']) && is_null($event_row['leave_time'])) {
                        $buttonText = "Leave Event";
                    }
                }
            }
        }
    } else {
        $error_message = "The token is invalid or has expired. Please try again.";
    }
} else {
    $error_message = "Token or Event ID is missing from the URL. Please provide the required information.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingress</title>
    <!-- Favicon -->
    <link rel="icon" href="icon.png" type="image/png">
    <!-- Tailwind CSS -->
    <link href="dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto">
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger mb-6"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success mb-6"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <?php if (!empty($info_message)): ?>
                <div class="alert alert-info mb-6"><?php echo htmlspecialchars($info_message); ?></div>
            <?php endif; ?>

            <?php if (empty($error_message) && empty($success_message) && isset($token) && isset($eventid)): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="bg-primary-600 px-6 py-4">
                        <h2 class="text-xl font-semibold text-white text-center"><?php echo htmlspecialchars($eventname); ?></h2>
                    </div>
                    <div class="p-6">
                        <form method="POST" action="">
                            <input type="hidden" name="eventid" value="<?php echo htmlspecialchars($eventid); ?>">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                            
                            <div class="mb-6">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                            </div>
                            
                            <button type="submit" class="btn-primary w-full"><?php echo htmlspecialchars($buttonText); ?></button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 