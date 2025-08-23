# Changelog

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
