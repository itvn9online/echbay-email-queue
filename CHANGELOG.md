# Changelog

## [1.1.3] - 2025-08-28

### Improved

- **Enhanced Iframe Display**: Now shows iframe src URLs instead of generic "[iframe removed for security]" message
- Better iframe tracking detection and analysis capabilities
- Comprehensive iframe source identification for email debugging

### Changed

- Iframe removal now uses callback function to extract and display src attribute
- Enhanced visual feedback showing actual iframe URLs: `[iframe: URL]`
- Added fallback handling for iframes without src attribute
- Added word-break styling for long iframe URLs

### Technical Details

- Used preg_replace_callback for iframe src extraction
- Separate handling for iframes with and without src attributes
- Maintains security while providing debugging information
- Consistent styling with other removed element types

## [1.1.2] - 2025-08-28

### Improved

- **Enhanced Tracking Pixel Display**: Now shows tracking pixel src URLs instead of generic "[Tracking pixel removed]" message
- Better tracking detection with support for both width-height and height-width attribute patterns
- Comprehensive tracking pixel identification for improved email analysis

### Changed

- Tracking pixel removal now uses callback function to extract and display src attribute
- Added support for both `width="1" height="1"` and `height="1" width="1"` patterns
- Enhanced visual feedback showing actual tracking pixel URLs: `[Tracking pixel: URL]`
- Added word-break styling for long tracking URLs

### Technical Details

- Used preg_replace_callback for tracking pixel src extraction
- Dual regex patterns to handle different attribute ordering
- Maintains security while providing debugging information
- Consistent styling with regular image removal

## [1.1.1] - 2025-08-28

### Improved

- **Enhanced Image Display**: Now shows image src URLs instead of generic "[Image removed for security]" message
- Better debugging experience by displaying actual image sources in safe preview mode
- Improved tracking pixel detection by processing them before general image removal
- Added word-break styling for long URLs to prevent layout issues

### Changed

- Image removal now uses callback function to extract and display src attribute
- Reordered regex processing: tracking pixels handled first, then regular images
- Enhanced visual feedback with actual image URLs for easier email debugging

### Technical Details

- Used preg_replace_callback to capture src attribute from img tags
- Added esc_html() sanitization for src URLs
- Improved regex pattern ordering to handle edge cases
- Added word-break: break-all CSS for better URL display

## [1.1.0] - 2025-08-23

### Added

- **Secure Email Display Interface**: Implemented tabbed email viewer to prevent tracking activation
- **Source Code View**: Raw email HTML display in textarea (default view) with double-click to select all
- **Safe Preview Mode**: Sanitized email preview with images, scripts, and tracking pixels removed
- Enhanced security warnings for tracking prevention
- Better email debugging experience with code inspection capability

### Changed

- Email message display now uses tabbed interface instead of direct HTML rendering
- Default view shows source code to prevent accidental tracking activation
- Preview mode strips all potentially dangerous elements (images, scripts, iframes)
- Improved visual distinction between different security levels

### Security

- Prevents tracking pixel activation during email queue review
- Removes scripts that could execute during email preview
- Blocks iframe content that might contain tracking beacons
- Maintains email content visibility while prioritizing security

### Technical Details

- Added CSS grid layout for email message tabs
- JavaScript tab switching with email ID-specific content areas
- Enhanced regex patterns for comprehensive tracking element removal
- WordPress security function integration (wp_kses_post, esc_html)

## [1.0.9] - 2025-08-23

### Added

- **Frontend Auto-run Cronjob option**: New setting `emqm_frontend_autorun` (default: disabled)
- Automatic cronjob execution in frontend footer when visitors access pages
- Triple cronjob reliability: Server cron + Admin backup + Frontend backup
- Integration with existing frontend.html auto-run script for frontend pages
- Performance-conscious implementation with default disabled setting

### Changed

- Enhanced admin settings page with new "Frontend Auto-run Cronjob" option
- Improved script injection comments to distinguish admin vs frontend execution
- Added proper frontend/admin context checks for script execution

### Technical Details

