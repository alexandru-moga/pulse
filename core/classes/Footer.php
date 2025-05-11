<?php
class Footer {
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
        )->fetchAll(PDO::FETCH_ASSOC);

        $footerContent = [];
        foreach ($sections as $section) {
            $footerContent[$section['section_type']] = json_decode($section['content'], true);
        }
        return $footerContent;
    }
}
