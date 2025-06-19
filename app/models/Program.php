<?php

class Program extends Model {
    protected $table = 'program_list';

    public function __construct() {
        parent::__construct();
    }

    public function getAllPrograms() {
        $sql = "SELECT 
                    pl.program_id, 
                    pl.program_name, 
                    d.department_name,
                    s.school_name,
                    pl.level_id,
                    pl.department_id
                FROM {$this->table} pl
                JOIN department d ON pl.department_id = d.department_id
                JOIN school s ON d.school_id = s.school_id
                ORDER BY s.school_name, d.department_name, pl.program_name";
        
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getProgramById($programId) {
        $sql = "SELECT 
                    pl.program_id, 
                    pl.program_name, 
                    d.department_name,
                    s.school_name,
                    pl.level_id,
                    pl.department_id
                FROM {$this->table} pl
                JOIN department d ON pl.department_id = d.department_id
                JOIN school s ON d.school_id = s.school_id
                WHERE pl.program_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $programId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getProgramsByDepartment($departmentId) {
        $sql = "SELECT program_id, program_name FROM {$this->table} WHERE department_id = ? ORDER BY program_name";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $departmentId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
} 