<?php

class ComponentManager {
    private $componentsPath;
    private $components;
    
    public function __construct($componentsPath = null) {
        // Handle legacy usage where a database object might be passed
        if (is_object($componentsPath)) {
            $this->componentsPath = __DIR__ . '/../../components/';
        } else {
            $this->componentsPath = $componentsPath ?? __DIR__ . '/../../components/';
        }
        $this->initializeComponents();
    }
    
    private function initializeComponents() {
        $this->components = array(
            'hero' => array(
                'name' => 'Hero Section',
                'description' => 'Large banner section with title, subtitle and call-to-action',
                'category' => 'header',
                'icon' => '🦸',
                'fields' => array(
                    'title' => array(
                        'type' => 'text',
                        'label' => 'Title',
                        'default' => 'Welcome to Our Website'
                    ),
                    'subtitle' => array(
                        'type' => 'textarea',
                        'label' => 'Subtitle',
                        'default' => 'We provide amazing services for your business'
                    ),
                    'button_text' => array(
                        'type' => 'text',
                        'label' => 'Button Text',
                        'default' => 'Get Started'
                    ),
                    'button_link' => array(
                        'type' => 'url',
                        'label' => 'Button Link',
                        'default' => '#'
                    )
                )
            ),
            'cta_banner' => array(
                'name' => 'Call to Action Banner',
                'description' => 'Prominent call-to-action section',
                'category' => 'content',
                'icon' => '🎯',
                'fields' => array(
                    'title' => array(
                        'type' => 'text',
                        'label' => 'Title',
                        'default' => 'Ready to Get Started?'
                    ),
                    'description' => array(
                        'type' => 'textarea',
                        'label' => 'Description',
                        'default' => 'Join thousands of satisfied customers today'
                    ),
                    'button_text' => array(
                        'type' => 'text',
                        'label' => 'Button Text',
                        'default' => 'Get Started Now'
                    ),
                    'button_link' => array(
                        'type' => 'url',
                        'label' => 'Button Link',
                        'default' => '#'
                    )
                )
            ),
            'text_block' => array(
                'name' => 'Text Block',
                'description' => 'Rich text content with formatting options',
                'category' => 'content',
                'icon' => '📝',
                'fields' => array(
                    'title' => array(
                        'type' => 'text',
                        'label' => 'Title',
                        'default' => ''
                    ),
                    'content' => array(
                        'type' => 'textarea',
                        'label' => 'Content',
                        'default' => '<p>Add your text content here...</p>'
                    ),
                    'text_align' => array(
                        'type' => 'select',
                        'label' => 'Text Alignment',
                        'options' => array('left' => 'Left', 'center' => 'Center', 'right' => 'Right'),
                        'default' => 'left'
                    )
                )
            ),
            'stats' => array(
                'name' => 'Statistics',
                'description' => 'Display key numbers and achievements',
                'category' => 'content',
                'icon' => '📊',
                'fields' => array(
                    'title' => array(
                        'type' => 'text',
                        'label' => 'Title',
                        'default' => 'Our Achievements'
                    ),
                    'subtitle' => array(
                        'type' => 'text',
                        'label' => 'Subtitle',
                        'default' => 'Numbers that speak for themselves'
                    )
                )
            ),
            'feature_grid' => array(
                'name' => 'Feature Grid',
                'description' => 'Grid of features with icons and descriptions',
                'category' => 'content',
                'icon' => '🔧',
                'fields' => array(
                    'title' => array(
                        'type' => 'text',
                        'label' => 'Title',
                        'default' => 'Our Features'
                    ),
                    'subtitle' => array(
                        'type' => 'text',
                        'label' => 'Subtitle',
                        'default' => 'What makes us special'
                    ),
                    'columns' => array(
                        'type' => 'select',
                        'label' => 'Columns',
                        'options' => array('2' => '2 Columns', '3' => '3 Columns', '4' => '4 Columns'),
                        'default' => '3'
                    )
                )
            ),
        
            // Layout Components
            'header' => array(
                'name' => 'Site Header',
                'description' => 'Main site header with navigation',
                'category' => 'header',
                'icon' => '🎯',
                'fields' => array(
                    'logo_text' => array(
                        'type' => 'text',
                        'label' => 'Logo Text',
                        'default' => 'PULSE'
                    ),
                    'show_navigation' => array(
                        'type' => 'checkbox',
                        'label' => 'Show Navigation',
                        'default' => true
                    )
                )
            ),
            'footer' => array(
                'name' => 'Site Footer',
                'description' => 'Main site footer with links and info',
                'category' => 'header',
                'icon' => '🦶',
                'fields' => array(
                    'copyright_text' => array(
                        'type' => 'text',
                        'label' => 'Copyright Text',
                        'default' => '© 2024 PULSE. All rights reserved.'
                    ),
                    'show_social_links' => array(
                        'type' => 'checkbox',
                        'label' => 'Show Social Links',
                        'default' => true
                    )
                )
            ),
            
            // Section Components  
            'about_section' => array(
                'name' => 'About Section',
                'description' => 'About us section with title and description',
                'category' => 'content',
                'icon' => 'ℹ️',
                'fields' => array(
                    'title' => array(
                        'type' => 'text',
                        'label' => 'Section Title',
                        'default' => 'About Us'
                    ),
                    'description' => array(
                        'type' => 'wysiwyg',
                        'label' => 'Description',
                        'default' => 'Learn more about our mission and values.'
                    ),
                    'image_url' => array(
                        'type' => 'image',
                        'label' => 'Background Image',
                        'default' => ''
                    )
                )
            ),
            'contact_section' => array(
                'name' => 'Contact Section',
                'description' => 'Contact information and form section',
                'category' => 'content',
                'icon' => '📞',
                'fields' => array(
                    'title' => array(
                        'type' => 'text',
                        'label' => 'Section Title',
                        'default' => 'Contact Us'
                    ),
                    'email' => array(
                        'type' => 'email',
                        'label' => 'Contact Email',
                        'default' => 'contact@pulse.com'
                    ),
                    'phone' => array(
                        'type' => 'text',
                        'label' => 'Phone Number',
                        'default' => '+1 (555) 123-4567'
                    ),
                    'address' => array(
                        'type' => 'textarea',
                        'label' => 'Address',
                        'default' => '123 Main St, City, State 12345'
                    )
                )
            ),
            'members_section' => array(
                'name' => 'Members Section',
                'description' => 'Display team members with photos and info',
                'category' => 'content',
                'icon' => '👥',
                'fields' => array(
                    'title' => array(
                        'type' => 'text',
                        'label' => 'Section Title',
                        'default' => 'Our Team'
                    ),
                    'subtitle' => array(
                        'type' => 'text',
                        'label' => 'Subtitle',
                        'default' => 'Meet the amazing people behind PULSE'
                    ),
                    'show_all_members' => array(
                        'type' => 'checkbox',
                        'label' => 'Show All Members',
                        'default' => true
                    ),
                    'members_per_row' => array(
                        'type' => 'select',
                        'label' => 'Members Per Row',
                        'options' => array('2' => '2', '3' => '3', '4' => '4'),
                        'default' => '3'
                    )
                )
            ),
            'projects_section' => array(
                'name' => 'Projects Section',
                'description' => 'Showcase projects and achievements',
                'category' => 'content',
                'icon' => '🚀',
                'fields' => array(
                    'title' => array(
                        'type' => 'text',
                        'label' => 'Section Title',
                        'default' => 'Our Projects'
                    ),
                    'subtitle' => array(
                        'type' => 'text',
                        'label' => 'Subtitle',
                        'default' => 'Check out what we\'ve been working on'
                    ),
                    'projects' => array(
                        'type' => 'repeater',
                        'label' => 'Projects',
                        'fields' => array(
                            'name' => array('type' => 'text', 'label' => 'Project Name'),
                            'description' => array('type' => 'textarea', 'label' => 'Description'),
                            'image' => array('type' => 'image', 'label' => 'Project Image'),
                            'url' => array('type' => 'url', 'label' => 'Project URL')
                        ),
                        'default' => array()
                    )
                )
            ),
            'services_section' => array(
                'name' => 'Services Section',
                'description' => 'List of services offered',
                'category' => 'content',
                'icon' => '🛠️',
                'fields' => array(
                    'title' => array(
                        'type' => 'text',
                        'label' => 'Section Title',
                        'default' => 'Our Services'
                    ),
                    'subtitle' => array(
                        'type' => 'text',
                        'label' => 'Subtitle',
                        'default' => 'What we can do for you'
                    ),
                    'services' => array(
                        'type' => 'repeater',
                        'label' => 'Services',
                        'fields' => array(
                            'name' => array('type' => 'text', 'label' => 'Service Name'),
                            'description' => array('type' => 'textarea', 'label' => 'Description'),
                            'icon' => array('type' => 'text', 'label' => 'Icon/Emoji'),
                            'price' => array('type' => 'text', 'label' => 'Price (optional)')
                        ),
                        'default' => array()
                    )
                )
            ),
            'stickers' => array(
                'name' => 'JukeBox Stickers',
                'description' => 'JukeBox custom stickers partnership announcement',
                'category' => 'content',
                'icon' => '🏷️',
                'fields' => array(
                    'title' => array(
                        'type' => 'text',
                        'label' => 'Section Title',
                        'default' => 'Sticker Credits Available!'
                    ),
                    'description' => array(
                        'type' => 'textarea',
                        'label' => 'Description',
                        'default' => 'Thanks to JukeBox, we can get amazing custom stickers for our events!'
                    )
                )
            ),
            
            // Special effects components
            'mouse_effects' => array(
                'name' => 'Mouse Effects',
                'description' => 'Interactive mouse cursor effects',
                'category' => 'media',
                'icon' => '🖱️',
                'fields' => array(
                    'effect_type' => array(
                        'type' => 'select',
                        'label' => 'Effect Type',
                        'options' => array('trail' => 'Trail', 'glow' => 'Glow', 'particles' => 'Particles'),
                        'default' => 'trail'
                    ),
                    'color' => array(
                        'type' => 'color',
                        'label' => 'Effect Color',
                        'default' => '#ef4444'
                    )
                )
            ),
            'globe_3d' => array(
                'name' => '3D Globe',
                'description' => 'Interactive 3D globe visualization',
                'category' => 'media',
                'icon' => '🌍',
                'fields' => array(
                    'show_labels' => array(
                        'type' => 'checkbox',
                        'label' => 'Show Country Labels',
                        'default' => true
                    ),
                    'rotation_speed' => array(
                        'type' => 'range',
                        'label' => 'Rotation Speed',
                        'min' => 0,
                        'max' => 10,
                        'default' => 5
                    )
                )
            ),
            'grid_background' => array(
                'name' => 'Grid Background',
                'description' => 'Animated grid background effect',
                'category' => 'media',
                'icon' => '⚏',
                'fields' => array(
                    'grid_size' => array(
                        'type' => 'range',
                        'label' => 'Grid Size',
                        'min' => 10,
                        'max' => 100,
                        'default' => 50
                    ),
                    'animation_speed' => array(
                        'type' => 'range',
                        'label' => 'Animation Speed',
                        'min' => 1,
                        'max' => 10,
                        'default' => 3
                    ),
                    'color' => array(
                        'type' => 'color',
                        'label' => 'Grid Color',
                        'default' => '#333333'
                    )
                )
            )
        );
    }
    
