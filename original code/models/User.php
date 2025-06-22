<?php

class User extends Model {
    protected $table = 'user_credentials';

    public function __construct() {
        parent::__construct();
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

    public function getUserById($uid) {
        $sql = "SELECT uid, fname, mname, lname, email, profilepicture, emailverified, is_student FROM {$this->table} WHERE uid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $uid);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getUserByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function createUser($data) {
        // Generate GUID for new user
        $uid = $this->generateGUID();
        
        $sql = "INSERT INTO {$this->table} (uid, fname, mname, lname, fullname, email, password, currboundtoken, emailverified, is_student, program_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        $isStudent = isset($data['is_student']) ? (int)$data['is_student'] : 0;
        $programId = isset($data['program_id']) && !empty($data['program_id']) ? (int)$data['program_id'] : 0;
        
        // Correct binding: 8 strings, 2 integers
        $stmt->bind_param("ssssssssii", 
            $uid,
            $data['fname'], 
            $data['mname'], 
            $data['lname'], 
            $data['fullname'], 
            $data['email'], 
            $data['password'], 
            $data['token'],
            $isStudent,
            $programId
        );
        
        if ($stmt->execute()) {
            return $uid; // Return the generated GUID
        }
        return false;
    }

    public function updateUser($uid, $data) {
        $sql = "UPDATE {$this->table} SET fname = ?, mname = ?, lname = ? WHERE uid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssss", $data['fname'], $data['mname'], $data['lname'], $uid);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function updatePassword($uid, $password) {
        $sql = "UPDATE {$this->table} SET password = ? WHERE uid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $password, $uid);
        return $stmt->execute();
    }

    public function updateProfilePicture($uid, $path) {
        $sql = "UPDATE {$this->table} SET profilepicture = ? WHERE uid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $path, $uid);
        
        if ($stmt->execute()) {
            // Update file storage
            $userData = $this->getUserById($uid);
            return true;
        }
        return false;
    }

    // New file-based event participation methods (replacing attendedevents JSON)
    public function addAttendedEvent($uid, $eventId) {
        // Get user data for file storage
        $user = $this->getUserById($uid);
        if (!$user) return false;
        
        // Add to file storage (new system)
        // $this->fileStorage->addParticipantToEvent($eventId, $uid, [
        //     'name' => trim($user['fname'] . ' ' . $user['lname']),
        //     'email' => $user['email']
        // ]);
        
        // Also update database for backward compatibility
        $sql = "UPDATE {$this->table} SET attendedevents = JSON_ARRAY_APPEND(COALESCE(attendedevents, '[]'), '$', ?) WHERE uid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $eventId, $uid);
        return $stmt->execute();
    }

    public function getAttendedEvents($uid) {
        // Use file storage for event participation (new system)
        // $userEvents = $this->fileStorage->getUserEvents($uid);
        $attendedEvents = [];
        
        // foreach ($userEvents as $userEvent) {
        //     $eventId = $userEvent['event_id'];
        //     // Get event details from database
        //     $sql = "SELECT eventid, eventname, startdate, enddate, location, eventshortinfo, eventbadgepath FROM events WHERE eventid = ?";
        //     $stmt = $this->db->prepare($sql);
        //     $stmt->bind_param("i", $eventId);
        //     $stmt->execute();
        //     $result = $stmt->get_result();
        //     $event = $result->fetch_assoc();
            
        //     if ($event) {
        //         $attendedEvents[] = $event;
        //     }
        // }
        
        return $attendedEvents;
    }

    // Legacy methods for backward compatibility
    public function updateEmail($uid, $email, $verificationCode) {
        $sql = "UPDATE {$this->table} SET new_email = ?, verification_code = ?, emailverified = 0 WHERE uid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sss", $email, $verificationCode, $uid);
        return $stmt->execute();
    }

    public function verifyEmail($verificationCode) {
        $sql = "UPDATE {$this->table} SET emailverified = 1, currboundtoken = NULL WHERE currboundtoken = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $verificationCode);
        return $stmt->execute() && $stmt->affected_rows > 0;
    }

    public function setPasswordResetToken($email, $token, $expirySeconds) {
        // Let MySQL handle the timestamp math to avoid PHP/server timezone discrepancies
        $sql = "UPDATE {$this->table} SET password_reset_token = ?, password_reset_expiry = DATE_ADD(NOW(), INTERVAL ? SECOND) WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sis", $token, $expirySeconds, $email);
        return $stmt->execute();
    }

    public function getUserByResetToken($token) {
        $sql = "SELECT * FROM {$this->table} WHERE password_reset_token = ? AND password_reset_expiry > NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function clearResetToken($email) {
        $sql = "UPDATE {$this->table} SET password_reset_token = NULL, password_reset_expiry = NULL WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $email);
        return $stmt->execute();
    }

    public function updateToken($uid, $token) {
        $sql = "UPDATE {$this->table} SET currboundtoken = ?, creationtime = NOW() WHERE uid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $token, $uid);
        return $stmt->execute();
    }

    public function getUserByToken($token) {
        $sql = "SELECT * FROM {$this->table} WHERE currboundtoken = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Deprecated methods (keeping for backward compatibility)
    public function findByEmail($email) {
        return $this->getUserByEmail($email);
    }

    public function create($data) {
        return $this->createUser($data);
    }

    public function update($id, $data) {
        return $this->updateUser($id, $data);
    }

    public function delete($id) {
        try {
            $id = (int)$id;
            $sql = "DELETE FROM users WHERE id = {$id}";
            return $this->db->query($sql);
        } catch (Exception $e) {
            error_log("Error deleting user: " . $e->getMessage());
            throw $e;
        }
    }

    public function findById($id) {
        return $this->getUserById($id);
    }

    public function searchUsers($query, $limit = 10) {
        $like = '%' . $query . '%';
        $sql = "SELECT uid, fname, lname, email, profilepicture FROM {$this->table} WHERE fname LIKE ? OR lname LIKE ? OR email LIKE ? ORDER BY fname ASC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sssi", $like, $like, $like, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $users = [];
        while ($row = $result->fetch_assoc()) {
            // Ensure profile picture has a default
            if (empty($row['profilepicture'])) {
                $row['profilepicture'] = '/public/profilePictures/default.png';
            }
            $users[] = $row;
        }
        return $users;
    }
} 