<?php
class Navigation {
    private $db;
    
    public function __construct($db = null) {
        if ($db === null) {
            global $db;
        }
        $this->db = $db;
    }
    
    public function getMenu() {
        $query = "
            SELECT m.*, p.name as page_name 
            FROM menus m
            JOIN pages p ON m.page_id = p.id
            WHERE p.menu_enabled = 1
            ORDER BY m.order_num ASC
        ";
        $items = $this->db->query($query)->fetchAll();
        return $this->buildTree($items);
    }
    
    private function buildTree(array $elements, $parentId = null) {
        $branch = [];
        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = $this->buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }
    
    public function renderMenu() {
        $menuItems = $this->getMenu();
        $output = '<nav class="main-menu"><ul>';
        
        foreach ($menuItems as $item) {
            $output .= $this->renderMenuItem($item);
        }
        
        $output .= '</ul></nav>';
        return $output;
    }
    
    private function renderMenuItem($item, $isChild = false) {
        $output = '<li>';
        $output .= '<a href="/' . $item['page_name'] . '.php">' . htmlspecialchars($item['title']) . '</a>';
        
        if (isset($item['children']) && !empty($item['children'])) {
            $output .= '<ul class="submenu">';
            foreach ($item['children'] as $child) {
                $output .= $this->renderMenuItem($child, true);
            }
            $output .= '</ul>';
        }
        
        $output .= '</li>';
        return $output;
    }
}
