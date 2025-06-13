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

# 🚀 Deploy using XAMPP

This guide will help you set up the Pulse PHP project on your own computer using **XAMPP** and **phpMyAdmin**.  
Just follow these steps to get your local development environment running!

---

## 1. Install XAMPP

- Download XAMPP from [https://www.apachefriends.org/index.html](https://www.apachefriends.org/index.html)
- Run the installer and follow the prompts (default settings are fine).
- Open the **XAMPP Control Panel** and click **Start** for both **Apache** and **MySQL**.

---

## 2. Copy Pulse Files to XAMPP

- Find your XAMPP installation directory (usually `C:\xampp` on Windows).
- Open the `htdocs` folder inside XAMPP.
- Copy all Pulse project files and folders into `C:\xampp\htdocs\pulse`
  - Your structure should look like:
    ```
    C:\xampp\htdocs\pulse\
        index.php
        core/
        components/
        css/
        ...etc.
    ```

---

## 3. Create a Database Using phpMyAdmin

- In your browser, go to [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
- Click the **Databases** tab at the top.
- Enter a name for your database, e.g. `pulse`
- Click **Create**

---

## 4. Set Database Details

- Go to [http://localhost/pulse/install](http://localhost/pulse/install) in your web browser.
- Fill Out the Installer Form
    Database Host: localhost
    Database Name: (e.g., pulse)
    Database User: root (default for XAMPP)
    Database Password: (leave blank for XAMPP default)
    Site URL: http://localhost/pulse
    Site Title: (e.g., Pulse Club)
    Click Install Pulse.



## **Troubleshooting**

- **Database connection errors:** Double-check your host, database name, user, and password in InfinityFree’s MySQL Databases page.
- **Installer not found:** Make sure you uploaded all files to the `htdocs` directory.
- **File permissions:** InfinityFree sets these automatically; you usually don’t need to change them.

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