    public function getComponent($type) {
        return isset($this->components[$type]) ? $this->components[$type] : null;
    }
    
    public function getComponents() {
        return $this->components;
    }
    
    public function getComponentsByCategory($category) {
        $filtered = array();
        foreach ($this->components as $key => $component) {
            if ($component['category'] === $category) {
                $filtered[$key] = $component;
            }
        }
        return $filtered;
    }
    
    public function renderComponent($componentType, $componentName, $props = array()) {
        // Handle legacy method signature where $componentType might be the component name directly
        if (is_array($componentName)) {
            // Legacy usage: renderComponent('hero', $data, $blockId)
            $type = $componentType;
            $data = $componentName;
            $blockId = $props;
            return $this->render($type, $data, $blockId);
        }
        
        $componentPath = $this->componentsPath . $componentType . '/' . $componentName . '.php';
        
        if (!file_exists($componentPath)) {
            throw new Exception("Component not found: " . $componentPath);
        }
        
        extract($props);
        
        ob_start();
        include $componentPath;
        return ob_get_clean();
    }
    
    public function renderBlock($blockName, $props = array()) {
        return $this->renderComponent('blocks', $blockName, $props);
    }
    
    public function componentExists($componentType, $componentName) {
        $componentPath = $this->componentsPath . $componentType . '/' . $componentName . '.php';
        return file_exists($componentPath);
    }
    
