<?php

class ComponentManager
{
    private $componentsPath;
    private $components;

    public function __construct($componentsPath = null)
    {
        // Handle legacy usage where a database object might be passed
        if (is_object($componentsPath)) {
            $this->componentsPath = __DIR__ . '/../../components/';
        } else {
            $this->componentsPath = $componentsPath ?? __DIR__ . '/../../components/';
        }
        $this->initializeComponents();
    }

    private function initializeComponents()
    {
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
                    'items' => array(
                        'type' => 'repeater',
                        'label' => 'Statistics Items',
                        'default' => '[
                            {"value": "150", "label": "Active Members"},
                            {"value": "25", "label": "Projects Active"},
                            {"value": "50", "label": "Projects Completed"}
                        ]',
                        'fields' => array(
                            'icon' => array(
                                'type' => 'image',
                                'label' => 'Icon/Image',
                                'default' => '',
                                'placeholder' => 'Upload image or select emoji'
                            ),
                            'value' => array(
                                'type' => 'text',
                                'label' => 'Value',
                                'default' => '100',
                                'placeholder' => 'e.g., 150'
                            ),
                            'label' => array(
                                'type' => 'text',
                                'label' => 'Label',
                                'default' => 'Statistic',
                                'placeholder' => 'e.g., Active Members'
                            )
                        )
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
                    ),
                    'features' => array(
                        'type' => 'repeater',
                        'label' => 'Features',
                        'fields' => array(
                            'icon' => array(
                                'type' => 'image',
                                'label' => 'Icon/Image',
                                'default' => 'emoji:⭐',
                                'placeholder' => 'Upload image or select emoji'
                            ),
                            'title' => array(
                                'type' => 'text',
                                'label' => 'Feature Title',
                                'default' => 'Feature Title',
                                'placeholder' => 'e.g., Fast Performance'
                            ),
                            'description' => array(
                                'type' => 'textarea',
                                'label' => 'Feature Description',
                                'default' => 'Feature description goes here.',
                                'placeholder' => 'e.g., Lightning fast performance for all your needs.'
                            )
                        ),
                        'default' => array(
                            array('icon' => 'emoji:🚀', 'title' => 'Fast Performance', 'description' => 'Lightning fast performance for all your needs.'),
                            array('icon' => 'emoji:🔒', 'title' => 'Secure', 'description' => 'Top-notch security to protect your data.'),
                            array('icon' => 'emoji:💡', 'title' => 'Innovative', 'description' => 'Cutting-edge solutions for modern challenges.')
                        )
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
                            'icon' => array(
                                'type' => 'image',
                                'label' => 'Icon/Image',
                                'default' => 'emoji:🛠️',
                                'placeholder' => 'Upload image or select emoji'
                            ),
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
            ),

