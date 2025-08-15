<?php

/**
 * Fired during plugin deactivation
 */

if (!defined('ABSPATH')) {
    exit;
}

class EMQM_Deactivator
{

    /**
     * Short Description.
     *
     * Long Description.
     */
    public static function deactivate()
    {
        // Không cần clear scheduled events vì đã bỏ WordPress cron
        // self::clear_scheduled_events();
    }

    /**
     * Clear all scheduled events
     */
    private static function clear_scheduled_events()
    {
        // wp_clear_scheduled_hook('emqm_process_queue');
        wp_clear_scheduled_hook('emqm_cleanup_old_emails');
    }
}
