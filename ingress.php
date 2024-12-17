<?php

require 'includes/db.php';

// Check if 'userid' is passed in the URL query string
if (isset($_GET['userid'])) {
    // Get the value of 'userid'
    $userid = $_GET['userid'];
    
    // Echo the value
    echo "User ID: " . htmlspecialchars($userid);
} else {
    echo "No User ID provided.";
}

?>
