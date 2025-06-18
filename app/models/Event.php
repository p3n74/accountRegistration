<?php

class Event extends Model {
    protected $table = 'events';
    private $fileStorage;

    public function __construct() {
        parent::__construct();
        require_once '../app/core/FileStorage.php';
        $this->fileStorage = new FileStorage();
    }

    public function getEventById($eventId) {
        $sql = "SELECT * FROM {$this->table} WHERE eventid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        $result = $stmt->get_result();
        $event = $result->fetch_assoc();
        
        if ($event) {
            // Sync to file storage
            $this->syncEventToFile($eventId, $event);
            
            // Get participant count from file storage (new system)
            $participants = $this->fileStorage->getEventParticipants($eventId);
            $event['participant_count_file'] = count($participants);
            $event['participants'] = $participants;
        }
        
        return $event;
    }

    public function getEventsByCreator($creatorId) {
        $sql = "SELECT * FROM {$this->table} WHERE eventcreator = ? ORDER BY startdate DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $creatorId);
        $stmt->execute();
        $result = $stmt->get_result();
        $events = [];
        
        while ($row = $result->fetch_assoc()) {
            // Get participant count from file storage
            $participants = $this->fileStorage->getEventParticipants($row['eventid']);
            $row['participant_count_file'] = count($participants);
            $events[] = $row;
        }
        
        return $events;
    }

    public function createEvent($data) {
        $sql = "INSERT INTO {$this->table} (eventname, startdate, enddate, location, eventshortinfo, eventcreator, eventkey, participantcount) VALUES (?, ?, ?, ?, ?, ?, ?, 0)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sssssss", 
            $data['eventname'], 
            $data['startdate'], 
            $data['enddate'], 
            $data['location'], 
            $data['eventshortinfo'], 
            $data['eventcreator'], 
            $data['eventkey']
        );
        
