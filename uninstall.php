<?php

/**
 * Fired when the plugin is uninstalled.
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete database table
global $wpdb;
$table_name = $wpdb->prefix . 'echbay_mail_queue';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// Delete plugin options
$options = array(
    'emqm_batch_size',
    'emqm_cron_interval',
    'emqm_enable_queue',
    'emqm_queue_for_guests_only',
    'emqm_enable_logging',
    'emqm_max_attempts',
    'emqm_delete_sent_after_days',
    'emqm_daily_email_limit',
    'emqm_active_hour_start',
    'emqm_active_hour_end',
    'emqm_use_wp_cron',
    'emqm_last_cron_run'
);

foreach ($options as $option) {
    delete_option($option);
}

// Clear scheduled events - không cần thiết vì đã bỏ WordPress cron
// wp_clear_scheduled_hook('emqm_process_queue');
// wp_clear_scheduled_hook('emqm_cleanup_old_emails');
