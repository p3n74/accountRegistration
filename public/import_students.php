<?php
// /**
//  * Web Interface for Student Data Import
//  */

// session_start();
// require_once '../app/config/config.php';
// require_once '../app/core/Database.php';

// $isAdmin = isset($_SESSION['uid']) && $_SESSION['uid'];
// $message = '';
// $messageType = 'info';
// $stats = null;

// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
//     if (!$isAdmin) {
//         $message = 'Authentication required to perform this action.';
//         $messageType = 'error';
//     } else {
//         try {
//             $db = new Database();
            
//             if ($_POST['action'] === 'prepare') {
//                 $db->query("ALTER TABLE existing_student_info ADD COLUMN IF NOT EXISTS program_id INT(11)");
//                 $db->query("DELETE FROM existing_student_info");
//                 $db->query("ALTER TABLE existing_student_info AUTO_INCREMENT = 1");
                
//                 $message = 'Table prepared successfully. Existing data cleared.';
//                 $messageType = 'success';
                
//             } elseif ($_POST['action'] === 'import') {
//                 $csvFile = '../suppdata/dcsimstudents.csv';
                
//                 if (!file_exists($csvFile)) {
//                     throw new Exception("CSV file not found: $csvFile");
//                 }
                
//                 // Get program mapping
//                 $mappingSQL = "
//                     SELECT program_id, 'BS CS' as code FROM program_list WHERE program_name = 'Bachelor of Science in Computer Science'
//                     UNION ALL
//                     SELECT program_id, 'BS IT' as code FROM program_list WHERE program_name = 'Bachelor of Science in Information Technology'
//                     UNION ALL  
//                     SELECT program_id, 'BS IS' as code FROM program_list WHERE program_name = 'Bachelor of Science in Information Systems'
//                     UNION ALL
//                     SELECT program_id, 'BS APPMATH' as code FROM program_list WHERE program_name = 'Bachelor of Science in Applied Mathematics'
//                     UNION ALL
//                     SELECT program_id, 'BS ICT' as code FROM program_list WHERE program_name = 'Bachelor of Science in Information and Communications Technology'
//                 ";
                
//                 $result = $db->query($mappingSQL);
//                 $programMapping = [];
                
//                 while ($row = $result->fetch_assoc()) {
//                     $programMapping[$row['code']] = $row['program_id'];
//                 }
                
//                 // Process CSV
//                 $handle = fopen($csvFile, 'r');
//                 fgetcsv($handle, 0, ',', '"', '\\'); // Skip header
                
//                 $insertSQL = "INSERT IGNORE INTO existing_student_info (student_id, last_name, first_name, middle_name, program, program_id, email) VALUES (?, ?, ?, ?, ?, ?, ?)";
//                 $stmt = $db->prepare($insertSQL);
                
//                 $db->beginTransaction();
                
//                 $totalRows = 0;
//                 $successfulInserts = 0;
//                 $failedInserts = 0;
//                 $errors = [];
                
//                 while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
//                     $totalRows++;
                    
//                     $studentId = trim($row[0]);
//                     $lastName = trim($row[1]);
//                     $firstName = trim($row[2]);
//                     $middleName = trim($row[3]);
//                     $programCode = trim($row[4]);
//                     $email = trim($row[5]);
                    
//                     if (empty($studentId) || empty($lastName) || empty($firstName) || empty($email)) {
//                         $failedInserts++;
//                         continue;
//                     }
                    
//                     if (!isset($programMapping[$programCode])) {
//                         $failedInserts++;
//                         $errors[] = "Unknown program code '$programCode' for student $studentId";
//                         continue;
//                     }
                    
//                     $programId = $programMapping[$programCode];
//                     $stmt->bind_param("sssssis", $studentId, $lastName, $firstName, $middleName, $programCode, $programId, $email);
                    
//                     if ($stmt->execute()) {
//                         $successfulInserts++;
//                     } else {
//                         $failedInserts++;
//                         $errors[] = "Database error for student $studentId";
//                     }
//                 }
                
//                 $db->commit();
//                 fclose($handle);
                
