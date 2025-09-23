# Inactive User Access Control Implementation

## Overview
Users with `active_member = 0` can still login but have limited access to their own data only.

## Access Rules for Inactive Users

### What they CAN do:
1. **Login**: Inactive users can login to the dashboard
2. **View Dashboard**: Access main dashboard with warning message
3. **View Own Projects**: See projects they are assigned to (but not new/unassigned projects)
4. **View Own Events**: See only events they have applied for or participated in
5. **View Own Certificates**: Download and view certificates assigned to them
6. **Edit Profile**: Update their basic profile information
7. **Change Password**: Update their password
8. **Logout**: End their session

### What they CANNOT do:
1. **Admin Features**: No access to administration panel (users, applications, etc.)
2. **Edit Integrations**: Cannot link/unlink OAuth accounts
3. **Join New Projects**: Cannot see or apply for unassigned projects
4. **See All Events**: Cannot see events they didn't participate in
5. **Download Other's Certificates**: Cannot access certificates of other users
6. **Admin Certificate Management**: Cannot assign certificates to others

## Implementation Details

### Core Functions (core/init.php)
- `checkActiveOrLimitedAccess()`: Main access control function for inactive users
- `isInactiveUser()`: Check if current user is inactive
- `isActiveUser()`: Check if current user is active

### Login Changes (dashboard/login.php)
- Removed blocking of inactive users at login
- Added debug logging for password reset issues

### Dashboard Changes
- **dashboard/index.php**: Shows warning banner for inactive users
- **dashboard/projects.php**: Filters projects for inactive users
- **dashboard/events.php**: Filters events for inactive users  
- **dashboard/certificates.php**: Maintains existing access control
- **dashboard/profile-edit.php**: Allows limited profile editing
- **dashboard/change-password.php**: Allows password changes

### Menu/UI Changes (dashboard/components/dashboard-header.php)
- Hides "Administration" section for inactive users
- Hides "Edit Integrations" menu for inactive users

### Certificate Access
- **download-manual-certificate.php**: Inactive users can download their own certificates
- Admin certificate functions require active admin status

## File Access Control
Inactive users are restricted to these pages only:
- `index.php` (Dashboard home)
- `profile-edit.php` (Edit profile)
- `change-password.php` (Change password)
- `logout.php` (Logout)
- `certificates.php` (View own certificates)
- `download-manual-certificate.php` (Download own certificates)
- `projects.php` (View own projects)
- `events.php` (View participated events)

Any access to other admin pages will result in 403 Forbidden error.

## Database Changes
No database schema changes required - uses existing `active_member` column in users table.

## Notes
- Inactive status is managed by administrators through the user management interface
- OAuth login methods (Discord, GitHub, Google, Slack) already check for active_member = 1
- Debug logging added for password reset troubleshooting (remove in production)
