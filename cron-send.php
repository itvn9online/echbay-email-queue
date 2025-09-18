<?php

/**
 * Cron file for processing email queue
 * This can be called via client-side JavaScript or server cron
 */

// 
header('Content-Type: application/json');

// Simple rate limiting to prevent abuse
$last_run_option = __DIR__ . '/emqm_last_cron_run.txt';
// xử lý để cùng 1 thời điểm chỉ có 1 cron được chạy, cron sau chờ cron trước xong mới thực hiện lệnh process_queue
$lock_file = __DIR__ . '/emqm_cron_lock.txt';
$current_time = time();

// Process lock mechanism - chỉ cho phép 1 cron chạy cùng lúc
if (!isset($_GET['emqm_id'])) {
    // Random delay để phân tán thời gian check lock, tránh race condition
    // 0.5-5 giây là đủ cho việc phân tán mà không delay quá lâu
    usleep(mt_rand(500000, 5000000)); // 0.5-5 giây

    // Kiểm tra xem có cron nào đang chạy không
    if (is_file($lock_file)) {
        $lock_time = (int) file_get_contents($lock_file);
        $lock_timeout = 120; // Timeout sau 2 phút nếu lock bị stuck

        // Nếu lock file quá cũ (stuck process), xóa nó
        if (($current_time - $lock_time) > $lock_timeout) {
            unlink($lock_file);
        } else {
            // Có cron khác đang chạy, chờ hoặc exit
            $wait_attempts = 0;
            $max_wait_attempts = 25; // Chờ tối đa 25 lần x 2 giây = 50 giây

            while (is_file($lock_file) && $wait_attempts < $max_wait_attempts) {
                sleep(2); // Chờ 2 giây
                $wait_attempts++;

                // Kiểm tra lại timeout
                if (is_file($lock_file)) {
                    $lock_time = (int) file_get_contents($lock_file);
                    if (($current_time - $lock_time) > $lock_timeout) {
                        unlink($lock_file);
                        break;
                    }
                }
            }

            // Nếu vẫn còn lock sau khi chờ, exit
            if (is_file($lock_file)) {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Another cron process is running. Please wait.',
                ));
                exit();
            }
        }
    }

    // Tạo lock file
    file_put_contents($lock_file, $current_time, LOCK_EX);

    $last_run = is_file($last_run_option) ? (int) file_get_contents($last_run_option) : 0;
    $min_interval = 55; // Minimum 55 seconds between runs

    if (($current_time - $last_run) < $min_interval) {
        // Xóa lock trước khi exit
        if (is_file($lock_file)) {
            unlink($lock_file);
        }

        echo json_encode(array(
            'success' => false,
            'message' => 'Rate limited',
            // 'last_run' => $current_time - $last_run,
        ));
        exit();
    }

    // Update last run time
    file_put_contents($last_run_option, $current_time, LOCK_EX);

    // 
    $file_count_failed = __DIR__ . '/failed_email_count.log';
    if (is_file($file_count_failed)) {
        $failed_email_count = intval(explode('|', file_get_contents($file_count_failed))[0]);
        // echo 'Failed email count: ' . $failed_email_count . '<br>' . PHP_EOL;

        // nếu gửi thất bại quá 5 lần
        if ($failed_email_count > 5) {
            // lấy thời gian ghi nhận cuối
            $failed_email_time = intval(explode('|', file_get_contents($file_count_failed))[1]);
            // echo 'Failed email time: ' . $failed_email_time . '<br>' . PHP_EOL;
            // giãn cách tầm 600s
            if (time() - $failed_email_time < 600) {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Failed email limit reached with ' . $failed_email_count . ' attempts in ' . date_i18n('Y-m-d H:i:s', $failed_email_time) . '. Please try again later.',
                ));
                exit();
            }
        }
    }
}

// Load WordPress
$wp_loaded = false;
$wp_load_php = '../wp-load.php';
for ($i = 0; $i < 10; $i++) {
    // echo $wp_load_php . '<br>' . PHP_EOL;
    if (is_file($wp_load_php)) {
        require_once $wp_load_php;
        $wp_loaded = true;
        break;
    }
    $wp_load_php = '../' . $wp_load_php; // Try parent directories
}