//                 $stats = [
//                     'total_rows' => $totalRows,
//                     'successful_inserts' => $successfulInserts,
//                     'failed_inserts' => $failedInserts,
//                     'errors' => $errors
//                 ];
                
//                 $message = "Import completed! $successfulInserts students imported successfully.";
//                 $messageType = 'success';
//             }
            
//         } catch (Exception $e) {
//             $message = 'Error: ' . $e->getMessage();
//             $messageType = 'error';
//         }
//     }
// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Data Import - USC DCISM</title>
    <link rel="icon" href="icon.png" type="image/png">
    <link href="dist/output.css" rel="stylesheet">
    <style>
        .alert {
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 0.5rem;
        }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-bold text-gray-900 mb-4">USC DCISM Student Data Import</h1>
                    
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!$isAdmin): ?>
                        <div class="alert alert-error">
                            <strong>Authentication Required:</strong> Please log in to access this functionality.
                            <a href="login.php" class="underline ml-2">Login here</a>
                        </div>
                    <?php else: ?>
                        
                        <!-- Import Controls -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <h3 class="font-semibold text-yellow-800 mb-2">Step 1: Prepare Table</h3>
                                <p class="text-sm text-yellow-700 mb-3">
                                    This will clear existing data and prepare the table structure.
                                </p>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="prepare">
                                    <button type="submit" 
                                            class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded"
                                            onclick="return confirm('This will delete all existing student data. Continue?')">
                                        Prepare Table
                                    </button>
                                </form>
                            </div>
                            
                            <div class="bg-green-50 p-4 rounded-lg">
                                <h3 class="font-semibold text-green-800 mb-2">Step 2: Import Data</h3>
                                <p class="text-sm text-green-700 mb-3">
                                    Import student data from CSV file.
                                </p>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="import">
                                    <button type="submit" 
                                            class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
                                        Import Students
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Import Statistics -->
                        <?php if ($stats): ?>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-semibold text-gray-800 mb-2">Import Results</h3>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-3">
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-blue-600"><?php echo $stats['total_rows']; ?></div>
                                        <div class="text-sm text-gray-600">Total Rows</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-green-600"><?php echo $stats['successful_inserts']; ?></div>
                                        <div class="text-sm text-gray-600">Successful</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-red-600"><?php echo $stats['failed_inserts']; ?></div>
                                        <div class="text-sm text-gray-600">Failed</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-purple-600">
                                            <?php echo $stats['total_rows'] > 0 ? number_format(($stats['successful_inserts'] / $stats['total_rows']) * 100, 1) : 0; ?>%
                                        </div>
                                        <div class="text-sm text-gray-600">Success Rate</div>
                                    </div>
                                </div>
                                
                                <?php if (!empty($stats['errors']) && count($stats['errors']) <= 10): ?>
                                    <div class="mt-3">
                                        <strong>Errors:</strong>
                                        <ul class="list-disc list-inside text-sm text-red-600 mt-1">
                                            <?php foreach (array_slice($stats['errors'], 0, 10) as $error): ?>
                                                <li><?php echo htmlspecialchars($error); ?></li>
                                            <?php endforeach; ?>
                                            <?php if (count($stats['errors']) > 10): ?>
                                                <li class="text-gray-500">... and <?php echo count($stats['errors']) - 10; ?> more errors</li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Instructions -->
                        <div class="mt-6 bg-blue-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-blue-800 mb-2">Instructions</h3>
                            <ol class="list-decimal list-inside text-sm text-blue-700 space-y-1">
                                <li>Ensure the USC programs have been imported into the database first</li>
                                <li>Click "Prepare Table" to clear existing data and set up the table structure</li>
                                <li>Click "Import Students" to import all student data from the CSV file</li>
                                <li>The script maintains atomicity - multiple program codes map to the same program entity</li>
                                <li>Students will be linked to their programs via program_id for referential integrity</li>
                            </ol>
                        </div>
                        
                    <?php endif; ?>
                    
                    <!-- Navigation -->
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 underline">← Back to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
