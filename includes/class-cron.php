<?php

/**
 * Cron Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class EMQM_Cron
{

    public function __construct()
    {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks()
    {
        // Add custom cron schedules
        add_filter('cron_schedules', array($this, 'add_cron_schedules'));

        // Hook cron actions
        // add_action('emqm_process_queue', array($this, 'process_queue'));
        add_action('emqm_cleanup_old_emails', array($this, 'cleanup_old_emails'));

        // Schedule cleanup if not already scheduled
        if (!wp_next_scheduled('emqm_cleanup_old_emails')) {
            wp_schedule_event(time(), 'daily', 'emqm_cleanup_old_emails');
        }
    }

    /**
     * Add custom cron schedules
     */
    public function add_cron_schedules($schedules)
    {
        $schedules['emqm_five_minutes'] = array(
            'interval' => 300,
            'display'  => __('Every 5 Minutes', 'echbay-mail-queue')
        );

        $schedules['emqm_two_minutes'] = array(
            'interval' => 120,
            'display'  => __('Every 2 Minutes', 'echbay-mail-queue')
        );

        $schedules['emqm_one_minute'] = array(
            'interval' => 60,
            'display'  => __('Every Minute', 'echbay-mail-queue')
        );

        return $schedules;
    }

    /**
     * Process email queue via cron
     */
    public function process_queue()
    {
        $mail_queue = new EMQM_Mail_Queue();
        // $mail_queue->process_queue();

        // Also cleanup duplicates periodically
        // $mail_queue->cleanup_duplicate_emails();
    }

    /**
     * Cleanup old emails via cron
     */
    public function cleanup_old_emails()
    {
        $mail_queue = new EMQM_Mail_Queue();
        $mail_queue->cleanup_old_emails();
    }
}
