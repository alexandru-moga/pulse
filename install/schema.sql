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
('welcome_title', 'heading', 'Welcome to PULSE', 1),
('welcome_description', 'text', 'Programming University Learning & Software Engineering', 2),
('about_title', 'heading', 'About Us', 3),
('about_description', 'text', 'PULSE is a student-led programming community...', 4),
('active_members', 'counter', '50', 5),
('active_projects', 'counter', '12', 6),
('completed_projects', 'counter', '35', 7),
('mission_title', 'heading', 'Our Mission', 8),
('mission_description', 'text', 'Empowering students through real-world programming experience...', 9),
('events_section', 'custom', '{"title":"Upcoming Events","events":[{"name":"Intro to Github & Web Development","date":"April 12, 2025","time":"14:00 - 17:00","description":"Learn the fundamentals of web development and share your site to get $5 for boba!","button_text":"Register Now","button_link":"/apply"}]}', 10);

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

INSERT INTO page_apply (block_name, block_type, content, order_num) VALUES
('apply_title', 'heading', 'Apply for a Project', 1),
('apply_description', 'text', 'Join one of our exciting projects and gain real-world experience', 2),
('project_list', 'dynamic', 'SELECT id, name, description FROM projects WHERE status = "active"', 3),
('application_form', 'form', '{"fields":[{"name":"project_id","type":"select","label":"Select Project","source":"projects"},{"name":"message","type":"textarea","label":"Why are you interested in this project?"}]}', 4);

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
('logo', '{"logo":"path/to/logo.png","alt":"PULSE Logo"}', 1),
('links', '{"links":[{"text":"Privacy Policy","url":"/privacy"},{"text":"Terms of Service","url":"/terms"}]}', 2),
('cta', '{"text":"Join Us Today!","button_text":"Sign Up","button_link":"/signup"}', 3),
('credits', '{"text":"© 2025 PULSE. All rights reserved."}', 4);