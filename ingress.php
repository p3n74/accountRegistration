<?php

require 'includes/db.php';

// Check if 'token' is passed in the URL query string
if(isset($_GET['token'])) {
    // Get the 32 Char token
    $token = $_GET['token'];

    // Prepare the SQL query to get user details based on the token
    $sql = "SELECT * FROM user_credentials WHERE token = ? LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("s", $token); // Binding the token to the query
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the user is found
    if($result->num_rows > 0) {
        // Fetch the user details
        $row = $result->fetch_assoc();

        // Get the creationtime and convert it to a timestamp
        $creationtime = strtotime($row['creationtime']);
        $current_time = time(); // Get the current time

        // Check if the token is older than 10 minutes
        if(($current_time - $creationtime) > 600) { // 600 seconds = 10 minutes
            echo "Token Is Expired";
            // Make the whole page inaccessible
            exit(); // Stops further script execution
        } else {
            // If token is valid, display the user id
            echo "User Token: " . $token . "<br>";
            echo "User ID: " . $row['userid']; // Display the user ID
        }
    } else {
        echo "Token Invalid Or Expired";
    }
} else {
    echo "Token Invalid Or Expired";
}

?>

