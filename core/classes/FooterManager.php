<?php
class FooterManager {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function getFooterContent() {
        $sections = $this->db->query(
            "SELECT section_type, content 
             FROM footer 
             WHERE is_active = 1
             ORDER BY order_num ASC"
        )->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);

        return array_map(function($item) {
            return json_decode($item['content'], true);
        }, $sections);
    }
}
