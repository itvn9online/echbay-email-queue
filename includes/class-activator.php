<?php

/**
 * Fired during plugin activation
 */

if (!defined('ABSPATH')) {
    exit;
}

class EMQM_Activator
{

    /**
     * Short Description.
     *
     * Long Description.
     */
    public static function activate()
    {
        self::create_tables();
        self::set_default_options();
        // Không cần schedule cron vì sử dụng cronjob server
        // self::schedule_cron();
    }

    /**
     * Create database tables
     */
    private static function create_tables()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'echbay_mail_queue';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            to_email varchar(255) NOT NULL,
            subject text NOT NULL,
            message longtext NOT NULL,
            headers text,
            attachments longtext,
            status varchar(20) DEFAULT 'pending',
            priority int(11) DEFAULT 10,
            attempts int(11) DEFAULT 0,
            max_attempts int(11) DEFAULT 3,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            scheduled_at datetime DEFAULT CURRENT_TIMESTAMP,
            sent_at datetime DEFAULT NULL,
            error_message text,
            PRIMARY KEY (id),
            KEY status (status),
            KEY scheduled_at (scheduled_at),
            KEY priority (priority)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Set default plugin options
     */
    private static function set_default_options()
    {
        $default_options = array(
            'batch_size' => 20,
            'cron_interval' => 5, // minutes
            'enable_queue' => 1,
            'queue_for_guests_only' => 1,
            'enable_logging' => 0,
            'max_attempts' => 3,
            'delete_sent_after_days' => 30,
            'use_wp_cron' => 0,
            'prevent_duplicates' => 1,
        );

        foreach ($default_options as $option => $value) {
            if (get_option('emqm_' . $option) === false) {
                add_option('emqm_' . $option, $value);
            }
        }
    }

    /**
     * Schedule cron job
     */
    private static function schedule_cron()
    {
        if (!wp_next_scheduled('emqm_process_queue')) {
            wp_schedule_event(time(), 'emqm_five_minutes', 'emqm_process_queue');
        }
    }
}
