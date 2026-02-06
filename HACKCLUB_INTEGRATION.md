# Hack Club OAuth Integration

Complete Hack Club authentication integration for your application.

## Features

- **OAuth 2.0 Authentication**: Users can sign in with their Hack Club account
- **Account Linking**: Link Hack Club accounts to existing user profiles
- **User Data Sync**: Automatically syncs user information including:
  - Name (first and last)
  - Email address
  - Slack ID
  - Verification status
  - YSWS eligibility
- **Admin Dashboard**: Manage settings and view statistics
- **Secure Token Management**: Stores access and refresh tokens securely

## Installation

### 1. Run Database Migration

Execute the SQL migration to create the necessary tables:

```bash
mysql -u your_username -p your_database < MIGRATION_HACKCLUB_OAUTH.sql
```

This creates:
- `hackclub_login_sessions` - Temporary OAuth session storage
- `hackclub_links` - User account links with Hack Club
- Settings entries for OAuth configuration

### 2. Create Hack Club App

1. Visit [https://auth.hackclub.com/developer/apps](https://auth.hackclub.com/developer/apps)
2. Turn on developer mode in your [identity settings](https://auth.hackclub.com/identity/edit)
3. Click "app me up!" to create a new app
4. Fill in your app details:
   - **Name**: Your application name
   - **Redirect URI**: `https://yourdomain.com/auth/hackclub/`
   - **Description**: Brief description of your app
5. Copy your **Client ID** and **Client Secret**

### 3. Configure OAuth Settings

1. Go to your dashboard: `/dashboard/hackclub-settings.php`
2. Enter your Client ID and Client Secret
3. Set your Redirect URI (must match the one in Hack Club app)
4. Click "Save Settings"

## File Structure

```
pulse/
├── auth/hackclub/
│   └── index.php                    # OAuth callback handler
├── core/classes/
│   └── HackClubOAuth.php           # Main OAuth class
├── dashboard/
│   ├── hackclub-settings.php       # Admin settings page
│   └── hackclub-linked.php         # Success page after linking
└── MIGRATION_HACKCLUB_OAUTH.sql    # Database schema
```

## Usage

### For Users

#### Login with Hack Club
1. Navigate to `/dashboard/login.php`
2. Click "Login with Hack Club"
3. Authorize the app on Hack Club
4. Redirected to dashboard upon success

#### Link Hack Club Account
1. Login to your existing account
2. Go to profile settings
3. Click "Link Hack Club Account"
4. Authorize and confirm linking

### For Developers

#### Check if User Has Linked Account

```php
require_once 'core/classes/HackClubOAuth.php';

$hackclub = new HackClubOAuth($db);
$link = $hackclub->getLinkedAccount($userId);

if ($link) {
    echo "Hack Club ID: " . $link['hackclub_id'];
    echo "Verification: " . $link['verification_status'];
    echo "YSWS Eligible: " . ($link['ysws_eligible'] ? 'Yes' : 'No');
}
```

#### Generate Auth URL

```php
$hackclub = new HackClubOAuth($db);

// For login
$loginUrl = $hackclub->generateAuthUrl(true);

// For account linking
$linkUrl = $hackclub->generateAuthUrl(false);
```

#### Unlink Account

```php
$hackclub = new HackClubOAuth($db);
$hackclub->unlinkAccount($userId);
```

## OAuth Flow

### Login Flow
```
User → Click "Login with Hack Club"
    → Redirect to auth.hackclub.com/oauth/authorize
    → User authorizes app
    → Redirect to /auth/hackclub/?code=xxx&state=xxx
    → Exchange code for access token
    → Fetch user info from /api/v1/me
    → Check if Hack Club account is linked
    → Log user in
    → Redirect to dashboard
```

### Account Linking Flow
```
Logged in User → Click "Link Hack Club"
    → Redirect to auth.hackclub.com/oauth/authorize
    → User authorizes app
    → Redirect to /auth/hackclub/?code=xxx&state=xxx
    → Exchange code for access token
    → Fetch user info from /api/v1/me
    → Create/update link in database
    → Redirect to success page
```

## API Endpoints

### Hack Club Auth API

- **Authorization**: `https://auth.hackclub.com/oauth/authorize`
- **Token Exchange**: `https://auth.hackclub.com/oauth/token`
- **User Info**: `https://auth.hackclub.com/api/v1/me`

### Scopes Requested

- `openid` - OpenID Connect
- `profile` - Basic profile information
- `email` - Email address
- `name` - First and last name
- `slack_id` - Slack user ID
- `verification_status` - Verification and YSWS eligibility

## Database Schema

### hackclub_links Table

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT | Primary key |
| `user_id` | INT | Foreign key to users table |
| `hackclub_id` | VARCHAR(255) | Hack Club user ID |
| `first_name` | VARCHAR(255) | User's first name |
| `last_name` | VARCHAR(255) | User's last name |
| `email` | VARCHAR(255) | User's email |
| `slack_id` | VARCHAR(255) | Slack user ID (optional) |
| `verification_status` | VARCHAR(50) | Verification status |
| `ysws_eligible` | TINYINT | YSWS eligibility flag |
| `access_token` | TEXT | OAuth access token |
| `refresh_token` | TEXT | OAuth refresh token |
| `token_expires_at` | DATETIME | Token expiration |
| `created_at` | TIMESTAMP | Link creation time |
| `updated_at` | TIMESTAMP | Last update time |

## Security Considerations

1. **CSRF Protection**: State tokens prevent CSRF attacks
2. **Token Storage**: Access tokens stored securely in database
3. **Session Validation**: Login sessions expire after 10 minutes
4. **Unique Constraints**: One Hack Club account per user
5. **Foreign Keys**: Cascading deletes when user is removed

## Troubleshooting

### "OAuth not configured" Error
- Ensure Client ID and Client Secret are set in settings
- Verify Redirect URI matches exactly

### "Invalid or expired state token"
- Clear browser cookies and try again
- Check that session storage is working

### "This Hack Club account is already linked"
- Account is linked to another user
- Contact admin to unlink or use different account

### Token Expired
- Tokens expire after 6 months
- Re-authenticate to refresh token
- Refresh logic not yet implemented

## Future Enhancements

- [ ] Automatic token refresh before expiration
- [ ] Admin panel to view all linked accounts
- [ ] Bulk import/sync from Hack Club
- [ ] Profile sync on login
- [ ] Support for additional scopes (HQ only)

## Documentation

- [Hack Club Auth TL;DR](https://auth.hackclub.com/docs/tldr)
- [OAuth Guide](https://auth.hackclub.com/docs/oauth-guide)
- [API Reference](https://auth.hackclub.com/docs/api)

## Support

For issues with:
- **Integration**: Check this documentation
- **Hack Club Auth**: Contact Hack Club support
- **Your app**: Check logs in error_log

## License

Part of the Pulse application. See main application license.
