<?php
class PageManager {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get the structure and content blocks for a page.
     * @param string $pageName e.g. 'index', 'members', etc.
     * @return array ['meta' => [...], 'components' => [...]]
     */
    public function getPageStructure($pageName) {
        // Fetch page metadata (title, description, table_name, etc.)
        $page = $this->db->query(
            "SELECT * FROM pages WHERE name = ?", 
            [$pageName]
        )->fetch();

        if (!$page || empty($page['table_name'])) {
            return ['meta' => [], 'components' => []];
        }

        // Fetch all blocks/components for this page from its own table
        $tableName = $page['table_name'];
        // Security: Only allow alphanumeric and underscore table names
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

    /**
     * Render a component/block according to its type.
     * @param array $block One row from the page's table
     * @return string Rendered HTML
     */
    public function renderComponent($block) {
        $type = $block['block_type'];
        $content = $block['content'];

        // Path to component template
        $filePath = __DIR__ . "/../../public/components/sections/{$type}.php";

        // Prepare variables for the template
        $block_name = $block['block_name'];
        $order_num = $block['order_num'];

        // Try to decode JSON, fallback to string
        $data = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $block_content = $data;
        } else {
            $block_content = $content;
        }

        // Extract variables for use in the component template
        extract([
            'block_name' => $block_name,
            'block_content' => $block_content,
            'order_num' => $order_num,
            'block' => $block, // for advanced usage
        ]);

        // Render the component template if it exists
        if (file_exists($filePath)) {
            ob_start();
            include $filePath;
            return ob_get_clean();
        } else {
            // Fallback: simple output
            return "<!-- Missing component template for type: {$type} -->";
        }
    }
}
