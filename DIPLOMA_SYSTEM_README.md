# Diploma Template System

## Overview
A comprehensive system for managing and distributing diplomas for event participation and project completion certificates.

## Features

### 1. **Diploma Templates Management** (`/dashboard/diploma-templates.php`)
Admins (Leader/Co-leader) can:
- Create diploma templates for events or projects
- Upload custom background images for diplomas
- Configure certificate text, signatures, and styling
- Enable/disable templates
- Link templates to specific events/projects or make them available to all

### 2. **User Diploma Portal** (`/diplomas.php`)
Users can:
- View all available diplomas they've earned
- Download diplomas for events they attended (status: 'going' or 'participated')
- Download certificates for projects they completed (status: 'accepted' or 'completed')
- See separate sections for event diplomas and project certificates

### 3. **Diploma Generation** (`/diploma_endpoint.php`)
- Generates PDF diplomas based on templates
- Tracks downloads in the `certificate_downloads` table
- Supports custom background images
- Uses professional certificate layout with customizable text

## Database Changes

### New Table: `diploma_templates`
```sql
CREATE TABLE `diploma_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `template_type` enum('project','event') NOT NULL,
  `related_id` int(11) DEFAULT NULL COMMENT 'project_id or event_id',
  `background_image` varchar(500) DEFAULT NULL,
  `certificate_text` text DEFAULT NULL,
  `signature_name` varchar(255) DEFAULT NULL,
  `signature_title` varchar(255) DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
);
```

### Updated Table: `certificate_downloads`
Added new fields to support event diplomas:
- `event_id` - ID of the event (nullable)
- `diploma_template_id` - ID of the diploma template used (nullable)
- `certificate_type` - Added 'event_participated' and 'event_diploma' to enum
- Made `project_id` nullable to support event-only downloads

## How to Use

### For Admins:

1. **Create a Diploma Template:**
   - Go to Dashboard → Diploma Templates
   - Click "Create Template"
   - Choose template type (Event or Project)
   - Optionally select a specific event/project or leave blank for "All"
   - Upload a background image (optional)
   - Enter certificate text (e.g., "For participating in Daydream Timisoara...")
   - Add signature name and title
   - Enable the template

2. **Example Template Created:**
   - Title: "Daydream Timisoara Participation Diploma"
   - Type: Event
   - Related Event: Daydream Timisoara (Event ID 9)
   - Enabled for all participants with status 'going' or 'participated'

### For Users:

1. **View Available Diplomas:**
   - Go to "My Diplomas" in the dashboard menu
   - See all event diplomas and project certificates available

2. **Download a Diploma:**
   - Click "Download Diploma" or "Download Certificate"
   - PDF will be generated and downloaded automatically

## Eligibility Rules

### Event Diplomas:
- User must have an entry in `event_attendance` table
- Status must be 'going' or 'participated'
- A diploma template must exist for the event (or a generic event template)
- Template must be enabled

### Project Certificates:
- User must have an entry in `project_assignments` table
- Status must be 'accepted' or 'completed'
- Certificates are enabled in settings

## API Endpoints

### `/diploma_endpoint.php`
**Parameters:**
- `type` - 'event' or 'project'
- `id` - Event ID or Project ID
- `template_id` (optional) - Specific template ID for events

**Example:**
```
/diploma_endpoint.php?type=event&id=9&template_id=1
/diploma_endpoint.php?type=project&id=2
```

## Navigation Updates

### User Menu:
- Added "My Diplomas" link in the main navigation
- Displays certificate icon next to "My Certificates"

### Admin Menu:
- Added "Diploma Templates" link in the Administration section
- Located below "Certificate Management"

## Files Created/Modified

### New Files:
1. `/dashboard/diploma-templates.php` - Admin interface for template management
2. `/diplomas.php` - User-facing diploma portal
3. `/diploma_endpoint.php` - Diploma generation endpoint
4. `/verify-diploma-system.php` - System verification script

### Modified Files:
1. `/phoenix.sql` - Database schema updates
2. `/core/classes/CertificateGenerator.php` - Added diploma generation methods
3. `/dashboard/components/dashboard-header.php` - Navigation updates

## Testing

Run `/verify-diploma-system.php` to verify:
- Database tables created correctly
- Templates inserted successfully
- CertificateGenerator methods working
- User eligibility checks functioning

## Example Usage

A user who attended "Daydream Timisoara" (Event ID 9):
1. Will see "Daydream Timisoara Participation Diploma" in their diplomas page
2. Can click to download a PDF diploma
3. Download is tracked in `certificate_downloads` table
4. Diploma includes their name, event details, and custom certificate text

## Future Enhancements

Potential improvements:
- Bulk diploma generation for all event participants
- Email diplomas to participants
- Diploma preview before download
- More customization options (fonts, colors, layouts)
- QR code verification on diplomas
- Multiple signature support
- Template versioning
