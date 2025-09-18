# Changelog

## [1.2.3] - 2025-09-19

### Added

- **Cron Process Locking**: Implemented file-based lock mechanism to ensure only one cron process runs at a time
- **Smart Wait System**: Second cron process waits for first to complete instead of failing immediately
- **Lock Timeout Protection**: Automatic cleanup of stuck lock files after 2 minutes
- **Comprehensive Cleanup**: Lock files properly cleaned up on success, error, and timeout

### Technical Details

- **Lock File**: `emqm_cron_lock.txt` stores timestamp of running process
- **Wait Mechanism**: Up to 30 attempts × 2 seconds = 60 seconds maximum wait time
- **Timeout Handling**: Lock files older than 120 seconds are automatically removed
- **Error Handling**: Lock cleanup in exception handlers prevents stuck locks

### Process Flow

1. **Check Existing Lock**: If lock file exists and is recent, wait for completion
2. **Create Lock**: Set lock file with current timestamp before processing
3. **Wait Logic**: Second process waits up to 60 seconds for first to complete
4. **Cleanup**: Lock file removed on successful completion, error, or timeout

### Benefits

- **No Race Conditions**: Prevents multiple cron jobs from processing same emails
- **Resource Protection**: Avoids server overload from concurrent email processing
- **Data Integrity**: Ensures consistent email queue state
- **Graceful Handling**: Second process waits instead of failing immediately

### Lock File Behavior

- **Created**: When cron starts processing (if no `emqm_id` parameter)
- **Contains**: Unix timestamp of process start
- **Timeout**: 120 seconds (2 minutes)
- **Cleanup**: Automatic on completion, error, or timeout

## [1.2.2] - 2025-09-18

### Added

- **Daily Email Limit Setting**: New option to limit maximum emails sent per day
- **Free Email Service Support**: Useful for Gmail, Yahoo, and other free services with daily sending limits
- **Smart Rate Limiting**: Foundation for intelligent email sending based on service limits

### Technical Details

- **New Option**: `emqm_daily_email_limit` with default value 0 (unlimited)
- **Input Range**: 0 to 10,000 emails per day
- **Form Integration**: Added to settings page with proper validation
- **Option Registration**: Registered in WordPress settings system

### Settings Interface

- **Field Name**: "Daily Email Limit"
- **Description**: "Maximum number of emails to send per day. Set to 0 for unlimited. Useful for free email services with daily limits (e.g., Gmail: 500/day)."
- **Input Type**: Number input with min=0, max=10000
- **Default Value**: 0 (unlimited)

### Common Email Service Limits

Examples for reference:

- **Gmail**: ~500 emails/day for free accounts
- **Yahoo Mail**: ~500 emails/day
- **Outlook.com**: ~300 emails/day
- **Custom SMTP**: Varies by provider

### Future Implementation

This setting establishes the foundation for:

- Daily sending quota enforcement
- Rate limiting based on time windows
- Service-specific limit configurations
- Automatic queue throttling

## [1.2.1] - 2025-09-15

### Changed

- **Admin Menu Location**: Moved plugin menu from Settings to Tools section
- **Menu Function**: Changed from `add_options_page()` to `add_management_page()`
- **Hook Reference**: Updated script enqueue hook from `settings_page_echbay-mail-queue` to `tools_page_echbay-mail-queue`

### Technical Details

- Plugin menu now appears under "Tools" instead of "Settings" in WordPress admin
- More appropriate location since this is primarily a management/maintenance tool
- Maintains same functionality and permissions (`manage_options` capability)
- Updated admin script loading to match new page hook

### Benefits

- **Better Organization**: Tools menu is more appropriate for queue management functionality
- **User Experience**: More intuitive location for email queue operations
- **Consistency**: Aligns with WordPress admin menu conventions for utility tools

### Menu Path

- **Before**: Settings → Email Queue
- **After**: Tools → Email Queue

## [1.2.0] - 2025-09-04

### Added

- **Statistics Caching**: Implemented caching for historical statistics that don't change
- **Cache Management**: Added `clear_stats_cache()` method for manual cache clearing
- **Performance Optimization**: Significantly reduced database queries for statistics

