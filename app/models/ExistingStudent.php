<?php

class ExistingStudent extends Model {
    protected $table = 'existing_student_info';

    public function __construct() {
        parent::__construct();
    }

    public function getStudentByEmail($email) {
        $sql = "SELECT first_name as fname, middle_name as mname, last_name as lname, email FROM {$this->table} WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function studentExists($email) {
        return $this->getStudentByEmail($email) !== null;
    }
} 