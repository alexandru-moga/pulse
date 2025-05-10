<?php
class PageManager {
    private $db;
    
    public function __construct($db = null) {
        if ($db === null) {
            global $db;
        }
        $this->db = $db;
    }
    
public function getPageData($pageName) {
    $query = "SELECT id, name, table_name, module_config, menu_enabled FROM pages WHERE name = ?";
    return $this->db->query($query, [$pageName])->fetch();
}

    
    public function getPageBlocks($pageName) {
        // First, get the page record to find the table name
        $page = $this->getPageData($pageName);
        
        if (!$page) {
            return [];
        }
        
        $tableName = $page['table_name'];
        $query = "SELECT * FROM $tableName WHERE is_active = 1 ORDER BY order_num ASC";
        return $this->db->query($query)->fetchAll();
    }
    
    public function renderBlock($block) {
        $content = '';
        
        switch ($block['block_type']) {
            case 'heading':
                $content = '<h2>' . htmlspecialchars($block['content']) . '</h2>';
                break;
                
            case 'text':
                $content = '<p>' . nl2br(htmlspecialchars($block['content'])) . '</p>';
                break;
                
            case 'counter':
                $content = '<div class="counter">' . htmlspecialchars($block['content']) . '+</div>';
                break;
                
            case 'dynamic':
                $content = $this->renderDynamicBlock($block);
                break;
                
            case 'form':
                $content = $this->renderFormBlock($block);
                break;
                
            case 'conditional':
                $content = $this->renderConditionalBlock($block);
                break;
                
            case 'custom':
                $content = $this->renderCustomBlock($block);
                break;
        }
        
        return $content;
    }
    
    private function renderDynamicBlock($block) {
        // Execute SQL query from content
        $query = $block['content'];
        
        // Replace variables in query
        global $currentUser;
        if ($currentUser) {
            $query = str_replace('{user_id}', $currentUser->id, $query);
            $query = str_replace('{username}', $currentUser->username, $query);
        }
        
        $results = $this->db->query($query)->fetchAll();
        
        // Return formatted results based on query type
        if (strpos($query, 'SELECT') !== false) {
            $output = '<div class="dynamic-content">';
            
            if (empty($results)) {
                $output .= '<p>No records found</p>';
            } else {
                // Get the keys from the first result for table headers
                $firstResult = $results[0];
                $output .= '<table class="data-table"><thead><tr>';
                
                foreach (array_keys($firstResult) as $key) {
                    if (!is_numeric($key)) {
                        $output .= '<th>' . ucfirst(str_replace('_', ' ', $key)) . '</th>';
                    }
                }
                
                $output .= '</tr></thead><tbody>';
                
                foreach ($results as $row) {
                    $output .= '<tr>';
                    foreach ($row as $key => $value) {
                        if (!is_numeric($key)) {
                            $output .= '<td>' . htmlspecialchars($value) . '</td>';
                        }
                    }
                    $output .= '</tr>';
                }
                
                $output .= '</tbody></table>';
            }
            
            $output .= '</div>';
            return $output;
        }
        
        return $block['content']; // Default fallback
    }
    
    private function renderFormBlock($block) {
        $formData = json_decode($block['content'], true);
        
        if (!$formData || !isset($formData['fields'])) {
            return '<p>Error: Invalid form configuration</p>';
        }
        
        $output = '<form method="post" class="dynamic-form">';
        
        foreach ($formData['fields'] as $field) {
            $output .= '<div class="form-group">';
            $output .= '<label for="' . $field['name'] . '">' . $field['label'] . '</label>';
            
            switch ($field['type']) {
                case 'text':
                case 'email':
                    $output .= '<input type="' . $field['type'] . '" name="' . $field['name'] . '" id="' . $field['name'] . '">';
                    break;
                    
                case 'textarea':
                    $output .= '<textarea name="' . $field['name'] . '" id="' . $field['name'] . '"></textarea>';
                    break;
                    
                case 'select':
                    $output .= '<select name="' . $field['name'] . '" id="' . $field['name'] . '">';
                    
                    if (isset($field['source'])) {
                        // Fetch options from database
                        $sourceQuery = "SELECT id, name FROM " . $field['source'];
                        $options = $this->db->query($sourceQuery)->fetchAll();
                        
                        foreach ($options as $option) {
                            $output .= '<option value="' . $option['id'] . '">' . htmlspecialchars($option['name']) . '</option>';
                        }
                    }
                    
                    $output .= '</select>';
                    break;
            }
            
            $output .= '</div>';
        }
        
        $output .= '<button type="submit" class="btn">Submit</button>';
        $output .= '</form>';
        
        return $output;
    }
    
    private function renderConditionalBlock($block) {
        $conditionalData = json_decode($block['content'], true);
        
        if (!$conditionalData || !isset($conditionalData['condition']) || !isset($conditionalData['content'])) {
            return '<p>Error: Invalid conditional configuration</p>';
        }
        
        // Evaluate condition
        $condition = $conditionalData['condition'];
        $result = false;
        
        if ($condition === 'isAdmin()') {
            $result = isAdmin();
        } elseif ($condition === 'isLoggedIn()') {
            $result = isLoggedIn();
        }
        
        if ($result) {
            return $conditionalData['content'];
        }
        
        return '';
    }
    
    private function renderCustomBlock($block) {
        $customData = json_decode($block['content'], true);
        
        if (!$customData) {
            return '<p>Error: Invalid custom block configuration</p>';
        }
        
        // Example for events section in index page
        if (isset($customData['title']) && isset($customData['events'])) {
            $output = '<section class="events-section">';
            $output .= '<h2>' . htmlspecialchars($customData['title']) . '</h2>';
            
            foreach ($customData['events'] as $event) {
                $output .= '<div class="event-card">';
                $output .= '<h3>' . htmlspecialchars($event['name']) . '</h3>';
                $output .= '<p class="event-date">' . htmlspecialchars($event['date']) . ' | ' . htmlspecialchars($event['time']) . '</p>';
                $output .= '<p>' . htmlspecialchars($event['description']) . '</p>';
                
                if (isset($event['button_text']) && isset($event['button_link'])) {
                    $output .= '<a href="' . $event['button_link'] . '" class="btn">' . htmlspecialchars($event['button_text']) . ' →</a>';
                }
                
                $output .= '</div>';
            }
            
            $output .= '</section>';
            return $output;
        }
        
        return '<div>' . htmlspecialchars($block['content']) . '</div>';
    }
}
public function getPageContent($pageName) {
    $pageData = $this->getPageData($pageName);
    $blocks = $this->getPageBlocks($pageName);
    
    return [
        'title' => $pageData['title'] ?? '',
        'description' => $pageData['description'] ?? '',
        'blocks' => $blocks
    ];
}

public function getBlockContent($blocks, $blockName) {
    foreach ($blocks as $block) {
        if ($block['block_name'] === $blockName) {
            return $block['content'];
        }
    }
    return '';
}
