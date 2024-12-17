<?php

require 'includes/db.php';

// Check if 'userid' is passed in the URL query string

//switched over to passing the token

$token;

if(isset($_GET['token'])) {
	// Get the 32 Char token
	$token = $_GET['token'];

	echo "User Token: " . $token;
} else {
	echo "Token Invalid Or Expired";
}

?>
