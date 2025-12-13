# Maintenance Mode Feature

## Overview
A flexible maintenance mode system that allows admins to show maintenance notifications to specific user types (job seekers or employers) without blocking access.

## Features

### ✅ Role-Specific Control
- **Separate toggles** for job seekers and employers
- **Independent messages** for each user type
- **Admin bypass** - Admins never see maintenance mode

### ✅ User-Friendly Notifications
- **Sticky banner** at the top of the page
- **Dismissible** - Users can close the banner
- **Animated** - Smooth slide-down animation
- **Warning style** - Clear yellow/orange color scheme

### ✅ Admin Management
- **Easy toggle** switches to enable/disable
- **Custom messages** for each user type (max 500 characters)
- **Real-time status** indicators
- **Instant updates** - Changes take effect immediately

## How to Use

### For Admins:

1. **Access Maintenance Settings**
   - Go to Admin Dashboard
   - Click "Maintenance Mode" in the sidebar (under System Settings)

2. **Enable Maintenance for Job Seekers**
   - Toggle "Enable Maintenance Mode" switch
   - Customize the message
   - Click "Save Maintenance Settings"

3. **Enable Maintenance for Employers**
   - Same process as job seekers
   - Can be enabled independently

4. **Disable Maintenance**
   - Simply toggle off the switch
   - Click "Save Maintenance Settings"

### For Users:

When maintenance is active:
- A yellow banner appears at the top of every page
- The banner shows the custom message set by admin
- Users can dismiss the banner (it will reappear on page refresh)
- **Users can still use the system normally** - it's just a notification

## Technical Implementation

### Database
- **Table**: `maintenance_settings`
- **Fields**: 
  - `key` (jobseeker_maintenance / employer_maintenance)
  - `is_active` (boolean)
  - `message` (text)

### Middleware
- **CheckMaintenanceMode** - Runs on every web request
- Checks user role and maintenance status
- Adds flash message if maintenance is active
- Admins bypass the check

### Caching
- Settings are cached for 60 seconds
- Cache is cleared when settings are updated
- Improves performance

### Components
- **maintenance-banner.blade.php** - Reusable banner component
- Included in jobseeker and employer layouts
- Sticky positioning for visibility

## Files Created/Modified

### New Files:
1. `database/migrations/2025_10_18_023625_create_maintenance_settings_table.php`
2. `app/Models/MaintenanceSetting.php`
3. `app/Http/Middleware/CheckMaintenanceMode.php`
4. `app/Http/Controllers/Admin/MaintenanceController.php`
5. `resources/views/admin/maintenance/index.blade.php`
6. `resources/views/components/maintenance-banner.blade.php`

### Modified Files:
1. `routes/admin.php` - Added maintenance routes
2. `resources/views/admin/sidebar.blade.php` - Added maintenance link
3. `resources/views/front/layouts/jobseeker-layout.blade.php` - Added banner
4. `resources/views/layouts/employer.blade.php` - Added banner
5. `app/Http/Kernel.php` - Registered middleware

## Routes

- `GET /admin/maintenance` - View maintenance settings
- `PUT /admin/maintenance/update` - Update maintenance settings

## Security

- ✅ Only admins can access maintenance settings
- ✅ Input validation (max 500 characters for messages)
- ✅ CSRF protection on form submission
- ✅ Admins never affected by maintenance mode

## Use Cases

1. **Scheduled Maintenance**
   - Inform users about upcoming system updates
   - Keep them informed without blocking access

2. **Feature Updates**
   - Notify specific user types about new features
   - "We're adding new features! Some functionality may be temporarily limited."

3. **Performance Issues**
   - Warn users about slow performance
   - "We're experiencing high traffic. Please be patient."

4. **Partial Outages**
   - If job seeker features are down, notify only job seekers
   - Employers can continue working normally

## Benefits

✅ **Non-intrusive** - Users can still access the system
✅ **Flexible** - Control each user type independently
✅ **Professional** - Clear communication with users
✅ **Easy to use** - Simple toggle switches
✅ **Performant** - Cached settings, minimal overhead
✅ **Customizable** - Tailor messages for each situation

## Testing

1. **Enable job seeker maintenance**
2. **Log in as a job seeker** - You should see the banner
3. **Log in as an employer** - No banner (unless employer maintenance is also enabled)
4. **Log in as admin** - No banner (admins bypass)
5. **Dismiss the banner** - It should disappear
6. **Refresh the page** - Banner reappears
7. **Disable maintenance** - Banner should not appear anymore

## Future Enhancements (Optional)

- Schedule maintenance windows (start/end times)
- Email notifications before maintenance
- Maintenance history log
- Different severity levels (info, warning, critical)
- Countdown timer for scheduled maintenance