- Frontend auto-run script injected via `wp_footer` hook
- Only executes on frontend pages (not admin) when option is enabled
- Uses same `frontend.html` content with URL placeholder replacement
- Provides third-level backup for email queue processing
- Default disabled to avoid performance impact unless specifically needed

## [1.0.8] - 2025-08-23

### Added

- **Admin Auto-run Cronjob option**: New setting `emqm_admin_autorun` (default: enabled)
- Automatic cronjob execution in admin footer when admin users visit pages
- Backup processing system in case server cron fails
- Integration with existing frontend.html auto-run script
- Admin-only execution with proper permission checks

### Changed

- Enhanced admin settings page with new "Admin Auto-run Cronjob" option
- Improved reliability with dual cronjob approach (server + admin backup)
- Admin footer injection only for users with `manage_options` capability

### Technical Details

- Auto-run script injected via `admin_footer` hook
- Uses existing `frontend.html` content with URL placeholder replacement
- Only executes for admin users and when option is enabled
- Provides seamless backup for email queue processing

## [1.0.7] - 2025-08-22hangelog

## [1.0.8] - 2025-08-22

### Added

- **Auto-update functionality**: Plugin can now automatically check and update from GitHub
- GitHub integration for version checking via `https://github.com/itvn9online/echbay-email-queue/raw/refs/heads/main/VERSION`
- Manual update check button in admin settings page
- Auto-download and install updates from GitHub releases
- Update notifications in WordPress admin plugins page
- `EMQM_Auto_Updater` class for handling all update operations

### Changed

- Plugin version reading now uses external VERSION file instead of hardcoded constant
- Enhanced admin interface with update checking section
- Added AJAX endpoint for manual update checking

### Technical Details

- Integrates with WordPress update system using `pre_set_site_transient_update_plugins` filter
- Handles GitHub archive extraction and proper plugin directory structure
- Supports WordPress standard update workflow and notifications
- Version comparison and update availability detection

## [1.0.5] - 2025-08-16

### Changed

- **BREAKING CHANGE**: Removed WordPress cron functionality completely
- Now uses only server cronjob for better reliability and performance
- Removed `class-cron.php` file as it's no longer needed
- Updated admin settings to show server cron instructions instead of WP cron status
- Simplified activation/deactivation processes without cron scheduling
- Updated cron command example with proper curl syntax for server crontab

### Removed

- WordPress cron schedules (`emqm_five_minutes`, `emqm_two_minutes`, `emqm_one_minute`)
- WP cron event scheduling and clearing
- `wp_schedule_event`, `wp_clear_scheduled_hook`, `wp_next_scheduled` dependencies
- `EMQM_Cron` class and related functionality

## [1.0.0] - 2025-08-14

### Added

- Initial release of Echbay Mail Queue Manager
- Email queue management system
- Database table for storing queued emails
- Admin interface for monitoring email queue
- Batch email sending functionality
- Cron job integration (WP Cron, Server Cron, Client-side Cron)
- SMTP plugin compatibility
- Email retry mechanism with configurable attempts
- Email status tracking (pending, sent, failed)
- Queue statistics and reporting
- Settings page for configuration
- Auto-cleanup of old sent emails
- Client-side cron support via JavaScript
- Server-side cron support via PHP
- AJAX actions for admin interface
- Email filtering and search
- Bulk actions for email management
- Responsive admin interface
- Translation ready
- Comprehensive error logging
- Rate limiting for cron endpoints
- Security nonce verification
- User capability checks
- Plugin activation/deactivation hooks
- Clean uninstall process

### Features

- **Smart Queueing**: Automatically queue emails for non-logged-in users
- **Flexible Cron**: Support for multiple cron methods
- **Performance Optimized**: Batch processing to avoid timeouts
- **User Friendly**: Intuitive admin interface
- **SMTP Ready**: Works with all popular SMTP plugins
- **Monitoring**: Real-time queue statistics and email tracking
- **Configurable**: Extensive settings for customization
- **Secure**: Built with WordPress security best practices

### Technical

- Compatible with WordPress 5.0+
- Requires PHP 7.4+
- MySQL database integration
- RESTful AJAX endpoints
- Responsive CSS framework
- JavaScript event handling
- WordPress hook system integration
- Plugin architecture following WordPress standards