            // Migrated old components
            'welcome' => array(
                'name' => 'Welcome Section',
                'description' => 'Hero-style welcome section with title, subtitle, and buttons',
                'category' => 'header',
                'icon' => '👋',
                'fields' => array(
                    'title' => array(
                        'type' => 'text',
                        'label' => 'Main Title',
                        'default' => 'Welcome to PULSE'
                    ),
                    'subtitle' => array(
                        'type' => 'text',
                        'label' => 'Subtitle/Badge Text',
                        'default' => 'STUDENT-LED TECH COMMUNITY'
                    ),
                    'description' => array(
                        'type' => 'textarea',
                        'label' => 'Description',
                        'default' => 'Join a vibrant community of students passionate about technology.'
                    ),
                    'primary_button_text' => array(
                        'type' => 'text',
                        'label' => 'Primary Button Text',
                        'default' => 'Get Involved'
                    ),
                    'primary_button_url' => array(
                        'type' => 'url',
                        'label' => 'Primary Button URL',
                        'default' => '#'
                    ),
                    'secondary_button_text' => array(
                        'type' => 'text',
                        'label' => 'Secondary Button Text',
                        'default' => 'Contact us'
                    ),
                    'secondary_button_url' => array(
                        'type' => 'url',
                        'label' => 'Secondary Button URL',
                        'default' => '#'
                    )
                )
            ),
            'core_values' => array(
                'name' => 'Core Values',
                'description' => 'Display organization values with icons and descriptions',
                'category' => 'content',
                'icon' => '⭐',
                'fields' => array(
                    'values' => array(
                        'type' => 'repeater',
                        'label' => 'Values',
                        'fields' => array(
                            'icon' => array(
                                'type' => 'image',
                                'label' => 'Icon/Image',
                                'default' => 'emoji:💎',
                                'placeholder' => 'Upload image or select emoji'
                            ),
                            'title' => array('type' => 'text', 'label' => 'Title', 'default' => 'Excellence'),
                            'description' => array('type' => 'textarea', 'label' => 'Description', 'default' => 'We strive for excellence in everything we do.')
                        ),
                        'default' => array(
                            array('icon' => 'emoji:💎', 'title' => 'Excellence', 'description' => 'We strive for excellence in everything we do.'),
                            array('icon' => 'emoji:🤝', 'title' => 'Collaboration', 'description' => 'We work together to achieve great things.'),
                            array('icon' => 'emoji:🚀', 'title' => 'Innovation', 'description' => 'We embrace new technologies and ideas.')
                        )
                    )
                )
            ),
            'stickers' => array(
                'name' => 'JukeBox Stickers',
                'description' => 'Special stickers partnership section',
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
                        'default' => 'Thanks to JukeBox for our custom stickers!'
                    ),
                    'partner_url' => array(
                        'type' => 'url',
                        'label' => 'Partner URL',
                        'default' => 'https://www.jukeboxprint.com/custom-stickers'
                    )
                )
            ),
            'scroll_arrow' => array(
                'name' => 'Scroll Arrow',
                'description' => 'Animated scroll indicator arrow',
                'category' => 'media',
                'icon' => '⬇️',
                'fields' => array(
                    'color' => array(
                        'type' => 'color',
                        'label' => 'Arrow Color',
                        'default' => 'rgba(255,255,255,0.7)'
                    ),
                    'size' => array(
                        'type' => 'range',
                        'label' => 'Arrow Size',
                        'min' => 16,
                        'max' => 48,
                        'default' => 24
                    )
                )
            ),
            'contact_form' => array(
                'name' => 'Contact Form',
                'description' => 'Contact form with customizable fields',
                'category' => 'forms',
                'icon' => '📧',
                'fields' => array(
                    'title' => array(
                        'type' => 'text',
                        'label' => 'Form Title',
                        'default' => 'Contact Us'
                    ),
                    'subtitle' => array(
                        'type' => 'text',
                        'label' => 'Subtitle',
                        'default' => 'Get in touch with our team'
                    ),
                    'description' => array(
                        'type' => 'textarea',
                        'label' => 'Description',
                        'default' => 'We would love to hear from you'
                    ),
                    'button_text' => array(
                        'type' => 'text',
                        'label' => 'Button Text',
                        'default' => 'Send Message'
                    ),
                    'fields' => array(
                        'type' => 'repeater',
                        'label' => 'Form Fields',
                        'fields' => array(
                            'name' => array('type' => 'text', 'label' => 'Field Name', 'default' => 'name'),
                            'label' => array('type' => 'text', 'label' => 'Field Label', 'default' => 'Name'),
                            'type' => array(
                                'type' => 'select',
                                'label' => 'Field Type',
                                'options' => array('text' => 'Text', 'email' => 'Email', 'textarea' => 'Textarea'),
                                'default' => 'text'
                            ),
                            'placeholder' => array('type' => 'text', 'label' => 'Placeholder', 'default' => ''),
                            'required' => array('type' => 'checkbox', 'label' => 'Required', 'default' => true)
                        ),
                        'default' => array(
                            array('name' => 'name', 'label' => 'Name', 'type' => 'text', 'placeholder' => 'Your name', 'required' => true),
                            array('name' => 'email', 'label' => 'Email', 'type' => 'email', 'placeholder' => 'your@email.com', 'required' => true),
                            array('name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'placeholder' => 'Your message', 'required' => true)
                        )
                    )
                )
            ),
            'apply_form' => array(
                'name' => 'Application Form',
                'description' => 'Multi-section application form',
                'category' => 'forms',
                'icon' => '📝',
                'fields' => array(
                    'sections' => array(
                        'type' => 'repeater',
                        'label' => 'Form Sections',
                        'fields' => array(
                            'title' => array('type' => 'text', 'label' => 'Section Title', 'default' => 'Personal Information'),
                            'fields' => array('type' => 'textarea', 'label' => 'Fields JSON', 'default' => '[]')
                        ),
                        'default' => array(
                            array(
                                'title' => 'Personal Information',
                                'fields' => '[{"name":"first_name","label":"First Name","type":"text","required":true},{"name":"last_name","label":"Last Name","type":"text","required":true}]'
                            )
                        )
                    )
                )
            ),
            'applied' => array(
                'name' => 'Application Success',
                'description' => 'Success message after form submission',
                'category' => 'content',
                'icon' => '✅',
                'fields' => array(
                    'title' => array(
                        'type' => 'text',
                        'label' => 'Title',
                        'default' => 'Application Received!'
                    ),
                    'message' => array(
                        'type' => 'textarea',
                        'label' => 'Success Message',
                        'default' => 'Your application has been successfully submitted.'
                    ),
                    'next_steps' => array(
                        'type' => 'repeater',
                        'label' => 'Next Steps',
                        'fields' => array(
                            'step' => array('type' => 'text', 'label' => 'Step', 'default' => 'We will review your application')
                        ),
                        'default' => array(
                            array('step' => 'We will review your application within 3-5 business days'),
                            array('step' => 'Check your email regularly for updates'),
                            array('step' => 'Join our Discord community for real-time updates')
                        )
                    )
                )
            ),
            'contacted' => array(
                'name' => 'Contact Success',
                'description' => 'Success message after contact form submission',
                'category' => 'content',
                'icon' => '📧',
                'fields' => array(
                    'title' => array(
                        'type' => 'text',
                        'label' => 'Title',
                        'default' => 'Message Received!'
                    ),
                    'message' => array(
                        'type' => 'textarea',
                        'label' => 'Success Message',
                        'default' => 'Your message has been successfully submitted.'
                    )
                )
            ),
            'title' => array(
                'name' => 'Title Section',
                'description' => 'Simple title section',
                'category' => 'content',
                'icon' => '📄',
                'fields' => array(
                    'text' => array(
                        'type' => 'text',
                        'label' => 'Title Text',
                        'default' => 'Section Title'
                    ),
                    'level' => array(
                        'type' => 'select',
                        'label' => 'Heading Level',
                        'options' => array('h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4'),
                        'default' => 'h2'
                    ),
                    'align' => array(
                        'type' => 'select',
                        'label' => 'Text Alignment',
                        'options' => array('left' => 'Left', 'center' => 'Center', 'right' => 'Right'),
                        'default' => 'center'
                    )
                )
            ),
            'title_2' => array(
                'name' => 'Title Style 2',
                'description' => 'Two-part title with different colors',
                'category' => 'content',
                'icon' => '📄',
                'fields' => array(
                    'first' => array(
                        'type' => 'text',
                        'label' => 'First Title (Grey)',
                        'default' => 'OUR',
                        'placeholder' => 'First part of title'
                    ),
                    'second' => array(
                        'type' => 'text',
                        'label' => 'Second Title (Red)',
                        'default' => 'MISSION',
                        'placeholder' => 'Second part of title'
                    )
                )
            ),
            'title_3' => array(
                'name' => 'Title Style 3',
                'description' => 'Third title style variation',
                'category' => 'content',
                'icon' => '📄',
                'fields' => array(
                    'text' => array(
                        'type' => 'text',
                        'label' => 'Title Text',
                        'default' => 'Section Title'
                    ),
                    'level' => array(
                        'type' => 'select',
                        'label' => 'Heading Level',
                        'options' => array('h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4'),
                        'default' => 'h3'
                    )
                )
            ),
            'values' => array(
                'name' => 'Values Hero',
                'description' => 'Hero section with values and mission',
                'category' => 'header',
                'icon' => '🎯',
                'fields' => array(
                    'title' => array(
                        'type' => 'text',
                        'label' => 'Main Title',
                        'default' => 'Welcome to <span class="text-red-500">Suceava Hacks</span>'
                    ),
                    'subtitle' => array(
                        'type' => 'text',
                        'label' => 'Subtitle',
                        'default' => 'STUDENT-LED TECH COMMUNITY'
                    ),
                    'description' => array(
                        'type' => 'textarea',
                        'label' => 'Description',
                        'default' => 'Join a vibrant community of students passionate about technology and innovation.'
                    ),
                    'primary_button_text' => array(
                        'type' => 'text',
                        'label' => 'Primary Button Text',
                        'default' => 'Get Involved'
                    ),
                    'primary_button_url' => array(
                        'type' => 'url',
                        'label' => 'Primary Button URL',
                        'default' => '/join.php'
                    ),
                    'secondary_button_text' => array(
                        'type' => 'text',
                        'label' => 'Secondary Button Text',
                        'default' => 'Learn More'
                    ),
                    'secondary_button_url' => array(
                        'type' => 'url',
                        'label' => 'Secondary Button URL',
                        'default' => '/about.php'
                    )
                )
            ),
            'box' => array(
                'name' => 'Content Box',
                'description' => 'Simple content container box',
                'category' => 'content',
                'icon' => '📦',
                'fields' => array(
                    'content' => array(
                        'type' => 'wysiwyg',
                        'label' => 'Box Content',
                        'default' => '<p>Your content here</p>'
                    ),
                    'background_color' => array(
                        'type' => 'color',
                        'label' => 'Background Color',
                        'default' => '#f8f9fa'
                    ),
                    'padding' => array(
                        'type' => 'select',
                        'label' => 'Padding',
                        'options' => array('small' => 'Small', 'medium' => 'Medium', 'large' => 'Large'),
                        'default' => 'medium'
                    )
                )
            ),
            'custom' => array(
                'name' => 'Custom HTML',
                'description' => 'Custom HTML content block',
                'category' => 'content',
                'icon' => '🔧',
                'fields' => array(
                    'html' => array(
                        'type' => 'wysiwyg',
                        'label' => 'Custom HTML',
                        'default' => '<div class="custom-section"><p>Your custom content here</p></div>'
                    )
                )
            ),
            'members_grid' => array(
                'name' => 'Members Grid',
                'description' => 'Display team members in a grid layout',
                'category' => 'content',
                'icon' => '👥',
                'fields' => array(
                    'title' => array(
                        'type' => 'text',
                        'label' => 'Grid Title',
                        'default' => 'Our Team'
                    ),
                    'subtitle' => array(
                        'type' => 'text',
                        'label' => 'Grid Subtitle',
                        'default' => 'Meet the PULSE community'
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
            )
        );
    }

    public function getComponent($type)
    {
        return isset($this->components[$type]) ? $this->components[$type] : null;
    }

    public function getComponents()
    {
        return $this->components;
    }

    public function getComponentsByCategory($category)
    {
        $filtered = array();
        foreach ($this->components as $key => $component) {
            if ($component['category'] === $category) {
                $filtered[$key] = $component;
            }
        }
        return $filtered;
    }

    public function renderComponent($componentType, $componentName, $props = array())
    {
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

    public function renderBlock($blockName, $props = array())
    {
        return $this->renderComponent('blocks', $blockName, $props);
    }

    public function componentExists($componentType, $componentName)
    {
        $componentPath = $this->componentsPath . $componentType . '/' . $componentName . '.php';
        return file_exists($componentPath);
    }

    public function getComponentPath($componentType, $componentName)
    {
        return $this->componentsPath . $componentType . '/' . $componentName . '.php';
    }

    public function render($type, $data = array(), $blockId = null)
    {
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
