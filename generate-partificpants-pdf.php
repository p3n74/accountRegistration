<?php
// Include TCPDF library
require_once('tcpdf/tcpdf.php');

// Start session and check login
session_start();
if (!isset($_SESSION['uid'])) {
    die("You must log in first.");
}

// Include database connection
require_once 'includes/db.php';

// Check if event ID is provided
if (!isset($_GET['eventid'])) {
    die("Event ID is missing.");
}

$eventid = $_GET['eventid'];

// Fetch event details (name and start date)
$sql_event = "SELECT eventname, startdate FROM events WHERE eventid = ?";
$stmt_event = $conn->prepare($sql_event);
$stmt_event->bind_param("i", $eventid);
$stmt_event->execute();
$stmt_event->store_result();
$stmt_event->bind_result($eventname, $startdate);

if (!$stmt_event->fetch()) {
    die("Event not found.");
}
$stmt_event->close();

// Clean up event name to make it safe for a filename
$eventname_clean = preg_replace('/[^A-Za-z0-9_\-]/', '_', $eventname); // Replace invalid filename characters
$startdate_clean = date('Y-m-d', strtotime($startdate)); // Format start date
$timestamp = date('Ymd_His'); // Current timestamp for uniqueness

// Generate the filename
$pdf_filename = "{$eventname_clean}_{$startdate_clean}_{$timestamp}.pdf";

// Fetch participants data
$sql_participants = "SELECT u.fname, u.lname, u.email, e.join_time, e.leave_time 
                     FROM event_participants e 
                     JOIN user_credentials u ON e.uid = u.uid 
                     WHERE e.eventid = ?";
$stmt_participants = $conn->prepare($sql_participants);
$stmt_participants->bind_param("i", $eventid);
$stmt_participants->execute();
$result = $stmt_participants->get_result();

// Create new PDF document
$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Event Management System');
$pdf->SetAuthor('Event Organizer');
$pdf->SetTitle('Participants List for ' . htmlspecialchars($eventname));
$pdf->SetMargins(15, 15, 15);

// Add a page
$pdf->AddPage();

// Title
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Participants List for ' . htmlspecialchars($eventname), 0, 1, 'C');

// Add a blank line
$pdf->Ln(5);

// Table header
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetFillColor(220, 220, 220);
$pdf->Cell(50, 10, 'Name', 1, 0, 'C', true);
$pdf->Cell(60, 10, 'Email', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Join Time', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Leave Time', 1, 1, 'C', true);

// Table content
$pdf->SetFont('helvetica', '', 12);
while ($row = $result->fetch_assoc()) {
    $name = htmlspecialchars($row['fname'] . ' ' . $row['lname']);
    $email = htmlspecialchars($row['email']);
    $join_time = htmlspecialchars($row['join_time']);
    $leave_time = htmlspecialchars($row['leave_time'] ?? 'N/A');

    $pdf->Cell(50, 10, $name, 1, 0, 'C');
    $pdf->Cell(60, 10, $email, 1, 0, 'C');
    $pdf->Cell(40, 10, $join_time, 1, 0, 'C');
    $pdf->Cell(40, 10, $leave_time, 1, 1, 'C');
}

// Close database connection
$stmt_participants->close();
$conn->close();

// Output PDF with dynamic filename
$pdf->Output($pdf_filename, 'D');
?>

