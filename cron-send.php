<?php

/**
 * Cron file for processing email queue
 * This can be called via client-side JavaScript or server cron
 */

// 
header('Content-Type: application/json');

// Simple rate limiting to prevent abuse
$last_run_option = __DIR__ . '/emqm_last_cron_run.txt';
$current_time = time();
if (!isset($_GET['emqm_id'])) {
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
        $mail_queue = new EMQM_Mail_Queue(true);
        $processed = $mail_queue->process_queue();

        // Clean up last run file
        if (!isset($_GET['emqm_id'])) {
            unlink($last_run_option);
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
