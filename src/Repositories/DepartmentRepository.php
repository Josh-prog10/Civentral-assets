<?php

class DepartmentRepository {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function findById($id) {
        $departments = $this->db->select('departments', ['department_id' => $id]);
        return !empty($departments) ? $departments[0] : null;
    }
}