    public function getComponentPath($componentType, $componentName) {
        return $this->componentsPath . $componentType . '/' . $componentName . '.php';
    }
    
    public function render($type, $data = array(), $blockId = null) {
        $component = $this->getComponent($type);
        if (!$component) {
            return '<div class="text-center p-8 text-gray-500 bg-gray-50 rounded">
                        <div class="text-6xl mb-4">📦</div>
                        <h3 class="text-lg font-medium">Unknown Component</h3>
                        <p class="text-sm">Component type: ' . htmlspecialchars($type) . '</p>
                    </div>';
        }
        
        $componentFile = $this->componentsPath . 'blocks/' . $type . '.php';
        if (file_exists($componentFile)) {
            foreach ($data as $key => $value) {
                $$key = $value;
            }
            
            $blockData = array('id' => $blockId, 'type' => $type, 'data' => $data);
            
            ob_start();
            include $componentFile;
            return ob_get_clean();
        }
        
        return '<div class="text-center p-8 text-gray-500 bg-gray-50 rounded">
                    <div class="text-6xl mb-4">' . htmlspecialchars($component['icon']) . '</div>
                    <h3 class="text-lg font-medium">' . htmlspecialchars($component['name']) . '</h3>
                    <p class="text-sm">' . htmlspecialchars($component['description']) . '</p>
                    <p class="text-xs mt-2 text-red-500">Component file not found: ' . htmlspecialchars($type . '.php') . '</p>
                </div>';
    }
}