if (!$wp_loaded) {
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'message' => 'WordPress not found'
    ));
    exit();
}

// 
if (!class_exists('EMQM_Mail_Queue')) {
    echo json_encode(array(
        'success' => false,
        'message' => 'Mail Queue class not found'
    ));
    exit();
}

// Process the email queue
try {
    if (isset($_GET['active_wp_mail'])) {
        // mặc là không giới hạn số lượng email gửi đi mỗi ngày
        $my_daily_email_limit = 0;
        // lấy giới hạn từ file settings nếu có
        if (is_file(__DIR__ . '/settings_cron.php')) {
            include __DIR__ . '/settings_cron.php';
        } else {
            // nếu không có file settings thì lấy giới hạn từ option và tạo file settings
            // để tránh việc đọc option nhiều lần
            $my_daily_email_limit = get_option('emqm_daily_email_limit', 0);

            file_put_contents(__DIR__ . '/settings_cron.php', implode(
                PHP_EOL,
                [
                    '<?php',
                    '// This file is auto-generated to store daily email limit setting',
                    '// Do not edit this file directly unless you know what you are doing',
                    '// Generated on ' . date_i18n('Y-m-d H:i:s'),
                    '// Generated in ' . $_SERVER['REQUEST_URI'],
                    '$my_daily_email_limit = ' . intval($my_daily_email_limit) . ';',
                ]
            ), LOCK_EX);
        }

        // Check daily email limit if set
        if ($my_daily_email_limit > 0) {
            // thêm prefix theo domain để chạy multiple site
            $domain_prefix = explode(':', $_SERVER['HTTP_HOST'])[0] . '_';
            $path_daily_limit = __DIR__ . '/' . $domain_prefix . 'daily_limit-' . date_i18n('Y-m-d') . '.log';
            if (is_file($path_daily_limit)) {
                $emails_sent_today = intval(file_get_contents($path_daily_limit));
            } else {
                $emails_sent_today = 0;
            }

            // Check daily send limit
            if ($emails_sent_today > $my_daily_email_limit) {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Daily send limit exceeded. Emails sent today: ' . $emails_sent_today . '. Limit: ' . $my_daily_email_limit,
                ));
                exit();
            }
        }

        // Process the email queue
        $mail_queue = new EMQM_Mail_Queue(true);
        $processed = $mail_queue->process_queue();

        // Update daily email count
        if ($my_daily_email_limit > 0 && $processed > 0) {
            $emails_sent_today += $processed;
            file_put_contents($path_daily_limit, $emails_sent_today, LOCK_EX);
        }

        // Clean up files
        if (!isset($_GET['emqm_id'])) {
            // Remove last run file
            if (is_file($last_run_option)) {
                unlink($last_run_option);
            }
            // Remove lock file
            if (is_file($lock_file)) {
                unlink($lock_file);
            }
        }
    } else {
        $processed = -1;
    }

    // Test condition
    if (1 > 2 && $processed < 1 && !isset($_GET['active_wp_mail'])) {
        echo json_encode(array(
            'success' => true,
            'processed' => $processed,
            'get' => $_GET,
            'message' => 'Test condition is true',
            'tests' => wp_mail([
                // 'itvn9online@gmail.com',
                'v0tjnhlangtu@gmail.com',
            ], 'Test multi Subject ' . EMQM_FIXED_TIME, 'Test multi Message at ' . EMQM_FIXED_TIME),
        ));
        exit();
    }

    // Clean up duplicates periodically
    // $mail_queue->cleanup_duplicate_emails();

    // Optional: Return JSON response
    echo json_encode(array(
        'success' => true,
        'message' => 'Queue processed successfully',
        'processed' => $processed,
        'timestamp' => $current_time
    ));
} catch (Exception $e) {
    // Clean up lock file in case of error
    if (!isset($_GET['emqm_id']) && is_file($lock_file)) {
        unlink($lock_file);
    }

    if (get_option('emqm_enable_logging', 0) > 0) {
        error_log('EMQM Cron Error: ' . $e->getMessage());
    }

    http_response_code(500);

    echo json_encode(array(
        'success' => false,
        'message' => 'Error processing queue',
        'error' => $e->getMessage()
    ));
}
