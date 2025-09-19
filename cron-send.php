<?php

/**
 * Cron file for processing email queue
 * This can be called via client-side JavaScript or server cron
 */

// Set the content type to JSON
header('Content-Type: application/json');

// Simple rate limiting to prevent abuse
$last_run_option = __DIR__ . '/emqm_last_cron_run.txt';
// xử lý để cùng 1 thời điểm chỉ có 1 cron được chạy, cron sau chờ cron trước xong mới thực hiện lệnh process_queue
$lock_file = __DIR__ . '/emqm_cron_lock.txt';
$current_time = time();
// thêm prefix theo domain để chạy multiple domain trên cùng 1 source
$domain_prefix = explode(':', $_SERVER['HTTP_HOST'])[0] . '_';
// mặc là không giới hạn số lượng email gửi đi mỗi ngày
$my_daily_email_limit = 0;
$emails_sent_today = 0;
$path_daily_limit = __DIR__ . '/' . $domain_prefix . 'daily_limit-' . date('Y-m-d') . '.log';

// Process lock mechanism - chỉ cho phép 1 cron chạy cùng lúc
if (!isset($_GET['emqm_id'])) {
    // nếu có file settings_cron.php thì lấy giới hạn từ file này
    if (is_file(__DIR__ . '/settings_cron.php')) {
        include __DIR__ . '/settings_cron.php';

        // Check daily email limit if set
        if ($my_daily_email_limit > 0) {
            if (is_file($path_daily_limit)) {
                $emails_sent_today = intval(file_get_contents($path_daily_limit));
            }

            // Check daily send limit
            if ($emails_sent_today > $my_daily_email_limit) {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Daily send limit exceeded. Emails sent today: ' . $emails_sent_today . '/' . $my_daily_email_limit,
                ));
                exit();
            }
        }
    }

    // Random delay để phân tán thời gian check lock, tránh race condition
    if (!is_file($lock_file)) {
        // 0.5-9 giây là đủ cho việc phân tán mà không delay quá lâu
        usleep(mt_rand(500, 9000) * 1000);
    }

    // Kiểm tra xem có cron nào đang chạy không
    if (is_file($lock_file)) {
        // nếu có file lock thì kiểm tra nội dung file
        // nếu nội dung file khác với domain_prefix hiện tại thì có cron khác đang chạy
        $lock_content =  file_get_contents($lock_file);
        if ($lock_content != $domain_prefix) {
            // xem thời gian tạo file lock lâu chưa, dưới 10 phút thì coi như cron đang chạy
            if ($current_time - filemtime($lock_file) < 600) {
                // thoát để tránh xung đột
                echo json_encode(array(
                    'success' => false,
                    'message' => $lock_content . ' domain cron process is running. Please wait...',
                ));
                exit();
            } else {
                // tạo giãn cách để tránh việc nhiều cron cùng chạy -> thằng nào có số to hơn thì lần tới sẽ chạy tiếp
                usleep(mt_rand(500, 9000) * 1000);
            }
        }
    }

    // Tạo lock file
    file_put_contents($lock_file, $domain_prefix, LOCK_EX);
    // thiết lập thời gian tạo file lock
    touch($lock_file, $current_time);

    // Rate limiting: chỉ cho phép chạy cron nếu đã đủ thời gian kể từ lần chạy cuối
    // tránh việc cron chạy liên tục trong thời gian ngắn
    // có thể bỏ đoạn này nếu muốn cron chạy liên tục không giới hạn
    // vì đã có cơ chế lock file để tránh việc chạy chồng chéo
    $last_run = is_file($last_run_option) ? (int) file_get_contents($last_run_option) : 0;
    $min_interval = 55; // Minimum 55 seconds between runs

    if (($current_time - $last_run) < $min_interval) {
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
    $file_count_failed = __DIR__ . '/' . $domain_prefix . 'failed_email_count.log';
    if (is_file($file_count_failed)) {
        $failed_email_count = intval(explode('|', file_get_contents($file_count_failed))[0]);
        // echo 'Failed email count: ' . $failed_email_count . '<br>' . PHP_EOL;

        // nếu gửi thất bại quá 5 lần
        if ($failed_email_count > 5) {
            // lấy thời gian ghi nhận cuối
            $failed_email_time = intval(explode('|', file_get_contents($file_count_failed))[1]);
            // echo 'Failed email time: ' . $failed_email_time . '<br>' . PHP_EOL;
            // giãn cách tầm 600s
            if ($current_time - $failed_email_time < 600) {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Failed email limit reached with ' . $failed_email_count . ' attempts in ' . date('Y-m-d H:i:s', $failed_email_time) . '. Please try again later.',
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
        // lấy giới hạn từ file settings nếu có
        if (!is_file(__DIR__ . '/settings_cron.php')) {
            // nếu không có file settings thì lấy giới hạn từ option và tạo file settings
            // để tránh việc đọc option nhiều lần
            $my_daily_email_limit = get_option('emqm_daily_email_limit', 0);
            // Lấy timezone từ cài đặt WordPress
            $timezone = get_option('timezone_string');
            if (empty($timezone)) {
                $timezone = 'UTC';
            }

            file_put_contents(__DIR__ . '/settings_cron.php', implode(
                PHP_EOL,
                [
                    '<?php',
                    '// This file is auto-generated to store daily email limit setting',
                    '// Do not edit this file directly unless you know what you are doing',
                    '// Generated on ' . date('Y-m-d H:i:s'),
                    '// Generated in ' . $_SERVER['REQUEST_URI'],
                    '$my_daily_email_limit = ' . intval($my_daily_email_limit) . ';',
                    '$my_default_timezone = \'' . $timezone . '\';',
                ]
            ), LOCK_EX);
        }

        // Process the email queue
        $mail_queue = new EMQM_Mail_Queue(true);
        $processed = $mail_queue->process_queue($file_count_failed);

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
        // 'timestamp' => $current_time,
        'sent' => $emails_sent_today . '/' . $my_daily_email_limit,
    ));
} catch (Exception $e) {
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
