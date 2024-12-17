<?php
// Start output buffering to prevent unexpected output
ob_start();
ob_end_clean();  // Clean any accidental output

// Include TCPDF library
require_once('tcpdf/tcpdf.php');

// Include the database connection
require_once 'includes/db.php';

// Ensure that eventid is passed
if (!isset($_GET['eventid'])) {
    die('Event ID is required.');
}

$eventid = htmlspecialchars($_GET['eventid']); // Get eventid safely

// Fetch the event name from the database
$sql_event = "SELECT eventname FROM events WHERE eventid = ?";
$stmt_event = $conn->prepare($sql_event);
$stmt_event->bind_param("i", $eventid);
$stmt_event->execute();
$result_event = $stmt_event->get_result();

// Check if the event exists
if ($result_event->num_rows > 0) {
    $event = $result_event->fetch_assoc();
    $eventname = $event['eventname'];
} else {
    die('Event not found.');
}

// Create a new PDF instance
$pdf = new TCPDF();
$pdf->AddPage();

// Set the document title
$pdf->SetTitle('Participants List');

// Set content font and title
$pdf->SetFont('helvetica', '', 12);

// Change header to display event name
$pdf->Cell(0, 10, 'Participants List for Event: ' . $eventname, 0, 1, 'C');

// SQL query to fetch participants from the database
$sql_participants = "SELECT u.fname, u.lname, u.email, e.join_time, e.leave_time 
                     FROM event_participants e 
                     JOIN user_credentials u ON e.uid = u.uid 
                     WHERE e.eventid = ?";
$stmt = $conn->prepare($sql_participants);
$stmt->bind_param("i", $eventid);
$stmt->execute();
$result_participants = $stmt->get_result();

// Check if participants are found
if ($result_participants->num_rows > 0) {
    // Generate the HTML table content for participants
    $html = '
    <table border="1" cellpadding="5">
        <tr>
            <th><b>Name</b></th>
            <th><b>Email</b></th>
            <th><b>Join Time</b></th>
            <th><b>Leave Time</b></th>
        </tr>';

    // Loop through participants and create table rows
    while ($row = $result_participants->fetch_assoc()) {
        $html .= '
        <tr>
            <td>' . htmlspecialchars($row['fname'] . ' ' . $row['lname']) . '</td>
            <td>' . htmlspecialchars($row['email']) . '</td>
            <td>' . htmlspecialchars($row['join_time']) . '</td>
            <td>' . htmlspecialchars($row['leave_time'] ?? 'N/A') . '</td>
        </tr>';
    }
    $html .= '</table>';
} else {
    // Handle case when no participants are found
    $html = '<p>No participants available for this event.</p>';
}

$pdf->writeHTML($html, true, false, true, false, '');

// Ensure the folder exists and is writable
$folder_path = __DIR__ . '/eventpdfs/'; // Path to save the PDF

// Check if folder exists, and create it if it doesn't
if (!file_exists($folder_path)) {
    mkdir($folder_path, 0777, true); // Use 0777 for full permissions (testing), change to 0755 for production
}

// Check if the folder is writable
if (!is_writable($folder_path)) {
    die('The folder is not writable. Please check folder permissions.');
}

// Define the filename for the PDF
$filename = $folder_path . 'participants_' . $eventid . '_' . time() . '.pdf';

// Save the PDF to the server
$pdf->Output($filename, 'F');  // 'F' means save the file to the file system

// Provide the download link
echo 'The PDF has been generated. <a href="eventpdfs/' . basename($filename) . '" download>Click here to download the PDF.</a>';

exit;
?>

