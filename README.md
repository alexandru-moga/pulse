# Pulse

Pulse is a modular PHP web application for building and managing dynamic websites, dashboards, and member portals. It is designed for easy deployment on shared hosting or webhost tools like Plesk, cPanel, or DirectAdmin.

---

## Features

- **Dynamic Pages:** Create, edit, and delete pages from the admin dashboard. Each page can have its own content blocks stored in a dedicated MySQL table.
- **Block Management:** Add, edit, and delete content blocks for each page via the dashboard.
- **User Management:** Role-based access (Leader, Co-leader, Member, Guest), user profiles, GitHub avatar integration.
- **Header/Menu Management:** Header menu is generated from the `pages` table, supports parent/child menus and role-based visibility.
- **GitHub Avatars:** Member profile photos are loaded directly from GitHub using their username.
- **Responsive Design:** Modern CSS for a clean, responsive interface.
- **No vendor lock-in:** Runs on any PHP/MySQL host, no Composer or Node.js dependencies.

---

## Quick Start (Plesk or Shared Hosting)

### 1. Upload Files

- Upload the entire `pulse` directory to your web root (e.g., `httpdocs/` on Plesk).

### 2. Create the Database

- In Plesk, go to **Databases** and create a new MySQL database (e.g., `pulse_db`).
- Create a new database user and assign it to the database.

### 3. Import the SQL Schema

- Use Plesk's **phpMyAdmin** or the **Import** tool to import the provided SQL file `pulse.sql` into your new database.

### 4. Configure Database Connection

- Edit `core/config.php` and set your database credentials:

```
$db_host = 'localhost';
$db_name = 'pulse_db';
$db_user = 'your_db_user';
$db_pass = 'your_db_password';
```
- Save and upload the file.

### 5. Set Permissions

- Ensure the `public` directory is writable by PHP if you want to allow dynamic page creation.

### 6. Set the default folder in plesk

- Set your default directoru to `/public/`
  
### 7. Access the Application

- Visit your domain (e.g., `https://yourdomain.com`).
- Log in with your admin credentials or create a new user if needed.

---

## Usage

- **Admin Dashboard:**  
- Create, edit, and delete pages and content blocks.
- Only pages with a non-empty `table_name` are editable for blocks.
- **Members:**  
- Users' GitHub avatars are shown if their GitHub username is set.
- **Blocks:**  
- Each page with a `table_name` has its own MySQL table for blocks (e.g., `page_members`).

---

## Project Structure

```
pulse/
├── core/
│ ├── classes/
│ ├── page-template.php
│ └── init.php
├── public/
│ ├── components/
│ │ └── sections/
│ │ └── members-grid.php
│ ├── css/
│ │ ├── main.css
│ │ └── components/sections/members.css
│ ├── dashboard/
│ │ ├── add-block.php
│ │ ├── edit-block.php
│ │ ├── delete-block.php
│ │ └── page-settings.php
│ ├── index.php
│ └── members.php
└── README.md
```

---

## Troubleshooting

- **"Invalid page or block ID":**  
  Make sure your URLs use `?id=...` and `&block_id=...` and that the IDs exist in your database.

- **GitHub Avatars not showing:**  
  Ensure the user’s `github_username` is set in the database.

- **PHP Deprecated/Warning:**  
  Always cast possible `null` values to string before using functions like `trim()`.

- **Permission errors:**  
  Make sure PHP has write access to the `public` directory for dynamic page creation.

- **Git Pull Errors:**  
  If you get a message about local changes, commit or stash your changes before pulling:
```
git stash
```

---

## License

This project is licensed under the Apache 2.0 License.

---

**Maintainer:**  
Alexandru Moga
