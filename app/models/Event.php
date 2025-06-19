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
        $stmt->bind_param("s", $eventId);
        $stmt->execute();
        $result = $stmt->get_result();
        $event = $result->fetch_assoc();
        
        if ($event) {
            // Fetch participants from DB
            $participants = $this->getEventParticipants($eventId);
            $event['participant_count_file'] = count($participants);
            $event['participants'] = $participants;
        }
        
        return $event;
    }

    public function getEventsByCreator($creatorId) {
        $sql = "SELECT * FROM {$this->table} WHERE eventcreator = ? ORDER BY startdate DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $creatorId);
        $stmt->execute();
        $result = $stmt->get_result();
        $events = [];
        
        while ($row = $result->fetch_assoc()) {
            // Fetch participants from DB
            $participants = $this->getEventParticipants($row['eventid']);
            $row['participant_count_file'] = count($participants);
            $events[] = $row;
        }
        
        return $events;
    }

    public function createEvent($data) {
        // Generate GUID for the new event
        $eventId = $this->generateGUID();

        $sql = "INSERT INTO {$this->table} (eventid, eventname, startdate, enddate, location, eventshortinfo, eventcreator, eventkey, participantcount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssssssss", 
            $eventId,
            $data['eventname'], 
            $data['startdate'], 
            $data['enddate'], 
            $data['location'], 
            $data['eventshortinfo'], 
            $data['eventcreator'], 
            $data['eventkey']
        );
        
        if ($stmt->execute()) {
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
        $stmt->bind_param("ssssss", 
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
        $stmt->bind_param("s", $eventId);
        
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
            // Fetch participants from DB
            $participants = $this->getEventParticipants($event['eventid']);
            $event['participant_count_file'] = count($participants);
            $event['participants'] = $participants;
        }
        
        return $event;
    }

    public function incrementParticipantCount($eventId) {
        $sql = "UPDATE {$this->table} SET participantcount = participantcount + 1 WHERE eventid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $eventId);
        return $stmt->execute();
    }

    public function decrementParticipantCount($eventId) {
        $sql = "UPDATE {$this->table} SET participantcount = GREATEST(participantcount - 1, 0) WHERE eventid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $eventId);
        return $stmt->execute();
    }

    /* ---------------- participant management (DB) ---------------- */
    public function addParticipant(string $eventId, ?string $uid = null, ?string $email = null, int $attendanceStatus = 1) {
        // resolve email if uid provided
        require_once '../app/models/User.php';
        $userModel = new User();
        $registered = 0;
        if ($uid) {
            $user = $userModel->getUserById($uid);
            if (!$user) return false;
            $email = $user['email'];
            $registered = 1;
        }
        if (!$email) return false;

        $pid = $this->generateGUID();
        $sql = "INSERT IGNORE INTO event_participants (participant_id, event_id, uid, email, registered, attendance_status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssssii", $pid, $eventId, $uid, $email, $registered, $attendanceStatus);
        $ok = $stmt->execute();

        if ($ok && $stmt->affected_rows) {
            $this->updateParticipantCount($eventId);
            if ($registered) {
                $userModel->addAttendedEvent($uid, $eventId);
            }
        }
        return ($ok && $stmt->affected_rows) ? $pid : false;
    }

    public function removeParticipant(string $eventId, ?string $participantId = null, ?string $email = null): bool {
        if (!$participantId && !$email) return false;
        if ($participantId) {
            $sql = "DELETE FROM event_participants WHERE event_id = ? AND participant_id = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ss", $eventId, $participantId);
        } else {
            $sql = "DELETE FROM event_participants WHERE event_id = ? AND email = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ss", $eventId, $email);
        }
        $stmt->execute();
        if ($stmt->affected_rows) {
            $this->updateParticipantCount($eventId);
            return true;
        }
        return false;
    }

    public function getEventParticipants(string $eventId, ?int $statusFilter=null): array {
        $sql = "SELECT ep.participant_id, ep.uid, ep.email, ep.registered, ep.joined_at, ep.attendance_status, u.fname, u.lname, u.profilepicture FROM event_participants ep LEFT JOIN user_credentials u ON u.uid = ep.uid WHERE ep.event_id = ?";
        if($statusFilter !== null){
            $sql .= " AND ep.attendance_status = ?";
        }
        $sql .= " ORDER BY ep.joined_at ASC";
        if($statusFilter !== null){
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("si", $eventId, $statusFilter);
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("s", $eventId);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $out = [];
        while ($row = $res->fetch_assoc()) {
            $key = $row['uid'] ?: $row['email'];
            $row['name'] = $row['fname'] ? trim($row['fname'].' '.$row['lname']) : $row['email'];
            $out[$key] = [
                'participant_id' => $row['participant_id'],
                'name' => $row['name'],
                'email' => $row['email'],
                'profile_picture' => $row['profilepicture'] ?? '',
                'joined_at' => $row['joined_at'],
                'registered' => (bool)$row['registered'],
                'attendance_status' => intval($row['attendance_status'])
            ];
        }
        return $out;
    }

    public function isUserParticipant(string $eventId, string $uid): bool {
        $sql = "SELECT 1 FROM event_participants WHERE event_id = ? AND uid = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $eventId, $uid);
        $stmt->execute();
        return (bool)$stmt->get_result()->num_rows;
    }

    private function updateParticipantCount(string $eventId): void {
        $sql = "UPDATE events SET participantcount = (SELECT COUNT(*) FROM event_participants WHERE event_id = ?) WHERE eventid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $eventId, $eventId);
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
            // Fetch participants from DB
            $participants = $this->getEventParticipants($row['eventid']);
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
            // Fetch participants from DB
            $participants = $this->getEventParticipants($row['eventid']);
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
            // Fetch participants from DB
            $participants = $this->getEventParticipants($row['eventid']);
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
            // Fetch participants from DB
            $participants = $this->getEventParticipants($row['eventid']);
            $row['participant_count_file'] = count($participants);
            $events[] = $row;
        }
        
        return $events;
    }

    private function generateGUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    public function updateAttendanceStatus(string $eventId, ?string $participantId = null, ?string $email = null, int $newStatus = 0): bool {
        if (!$participantId && !$email) return false;
        if ($participantId) {
            $sql = "UPDATE event_participants SET attendance_status = ? WHERE event_id = ? AND participant_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("iss", $newStatus, $eventId, $participantId);
        } else {
            $sql = "UPDATE event_participants SET attendance_status = ? WHERE event_id = ? AND email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("iss", $newStatus, $eventId, $email);
        }
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
} 