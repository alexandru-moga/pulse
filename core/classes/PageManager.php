<?php
class PageManager
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }
    public function getPageStructure($pageName)
    {
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

        // Check if table uses old or new structure
        $stmt = $this->db->query("DESCRIBE `$tableName`");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $usesOldStructure = in_array('block_type', $columns);

        // Use appropriate column names for ordering
        $orderColumn = 'id'; // Default fallback
        if ($usesOldStructure && in_array('order_num', $columns)) {
            $orderColumn = 'order_num';
        } elseif (!$usesOldStructure && in_array('position', $columns)) {
            $orderColumn = 'position';
        }

        $sql = "SELECT * FROM `$tableName` WHERE is_active = 1 ORDER BY `$orderColumn` ASC";
        $components = $this->db->query($sql)->fetchAll();

        return [
            'meta' => $page,
            'components' => $components
        ];
    }
    public function renderComponent($block)
    {
        // Support both old and new structures
        $type = $block['component_type'] ?? $block['block_type'] ?? 'unknown';
        $content = $block['settings'] ?? $block['content'] ?? '';

        // Determine template path - try new first, then old
        $newTemplatePath = ROOT_DIR . "/components/templates/{$type}.php";
        $oldTemplatePath = ROOT_DIR . "/components/sections/{$type}.php";

        if (file_exists($newTemplatePath)) {
            $filePath = $newTemplatePath;
            // For new templates, extract settings as variables
            $data = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                extract($data);
            }
        } else {
            $filePath = $oldTemplatePath;
            // For old templates, use legacy structure
            $block_name = $block['block_name'] ?? '';
            $order_num = $block['position'] ?? $block['order_num'] ?? 0;
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
        }

        if (file_exists($filePath)) {
            extract(['content' => $content]);
            ob_start();
            include $filePath;
            return ob_get_clean();
        } else {
            return "<!-- Template not found for component type: {$type} -->";
        }
    }
}
