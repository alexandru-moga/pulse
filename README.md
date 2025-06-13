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

# 🚀 Deploy Pulse on InfinityFree (Step-by-Step)

## 1. Create Your Free InfinityFree Account

- Go to [https://dash.infinityfree.com/](https://dash.infinityfree.com/)
- Click **Create Account** and select **InfinityFree ($0 forever)**
- Click **Create Now**

---

## 2. Set Up Your Domain

- Enter a subdomain (e.g., `pulse-test`) and check availability  
- Leave the domain extension as default (e.g., `.wuaze.com`)
- Click **Create Account**
- Your domain (e.g., `pulse-test.wuaze.com`) will be shown on your account home page  
---

## 3. Create Your MySQL Database

- In your InfinityFree dashboard, click **MySQL Databases**
- Enter a database name (e.g., `pulse`)
- Click **Create Database**  
- Note your database name, username, and password (shown in the dashboard)

---

## 4. Upload Pulse Files

- Go to **FTP Details** in InfinityFree
- Use an FTP client like [WinSCP](https://winscp.net/) or [FileZilla](https://filezilla-project.org/)
  - Host, Username, Password: copy from your FTP Details in InfinityFree
- Connect and upload all Pulse files and folders (including `/install`) to the `htdocs` directory

---

## 5. Run the Installer

- In your browser, go to `https://your-subdomain.wuaze.com/install`
- Fill in:
  - **Database Host:** (from MySQL Databases, e.g., `sqlXXX.infinityfree.com`)
  - **Database Name:** (e.g., `if0_39226352_pulse`)
  - **Database User:** (e.g., `if0_39226352`)
  - **Database Password:** (from MySQL Databases)
  - **Site URL:** Your full URL (e.g., `https://pulse-test.wuaze.com`)
  - **Site Title:** Your site’s name (e.g., `Pulse Club`)
- Click **Install Pulse**
- The installer will:
  - Connect to your database
  - Import the database structure and data automatically
  - Save your config file
  - Delete the `/install` folder for security

---

## 6. Visit Your Site

- Go to `https://your-subdomain.wuaze.com`
- Your Pulse site is now live!

---

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
