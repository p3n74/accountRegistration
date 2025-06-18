<?php

class User extends Model {
    protected $table = 'user_credentials';
    private $fileStorage;

    public function __construct() {
        parent::__construct();
        require_once '../app/core/FileStorage.php';
        $this->fileStorage = new FileStorage();
    }

    public function getUserById($uid) {
        $sql = "SELECT uid, fname, mname, lname, email, profilepicture, emailverified FROM {$this->table} WHERE uid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        // Sync user data to file storage for future migration
        if ($user) {
            $this->syncUserToFile($uid, $user);
        }
        
        return $user;
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
        $sql = "INSERT INTO {$this->table} (fname, mname, lname, fullname, email, password, currboundtoken, emailverified) VALUES (?, ?, ?, ?, ?, ?, ?, 0)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sssssss", 
            $data['fname'], 
            $data['mname'], 
            $data['lname'], 
            $data['fullname'], 
            $data['email'], 
            $data['password'], 
            $data['token']
        );
        
        if ($stmt->execute()) {
            $uid = $this->db->getLastId();
            // Create user file for future migration
            $this->syncUserToFile($uid, $data);
            return true;
        }
        return false;
    }

    public function updateUser($uid, $data) {
        $sql = "UPDATE {$this->table} SET fname = ?, mname = ?, lname = ? WHERE uid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sssi", $data['fname'], $data['mname'], $data['lname'], $uid);
        
        if ($stmt->execute()) {
            // Update file storage
            $this->syncUserToFile($uid, $data);
            return true;
        }
        return false;
    }

    public function updatePassword($uid, $password) {
        $sql = "UPDATE {$this->table} SET password = ? WHERE uid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $password, $uid);
        return $stmt->execute();
    }

    public function updateProfilePicture($uid, $path) {
        $sql = "UPDATE {$this->table} SET profilepicture = ? WHERE uid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $path, $uid);
        
        if ($stmt->execute()) {
            // Update file storage
            $userData = $this->getUserById($uid);
            $this->syncUserToFile($uid, $userData);
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
        $this->fileStorage->addParticipantToEvent($eventId, $uid, [
            'name' => trim($user['fname'] . ' ' . $user['lname']),
            'email' => $user['email']
        ]);
        
        // Also update database for backward compatibility
        $sql = "UPDATE {$this->table} SET attendedevents = JSON_ARRAY_APPEND(COALESCE(attendedevents, '[]'), '$', ?) WHERE uid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $eventId, $uid);
        return $stmt->execute();
    }

    public function getAttendedEvents($uid) {
        // Use file storage for event participation (new system)
        $userEvents = $this->fileStorage->getUserEvents($uid);
        $attendedEvents = [];
        
        foreach ($userEvents as $userEvent) {
            $eventId = $userEvent['event_id'];
            // Get event details from database
            $sql = "SELECT eventid, eventname, startdate, enddate, location, eventshortinfo, eventbadgepath FROM events WHERE eventid = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $eventId);
            $stmt->execute();
            $result = $stmt->get_result();
            $event = $result->fetch_assoc();
            
            if ($event) {
                $attendedEvents[] = $event;
            }
        }
        
        return $attendedEvents;
    }

    // Private method to sync user data to file storage
    private function syncUserToFile($uid, $userData) {
        $fileData = [
            'uid' => $uid,
            'fname' => $userData['fname'] ?? '',
            'mname' => $userData['mname'] ?? '',
            'lname' => $userData['lname'] ?? '',
            'email' => $userData['email'] ?? '',
            'profilepicture' => $userData['profilepicture'] ?? '',
            'emailverified' => $userData['emailverified'] ?? 0,
            'last_updated' => date('Y-m-d H:i:s'),
            'created_at' => $userData['creationtime'] ?? date('Y-m-d H:i:s')
        ];
        
        $this->fileStorage->saveUserData($uid, $fileData);
    }

    // Legacy methods for backward compatibility
    public function updateEmail($uid, $email, $verificationCode) {
        $sql = "UPDATE {$this->table} SET new_email = ?, verification_code = ?, emailverified = 0 WHERE uid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssi", $email, $verificationCode, $uid);
        return $stmt->execute();
    }

    public function verifyEmail($verificationCode) {
        $sql = "UPDATE {$this->table} SET email = new_email, verification_code = NULL, emailverified = 1 WHERE verification_code = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $verificationCode);
        return $stmt->execute();
    }

    public function setPasswordResetToken($email, $token, $expiry) {
        $sql = "UPDATE {$this->table} SET password_reset_token = ?, password_reset_expiry = ? WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sss", $token, $expiry, $email);
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
        $stmt->bind_param("si", $token, $uid);
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
} 