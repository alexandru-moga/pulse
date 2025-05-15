CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(20) DEFAULT 'member',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  image VARCHAR(255),
  status VARCHAR(20) DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE applications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  project_id INT,
  status VARCHAR(20) DEFAULT 'pending',
  applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
);

CREATE TABLE pages (
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

CREATE TABLE menus (
  id INT AUTO_INCREMENT PRIMARY KEY,
  page_id INT NOT NULL,
  parent_id INT DEFAULT NULL,
  title VARCHAR(100) NOT NULL,
  order_num INT DEFAULT 0,
  FOREIGN KEY (page_id) REFERENCES pages(id),
  FOREIGN KEY (parent_id) REFERENCES menus(id)
);

CREATE TABLE page_index (
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

CREATE TABLE page_members (
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

CREATE TABLE page_apply (
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

CREATE TABLE page_contact (
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
('contact_form', 'form', '{"fields":[{"name":"name","type":"text","label":"Name"},{"name":"email","type":"email","label":"Email"},{"name":"message","type":"textarea","label":"Message"}]}', 3);

CREATE TABLE page_dashboard (
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

CREATE TABLE footer (
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