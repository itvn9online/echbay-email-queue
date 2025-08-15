<?php

/**
 * Helper functions
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get email queue instance
 */
function emqm_get_mail_queue()
{
    return new EMQM_Mail_Queue();
}

/**
 * Add email to queue manually
 */
function emqm_queue_email($to, $subject, $message, $headers = '', $attachments = array(), $priority = 10)
{
    $mail_queue = emqm_get_mail_queue();
    return $mail_queue->add_to_queue($to, $subject, $message, $headers, $attachments, $priority);
}

/**
 * Get queue statistics
 */
function emqm_get_queue_stats()
{
    $mail_queue = emqm_get_mail_queue();
    return $mail_queue->get_queue_stats();
}

/**
 * Process queue manually
 */
function emqm_process_queue()
{
    $mail_queue = emqm_get_mail_queue();
    // return $mail_queue->process_queue();
}

/**
 * Test if current user should bypass queue
 */
function emqm_test_bypass_queue()
{
    $mail_queue = new EMQM_Mail_Queue();

    $debug_info = array(
        'is_user_logged_in' => is_user_logged_in(),
        'is_admin' => is_admin(),
        'current_user_can_manage_options' => current_user_can('manage_options'),
        'queue_for_guests_only' => get_option('emqm_queue_for_guests_only', 1),
        'enable_queue' => get_option('emqm_enable_queue', 1),
    );

    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        $debug_info['current_user'] = array(
            'ID' => $current_user->ID,
            'user_login' => $current_user->user_login,
            'roles' => $current_user->roles
        );
    }

    return $debug_info;
}

/**
 * Format email status for display
 */
function emqm_format_status($status)
{
    $statuses = array(
        'pending' => __('Pending', 'echbay-mail-queue'),
        'sent' => __('Sent', 'echbay-mail-queue'),
        'failed' => __('Failed', 'echbay-mail-queue')
    );

    return isset($statuses[$status]) ? $statuses[$status] : $status;
}

/**
 * Get status badge HTML
 */
function emqm_get_status_badge($status)
{
    $badges = array(
        'pending' => '<span class="badge badge-warning">%s</span>',
        'sent' => '<span class="badge badge-success">%s</span>',
        'failed' => '<span class="badge badge-danger">%s</span>'
    );

    $template = isset($badges[$status]) ? $badges[$status] : '<span class="badge badge-secondary">%s</span>';
    return sprintf($template, emqm_format_status($status));
}

/**
 * Format date for display
 */
function emqm_format_date($date)
{
    if (empty($date) || $date == '0000-00-00 00:00:00') {
        return '-';
    }

    return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($date));
}

/**
 * Truncate text
 */
function emqm_truncate($text, $length = 50)
{
    if (strlen($text) < $length) {
        return $text;
    }

    return substr($text, 0, $length) . '...';
}