### Improved

- **Cached Statistics**:
  - `sent_yesterday`: Cached for 24 hours (86400 seconds)
  - `sent_last_week`: Cached for 7 days (604800 seconds)
  - `sent_last_month`: Cached for 30 days (2592000 seconds)
- **Database Performance**: Historical stats now load from cache instead of running expensive queries
- **Smart Cache Keys**: Date-specific cache keys ensure accurate data retrieval

### Technical Details

- Used WordPress transient API (`get_transient`, `set_transient`, `delete_transient`)
- Cache keys include date strings for uniqueness:
  - `emqm_sent_yesterday_2025-09-03`
  - `emqm_sent_last_week_2025-08-26`
  - `emqm_sent_last_month_2025-08`
- Fallback to database query if cache miss occurs
- Cache invalidation available through `clear_stats_cache()` method

### Benefits

- **Performance**: Up to 75% reduction in database queries for statistics
- **Scalability**: Better performance on sites with large email queue history
- **Resource Usage**: Reduced server load during admin dashboard views
- **User Experience**: Faster loading of statistics dashboard

### Cache Behavior

- **Yesterday**: Cached permanently once the day is over
- **Last Week**: Cached permanently once the week (Monday-Sunday) is complete
- **Last Month**: Cached permanently once the month is over
- **Current Period**: Always fresh from database (not cached)

## [1.1.9] - 2025-09-04

### Fixed

- **Week Calculation Logic**: Fixed incorrect week statistics calculation in email queue stats
- **Proper Week Boundaries**: Now correctly calculates "this week" and "last week" from Monday to Sunday
- **Date Range Accuracy**: Improved date range queries for better statistical accuracy

### Changed

- `sent_this_week`: Now calculates from Monday of current week to Monday of next week
- `sent_last_week`: Now calculates from Monday of last week to Monday of current week
- Enhanced date parameter handling in SQL queries for better security
- Improved `sent_today` and `sent_yesterday` calculations using proper date ranges

### Technical Details

- Used `strtotime('monday this week')` and `strtotime('monday last week')` for accurate week boundaries
- Replaced string concatenation in SQL with proper parameter binding
- Enhanced date range logic to avoid timezone and boundary issues
- Consistent use of `>=` and `<` operators for precise range queries

### Before vs After

**Before (Incorrect):**

- `sent_this_week`: Last 7 days from today
- `sent_last_week`: 14 days ago to 7 days ago

**After (Correct):**

- `sent_this_week`: Monday of current week to Sunday of current week
- `sent_last_week`: Monday of last week to Sunday of last week

## [1.1.4] - 2025-08-28hangelog

## [1.1.8] - 2025-08-28

### Changed

- Change css

## [1.1.7] - 2025-08-28

### Changed

- Change css

## [1.1.6] - 2025-08-28

### Changed

- Change css

## [1.1.5] - 2025-08-28

### Changed

- Change css

## [1.1.4] - 2025-08-28

### Improved

- **CSS Code Organization**: Moved inline styles to CSS classes for better maintainability
- **Cleaner HTML Structure**: Removed numerous inline style attributes from list-emails.php
- **Better Styling Consistency**: Centralized styling in admin-style.css

### Added

- **New CSS Classes**:
  - `.email-source-textarea` - For email source code display
  - `.email-preview-notice` - For preview warning messages
  - `.email-message-preview` - For email preview container
  - `.removed-tracking-pixel` - For tracking pixel notifications
  - `.removed-image` - For removed image notifications with src
  - `.removed-image-no-src` - For removed images without src
  - `.removed-script` - For removed script notifications
  - `.removed-iframe` - For removed iframe notifications with src
  - `.removed-iframe-no-src` - For removed iframes without src

### Changed

- Replaced inline styles with semantic CSS classes
- Improved code readability and maintainability
- Consistent styling across all removed element types

### Technical Details

- Separated presentation from content structure
- Enhanced CSS organization in admin-style.css
- Easier customization and theme compatibility
- Reduced HTML file size and improved performance

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
