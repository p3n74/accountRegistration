<?php

class User extends Model {
    protected $table = 'user_credentials';

    public function getUserById($uid) {
        $sql = "SELECT uid, fname, mname, lname, email, profilepicture, emailverified FROM {$this->table} WHERE uid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $uid);
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
        return $stmt->execute();
    }

    public function updateUser($uid, $data) {
        $sql = "UPDATE {$this->table} SET fname = ?, mname = ?, lname = ? WHERE uid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sssi", $data['fname'], $data['mname'], $data['lname'], $uid);
        return $stmt->execute();
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
        return $stmt->execute();
    }

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

    public function addAttendedEvent($uid, $eventId) {
        $sql = "UPDATE {$this->table} SET attendedevents = JSON_ARRAY_APPEND(attendedevents, '$', ?) WHERE uid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $eventId, $uid);
        return $stmt->execute();
    }

    public function getAttendedEvents($uid) {
        $sql = "WITH user_events AS (
            SELECT JSON_UNQUOTE(JSON_EXTRACT(attendedevents, CONCAT('$[', n.n, ']'))) AS eventid
            FROM {$this->table} u
            JOIN (
                SELECT 0 AS n UNION ALL SELECT 1 UNION ALL SELECT 2 
                UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 
                UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 
                UNION ALL SELECT 9
            ) AS n
            WHERE u.uid = ?
            AND JSON_UNQUOTE(JSON_EXTRACT(u.attendedevents, CONCAT('$[', n.n, ']'))) IS NOT NULL
        )
        SELECT e.eventid, e.eventname, e.startdate, e.enddate, e.location, e.eventshortinfo, e.eventbadgepath
        FROM events e
        JOIN user_events ue ON e.eventid = ue.eventid";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
} 