        if ($stmt->execute()) {
            $eventId = $this->db->getLastId();
            
            // Create event file for new storage system
            $eventFileData = [
                'eventid' => $eventId,
                'eventname' => $data['eventname'],
                'startdate' => $data['startdate'],
                'enddate' => $data['enddate'],
                'location' => $data['location'],
                'eventshortinfo' => $data['eventshortinfo'],
                'eventcreator' => $data['eventcreator'],
                'eventkey' => $data['eventkey'],
                'created_at' => date('Y-m-d H:i:s'),
                'last_updated' => date('Y-m-d H:i:s'),
                'settings' => [
                    'registration_open' => true,
                    'max_participants' => null,
                    'require_approval' => false
                ]
            ];
            
            $this->fileStorage->saveEventData($eventId, $eventFileData);
            return $eventId;
        }
        return false;
    }

    public function updateEvent($eventId, $data) {
        $sql = "UPDATE {$this->table} SET eventname = ?, startdate = ?, enddate = ?, location = ?, eventshortinfo = ? WHERE eventid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sssssi", 
            $data['eventname'], 
            $data['startdate'], 
            $data['enddate'], 
            $data['location'], 
            $data['eventshortinfo'], 
            $eventId
        );
        
        if ($stmt->execute()) {
            // Update file storage
            $event = $this->getEventById($eventId);
            $this->syncEventToFile($eventId, $event);
            return true;
        }
        return false;
    }

    public function deleteEvent($eventId) {
        $sql = "DELETE FROM {$this->table} WHERE eventid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $eventId);
        
        if ($stmt->execute()) {
            // Clean up file storage
            $this->fileStorage->deleteEventData($eventId);
            return true;
        }
        return false;
    }

    public function getEventByName($eventName) {
        $sql = "SELECT * FROM {$this->table} WHERE eventname = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $eventName);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getEventByKey($eventKey) {
        $sql = "SELECT * FROM {$this->table} WHERE eventkey = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $eventKey);
        $stmt->execute();
        $result = $stmt->get_result();
        $event = $result->fetch_assoc();
        
        if ($event) {
            // Get participant count from file storage
            $participants = $this->fileStorage->getEventParticipants($event['eventid']);
            $event['participant_count_file'] = count($participants);
            $event['participants'] = $participants;
        }
        
        return $event;
    }

    public function incrementParticipantCount($eventId) {
        $sql = "UPDATE {$this->table} SET participantcount = participantcount + 1 WHERE eventid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $eventId);
        return $stmt->execute();
    }

    public function decrementParticipantCount($eventId) {
        $sql = "UPDATE {$this->table} SET participantcount = GREATEST(participantcount - 1, 0) WHERE eventid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $eventId);
        return $stmt->execute();
    }

    // New file-based participant management (replaces event_participants table)
    public function addParticipant($eventId, $uid) {
        // Get user data
        require_once '../app/models/User.php';
        $userModel = new User();
        $user = $userModel->getUserById($uid);
        
        if (!$user) return false;
        
        // Add to file storage
        $success = $this->fileStorage->addParticipantToEvent($eventId, $uid, [
            'name' => trim($user['fname'] . ' ' . $user['lname']),
            'email' => $user['email'],
            'profile_picture' => $user['profilepicture']
        ]);
        
        if ($success) {
            // Update participant count in database
            $this->updateParticipantCount($eventId);
            
            // Also add to user's attended events
            $userModel->addAttendedEvent($uid, $eventId);
        }
        
        return $success;
    }

    public function removeParticipant($eventId, $uid) {
        $success = $this->fileStorage->removeParticipantFromEvent($eventId, $uid);
        
        if ($success) {
            // Update participant count in database
            $this->updateParticipantCount($eventId);
        }
        
        return $success;
    }

    public function getEventParticipants($eventId) {
        return $this->fileStorage->getEventParticipants($eventId);
    }

    public function isUserParticipant($eventId, $uid) {
        return $this->fileStorage->isUserParticipant($eventId, $uid);
    }

    private function updateParticipantCount($eventId) {
        $participants = $this->fileStorage->getEventParticipants($eventId);
        $count = count($participants);
        
        $sql = "UPDATE {$this->table} SET participantcount = ? WHERE eventid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $count, $eventId);
        $stmt->execute();
    }

    private function syncEventToFile($eventId, $eventData) {
        $fileData = [
            'eventid' => $eventId,
            'eventname' => $eventData['eventname'] ?? '',
            'startdate' => $eventData['startdate'] ?? '',
            'enddate' => $eventData['enddate'] ?? '',
            'location' => $eventData['location'] ?? '',
            'eventshortinfo' => $eventData['eventshortinfo'] ?? '',
            'eventcreator' => $eventData['eventcreator'] ?? '',
            'eventkey' => $eventData['eventkey'] ?? '',
            'eventbadgepath' => $eventData['eventbadgepath'] ?? '',
            'eventinfopath' => $eventData['eventinfopath'] ?? '',
            'last_updated' => date('Y-m-d H:i:s'),
            'settings' => [
                'registration_open' => true,
                'max_participants' => null,
                'require_approval' => false
            ]
        ];
        
        $this->fileStorage->saveEventData($eventId, $fileData);
    }

    // Legacy methods for backward compatibility with old event_participants table
    public function joinEvent($eventId, $uid) {
        return $this->addParticipant($eventId, $uid);
    }

    public function leaveEvent($eventId, $uid) {
        return $this->removeParticipant($eventId, $uid);
    }

    // Search and filter methods
    public function searchEvents($query) {
        $sql = "SELECT * FROM {$this->table} WHERE eventname LIKE ? OR eventshortinfo LIKE ? ORDER BY startdate DESC";
        $searchTerm = "%{$query}%";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        $events = [];
        
        while ($row = $result->fetch_assoc()) {
            // Get participant count from file storage
            $participants = $this->fileStorage->getEventParticipants($row['eventid']);
            $row['participant_count_file'] = count($participants);
            $events[] = $row;
        }
        
        return $events;
    }

    public function getUpcomingEvents($limit = 10) {
        $sql = "SELECT * FROM {$this->table} WHERE startdate > NOW() ORDER BY startdate ASC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $events = [];
        
        while ($row = $result->fetch_assoc()) {
            // Get participant count from file storage
            $participants = $this->fileStorage->getEventParticipants($row['eventid']);
            $row['participant_count_file'] = count($participants);
            $events[] = $row;
        }
        
        return $events;
    }

    public function getPastEvents($limit = 10) {
        $sql = "SELECT * FROM {$this->table} WHERE enddate < NOW() ORDER BY enddate DESC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $events = [];
        
        while ($row = $result->fetch_assoc()) {
            // Get participant count from file storage
            $participants = $this->fileStorage->getEventParticipants($row['eventid']);
            $row['participant_count_file'] = count($participants);
            $events[] = $row;
        }
        
        return $events;
    }

    // Utility methods
    public function generateEventKey() {
        return strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
    }

    public function getAllEvents() {
        $sql = "SELECT * FROM {$this->table} ORDER BY startdate DESC";
        $result = $this->db->query($sql);
        $events = [];
        
        while ($row = $result->fetch_assoc()) {
            // Get participant count from file storage
            $participants = $this->fileStorage->getEventParticipants($row['eventid']);
            $row['participant_count_file'] = count($participants);
            $events[] = $row;
        }
        
        return $events;
    }
} 