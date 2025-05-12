<?php
class Navigation {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }
    public function getMenu() {
        return $this->db->query("
            SELECT m.id, m.title, p.name AS page_name 
            FROM menus m
            JOIN pages p ON m.page_id = p.id
            ORDER BY m.order_num ASC
        ")->fetchAll();
    }
}