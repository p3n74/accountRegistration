<?php
/**
 * Student Data Import Script
 * Reads student data from CSV and inserts into existing_student_info table
 * Uses the existing database connectivity pattern from the codebase
 */

// Include necessary files
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

class StudentDataImporter {
    private $db;
    private $csvFile;
    private $programMapping;
    private $stats;

    public function __construct($csvFile = null) {
        if ($csvFile === null) {
            $csvFile = __DIR__ . '/../suppdata/dcsimstudents.csv';
        }
        $this->db = new Database();
        $this->csvFile = $csvFile;
        $this->stats = [
            'total_rows' => 0,
            'successful_inserts' => 0,
            'failed_inserts' => 0,
            'skipped_rows' => 0,
            'errors' => []
        ];
        $this->initializeProgramMapping();
    }

    /**
     * Initialize program mapping from database
     */
    private function initializeProgramMapping() {
        echo "Initializing program mapping...\n";
        
        // Create program mapping query
        $sql = "
            SELECT program_id, 'BS CS' as code FROM program_list WHERE program_name = 'Bachelor of Science in Computer Science'
            UNION ALL
            SELECT program_id, 'BS IT' as code FROM program_list WHERE program_name = 'Bachelor of Science in Information Technology'
            UNION ALL
            SELECT program_id, 'BSIT' as code FROM program_list WHERE program_name = 'Bachelor of Science in Information Technology'
            UNION ALL  
            SELECT program_id, 'BS IS' as code FROM program_list WHERE program_name = 'Bachelor of Science in Information Systems'
            UNION ALL
            SELECT program_id, 'BS APPMATH' as code FROM program_list WHERE program_name = 'Bachelor of Science in Applied Mathematics'
            UNION ALL
            SELECT program_id, 'BS ICT' as code FROM program_list WHERE program_name = 'Bachelor of Science in Information and Communications Technology'
        ";
        
        $result = $this->db->query($sql);
        $this->programMapping = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $this->programMapping[$row['code']] = $row['program_id'];
            }
        }
        
        echo "Program mapping loaded: " . count($this->programMapping) . " mappings\n";
        foreach ($this->programMapping as $code => $id) {
            echo "  - $code => Program ID $id\n";
        }
    }

    /**
     * Prepare the existing_student_info table
     */
    public function prepareTable() {
        echo "\nPreparing existing_student_info table...\n";
        
        try {
            // Add columns if they don't exist
            $this->db->query("ALTER TABLE existing_student_info ADD COLUMN IF NOT EXISTS program_id INT(11)");
            
            // Add foreign key constraint if it doesn't exist
            $checkFK = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                       WHERE TABLE_SCHEMA = DATABASE() 
                       AND TABLE_NAME = 'existing_student_info' 
                       AND REFERENCED_TABLE_NAME = 'program_list'";
            
            $result = $this->db->query($checkFK);
            $row = $result->fetch_assoc();
            
            if ($row['count'] == 0) {
                $this->db->query("ALTER TABLE existing_student_info ADD FOREIGN KEY (program_id) REFERENCES program_list(program_id)");
                echo "Added foreign key constraint to program_list\n";
            } else {
                echo "Foreign key constraint already exists\n";
            }
            
            // Clear existing data
            $this->db->query("DELETE FROM existing_student_info");
            $this->db->query("ALTER TABLE existing_student_info AUTO_INCREMENT = 1");
            echo "Cleared existing data\n";
            
        } catch (Exception $e) {
            echo "Error preparing table: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    /**
     * Import student data from CSV
     */
    public function importData() {
        echo "\nStarting data import...\n";
        
        if (!file_exists($this->csvFile)) {
            throw new Exception("CSV file not found: " . $this->csvFile);
        }
        
        $handle = fopen($this->csvFile, 'r');
        if (!$handle) {
            throw new Exception("Cannot open CSV file: " . $this->csvFile);
        }
        
        // Skip header row
        $header = fgetcsv($handle, 0, ',', '"', '\\');
        echo "CSV Headers: " . implode(', ', $header) . "\n";
        
        // Prepare insert statement (include program column to maintain compatibility)
        // Use INSERT IGNORE to handle duplicate student IDs gracefully
        $insertSQL = "INSERT IGNORE INTO existing_student_info (student_id, last_name, first_name, middle_name, program, program_id, email) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($insertSQL);
        
        // Begin transaction
        $this->db->beginTransaction();
        
        try {
            while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
                $this->stats['total_rows']++;
                
                // Parse CSV row
                $studentId = trim($row[0]);
                $lastName = trim($row[1]);
                $firstName = trim($row[2]);
                $middleName = trim($row[3]);
                $programCode = trim($row[4]);
                $email = trim($row[5]);
                
                // Validate required fields
                if (empty($studentId) || empty($lastName) || empty($firstName) || empty($email)) {
                    $this->stats['skipped_rows']++;
                    $this->stats['errors'][] = "Row {$this->stats['total_rows']}: Missing required fields";
                    continue;
                }
                
                // Get program ID
                if (!isset($this->programMapping[$programCode])) {
                    $this->stats['failed_inserts']++;
                    $this->stats['errors'][] = "Row {$this->stats['total_rows']}: Unknown program code '$programCode' for student $studentId";
                    continue;
                }
                
                $programId = $this->programMapping[$programCode];
                
                // Insert student data (include program code for compatibility)
                $stmt->bind_param("sssssis", $studentId, $lastName, $firstName, $middleName, $programCode, $programId, $email);
                
                if ($stmt->execute()) {
                    $this->stats['successful_inserts']++;
                    if ($this->stats['successful_inserts'] % 50 == 0) {
                        echo "Imported {$this->stats['successful_inserts']} students...\n";
                    }
                } else {
                    $this->stats['failed_inserts']++;
                    if (strpos($stmt->error, 'Duplicate entry') !== false) {
                        $this->stats['errors'][] = "Row {$this->stats['total_rows']}: Duplicate student ID $studentId";
                    } else {
                        $this->stats['errors'][] = "Row {$this->stats['total_rows']}: Database error for student $studentId - " . $stmt->error;
                    }
                }
            }
            
            // Commit transaction
            $this->db->commit();
            echo "Transaction committed successfully\n";
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception("Import failed: " . $e->getMessage());
        } finally {
            fclose($handle);
        }
    }

    /**
     * Verify imported data
     */
    public function verifyData() {
        echo "\nVerifying imported data...\n";
        
        // Count total students
        $result = $this->db->query("SELECT COUNT(*) as total FROM existing_student_info");
        $row = $result->fetch_assoc();
        echo "Total students in database: " . $row['total'] . "\n";
        
        // Count by program
        $sql = "
            SELECT 
                pl.program_name,
                COUNT(*) as student_count
            FROM existing_student_info esi
            JOIN program_list pl ON esi.program_id = pl.program_id
            GROUP BY pl.program_id, pl.program_name
            ORDER BY student_count DESC
        ";
        
        $result = $this->db->query($sql);
        echo "\nStudents by program:\n";
        while ($row = $result->fetch_assoc()) {
            echo "  - {$row['program_name']}: {$row['student_count']} students\n";
        }
        
        // Sample data with hierarchy
        $sql = "
            SELECT 
                esi.student_id,
                esi.last_name,
                esi.first_name,
                pl.program_name,
                d.department_name,
                s.school_name
            FROM existing_student_info esi
            JOIN program_list pl ON esi.program_id = pl.program_id
            JOIN department d ON pl.department_id = d.department_id
            JOIN school s ON d.school_id = s.school_id
            LIMIT 5
        ";
        
        $result = $this->db->query($sql);
        echo "\nSample data with full hierarchy:\n";
        while ($row = $result->fetch_assoc()) {
            echo "  - {$row['student_id']}: {$row['first_name']} {$row['last_name']} | {$row['program_name']} | {$row['department_name']} | {$row['school_name']}\n";
        }
    }

    /**
     * Print import statistics
     */
    public function printStats() {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "IMPORT STATISTICS\n";
        echo str_repeat("=", 50) . "\n";
        echo "Total rows processed: " . $this->stats['total_rows'] . "\n";
        echo "Successful inserts: " . $this->stats['successful_inserts'] . "\n";
        echo "Failed inserts: " . $this->stats['failed_inserts'] . "\n";
        echo "Skipped rows: " . $this->stats['skipped_rows'] . "\n";
        
        if (!empty($this->stats['errors'])) {
            echo "\nErrors encountered:\n";
            foreach ($this->stats['errors'] as $error) {
                echo "  - $error\n";
            }
        }
        
        $successRate = $this->stats['total_rows'] > 0 ? 
            ($this->stats['successful_inserts'] / $this->stats['total_rows']) * 100 : 0;
        echo "\nSuccess rate: " . number_format($successRate, 2) . "%\n";
    }
}

// Main execution
try {
    echo "USC Student Data Import Script\n";
    echo str_repeat("=", 50) . "\n";
    
    $importer = new StudentDataImporter();
    
    // Prepare table
    $importer->prepareTable();
    
    // Import data
    $importer->importData();
    
    // Verify results
    $importer->verifyData();
    
    // Print statistics
    $importer->printStats();
    
    echo "\nImport completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?> 