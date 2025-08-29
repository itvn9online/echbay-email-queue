<?php

/**
 * Mail Queue Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class EMQM_Mail_Queue
{

    private $table_name;
    // public $the_send;
    private $enable_logging;

    public function __construct($the_send = false)
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'echbay_mail_queue';
        // $this->the_send = $the_send;
        $this->enable_logging = get_option('emqm_enable_logging', 0) > 0 ? true : false;

        // Initialize hooks
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks()
    {
        // Hook into wp_mail to intercept emails
        // remove_filter('pre_wp_mail', array($this, 'intercept_wp_mail'), 10);
        add_filter('pre_wp_mail', array($this, 'intercept_wp_mail'), 10, 2);
    }

    /**
     * Intercept wp_mail and queue emails for non-logged-in users
     */
    public function intercept_wp_mail($null, $atts)
    {
        // Only queue emails if the feature is enabled
        if (isset($_GET['active_wp_mail']) || !get_option('emqm_enable_queue', 1)) {
            return $null;
        }

        // Check if we should bypass queue for logged-in users
        if ($this->should_bypass_queue()) {
            if ($this->enable_logging) {
                $current_user = wp_get_current_user();
                $user_info = $current_user->exists() ? $current_user->user_login : 'guest';
                error_log('EMQM: Email sent immediately for user: ' . $user_info . ' - Subject: ' . $atts['subject']);
            }
            return $null;
        }
        // echo __CLASS__ . ':' . debug_backtrace()[1]['function'] . '<br>' . PHP_EOL;

        // Extract email details
        $to = $atts['to'];
        $subject = $atts['subject'];
        $message = $atts['message'];
        $headers = isset($atts['headers']) ? $atts['headers'] : '';
        $attachments = isset($atts['attachments']) ? $atts['attachments'] : array();
        // có đính kèm thì cũng bỏ qua luôn
        if (!empty($attachments)) {
            return $null;
        }

        // Add to queue
        $this->add_to_queue($to, $subject, $message, $headers, $attachments);

        // Return true to prevent wp_mail from executing
        return true;
    }

    /**
     * Check if should bypass queue for current user
     */
    private function should_bypass_queue()
    {
        // If queue is only for guests and user is logged in, bypass
        if (get_option('emqm_queue_for_guests_only', 1)) {
            foreach ($_COOKIE as $key => $value) {
                if (strpos($key, 'wordpress_logged_in_') === 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Add email to queue
     */
    public function add_to_queue($to, $subject, $message, $headers = '', $attachments = array(), $priority = 10)
    {
        // global $wpdb;
        // echo __CLASS__ . ':' . debug_backtrace()[1]['function'] . '<br>' . PHP_EOL;

        // Handle multiple recipients
        if (is_array($to)) {
            foreach ($to as $recipient) {
                $this->add_single_email_to_queue($recipient, $subject, $message, $headers, $attachments, $priority);
            }
        } else {
            $this->add_single_email_to_queue($to, $subject, $message, $headers, $attachments, $priority);
        }
    }

    /**
     * Add single email to queue
     */
    private function add_single_email_to_queue($to, $subject, $message, $headers, $attachments, $priority)
    {
        global $wpdb;

        // Check for recent duplicate emails (within last 5 minutes) if prevention is enabled
        if (get_option('emqm_prevent_duplicates', 1)) {
            $recent_duplicate = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(id) FROM {$this->table_name} 
                WHERE to_email = %s 
                AND created_at = %s
                AND status = 'pending'
            ", $to, EMQM_FIXED_TIME));

            if ($recent_duplicate > 0) {
                if ($this->enable_logging) {
                    error_log('EMQM: Duplicate email prevented for ' . $to . ' - Time: ' . EMQM_FIXED_TIME);
                }
                // echo __CLASS__ . ':' . debug_backtrace()[1]['function'] . '<br>' . PHP_EOL;
                return false;
            }
        }

        $data = array(
            'to_email' => is_array($to) ? implode(',', $to) : $to,
            'subject' => $subject,
            'message' => $message,
            'headers' => is_array($headers) ? implode("\n", $headers) : $headers,
            // 'attachments' => maybe_serialize($attachments),
            'status' => 'pending',
            'priority' => $priority,
            'max_attempts' => get_option('emqm_max_attempts', 3),
            'created_at' => EMQM_FIXED_TIME,
            'scheduled_at' => EMQM_FIXED_TIME,
        );
        // echo __CLASS__ . ':' . debug_backtrace()[1]['function'] . '<br>' . PHP_EOL;
        // print_r($data);
        // echo '<br>' . PHP_EOL;

        $result = $wpdb->insert($this->table_name, $data);

        if ($result && $this->enable_logging) {
            error_log('EMQM: Email queued for ' . $to . ' - Subject: ' . $subject);
        }

        return $result;
    }

    /**
     * Process email queue
     */
    public function process_queue()
    {
        // Process email queue
        global $wpdb;

        // nếu có ID được truyền vào thì lấy bản ghi theo ID -> dùng khi người dùng muốn gửi lại
        if (isset($_GET['emqm_id'])) {
            $emails = $wpdb->get_results($wpdb->prepare("
                SELECT * FROM {$this->table_name} 
                WHERE id = %d
            ", intval($_GET['emqm_id'])));
        } else {
            $batch_size = get_option('emqm_batch_size', 5);

            // Get pending emails ordered by priority and created date
            $emails = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$this->table_name} 
            WHERE status = 'pending' 
            AND scheduled_at < %s 
            AND attempts < max_attempts 
            ORDER BY id ASC, attempts ASC
            LIMIT %d
            ", EMQM_FIXED_TIME, $batch_size));
        }
        // print_r($emails);
        if (empty($emails)) {
            return 0;
        }

        // Temporarily remove our hook to prevent infinite loop
        // remove_filter('pre_wp_mail', array($this, 'intercept_wp_mail'), 10);

        // giới hạn thời gian cho vòng lặp không quá 55 giây
        $time_limit = time() + 55;

        // thiết lập timeout
        set_time_limit(60);

        // Initialize counters
        $count = 0;
        foreach ($emails as $email) {
            // Check if we've reached the time limit
            if (time() > $time_limit) {
                break;
            }

            // 
            if (!$this->send_email($email)) {
                // Handle failed email -> gửi lỗi thì out luôn thôi
                break;
            }
            $count++;

            // Thêm sleep để tránh quá tải server, có thể điều chỉnh theo nhu cầu
            sleep(1);
        }

        // Re-add our hook
        // add_filter('pre_wp_mail', array($this, 'intercept_wp_mail'), 10, 2);

        return $count;
    }

    /**
     * Send individual email
     */
    private function send_email($email)
    {
        global $wpdb;

        // Increment attempts
        $wpdb->update(
            $this->table_name,
            array('attempts' => $email->attempts + 1),
            array('id' => $email->id)
        );

        // Prepare email data
        $to = $email->to_email;
        $subject = $email->subject;
        $message = $email->message;
        $headers = $email->headers ? explode("\n", $email->headers) : array();
        // $attachments = maybe_unserialize($email->attachments);

        // Send the email
        // $sent = wp_mail($to, $subject, $message, $headers, $attachments);
        $sent = wp_mail($to, $subject, $message, $headers);

        // tạo file log để tính số lần gửi mail thất bại
        $file_count_failed = EMQM_PLUGIN_PATH . 'failed_email_count.log';

        if ($sent) {
            // Mark as sent
            $wpdb->update(
                $this->table_name,
                array(
                    'status' => 'sent',
                    // lấy thời gian thực theo múi giờ
                    'sent_at' => current_time('mysql'),
                    'error_message' => null
                ),
                array('id' => $email->id)
            );

            if ($this->enable_logging) {
                error_log('EMQM: Email sent successfully to ' . $to . ' - Subject: ' . $subject);
            }

            // xóa file log
            if (is_file($file_count_failed)) {
                unlink($file_count_failed);
            }

            // 
            return true;
        } else {
            // Check if max attempts reached
            if ($email->attempts + 1 >= $email->max_attempts) {
                $wpdb->update(
                    $this->table_name,
                    array(
                        'status' => 'failed',
                        'error_message' => 'Max attempts reached'
                    ),
                    array('id' => $email->id)
                );
            }

            if ($this->enable_logging) {
                error_log('EMQM: Failed to send email to ' . $to . ' - Subject: ' . $subject);
            }

            // tính toán số lần thất bại
            if (is_file($file_count_failed)) {
                $failed_email_count = 0;
                $failed_email_count = intval(explode('|', file_get_contents($file_count_failed))[0]);
                file_put_contents($file_count_failed, ($failed_email_count + 1) . '|' . time(), LOCK_EX);
            } else {
                file_put_contents($file_count_failed, '1|' . time(), LOCK_EX);
            }

            // 
            return false;
        }
    }

    /**
     * Retry failed email
     */
    public function retry_email($email_id)
    {
        global $wpdb;

        $wpdb->update(
            $this->table_name,
            array(
                'status' => 'pending',
                'attempts' => 0,
                'scheduled_at' => EMQM_FIXED_TIME,
                'error_message' => null
            ),
            array('id' => $email_id)
        );
    }

    /**
     * Delete email from queue
     */
    public function delete_email($email_id)
    {
        global $wpdb;

        return $wpdb->delete($this->table_name, array('id' => $email_id));
    }

    /**
     * Get queue stats
     */
    public function get_queue_stats()
    {
        global $wpdb;

        $stats = array();

        $stats['pending'] = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM {$this->table_name} WHERE status = %s", 'pending'));
        $stats['sent'] = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM {$this->table_name} WHERE status = %s", 'sent'));
        $stats['failed'] = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM {$this->table_name} WHERE status = %s", 'failed'));
        $stats['total'] = $wpdb->get_var("SELECT COUNT(id) FROM {$this->table_name}");

        return $stats;
    }

    /**
     * Clean up old sent emails
     */
    public function cleanup_old_emails()
    {
        global $wpdb;

        $days = get_option('emqm_delete_sent_after_days', 30);

        if ($days > 0) {
            $wpdb->query($wpdb->prepare("
                DELETE FROM {$this->table_name} 
                WHERE status = 'sent' 
                AND sent_at < DATE_SUB(NOW(), INTERVAL %d DAY)
            ", $days));
        }
    }

    /**
     * Clean up duplicate pending emails
     */
    public function cleanup_duplicate_emails()
    {
        global $wpdb;

        // Delete duplicate pending emails, keeping only the oldest one
        $wpdb->query("
            DELETE t1 FROM {$this->table_name} t1
            INNER JOIN {$this->table_name} t2 
            WHERE t1.id > t2.id 
            AND t1.to_email = t2.to_email 
            AND t1.created_at = t2.created_at 
            AND t1.status = 'pending' 
            AND t2.status = 'pending'
        ");

        if ($this->enable_logging) {
            error_log('EMQM: Cleaned up duplicate emails');
        }
    }
}
