<?php

/**
 * Drag & Drop Website Builder
 * WordPress-like interface for building pages
 */

class DragDropBuilder
{
    private $db;
    private $componentRegistry;

    public function __construct($db)
    {
        $this->db = $db;
        $this->componentRegistry = [];
        $this->registerDefaultComponents();
    }

    /**
     * Register default components
     */
    private function registerDefaultComponents()
    {
        $this->registerComponent('heading', [
            'name' => 'Heading',
            'icon' => 'text-size',
            'category' => 'content',
            'description' => 'Add headings and titles',
            'settings' => [
                'text' => ['type' => 'text', 'label' => 'Heading Text', 'default' => 'Your Heading'],
                'level' => ['type' => 'select', 'label' => 'Heading Level', 'options' => ['h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'h5' => 'H5', 'h6' => 'H6'], 'default' => 'h2'],
                'align' => ['type' => 'select', 'label' => 'Alignment', 'options' => ['left' => 'Left', 'center' => 'Center', 'right' => 'Right'], 'default' => 'left'],
                'color' => ['type' => 'color', 'label' => 'Text Color', 'default' => '#1f2937']
            ]
        ]);

        $this->registerComponent('text', [
            'name' => 'Text Block',
            'icon' => 'text',
            'category' => 'content',
            'description' => 'Add text content and paragraphs',
            'settings' => [
                'content' => ['type' => 'wysiwyg', 'label' => 'Content', 'default' => 'Enter your text content here...'],
                'align' => ['type' => 'select', 'label' => 'Alignment', 'options' => ['left' => 'Left', 'center' => 'Center', 'right' => 'Right', 'justify' => 'Justify'], 'default' => 'left']
            ]
        ]);

        $this->registerComponent('hero', [
            'name' => 'Hero Section',
            'icon' => 'hero',
            'category' => 'layout',
            'description' => 'Large banner with title and call-to-action',
            'settings' => [
                'title' => ['type' => 'text', 'label' => 'Main Title', 'default' => 'Welcome to Our Website'],
                'subtitle' => ['type' => 'textarea', 'label' => 'Subtitle', 'default' => 'Discover amazing things with us'],
                'background_image' => ['type' => 'image', 'label' => 'Background Image', 'default' => ''],
                'cta_text' => ['type' => 'text', 'label' => 'Button Text', 'default' => 'Get Started'],
                'cta_link' => ['type' => 'text', 'label' => 'Button Link', 'default' => '#'],
                'height' => ['type' => 'select', 'label' => 'Section Height', 'options' => ['small' => 'Small', 'medium' => 'Medium', 'large' => 'Large', 'full' => 'Full Screen'], 'default' => 'medium']
            ]
        ]);

        $this->registerComponent('image', [
            'name' => 'Image',
            'icon' => 'image',
            'category' => 'media',
            'description' => 'Add images with captions',
            'settings' => [
                'src' => ['type' => 'image', 'label' => 'Image', 'default' => ''],
                'alt' => ['type' => 'text', 'label' => 'Alt Text', 'default' => ''],
                'caption' => ['type' => 'text', 'label' => 'Caption', 'default' => ''],
                'width' => ['type' => 'select', 'label' => 'Width', 'options' => ['25' => '25%', '50' => '50%', '75' => '75%', '100' => '100%'], 'default' => '100'],
                'align' => ['type' => 'select', 'label' => 'Alignment', 'options' => ['left' => 'Left', 'center' => 'Center', 'right' => 'Right'], 'default' => 'center']
            ]
        ]);

        $this->registerComponent('button', [
            'name' => 'Button',
            'icon' => 'button',
            'category' => 'content',
            'description' => 'Call-to-action buttons',
            'settings' => [
                'text' => ['type' => 'text', 'label' => 'Button Text', 'default' => 'Click Me'],
                'link' => ['type' => 'text', 'label' => 'Link URL', 'default' => '#'],
                'style' => ['type' => 'select', 'label' => 'Button Style', 'options' => ['primary' => 'Primary', 'secondary' => 'Secondary', 'outline' => 'Outline'], 'default' => 'primary'],
                'size' => ['type' => 'select', 'label' => 'Size', 'options' => ['sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large'], 'default' => 'md'],
                'align' => ['type' => 'select', 'label' => 'Alignment', 'options' => ['left' => 'Left', 'center' => 'Center', 'right' => 'Right'], 'default' => 'left']
            ]
        ]);

        $this->registerComponent('spacer', [
            'name' => 'Spacer',
            'icon' => 'spacer',
            'category' => 'layout',
            'description' => 'Add vertical spacing',
            'settings' => [
                'height' => ['type' => 'select', 'label' => 'Height', 'options' => ['20' => 'Small (20px)', '40' => 'Medium (40px)', '60' => 'Large (60px)', '80' => 'Extra Large (80px)'], 'default' => '40']
            ]
        ]);

        $this->registerComponent('columns', [
            'name' => 'Columns',
            'icon' => 'columns',
            'category' => 'layout',
            'description' => 'Multi-column layout',
            'settings' => [
                'columns' => ['type' => 'select', 'label' => 'Number of Columns', 'options' => ['1' => '1 Column', '2' => '2 Columns', '3' => '3 Columns', '4' => '4 Columns'], 'default' => '2'],
                'gap' => ['type' => 'select', 'label' => 'Column Gap', 'options' => ['sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large'], 'default' => 'md']
            ],
            'has_children' => true
        ]);

        // Legacy components from old builder
        $this->registerComponent('members_grid', [
            'name' => 'Members Grid',
            'icon' => 'users',
            'category' => 'content',
            'description' => 'Display team members in a grid layout',
            'settings' => [
                'title' => ['type' => 'text', 'label' => 'Grid Title', 'default' => 'Our Team'],
                'subtitle' => ['type' => 'text', 'label' => 'Grid Subtitle', 'default' => 'Meet the PULSE community']
            ]
        ]);

        $this->registerComponent('contact_form', [
            'name' => 'Contact Form',
            'icon' => 'mail',
            'category' => 'forms',
            'description' => 'Contact form with customizable fields',
            'settings' => [
                'title' => ['type' => 'text', 'label' => 'Form Title', 'default' => 'Get in Touch'],
                'subtitle' => ['type' => 'text', 'label' => 'Form Subtitle', 'default' => 'We\'ll respond within 24 hours'],
                'description' => ['type' => 'textarea', 'label' => 'Form Description', 'default' => 'Have a question or want to join our team? Fill out the form below.'],
                'button_text' => ['type' => 'text', 'label' => 'Button Text', 'default' => 'Send Message']
            ]
        ]);

        $this->registerComponent('apply_form', [
            'name' => 'Application Form',
            'icon' => 'clipboard',
            'category' => 'forms',
            'description' => 'Multi-section application form',
            'settings' => [
                'sections' => ['type' => 'json', 'label' => 'Form Sections', 'default' => '[]']
            ]
        ]);
    }

    /**
     * Register a new component
     */
    public function registerComponent($type, $config)
    {
        $this->componentRegistry[$type] = $config;
    }

    /**
     * Get all registered components
     */
    public function getComponents()
    {
        return $this->componentRegistry;
    }

    /**
     * Get components by category
     */
    public function getComponentsByCategory($category = null)
    {
        if (!$category) {
            return $this->componentRegistry;
        }

        return array_filter($this->componentRegistry, function ($component) use ($category) {
            return ($component['category'] ?? 'other') === $category;
        });
    }

    /**
     * Get a specific component
     */
    public function getComponent($type)
    {
        return $this->componentRegistry[$type] ?? null;
    }

    /**
     * Add a component to a page
     */
    public function addComponent($pageId, $componentType, $settings = [], $position = null)
    {
        error_log("DragDropBuilder::addComponent called with position: " . var_export($position, true));
        
        $page = $this->getPage($pageId);
        if (!$page) {
            throw new Exception('Page not found');
        }

        $component = $this->getComponent($componentType);
        if (!$component) {
            throw new Exception('Component type not found');
        }

        // Get default settings and merge with provided settings
        $defaultSettings = [];
        foreach ($component['settings'] as $key => $setting) {
            $defaultSettings[$key] = $setting['default'] ?? '';
        }
        $settings = array_merge($defaultSettings, $settings);

        // Determine position - ensure it's always an integer
        if ($position === null || $position === 'end' || !is_numeric($position)) {
            // Check if table uses old or new structure
            $stmt = $this->db->query("DESCRIBE " . $page['table_name']);
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $usesOldStructure = in_array('block_type', $columns);

            $positionColumn = $usesOldStructure ? 'order_num' : 'position';

            $stmt = $this->db->prepare("SELECT MAX(`$positionColumn`) as max_pos FROM " . $page['table_name']);
            $stmt->execute();
            $result = $stmt->fetch();
            $position = ($result['max_pos'] ?? 0) + 1;
            error_log("Calculated new position: $position");
        } else {
            // Ensure position is always an integer
            $position = intval($position);
            if ($position <= 0) {
                $position = 1;
            }
            error_log("Using provided position: $position");
        }

        // Check table structure and insert component accordingly
        $stmt = $this->db->query("DESCRIBE " . $page['table_name']);
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $usesOldStructure = in_array('block_type', $columns);

        // Final safety check - ensure position is ALWAYS an integer before database insertion
        if (!is_numeric($position) || $position === null || $position === 'end' || $position === '') {
            // Recalculate position if it's invalid
            $positionColumn = $usesOldStructure ? 'order_num' : 'position';
            $stmt = $this->db->prepare("SELECT MAX(`$positionColumn`) as max_pos FROM " . $page['table_name']);
            $stmt->execute();
            $result = $stmt->fetch();
            $position = ($result['max_pos'] ?? 0) + 1;
            error_log("Position was invalid, recalculated to: $position");
        }
        
        // Ensure it's an integer
        $position = intval($position);
        if ($position <= 0) {
            $position = 1;
        }

        try {
            error_log("About to insert component with final position: " . var_export($position, true));
            
            if ($usesOldStructure) {
                // Insert using old structure
                $stmt = $this->db->prepare("INSERT INTO " . $page['table_name'] . " (block_name, block_type, content, order_num, is_active) VALUES (?, ?, ?, ?, 1)");
                $stmt->execute(['component_' . time(), $componentType, json_encode($settings), $position]);
            } else {
                // Insert using new structure
                $stmt = $this->db->prepare("INSERT INTO " . $page['table_name'] . " (component_type, settings, position, is_active) VALUES (?, ?, ?, 1)");
                $stmt->execute([$componentType, json_encode($settings), $position]);
            }

            return $this->db->lastInsertId();
        } catch (Exception $e) {
            // Enhanced error message with debugging info
            $error = "Failed to add component. ";
            $error .= "Table: " . $page['table_name'] . ", ";
            $error .= "Structure: " . ($usesOldStructure ? 'old' : 'new') . ", ";
            $error .= "Component Type: $componentType, ";
            $error .= "Error: " . $e->getMessage();
            throw new Exception($error);
        }
    }

    /**
     * Update a component
     */
    public function updateComponent($pageId, $componentId, $settings)
    {
        error_log("DragDropBuilder::updateComponent called - PageID: $pageId, ComponentID: $componentId, Settings: " . var_export($settings, true));
        
        $page = $this->getPage($pageId);
        if (!$page) {
            throw new Exception('Page not found');
        }

        error_log("DragDropBuilder::updateComponent - Page found: " . $page['table_name']);

        // Check if table uses old or new structure
        $stmt = $this->db->query("DESCRIBE " . $page['table_name']);
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $usesOldStructure = in_array('block_type', $columns);

        error_log("DragDropBuilder::updateComponent - Table columns: " . implode(', ', $columns));
        error_log("DragDropBuilder::updateComponent - Uses old structure: " . ($usesOldStructure ? 'yes' : 'no'));

        try {
            if ($usesOldStructure) {
                // Update old structure
                $stmt = $this->db->prepare("UPDATE " . $page['table_name'] . " SET content = ? WHERE id = ?");
                error_log("DragDropBuilder::updateComponent - Using old structure query");
            } else {
                // Update new structure
                $stmt = $this->db->prepare("UPDATE " . $page['table_name'] . " SET settings = ? WHERE id = ?");
                error_log("DragDropBuilder::updateComponent - Using new structure query");
            }

            $settingsJson = json_encode($settings);
            error_log("DragDropBuilder::updateComponent - Final JSON to save: " . $settingsJson);
            
            $result = $stmt->execute([$settingsJson, $componentId]);
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                throw new Exception('Failed to update component in database. SQL Error: ' . implode(' - ', $errorInfo));
            }
            
            $affectedRows = $stmt->rowCount();
            error_log("DragDropBuilder::updateComponent - Update successful, affected rows: " . $affectedRows);
            
            if ($affectedRows === 0) {
                // Check if the component exists
                $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM " . $page['table_name'] . " WHERE id = ?");
                $checkStmt->execute([$componentId]);
                $exists = $checkStmt->fetchColumn();
                
                if ($exists == 0) {
                    throw new Exception("Component with ID $componentId not found in table " . $page['table_name']);
                } else {
                    error_log("DragDropBuilder::updateComponent - Component exists but no rows were updated. This might indicate the settings are identical.");
                }
            }
            
        } catch (Exception $e) {
            error_log("DragDropBuilder::updateComponent - Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a component
     */
    public function deleteComponent($pageId, $componentId)
    {
        $page = $this->getPage($pageId);
        if (!$page) {
            throw new Exception('Page not found');
        }

        $stmt = $this->db->prepare("DELETE FROM " . $page['table_name'] . " WHERE id = ?");
        $stmt->execute([$componentId]);
    }

    /**
     * Reorder components
     */
    public function reorderComponents($pageId, $componentIds)
    {
        $page = $this->getPage($pageId);
        if (!$page) {
            throw new Exception('Page not found');
        }

        // Check if table uses old or new structure
        $stmt = $this->db->query("DESCRIBE " . $page['table_name']);
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $usesOldStructure = in_array('block_type', $columns);

        // Use appropriate column name for position
        $positionColumn = $usesOldStructure ? 'order_num' : 'position';

        foreach ($componentIds as $position => $componentId) {
            $stmt = $this->db->prepare("UPDATE " . $page['table_name'] . " SET `$positionColumn` = ? WHERE id = ?");
            $stmt->execute([$position + 1, $componentId]);
        }
    }

    /**
     * Get page components
     */
    public function getPageComponents($pageId)
    {
        $page = $this->getPage($pageId);
        if (!$page) {
            return [];
        }

        // Check if table uses old or new structure
        try {
            $stmt = $this->db->query("DESCRIBE " . $page['table_name']);
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $usesOldStructure = in_array('block_type', $columns);

            // Use appropriate column names for ordering
            $orderColumn = 'id'; // Default fallback
            if ($usesOldStructure && in_array('order_num', $columns)) {
                $orderColumn = 'order_num';
            } elseif (!$usesOldStructure && in_array('position', $columns)) {
                $orderColumn = 'position';
            }

            $stmt = $this->db->prepare("SELECT * FROM " . $page['table_name'] . " WHERE is_active = 1 ORDER BY `$orderColumn` ASC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            // Fallback - just get all components without ordering
            $stmt = $this->db->prepare("SELECT * FROM " . $page['table_name'] . " WHERE is_active = 1");
            $stmt->execute();
            return $stmt->fetchAll();
        }
    }

    /**
     * Render a component
     */
    public function renderComponent($component, $isEditor = false)
    {
        // Handle both old and new component structures
        $type = $component['component_type'] ?? $component['block_type'] ?? 'unknown';
        $settingsJson = $component['settings'] ?? $component['content'] ?? '{}';

        // Ensure settings is always an array
        if (is_string($settingsJson)) {
            $settings = json_decode($settingsJson, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($settings)) {
                $settings = [];
            }
        } else {
            $settings = is_array($settingsJson) ? $settingsJson : [];
        }

        $componentConfig = $this->getComponent($type);

        if (!$componentConfig) {
            return '<!-- Unknown component: ' . htmlspecialchars($type) . ' -->';
        }

        // Load component template
        $templateFile = __DIR__ . '/../../components/templates/' . $type . '.php';
        if (!file_exists($templateFile)) {
            return '<!-- Template not found: ' . htmlspecialchars($type) . ' -->';
        }

        ob_start();

        // Extract settings for template - now guaranteed to be an array
        if (!empty($settings) && is_array($settings)) {
            extract($settings);
        }
        $componentData = $component;
        $editorMode = $isEditor;

        include $templateFile;

        $output = ob_get_clean();

        if ($isEditor) {
            // Wrap with editor controls
            return $this->wrapWithEditorControls($component, $output);
        }

        return $output;
    }

    /**
     * Wrap component with editor controls
     */
    private function wrapWithEditorControls($component, $content)
    {
        $type = $component['component_type'] ?? $component['block_type'] ?? 'unknown';
        $componentConfig = $this->getComponent($type);

        return sprintf(
            '<div class="ddb-component" data-component-id="%d" data-component-type="%s">
                <div class="ddb-component-controls">
                    <span class="ddb-component-name">%s</span>
                    <div class="ddb-component-actions">
                        <button class="ddb-edit-component" title="Edit">✏️</button>
                        <button class="ddb-delete-component" title="Delete">🗑️</button>
                        <button class="ddb-move-component" title="Move">↕️</button>
                    </div>
                </div>
                <div class="ddb-component-content">%s</div>
            </div>',
            $component['id'],
            htmlspecialchars($type),
            htmlspecialchars($componentConfig['name'] ?? $type),
            $content
        );
    }

    /**
     * Get page information
     */
    public function getPage($pageId)
    {
        $stmt = $this->db->prepare("SELECT * FROM pages WHERE id = ?");
        $stmt->execute([$pageId]);
        return $stmt->fetch();
    }

    /**
     * Export page structure
     */
    public function exportPage($pageId)
    {
        $page = $this->getPage($pageId);
        if (!$page) {
            return null;
        }

        $components = $this->getPageComponents($pageId);

        return [
            'page' => $page,
            'components' => $components
        ];
    }

    /**
     * Import page structure
     */
    public function importPage($pageId, $data)
    {
        $page = $this->getPage($pageId);
        if (!$page) {
            throw new Exception('Page not found');
        }

        // Clear existing components
        $stmt = $this->db->prepare("DELETE FROM " . $page['table_name']);
        $stmt->execute();

        // Import components
        foreach ($data['components'] as $component) {
            $this->addComponent(
                $pageId,
                $component['component_type'],
                json_decode($component['settings'], true),
                $component['position']
            );
        }
    }
}
