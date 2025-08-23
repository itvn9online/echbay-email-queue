<?php

/**
 * Plugin Name: Echbay Mail Queue Manager
 * Plugin URI: https://echbay.com
 * Description: Quản lý hàng đợi email cho WordPress, tránh làm chậm website khi gửi mail, hỗ trợ gửi theo batch qua cron.
 * Version: 1.0.9
 * Author: Dao Quoc Dai
 * License: GPL2
 * Text Domain: echbay-mail-queue
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('EMQM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EMQM_PLUGIN_PATH', __DIR__ . '/');
define('EMQM_VERSION', file_get_contents(EMQM_PLUGIN_PATH . 'VERSION'));
// dùng để xác định bản ghi trùng lặp
define('EMQM_FIXED_TIME', date('Y-m-d H:i:s'));

// Include required files
require_once EMQM_PLUGIN_PATH . 'includes/class-activator.php';
require_once EMQM_PLUGIN_PATH . 'includes/class-deactivator.php';
require_once EMQM_PLUGIN_PATH . 'includes/class-mail-queue.php';
require_once EMQM_PLUGIN_PATH . 'includes/class-admin-page.php';
require_once EMQM_PLUGIN_PATH . 'includes/class-auto-updater.php';
// Bỏ class-cron.php vì sử dụng cronjob server thay vì WordPress cron
// require_once EMQM_PLUGIN_PATH . 'includes/class-cron.php';
require_once EMQM_PLUGIN_PATH . 'includes/helpers.php';

// Activation and deactivation hooks
register_activation_hook(__FILE__, array('EMQM_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('EMQM_Deactivator', 'deactivate'));

// Initialize the plugin
function emqm_init()
{
    global $emqm_auto_updater;

    // Initialize main classes
    new EMQM_Mail_Queue();
    new EMQM_Admin_Page();
    // Initialize auto updater
    $emqm_auto_updater = new EMQM_Auto_Updater(__FILE__);
    // Bỏ EMQM_Cron vì sử dụng cronjob server
    // new EMQM_Cron();
}

add_action('plugins_loaded', 'emqm_init');

// Load text domain
function emqm_load_textdomain()
{
    load_plugin_textdomain('echbay-mail-queue', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('init', 'emqm_load_textdomain');

// Add settings link to plugin action links
function emqm_add_settings_link($links)
{
    $settings_link = '<a href="' . admin_url('options-general.php?page=echbay-mail-queue') . '">' . __('Settings', 'echbay-mail-queue') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
if (strpos($_SERVER['REQUEST_URI'], '/plugins.php') !== false) {
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'emqm_add_settings_link');
}
