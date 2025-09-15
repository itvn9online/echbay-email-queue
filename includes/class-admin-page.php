<?php

/**
 * Admin Page Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class EMQM_Admin_Page
{

    private $mail_queue;

    public function __construct()
    {
        $this->mail_queue = new EMQM_Mail_Queue();
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_emqm_retry_email', array($this, 'ajax_retry_email'));
        add_action('wp_ajax_emqm_delete_email', array($this, 'ajax_delete_email'));
        add_action('wp_ajax_emqm_process_queue_manually', array($this, 'ajax_process_queue_manually'));
        add_action('wp_ajax_emqm_check_update', array($this, 'ajax_check_update'));

        // Add admin footer script if autorun is enabled
        add_action('admin_footer', array($this, 'admin_footer_autorun_script'));

        // Add frontend footer script if autorun is enabled
        add_action('wp_footer', array($this, 'frontend_footer_autorun_script'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu()
    {
        add_management_page(
            __('Email Queue Manager', 'echbay-mail-queue'),
            __('Email Queue', 'echbay-mail-queue'),
            'manage_options',
            'echbay-mail-queue',
            array($this, 'admin_page')
        );
    }

    /**
     * Admin init
     */
    public function admin_init()
    {
        register_setting('emqm_settings', 'emqm_batch_size');
        register_setting('emqm_settings', 'emqm_cron_interval');
        register_setting('emqm_settings', 'emqm_enable_queue');
        register_setting('emqm_settings', 'emqm_queue_for_guests_only');
        register_setting('emqm_settings', 'emqm_enable_logging');
        register_setting('emqm_settings', 'emqm_max_attempts');
        register_setting('emqm_settings', 'emqm_delete_sent_after_days');
        register_setting('emqm_settings', 'emqm_use_wp_cron');
        register_setting('emqm_settings', 'emqm_prevent_duplicates');
        register_setting('emqm_settings', 'emqm_admin_autorun');
        register_setting('emqm_settings', 'emqm_frontend_autorun');
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook)
    {
        if ($hook != 'tools_page_echbay-mail-queue') {
            return;
        }

        wp_enqueue_script('emqm-admin', EMQM_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), EMQM_VERSION, true);
        wp_enqueue_style('emqm-admin', EMQM_PLUGIN_URL . 'admin/css/admin-style.css', array(), EMQM_VERSION);

        wp_localize_script('emqm-admin', 'emqm_ajax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('emqm_nonce')
        ));
    }

    /**
     * Admin page
     */
    public function admin_page()
    {
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'queue';
?>
        <div class="wrap">
            <h1><?php _e('Email Queue Manager', 'echbay-mail-queue'); ?></h1>

            <nav class="nav-tab-wrapper">
                <a href="?page=echbay-mail-queue&tab=queue" class="nav-tab <?php echo $active_tab == 'queue' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Email Queue', 'echbay-mail-queue'); ?>
                </a>
                <a href="?page=echbay-mail-queue&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Settings', 'echbay-mail-queue'); ?>
                </a>
            </nav>

            <div class="tab-content">
                <?php
                switch ($active_tab) {
                    case 'settings':
                        $this->render_settings_tab();
                        break;
                    default:
                        $this->render_queue_tab();
                        break;
                }
                ?>
            </div>
        </div>
<?php
    }

    /**
     * Render queue tab
     */
    private function render_queue_tab()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'echbay_mail_queue';
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';
        $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
        $per_page = 50;
        $offset = ($paged - 1) * $per_page;

        // Build query
        $where = '';
        if ($status_filter != 'all') {
            $where = $wpdb->prepare(" WHERE status = %s", $status_filter);
        }

        $emails = $wpdb->get_results("SELECT * FROM {$table_name}{$where} ORDER BY id DESC LIMIT {$per_page} OFFSET {$offset}");
        // print_r($emails);
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM {$table_name}{$where}");
        $total_pages = ceil($total_items / $per_page);

        $stats = $this->mail_queue->get_queue_stats();

        include EMQM_PLUGIN_PATH . 'admin/views/list-emails.php';
    }

    /**
     * Render settings tab
     */
    private function render_settings_tab()
    {
        if (isset($_POST['submit'])) {
            // Update settings
            update_option('emqm_batch_size', absint($_POST['emqm_batch_size']));
            update_option('emqm_cron_interval', absint($_POST['emqm_cron_interval']));
            update_option('emqm_enable_queue', isset($_POST['emqm_enable_queue']) ? 1 : 0);
            update_option('emqm_queue_for_guests_only', isset($_POST['emqm_queue_for_guests_only']) ? 1 : 0);
            update_option('emqm_enable_logging', isset($_POST['emqm_enable_logging']) ? 1 : 0);
            update_option('emqm_max_attempts', absint($_POST['emqm_max_attempts']));
            update_option('emqm_delete_sent_after_days', absint($_POST['emqm_delete_sent_after_days']));
            update_option('emqm_use_wp_cron', isset($_POST['emqm_use_wp_cron']) ? 1 : 0);
            update_option('emqm_prevent_duplicates', isset($_POST['emqm_prevent_duplicates']) ? 1 : 0);
            update_option('emqm_admin_autorun', isset($_POST['emqm_admin_autorun']) ? 1 : 0);
            update_option('emqm_frontend_autorun', isset($_POST['emqm_frontend_autorun']) ? 1 : 0);

            echo '<div class="notice notice-success"><p>' . __('Settings saved.', 'echbay-mail-queue') . '</p></div>';
        }

        // Test bypass logic for debugging
        if (isset($_GET['test_bypass'])) {
            $debug_info = emqm_test_bypass_queue();
            echo '<div class="notice notice-info"><p><strong>Debug Info:</strong><br>';
            echo 'Logged in: ' . ($debug_info['is_user_logged_in'] ? 'Yes' : 'No') . '<br>';
            echo 'Is admin: ' . ($debug_info['is_admin'] ? 'Yes' : 'No') . '<br>';
            echo 'Can manage options: ' . ($debug_info['current_user_can_manage_options'] ? 'Yes' : 'No') . '<br>';
            echo 'Queue for guests only: ' . ($debug_info['queue_for_guests_only'] ? 'Yes' : 'No') . '<br>';
            echo 'Queue enabled: ' . ($debug_info['enable_queue'] ? 'Yes' : 'No') . '<br>';
            if (isset($debug_info['current_user'])) {
                echo 'Current user: ' . $debug_info['current_user']['user_login'] . ' (ID: ' . $debug_info['current_user']['ID'] . ')';
            }
            echo '</p></div>';
        }

        include EMQM_PLUGIN_PATH . 'admin/views/settings.php';
    }

    /**
     * AJAX retry email
     */
    public function ajax_retry_email()
    {
        check_ajax_referer('emqm_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'echbay-mail-queue'));
        }

        $email_id = absint($_POST['email_id']);
        $this->mail_queue->retry_email($email_id);

        wp_send_json_success(__('Email queued for retry.', 'echbay-mail-queue'));
    }

    /**
     * AJAX delete email
     */
    public function ajax_delete_email()
    {
        check_ajax_referer('emqm_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'echbay-mail-queue'));
        }

        $email_id = absint($_POST['email_id']);
        $result = $this->mail_queue->delete_email($email_id);

        if ($result) {
            wp_send_json_success(__('Email deleted.', 'echbay-mail-queue'));
        } else {
            wp_send_json_error(__('Failed to delete email.', 'echbay-mail-queue'));
        }
    }

    /**
     * AJAX process queue manually
     */
    public function ajax_process_queue_manually()
    {
        check_ajax_referer('emqm_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'echbay-mail-queue'));
        }

        $this->mail_queue->process_queue();

        wp_send_json_success(__('Queue processed successfully.', 'echbay-mail-queue'));
    }

    /**
     * AJAX handler for checking updates
     */
    public function ajax_check_update()
    {
        check_ajax_referer('emqm_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'echbay-mail-queue'));
        }

        // Use global auto updater instance
        global $emqm_auto_updater;

        if (!$emqm_auto_updater) {
            $emqm_auto_updater = new EMQM_Auto_Updater(EMQM_PLUGIN_PATH . 'echbay-mail-queue-manager.php');
        }

        $result = $emqm_auto_updater->manual_check_update();

        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }

    /**
     * Inject auto-run script into admin footer
     */
    public function admin_footer_autorun_script()
    {
        // Only run on admin pages and if autorun is enabled
        if (!get_option('emqm_admin_autorun', 1)) {
            return;
        }

        // Read frontend.html content and inject it
        $frontend_html_path = EMQM_PLUGIN_PATH . 'assets/frontend.html';

        if (file_exists($frontend_html_path)) {
            $content = file_get_contents($frontend_html_path);

            // Replace placeholder with actual plugin URL
            $content = str_replace('{base_plugin_url}', EMQM_PLUGIN_URL, $content);

            // Output the script
            echo $content;
        }
    }

    /**
     * Inject auto-run script into frontend footer
     */
    public function frontend_footer_autorun_script()
    {
        // Only run on frontend pages and if autorun is enabled
        if (!get_option('emqm_frontend_autorun', 0)) {
            return;
        }

        // Read frontend.html content and inject it
        $frontend_html_path = EMQM_PLUGIN_PATH . 'assets/frontend.html';

        if (file_exists($frontend_html_path)) {
            $content = file_get_contents($frontend_html_path);

            // Replace placeholder with actual plugin URL
            $content = str_replace('{base_plugin_url}', EMQM_PLUGIN_URL, $content);

            // Output the script
            echo $content;
        }
    }
}
