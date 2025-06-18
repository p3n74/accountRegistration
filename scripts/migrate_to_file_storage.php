<?php
/**
 * Migration script to move existing data to file storage system
 * Run this script to migrate existing users and events to the new file-based storage
 */

// Include necessary files
require_once '../app/core/Database.php';
require_once '../app/core/FileStorage.php';
require_once '../app/config/config.php';

echo "Starting migration to file storage system...\n";

try {
    $db = new Database();
    $fileStorage = new FileStorage();
    
    // Migrate Users
    echo "Migrating users...\n";
    $sql = "SELECT * FROM user_credentials";
    $result = $db->query($sql);
    $userCount = 0;
    
    while ($user = $result->fetch_assoc()) {
        $fileData = [
            'uid' => $user['uid'],
            'fname' => $user['fname'] ?? '',
            'mname' => $user['mname'] ?? '',
            'lname' => $user['lname'] ?? '',
            'email' => $user['email'] ?? '',
            'profilepicture' => $user['profilepicture'] ?? '',
            'emailverified' => $user['emailverified'] ?? 0,
            'created_at' => $user['creationtime'] ?? date('Y-m-d H:i:s'),
            'last_updated' => date('Y-m-d H:i:s'),
            'migration_date' => date('Y-m-d H:i:s')
        ];
        
        if ($fileStorage->saveUserData($user['uid'], $fileData)) {
            $userCount++;
            echo "Migrated user: {$user['email']}\n";
        } else {
            echo "Failed to migrate user: {$user['email']}\n";
        }
    }
    
    // Migrate Events
    echo "Migrating events...\n";
    $sql = "SELECT * FROM events";
    $result = $db->query($sql);
    $eventCount = 0;
    
    while ($event = $result->fetch_assoc()) {
        $fileData = [
            'eventid' => $event['eventid'],
            'eventname' => $event['eventname'] ?? '',
            'startdate' => $event['startdate'] ?? '',
            'enddate' => $event['enddate'] ?? '',
            'location' => $event['location'] ?? '',
            'eventshortinfo' => $event['eventshortinfo'] ?? '',
            'eventcreator' => $event['eventcreator'] ?? '',
            'eventkey' => $event['eventkey'] ?? '',
            'eventbadgepath' => $event['eventbadgepath'] ?? '',
            'eventinfopath' => $event['eventinfopath'] ?? '',
            'last_updated' => date('Y-m-d H:i:s'),
            'migration_date' => date('Y-m-d H:i:s'),
            'settings' => [
                'registration_open' => true,
                'max_participants' => null,
                'require_approval' => false
            ]
        ];
        
        if ($fileStorage->saveEventData($event['eventid'], $fileData)) {
            $eventCount++;
            echo "Migrated event: {$event['eventname']}\n";
        } else {
            echo "Failed to migrate event: {$event['eventname']}\n";
        }
    }
    
    // Migrate Event Participants (from attendedevents JSON field)
    echo "Migrating event participants...\n";
    $sql = "SELECT uid, attendedevents FROM user_credentials WHERE attendedevents IS NOT NULL AND attendedevents != '[]'";
    $result = $db->query($sql);
    $participantCount = 0;
    
    while ($userRow = $result->fetch_assoc()) {
        $uid = $userRow['uid'];
        $attendedEvents = json_decode($userRow['attendedevents'], true);
        
        if (is_array($attendedEvents)) {
            foreach ($attendedEvents as $eventId) {
                // Get user data for participant info
                $userSql = "SELECT fname, lname, email, profilepicture FROM user_credentials WHERE uid = ?";
                $userStmt = $db->prepare($userSql);
                $userStmt->bind_param("i", $uid);
                $userStmt->execute();
                $userResult = $userStmt->get_result();
                $userData = $userResult->fetch_assoc();
                
                if ($userData) {
                    $participantData = [
                        'name' => trim($userData['fname'] . ' ' . $userData['lname']),
                        'email' => $userData['email'],
                        'profile_picture' => $userData['profilepicture'],
                        'migrated_from_db' => true
                    ];
                    
                    if ($fileStorage->addParticipantToEvent($eventId, $uid, $participantData)) {
                        $participantCount++;
                        echo "Migrated participant: {$userData['email']} -> Event {$eventId}\n";
                    }
                }
            }
        }
    }
    
    // Migrate from event_participants table if it exists
    echo "Checking for event_participants table...\n";
    $sql = "SHOW TABLES LIKE 'event_participants'";
    $result = $db->query($sql);
    
    if ($result->num_rows > 0) {
        echo "Migrating from event_participants table...\n";
        $sql = "SELECT ep.*, u.fname, u.lname, u.email, u.profilepicture 
                FROM event_participants ep 
                JOIN user_credentials u ON ep.uid = u.uid";
        $result = $db->query($sql);
        
        while ($row = $result->fetch_assoc()) {
            $participantData = [
                'name' => trim($row['fname'] . ' ' . $row['lname']),
                'email' => $row['email'],
                'profile_picture' => $row['profilepicture'],
                'migrated_from_participants_table' => true
            ];
            
            if ($fileStorage->addParticipantToEvent($row['eventid'], $row['uid'], $participantData)) {
                $participantCount++;
                echo "Migrated participant from table: {$row['email']} -> Event {$row['eventid']}\n";
            }
        }
    }
    
    echo "\n=== Migration Complete ===\n";
    echo "Users migrated: {$userCount}\n";
    echo "Events migrated: {$eventCount}\n";
    echo "Participants migrated: {$participantCount}\n";
    echo "\nFile storage directories created:\n";
    echo "- storage/users/\n";
    echo "- storage/events/\n";
    echo "- storage/participants/\n";
    echo "\nYou can now safely deprecate the event_participants table.\n";
    echo "The attendedevents JSON field in user_credentials is now redundant.\n";
    
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
} 