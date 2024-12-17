<?php
// Include the database connection file
require "includes/db.php"; // Replace with actual path to your db connection file

// Initialize variables
$searchError = "";
$userDetails = null; // To store the queried user details
$selectedUserId = null;
$eventStatusButton = ""; // Variable to hold the button status
$userEventStatus = null; // Store user event status (0, 1, or 2)
$localToken = ''; // Store the generated token (if any)

$eventid = $_SESSION['eventid'];  // Assuming you have the event ID stored in session

// Handle the search query
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['searchName'])) {
    // Sanitize user input
    $searchInput = trim($_POST['searchName']);
    $names = explode(" ", $searchInput);

    // Ensure at least first and last name are provided
    if (count($names) < 2) {
        $searchError = "Please enter both first and last name.";
    } else {
        $fname = $names[0];
        $lname = $names[1];

        // Query the user based on first and last name
        $stmt = $conn->prepare("SELECT uid, fname, lname, email FROM user_credentials WHERE fname = ? AND lname = ?");
        $stmt->bind_param("ss", $fname, $lname);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $userDetails = $result->fetch_assoc();
            $selectedUserId = $userDetails['uid'];  // Store selected user ID

            // Query the event_participants table to check if the user has joined the event
            $eventQuery = $conn->prepare("SELECT join_time, leave_time FROM event_participants WHERE uid = ? AND eventid = ?");
            $eventQuery->bind_param("ii", $selectedUserId, $eventid);
            $eventQuery->execute();
            $eventResult = $eventQuery->get_result();

            // Check if the user is part of the event participants
            if ($eventResult && $eventResult->num_rows > 0) {
                // User has joined the event, now check their join and leave times
                $eventData = $eventResult->fetch_assoc();
                $joinTime = $eventData['join_time'];
                $leaveTime = $eventData['leave_time'];

                if (is_null($joinTime) && is_null($leaveTime)) {
                    // User has not joined yet
                    $userEventStatus = 0; // Join Event
                } elseif (!is_null($joinTime) && is_null($leaveTime)) {
                    // User has joined but not left yet
                    $userEventStatus = 1; // Leave Event
                } else {
                    // User has both joined and left
                    $userEventStatus = 2; // Already attended
                }
            } else {
                // User has not joined the event
                $userEventStatus = 0; // Join Event
            }
        } else {
            // User not found in the database
            $searchError = "User not found.";
        }
    }
}

// Logic to generate a token (for demonstration, it could be more complex)
if ($userDetails && $userEventStatus !== null) {
    $localToken = bin2hex(random_bytes(32)); 
    $tokenCreationTime = date('Y-m-d H:i:s'); // Set the current time as token creation time

    // Update the token and creation time in the database
    $updateTokenStmt = $conn->prepare("UPDATE user_credentials SET currboundtoken = ?, creationtime = ? WHERE uid = ?");
    $updateTokenStmt->bind_param("ssi", $localToken, $tokenCreationTime, $selectedUserId);
    $updateTokenStmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search User</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet">

    <!-- Include the necessary jQuery and Bootstrap JS for modal -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <script>
        // Function to open the modal with the QR code using the QR Server API
        function showQRCode(eventid, token) {
            event.preventDefault(); // Prevent form submission and page refresh

            // Construct the URL with token and event parameters
            const url = `https://accounts.dcism.org/accountRegistration/ingress.php?token=${token}&event=${eventid}`;

            // Encode the URL to ensure proper handling of special characters
            const encodedUrl = encodeURIComponent(url);

            // Generate the QR code URL by passing the encoded URL
            const qrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=${encodedUrl}`;

            // Set the QR code URL to the image inside the modal
            $('#qrCodeModal img').attr('src', qrCodeUrl);

            // Show the modal
            $('#qrCodeModal').modal('show');
        }
    </script>
</head>
<body>
    <section class="vh-100 gradient-custom">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                    <div class="card bg-dark text-white" style="border-radius: 1rem;">
                        <div class="card-body p-5 text-center">
                            <h2 class="fw-bold mb-4 text-uppercase">Search User</h2>
                            
                            <!-- Search Form -->
                            <form method="POST" action="">
                                <div class="form-outline form-white mb-4">
                                    <input type="text" name="searchName" class="form-control form-control-lg" placeholder="Enter First and Last Name" autocomplete="off" required />
                                </div>
                                <button class="btn btn-outline-light btn-lg px-5" type="submit">Search</button>
                            </form>

                            <!-- Error Message -->
                            <?php if ($searchError): ?>
                                <div class="alert alert-danger mt-3"><?php echo $searchError; ?></div>
                            <?php endif; ?>

                            <!-- Display User Details -->
                            <?php if ($userDetails): ?>
                                <div class="mt-4">
                                    <h4>Is This You?:</h4>
                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($userDetails['fname'] . " " . $userDetails['lname']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($userDetails['email']); ?></p>
                                    
                                    <!-- Confirmation Form -->
                                    <form method="POST" action="">
                                        <input type="hidden" name="userId" value="<?php echo $userDetails['uid']; ?>" />

                                        <!-- Button logic based on user event status -->
                                        <?php if ($userEventStatus === 0): ?>
                                            <button class="btn btn-primary" 
                                                    onclick="showQRCode('<?php echo $eventid; ?>', '<?php echo $localToken; ?>')">Join Event</button>
                                        <?php elseif ($userEventStatus === 1): ?>
                                            <button class="btn btn-danger" 
                                                    onclick="showQRCode('<?php echo $eventid?>', '<?php echo $localToken; ?>')">Leave Event</button>
                                        <?php elseif ($userEventStatus === 2): ?>
                                            <button class="btn btn-secondary" disabled>You have already attended</button>
                                        <?php endif; ?>
                                    </form>                                </div>
                            <?php endif; ?>

                            <!-- Display Local Token if available -->
                            <?php if (!empty($localToken)): ?>
                                <div class="alert alert-info mt-4">
                                    <strong>Token:</strong> <?php echo htmlspecialchars($localToken); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal for QR Code -->
    <div class="modal fade" id="qrCodeModal" tabindex="-1" aria-labelledby="qrCodeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrCodeModalLabel">QR Code for Registration</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <img src="" alt="QR Code" class="img-fluid" />
                </div>
            </div>
        </div>
    </div>
</body>
</html>


