<?php
// Start output buffering and clean any unexpected output
ob_start();
ob_end_clean();

// Include TCPDF library
require_once('tcpdf/tcpdf.php');

// Include database connection
require_once 'includes/db.php';

// Check if eventid is passed
if (!isset($_GET['eventid'])) {
    die('Event ID is required.');
}

$eventid = htmlspecialchars($_GET['eventid']);

// Create a new PDF instance
$pdf = new TCPDF();
$pdf->AddPage();

// Set document title
$pdf->SetTitle('Participants List');

// Set content
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, 'Participants List for Event ID: ' . $eventid, 0, 1, 'C');

// Connect to your database to fetch participant details
$sql_participants = "SELECT u.fname, u.lname, u.email, e.join_time, e.leave_time 
                     FROM event_participants e 
                     JOIN user_credentials u ON e.uid = u.uid 
                     WHERE e.eventid = ?";
$stmt = $conn->prepare($sql_participants);
$stmt->bind_param("i", $eventid);
$stmt->execute();
$result_participants = $stmt->get_result();

// Check if there are participants
if ($result_participants->num_rows > 0) {
    // Generate HTML table content for participants
    $html = '
    <table border="1" cellpadding="5">
        <tr>
            <th><b>Name</b></th>
            <th><b>Email</b></th>
            <th><b>Join Time</b></th>
            <th><b>Leave Time</b></th>
        </tr>';
    
    // Loop through participants and add rows to the table
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

// Save PDF in the "eventpdfs" folder
$filename = 'eventpdfs/participants_' . $eventid . '_' . time() . '.pdf';
$pdf->Output($filename, 'F'); // Save to file system
$pdf->Output(basename($filename), 'D'); // Download to user browser
exit;
?>

