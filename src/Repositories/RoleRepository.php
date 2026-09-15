<?php

class RoleRepository {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function findById($id) {
        $roles = $this->db->select('roles', ['role_id' => $id]);
        return !empty($roles) ? $roles[0] : null;
    }
}
