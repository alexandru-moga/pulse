CREATE TABLE IF NOT EXISTS users (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(100) DEFAULT NULL,
  last_name VARCHAR(100) DEFAULT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  discord_id VARCHAR(255) DEFAULT NULL,
  slack_id TEXT DEFAULT NULL,
  github_username TEXT DEFAULT NULL,
  school VARCHAR(255) DEFAULT NULL,
  ysws_projects TEXT DEFAULT NULL,
  hcb_member VARCHAR(255) DEFAULT NULL,
  birthdate DATE DEFAULT NULL,
  class VARCHAR(20) DEFAULT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  role VARCHAR(50) DEFAULT NULL,
  join_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  description TEXT DEFAULT NULL,
  active_member BOOLEAN DEFAULT FALSE
  );

CREATE TABLE IF NOT EXISTS applications (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  school VARCHAR(255) NOT NULL,
  class VARCHAR(20) NOT NULL,
  birthdate DATE NOT NULL,
  phone VARCHAR(20) NOT NULL,
  superpowers TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  student_id VARCHAR(255) DEFAULT NULL,
  discord_username VARCHAR(255) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS contacts (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS pages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) UNIQUE NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  table_name VARCHAR(100) NULL,
  module_config TEXT,
  menu_enabled BOOLEAN DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO pages (name, title, description, table_name, menu_enabled) VALUES
('index', 'Home', 'Welcome to PULSE - Programming University Learning & Software Engineering', 'page_index', 1),
('members', 'Members', 'Our PULSE community members', 'page_members', 1),
('apply', 'Apply', 'Apply for a PULSE project', 'page_apply', 1),
('contact', 'Contact', 'Get in touch with PULSE', 'page_contact', 1),
('dashboard', 'Dashboard', 'Member dashboard', 'page_dashboard', 1);

CREATE TABLE IF NOT EXISTS menus (
  id INT AUTO_INCREMENT PRIMARY KEY,
  page_id INT NOT NULL,
  parent_id INT DEFAULT NULL,
  title VARCHAR(100) NOT NULL,
  order_num INT DEFAULT 0,
  FOREIGN KEY (page_id) REFERENCES pages(id),
  FOREIGN KEY (parent_id) REFERENCES menus(id)
);

ALTER TABLE menus ADD COLUMN visibility VARCHAR(50) DEFAULT NULL;
ALTER TABLE menus ADD COLUMN roles VARCHAR(255) DEFAULT NULL;

CREATE TABLE IF NOT EXISTS page_index (
  id INT AUTO_INCREMENT PRIMARY KEY,
  block_name VARCHAR(100) NOT NULL,
  block_type VARCHAR(50) NOT NULL,
  content TEXT,
  order_num INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
);

INSERT INTO page_index (block_name, block_type, content, order_num) VALUES
('welcome', 'welcome', '{
  "title": "Welcome to <span class=\"text-red-500\">PULSE</span>",
  "subtitle": "STUDENT-LED TECH COMMUNITY",
  "description": "Join a vibrant community of students passionate about technology and innovation. We build, learn, and grow together through hackathons, workshops, and collaborative projects.",
  "primaryButton": {
    "text": "Get Involved",
    "url": "/apply.php"
  },
  "secondaryButton": {
    "text": "Our team",
    "url": "/team.php"
  }
}', 1),
('title-2', 'title-2', '{
  "first": "OUR",
  "second": "ACTIVITY"
}', 2),
('active_members', 'stats', '{
  "first": "OUR",
  "second": "IMPACT"
}', 3),
('title-2', 'title-2', '{
  "first": "OUR",
  "second": "IMPACT"
}', 4),
('core_values', 'core_values', '{
    "values": [
      {
        "title": "Excellence",
        "description": "We strive for excellence in everything we do, pushing boundaries and challenging the status quo.",
        "icon": "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" fill=\"currentColor\" viewBox=\"0 0 24 24\"><path d=\"M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.967-7.417 3.967 1.481-8.279-6.064-5.828 8.332-1.151z\"/></svg>"
      },
      {
        "title": "Innovation",
        "description": "We embrace innovation, encouraging creative thinking and novel approaches to problem-solving.",
        "icon": "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" fill=\"currentColor\" viewBox=\"0 0 24 24\"><path d=\"M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-1.5 5h3v7.5h-3v-7.5zm1.5 12.75c-.69 0-1.25-.56-1.25-1.25s.56-1.25 1.25-1.25 1.25.56 1.25 1.25-.56 1.25-1.25 1.25z\"/></svg>"
      },
      {
        "title": "Integrity",
        "description": "We act with integrity in all our dealings, maintaining the highest ethical standards and transparency.",
        "icon": "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" fill=\"currentColor\" viewBox=\"0 0 24 24\"><path d=\"M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-1.31 7.526c-.099-.807.528-1.526 1.348-1.526.771 0 1.377.676 1.28 1.451l-.757 6.053c-.035.283-.276.496-.561.496s-.526-.213-.562-.496l-.748-5.978zm1.31 10.724c-.69 0-1.25-.56-1.25-1.25s.56-1.25 1.25-1.25 1.25.56 1.25 1.25-.56 1.25-1.25 1.25z\"/></svg>"
      }
    ]
  }', 5),

CREATE TABLE IF NOT EXISTS page_members (
  id INT AUTO_INCREMENT PRIMARY KEY,
  block_name VARCHAR(100) NOT NULL,
  block_type VARCHAR(50) NOT NULL,
  content TEXT,
  order_num INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
);

INSERT INTO page_members (block_name, block_type, content, order_num) VALUES
('members_title', 'heading', 'Our Members', 1),
('members_description', 'text', 'Meet the talented programmers who make up PULSE', 2),
('members_list', 'dynamic', 'SELECT id, username, email FROM users', 3);

CREATE TABLE IF NOT EXISTS page_apply (
  id INT AUTO_INCREMENT PRIMARY KEY,
  block_name VARCHAR(100) NOT NULL,
  block_type VARCHAR(50) NOT NULL,
  content TEXT,
  order_num INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
);

INSERT INTO page_apply 
(block_name, block_type, content, order_num, is_active) 
VALUES 
('apply_title', 'title-3', '{"text": "Join Suceava Hacks"}', 1, 1),
('apply_subtitle', 'heading-3', '{"text": "Ready to start your coding journey? Fill out... developers in Suceava."}', 2, 1),
('apply_form', 'apply-form', '{
  "sections": [
    {
      "title": "Personal Information",
      "fields": [
        {
          "name": "first_name",
          "label": "First Name",
          "type": "text",
          "placeholder": "Your first name",
          "required": true
        },
        {
          "name": "last_name",
          "label": "Last Name",
          "type": "text",
          "placeholder": "Your last name",
          "required": true
        }
      ]
    },
    {
      "title": "Academic Information",
      "fields": [
        {
          "name": "school",
          "label": "School",
          "type": "text",
          "placeholder": "Your school name",
          "required": true
        }
      ]
    }
  ]
}', 3, 1);

CREATE TABLE IF NOT EXISTS page_applied (
  id INT AUTO_INCREMENT PRIMARY KEY,
  block_name VARCHAR(100) NOT NULL,
  block_type VARCHAR(50) NOT NULL,
  content TEXT,
  order_num INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
);

INSERT INTO page_applied (block_name, block_type, content, order_num) VALUES
('success_message', 'applied', '{
    "title": "Application Received!",
    "message": "Your application has been successfully submitted. Our team will review your submission and contact you shortly.",
    "next_steps": [
      "We will review your application within 3-5 business days",
      "Check your email regularly for updates",
      "Join our Discord community for real-time updates"
    ],
    "cta": {
      "text": "Return to Homepage",
      "url": "/"
    }
  }', 1),

CREATE TABLE IF NOT EXISTS page_contact (
  id INT AUTO_INCREMENT PRIMARY KEY,
  block_name VARCHAR(100) NOT NULL,
  block_type VARCHAR(50) NOT NULL,
  content TEXT,
  order_num INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
);

INSERT INTO page_contact (block_name, block_type, content, order_num) VALUES
('contact_title', 'heading', 'Contact Us', 1),
('contact_description', 'text', 'Get in touch with our team', 2),
('contact_form', 'form', '{
    "title": "Get in Touch",
    "subtitle": "We'll respond within 24 hours",
    "description": "Have a question or want to join our team? Fill out the form below and we'll get back to you.",
    "fields": [
      {"name": "name", "label": "Full Name", "required": true, "placeholder": "Your name"},
      {"name": "email", "label": "Email Address", "required": true, "placeholder": "your.email@example.com"},
      {"name": "message", "label": "Message", "type": "textarea", "required": true, "placeholder": "Write your message here..."}
    ],
    "button_text": "Send Message"
}
', 3);

CREATE TABLE IF NOT EXISTS page_dashboard (
  id INT AUTO_INCREMENT PRIMARY KEY,
  block_name VARCHAR(100) NOT NULL,
  block_type VARCHAR(50) NOT NULL,
  content TEXT,
  order_num INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
);

INSERT INTO page_dashboard (block_name, block_type, content, order_num) VALUES
('dashboard_title', 'heading', 'Member Dashboard', 1),
('dashboard_welcome', 'dynamic', 'Welcome, {username}!', 2),
('my_projects', 'dynamic', 'SELECT p.id, p.name, p.status FROM projects p JOIN applications a ON p.id = a.project_id WHERE a.user_id = {user_id} AND a.status = "approved"', 3),
('my_applications', 'dynamic', 'SELECT a.id, p.name, a.status, a.applied_at FROM applications a JOIN projects p ON a.project_id = p.id WHERE a.user_id = {user_id}', 4),
('admin_panel', 'conditional', '{"condition":"isAdmin()","content":"Admin Panel"}', 5);

CREATE TABLE IF NOT EXISTS footer (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_type ENUM('logo', 'links', 'cta', 'credits') NOT NULL,
    content JSON NOT NULL,
    order_num INT DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO footer (section_type, content, order_num) VALUES
('logo', '{"path": "images/logo.png", "alt": "PULSE Logo"}', 1),
('links', '{
    "title": "Explore",
    "items": [
        {"text": "Members", "url": "/members.php"},
        {"text": "Projects", "url": "/projects.php"},
        {"text": "Contact", "url": "/contact.php"}
    ]
}', 2),
('cta', '{
    "title": "Join Our Community",
    "text": "Get Started Today",
    "url": "/apply.php"
}', 3),
('credits', '{"show_attribution": true}', 4);

CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    value TEXT NOT NULL
);

INSERT INTO settings (name, value) VALUES
('base_url', 'https://example.com'),
('smtp_host', 'smtp.gmail.com'),
('smtp_port', '587'),
('smtp_user', 'your_gmail@gmail.com'),
('smtp_pass', 'your_app_password'),
('smtp_from', 'your_gmail@gmail.com'),
('smtp_from_name', 'PULSE Team');

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id),
    INDEX (token)
);

CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('active', 'completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE user_projects (
    user_id INT,
    project_id INT,
    assigned_by INT,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, project_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (project_id) REFERENCES projects(id)
);

