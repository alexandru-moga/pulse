![Hackatime Stats](https://github-readme-stats.hackclub.dev/api/wakatime?username=1727&api_domain=hackatime.hackclub.com&&custom_title=Hackatime+Stats&layout=compact&cache_seconds=0&langs_count=8&theme=dracula)
# Pulse

Pulse is a modular PHP web application for building and managing dynamic websites, dashboards, and member portals. - **GitHub Avatars not showing:**  
  Ensure the user has linked their GitHub account through the dashboard integrations page. is designed for easy deployment on shared hosting or webhost tools like Plesk, cPanel, or DirectAdmin.

---

## Features

- **Dynamic Pages:** Create, edit, and delete pages from the admin dashboard. Each page can have its own content blocks stored in a dedicated MySQL table.
- **Block Management:** Add, edit, and delete content blocks for each page via the dashboard.
- **User Management:** Role-based access (Leader, Co-leader, Member, Guest), user profiles, GitHub, Discord, Google, and Slack integration.
- **Header/Menu Management:** Header menu is generated from the `pages` table, supports parent/child menus and role-based visibility.
- **GitHub Integration:** Member profile photos and links loaded from GitHub, OAuth login and account linking.
- **Discord Integration:** Automated role management for project acceptance, pizza grants, and event participation with cleanup functionality.
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

## 4. Import the Database Schema

- With your new database selected in phpMyAdmin, click the **Import** tab.
- Click **Browse** and select the `pulse.sql` file from the Pulse project (download from GitHub if needed).
- Click **Go** to import the database structure and initial data.

---

## 5. Access website
- You can access pulse by going to [http://localhost/pulse](http://localhost/pulse)

---

## **Troubleshooting**

- **Database connection errors:** Database credentials detaults are set for XAMPP. If you want to change database name, user, password or host you have to modify .env file.

---

## Usage

- **Admin Dashboard:**
- Default credentials are:
    User: admin@example.com
    Password: pulse
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

---

## OAuth Integration Setup

### Google OAuth Setup
1. Go to [Google Cloud Console](https://console.cloud.google.com/apis/credentials)
2. Create a new project or select existing
3. Enable Google+ API
4. Create OAuth 2.0 credentials:
   - Application type: Web application
   - Authorized JavaScript origins: `http://localhost/pulse`
   - Authorized redirect URIs: `http://localhost/pulse/auth/google/`
5. Configure in Dashboard → Settings → Google Settings

### Discord OAuth Setup
1. Go to [Discord Developer Portal](https://discord.com/developers/applications)
2. Create New Application
3. Go to OAuth2 settings:
   - Redirect URLs: `http://localhost/pulse/auth/discord/`
   - Scopes: identify, email
4. Configure in Dashboard → Settings → Discord Settings

### GitHub OAuth Setup
1. Go to [GitHub Developer Settings](https://github.com/settings/applications/new)
2. Create OAuth App:
   - Homepage URL: `http://localhost/pulse`
   - Authorization callback URL: `http://localhost/pulse/auth/github/`
3. Configure in Dashboard → Settings → GitHub Settings

### Slack OAuth Setup
1. Go to [Slack API Apps](https://api.slack.com/apps)
2. Create New App
3. Configure OAuth & Permissions:
   - Redirect URLs: `http://localhost/pulse/auth/slack/`
   - Scopes: identity.basic, identity.email, identity.team
4. Configure in Dashboard → Settings → Slack Settings

## File Structure
```
auth/
├── google/
│   └── index.php          # Google OAuth handler
├── discord/
│   └── index.php          # Discord OAuth handler  
├── github/
│   └── index.php          # GitHub OAuth handler
└── slack/
    └── index.php          # Slack OAuth handler
```

---

## Component System Migration (2025)

Pulse has been migrated from the old component system to a new drag-and-drop component architecture. All legacy components have been successfully migrated.

### Migration Summary

**Migrated Components:**
- `components/old-components/` → `components/templates/`
- Updated all component type names from kebab-case to snake_case (e.g., `title-3` → `title_3`)
- All page data tables (`page_apply`, `page_contact`, `page_index`, `page_members`) updated to use new component types

**New Component System Features:**
- Drag-and-drop interface in dashboard
- Standardized template structure in `components/templates/`
- Centralized component management via `ComponentManager.php`
- Backward compatibility maintained through `PageManager.php`

**Legacy Components Archived:**
- Old components moved to `components/archived-components/` for reference
- New component templates created for all migrated components
- Database schema updated with new component types

---
