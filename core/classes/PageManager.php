<?php
class PageManager {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }
    public function getPageStructure($pageName) {
        $page = $this->db->query(
            "SELECT * FROM pages WHERE name = ?", 
            [$pageName]
        )->fetch();
        if (!$page || empty($page['table_name'])) {
            return ['meta' => [], 'components' => []];
        }
        $tableName = $page['table_name'];
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
            throw new Exception('Invalid page table name.');
        }
        $sql = "SELECT * FROM `$tableName` WHERE is_active = 1 ORDER BY order_num ASC";
        $components = $this->db->query($sql)->fetchAll();

        return [
            'meta' => $page,
            'components' => $components
        ];
    }
    public function renderComponent($block) {
        $type = $block['block_type'];
        $content = $block['content'];
        $filePath = ROOT_DIR . "/components/sections/{$type}.php";
        $block_name = $block['block_name'];
        $order_num = $block['order_num'];
        $data = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $block_content = $data;
        } else {
            $block_content = $content;
        }
        extract([
            'block_name' => $block_name,
            'block_content' => $block_content,
            'order_num' => $order_num,
            'block' => $block,
        ]);
        if (file_exists($filePath)) {
            extract(['content' => $content]);
            ob_start();
            include $filePath;
            return ob_get_clean();
        } else {
            return "<!-- Missing component template for type: {$type} -->";
        }
    }
}
