<?php

class PositionRepository {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function findById($id) {
        $positions = $this->db->select('positions', ['position_id' => $id]);
        return !empty($positions) ? $positions[0] : null;
    }
}
