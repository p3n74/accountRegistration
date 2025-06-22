<?php

class ExistingStudent extends Model {
    protected $table = 'existing_student_info';

    public function __construct() {
        parent::__construct();
    }

    public function getStudentByEmail($email) {
        $sql = "SELECT 
                    esi.first_name as fname, 
                    esi.middle_name as mname, 
                    esi.last_name as lname, 
                    esi.email,
                    esi.program_id,
                    pl.program_name,
                    d.department_name,
                    s.school_name
                FROM {$this->table} esi
                LEFT JOIN program_list pl ON esi.program_id = pl.program_id
                LEFT JOIN department d ON pl.department_id = d.department_id
                LEFT JOIN school s ON d.school_id = s.school_id
                WHERE esi.email = ?";
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