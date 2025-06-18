<?php
// Local database connection for migration
$servername = "127.0.0.1";
$username = "s21102134_palisade"; 
$password = "webwebwebweb";
$dbname = "s21102134_palisade";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function generateGUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

echo "Starting UID to GUID migration...\n";

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Step 1: Add a temporary GUID column
    echo "Adding temporary GUID column...\n";
    $conn->query("ALTER TABLE user_credentials ADD COLUMN new_uid VARCHAR(36) NULL");
    
    // Step 2: Generate GUIDs for existing users
    echo "Generating GUIDs for existing users...\n";
    $result = $conn->query("SELECT uid FROM user_credentials WHERE new_uid IS NULL");
    
    $updateStmt = $conn->prepare("UPDATE user_credentials SET new_uid = ? WHERE uid = ?");
    $count = 0;
    
    while ($row = $result->fetch_assoc()) {
        $guid = generateGUID();
        $updateStmt->bind_param("si", $guid, $row['uid']);
        $updateStmt->execute();
        $count++;
        echo "Migrated user {$row['uid']} to GUID: $guid\n";
    }
    
    echo "Migrated $count existing users to GUIDs.\n";
    
    // Step 3: Update any foreign key references (if they exist)
    // Check for tables that might reference user_credentials.uid
    $foreignKeyTables = [];
    
    // Check events table for eventcreator field
    $eventsCheck = $conn->query("SHOW COLUMNS FROM events LIKE 'eventcreator'");
    if ($eventsCheck && $eventsCheck->num_rows > 0) {
        echo "Updating events.eventcreator column type first...\n";
        
        // Drop foreign key constraint if it exists
        $fkResult = $conn->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'events' AND COLUMN_NAME = 'eventcreator' AND CONSTRAINT_SCHEMA = DATABASE()");
        if ($fkResult && $fkResult->num_rows > 0) {
            $fkRow = $fkResult->fetch_assoc();
            $constraintName = $fkRow['CONSTRAINT_NAME'];
            echo "Dropping foreign key constraint: $constraintName\n";
            $conn->query("ALTER TABLE events DROP FOREIGN KEY $constraintName");
        }
        
        $conn->query("ALTER TABLE events MODIFY eventcreator VARCHAR(36)");
        echo "Updated events.eventcreator column type.\n";
        
        echo "Updating events.eventcreator references...\n";
        
        // Get mapping of old uid to new_uid
        $mappingResult = $conn->query("SELECT uid, new_uid FROM user_credentials");
        $uidMapping = [];
        while ($row = $mappingResult->fetch_assoc()) {
            $uidMapping[$row['uid']] = $row['new_uid'];
        }
        
        // Update events table
        $eventsResult = $conn->query("SELECT eventid, eventcreator FROM events WHERE eventcreator IS NOT NULL");
        $updateEventsStmt = $conn->prepare("UPDATE events SET eventcreator = ? WHERE eventid = ?");
        
        while ($eventRow = $eventsResult->fetch_assoc()) {
            if (isset($uidMapping[$eventRow['eventcreator']])) {
                $newCreatorGuid = $uidMapping[$eventRow['eventcreator']];
                $updateEventsStmt->bind_param("si", $newCreatorGuid, $eventRow['eventid']);
                $updateEventsStmt->execute();
                echo "Updated event {$eventRow['eventid']} creator to GUID: $newCreatorGuid\n";
            }
        }
    }
    
    // Step 4: Drop the old uid column and rename new_uid to uid
    echo "Dropping old uid column and renaming new_uid...\n";
    
    // First, remove auto_increment from uid column if it exists
    $conn->query("ALTER TABLE user_credentials MODIFY uid INT(11) NOT NULL");
    $conn->query("ALTER TABLE user_credentials DROP PRIMARY KEY");
    $conn->query("ALTER TABLE user_credentials DROP COLUMN uid");
    $conn->query("ALTER TABLE user_credentials CHANGE new_uid uid VARCHAR(36) NOT NULL");
    $conn->query("ALTER TABLE user_credentials ADD PRIMARY KEY (uid)");
    
    // Commit transaction
    $conn->commit();
    
    echo "Migration completed successfully!\n";
    echo "All user UIDs have been converted to GUIDs.\n";
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo "Migration failed: " . $e->getMessage() . "\n";
    echo "All changes have been rolled back.\n";
    exit(1);
}

$conn->close();
echo "Migration script finished.\n";
?> 