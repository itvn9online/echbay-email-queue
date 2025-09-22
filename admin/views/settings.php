<?php

/**
 * Settings view
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get domain prefix for Gmail API options
require_once EMQM_PLUGIN_PATH . 'includes/class-gmail-api.php';
$gmail_domain_prefix = EMQM_Gmail_API::get_domain_prefix_static();
?>

<form method="post" action="">
    <?php wp_nonce_field('emqm_settings', 'emqm_settings_nonce'); ?>

    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row">
                    <label for="emqm_enable_queue"><?php _e('Enable Email Queue', 'echbay-mail-queue'); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="emqm_enable_queue" name="emqm_enable_queue" value="1" <?php checked(get_option('emqm_enable_queue', 1), 1); ?> />
                    <p class="description"><?php _e('Enable the email queue system. When disabled, emails will be sent immediately.', 'echbay-mail-queue'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="emqm_queue_for_guests_only"><?php _e('Queue for Guests Only', 'echbay-mail-queue'); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="emqm_queue_for_guests_only" name="emqm_queue_for_guests_only" value="1" <?php checked(get_option('emqm_queue_for_guests_only', 1), 1); ?> />
                    <p class="description"><?php _e('Only queue emails when users are not logged in. Logged-in users will have emails sent immediately.', 'echbay-mail-queue'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="emqm_batch_size"><?php _e('Batch Size', 'echbay-mail-queue'); ?></label>
                </th>
                <td>
                    <input type="number" id="emqm_batch_size" name="emqm_batch_size" value="<?php echo esc_attr(get_option('emqm_batch_size', 5)); ?>" min="1" max="33" />
                    <p class="description"><?php _e('Number of emails to process in each batch. Higher values may cause performance issues.', 'echbay-mail-queue'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="emqm_cron_interval"><?php _e('Cron Interval (minutes)', 'echbay-mail-queue'); ?></label>
                </th>
                <td>
                    <select id="emqm_cron_interval" name="emqm_cron_interval">
                        <option value="1" <?php selected(get_option('emqm_cron_interval', 5), 1); ?>><?php _e('Every Minute', 'echbay-mail-queue'); ?></option>
                        <option value="2" <?php selected(get_option('emqm_cron_interval', 5), 2); ?>><?php _e('Every 2 Minutes', 'echbay-mail-queue'); ?></option>
                        <option value="5" <?php selected(get_option('emqm_cron_interval', 5), 5); ?>><?php _e('Every 5 Minutes', 'echbay-mail-queue'); ?></option>
                        <option value="10" <?php selected(get_option('emqm_cron_interval', 5), 10); ?>><?php _e('Every 10 Minutes', 'echbay-mail-queue'); ?></option>
                        <option value="15" <?php selected(get_option('emqm_cron_interval', 5), 15); ?>><?php _e('Every 15 Minutes', 'echbay-mail-queue'); ?></option>
                        <option value="30" <?php selected(get_option('emqm_cron_interval', 5), 30); ?>><?php _e('Every 30 Minutes', 'echbay-mail-queue'); ?></option>
                    </select>
                    <p class="description"><?php _e('How often the cron job should run to process the email queue.', 'echbay-mail-queue'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="emqm_max_attempts"><?php _e('Max Attempts', 'echbay-mail-queue'); ?></label>
                </th>
                <td>
                    <input type="number" id="emqm_max_attempts" name="emqm_max_attempts" value="<?php echo esc_attr(get_option('emqm_max_attempts', 3)); ?>" min="1" max="10" />
                    <p class="description"><?php _e('Maximum number of attempts to send an email before marking it as failed.', 'echbay-mail-queue'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="emqm_delete_sent_after_days"><?php _e('Delete Sent Emails After (days)', 'echbay-mail-queue'); ?></label>
                </th>
                <td>
                    <input type="number" id="emqm_delete_sent_after_days" name="emqm_delete_sent_after_days" value="<?php echo esc_attr(get_option('emqm_delete_sent_after_days', 365)); ?>" min="0" max="365" />
                    <p class="description"><?php _e('Automatically delete sent emails after this many days. Set to 0 to keep all emails.', 'echbay-mail-queue'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="emqm_daily_email_limit"><?php _e('Daily Email Limit', 'echbay-mail-queue'); ?></label>
                </th>
                <td>
                    <input type="number" id="emqm_daily_email_limit" name="emqm_daily_email_limit" value="<?php echo esc_attr(get_option('emqm_daily_email_limit', 0)); ?>" min="0" max="10000" />
                    <p class="description"><?php _e('Maximum number of emails to send per day. Set to 0 for unlimited. Useful for free email services with daily limits (e.g., Gmail: 500/day).', 'echbay-mail-queue'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="emqm_active_hour_start"><?php _e('Active Hour Start', 'echbay-mail-queue'); ?></label>
                </th>
                <td>
                    <select id="emqm_active_hour_start" name="emqm_active_hour_start">
                        <option value="0" <?php selected(get_option('emqm_active_hour_start', 0), 0); ?>><?php _e('00:00 (Midnight)', 'echbay-mail-queue'); ?></option>
                        <?php for ($i = 1; $i < 24; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php selected(get_option('emqm_active_hour_start', 0), $i); ?>><?php echo sprintf('%02d:00', $i); ?></option>
                        <?php endfor; ?>
                    </select>
                    <p class="description"><?php _e('Hour when cronjob should start processing emails (24-hour format). Set to 0 for no time restriction.', 'echbay-mail-queue'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="emqm_active_hour_end"><?php _e('Active Hour End', 'echbay-mail-queue'); ?></label>
                </th>
                <td>
                    <select id="emqm_active_hour_end" name="emqm_active_hour_end">
                        <option value="0" <?php selected(get_option('emqm_active_hour_end', 0), 0); ?>><?php _e('00:00 (Midnight)', 'echbay-mail-queue'); ?></option>
                        <?php for ($i = 1; $i < 24; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php selected(get_option('emqm_active_hour_end', 0), $i); ?>><?php echo sprintf('%02d:00', $i); ?></option>
                        <?php endfor; ?>
                    </select>
                    <p class="description"><?php _e('Hour when cronjob should stop processing emails (24-hour format). Set to 0 for no time restriction.', 'echbay-mail-queue'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="emqm_mail_method"><?php _e('Email Method', 'echbay-mail-queue'); ?></label>
                </th>
                <td>
                    <select id="emqm_mail_method" name="emqm_mail_method">
                        <option value="wp_mail"><?php _e('WordPress wp_mail()', 'echbay-mail-queue'); ?></option>
                        <option value="gmail_api" <?php selected(get_option($gmail_domain_prefix . 'emqm_mail_method', 'wp_mail'), 'gmail_api'); ?>><?php _e('Gmail API', 'echbay-mail-queue'); ?></option>
                    </select>
                    <p class="description"><?php _e('Choose email sending method. Gmail API provides better deliverability and detailed tracking.', 'echbay-mail-queue'); ?></p>
                </td>
            </tr>

            <tr id="gmail_api_settings" style="display: <?php echo get_option($gmail_domain_prefix . 'emqm_mail_method', 'wp_mail') === 'gmail_api' ? 'table-row' : 'none'; ?>;">
                <th scope="row">
                    <label><?php _e('Gmail API Settings', 'echbay-mail-queue'); ?></label>
                </th>
                <td>
                    <table class="form-table" style="margin: 0;">
                        <tr>
                            <th scope="row" style="width: 150px;">
                                <label for="emqm_gmail_client_id"><?php _e('Client ID', 'echbay-mail-queue'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="emqm_gmail_client_id" name="emqm_gmail_client_id" value="<?php echo esc_attr(get_option($gmail_domain_prefix . 'emqm_gmail_client_id', '')); ?>" style="width: 400px;" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="emqm_gmail_client_secret"><?php _e('Client Secret', 'echbay-mail-queue'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="emqm_gmail_client_secret" name="emqm_gmail_client_secret" value="<?php echo esc_attr(get_option($gmail_domain_prefix . 'emqm_gmail_client_secret', '')); ?>" placeholder="Gmail client secret" style="width: 400px; color: transparent;" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="emqm_gmail_refresh_token"><?php _e('Refresh Token', 'echbay-mail-queue'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="emqm_gmail_refresh_token" name="emqm_gmail_refresh_token" value="<?php echo esc_attr(get_option($gmail_domain_prefix . 'emqm_gmail_refresh_token', '')); ?>" placeholder="Gmail refresh token" style="width: 400px; color: transparent;" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="emqm_gmail_from_email"><?php _e('From Email', 'echbay-mail-queue'); ?></label>
                            </th>
                            <td>
                                <input type="email" id="emqm_gmail_from_email" name="emqm_gmail_from_email" value="<?php echo esc_attr(get_option($gmail_domain_prefix . 'emqm_gmail_from_email', '')); ?>" style="width: 400px;" />
                                <p class="description"><?php _e('Email address that will appear as sender (must be authorized for your Gmail account)', 'echbay-mail-queue'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="emqm_gmail_from_name"><?php _e('From Name', 'echbay-mail-queue'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="emqm_gmail_from_name" name="emqm_gmail_from_name" value="<?php echo esc_attr(get_option($gmail_domain_prefix . 'emqm_gmail_from_name', '')); ?>" style="width: 400px;" />
                                <p class="description"><?php _e('Display name that will appear as sender', 'echbay-mail-queue'); ?></p>
                            </td>
                        </tr>
                    </table>
                    <p class="description">
                        * Plugin này thường được sử dụng kèm với plugin `Mail Marketing Importer`. Nếu có sẵn rồi hãy <a href="<?php echo admin_url(); ?>tools.php?page=email-campaigns&google-workspace=true" style="font-weight: bold;" target="_blank">vào đây để kết nối Gmail API</a> sau đó copy dữ liệu qua bên này.<br><br>
                        <strong><?php _e('Setup Instructions:', 'echbay-mail-queue'); ?></strong><br>
                        1. Go to <a href="https://console.developers.google.com/" target="_blank">Google Cloud Console</a><br>
                        2. Create a new project or select existing one<br>
                        3. Enable Gmail API<br>
                        4. Create OAuth 2.0 credentials<br>
                        5. Use OAuth 2.0 Playground to get refresh token
                    </p>
                    <p>
                        <button type="button" id="emqm-test-gmail" class="button" style="display: inline-block;">
                            <?php _e('Test Gmail API Connection', 'echbay-mail-queue'); ?>
                        </button>
                        <span id="emqm-gmail-test-result" style="margin-left: 10px;"></span>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="emqm_use_wp_cron"><?php _e('Use WP Cron', 'echbay-mail-queue'); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="emqm_use_wp_cron" name="emqm_use_wp_cron" value="1" <?php checked(get_option('emqm_use_wp_cron', 0), 1); ?> />
                    <p class="description"><?php _e('Use WordPress cron system. Disable if you have server-level cron configured.', 'echbay-mail-queue'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="emqm_enable_logging"><?php _e('Enable Logging', 'echbay-mail-queue'); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="emqm_enable_logging" name="emqm_enable_logging" value="1" <?php checked(get_option('emqm_enable_logging', 0), 1); ?> />
                    <p class="description"><?php _e('Log email queue activities to the WordPress error log.', 'echbay-mail-queue'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="emqm_prevent_duplicates"><?php _e('Prevent Duplicates', 'echbay-mail-queue'); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="emqm_prevent_duplicates" name="emqm_prevent_duplicates" value="1" <?php checked(get_option('emqm_prevent_duplicates', 1), 1); ?> />
                    <p class="description"><?php _e('Prevent duplicate emails with same recipient and subject within 5 minutes.', 'echbay-mail-queue'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="emqm_admin_autorun"><?php _e('Admin Auto-run Cronjob', 'echbay-mail-queue'); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="emqm_admin_autorun" name="emqm_admin_autorun" value="1" <?php checked(get_option('emqm_admin_autorun', 1), 1); ?> />
                    <p class="description"><?php _e('Automatically run cronjob in admin footer when admin users visit pages. This provides backup processing if server cron fails.', 'echbay-mail-queue'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="emqm_frontend_autorun"><?php _e('Frontend Auto-run Cronjob', 'echbay-mail-queue'); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="emqm_frontend_autorun" name="emqm_frontend_autorun" value="1" <?php checked(get_option('emqm_frontend_autorun', 0), 1); ?> />
                    <p class="description"><?php _e('Automatically run cronjob in frontend footer when visitors access pages. Only enable if server cron is unreliable as this may impact page performance.', 'echbay-mail-queue'); ?></p>
                </td>
            </tr>
        </tbody>
    </table>

    <?php submit_button(); ?>
</form>

<div>
    <div class="emqm-debug-info">
        <h3><?php _e('Debug Information', 'echbay-mail-queue'); ?></h3>
        <p>
            <a href="<?php echo esc_url(add_query_arg('test_bypass', '1')); ?>" class="button button-secondary">
                <?php _e('Test Bypass Logic', 'echbay-mail-queue'); ?>
            </a>
        </p>
    </div>

    <div class="emqm-cron-info">
        <h3><?php _e('Cron Job Information', 'echbay-mail-queue'); ?></h3>

        <h4><?php _e('Client-side Cron (Recommended)', 'echbay-mail-queue'); ?></h4>
        <p><?php _e('Add this JavaScript code to your theme footer to process emails on each page visit:', 'echbay-mail-queue'); ?></p>
        <div>
            <textarea readonly rows="12" ondblclick="this.select();" style="width: 99%;"><?php echo esc_html(str_replace('{base_plugin_url}', EMQM_PLUGIN_URL, file_get_contents(EMQM_PLUGIN_PATH . 'assets/frontend.html'))); ?></textarea>
        </div>

        <h4><?php _e('Plugin Updates', 'echbay-mail-queue'); ?></h4>
        <div id="emqm-update-section">
            <p>
                <button type="button" id="emqm-check-update" class="button"><?php _e('Check for Updates', 'echbay-mail-queue'); ?></button>
                <span id="emqm-update-status" style="margin-left: 10px;"></span>
            </p>
            <div id="emqm-update-info" style="display: none; margin-top: 10px; padding: 10px; border: 1px solid #ddd; background: #f9f9f9;">
                <p><strong><?php _e('Current Version:', 'echbay-mail-queue'); ?></strong> <?php echo EMQM_VERSION; ?></p>
                <div id="emqm-update-details"></div>
            </div>
        </div>

        <h4><?php _e('Server Cron (Required)', 'echbay-mail-queue'); ?></h4>
        <p><?php _e('Add this to your server crontab (runs every minute):', 'echbay-mail-queue'); ?></p>
        <code>
            * * * * * curl -s "<?php echo esc_html(str_replace(ABSPATH, get_home_url() . '/', EMQM_PLUGIN_PATH)); ?>cron-send.php?active_wp_mail=1" > /dev/null 2>&1
        </code>
        <p><em><?php _e('This plugin no longer uses WordPress cron. Please use server cron for better reliability.', 'echbay-mail-queue'); ?></em></p>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.querySelector('form');
        if (!form) return;

        // Handle email method selection
        var emailMethodSelect = document.getElementById('emqm_mail_method');
        var gmailSettings = document.getElementById('gmail_api_settings');
        var testGmailBtn = document.getElementById('emqm-test-gmail');

        if (emailMethodSelect && gmailSettings) {
            // Initialize display based on current value
            function updateGmailSettings() {
                if (emailMethodSelect.value === 'gmail_api') {
                    gmailSettings.style.display = 'table-row';
                    if (testGmailBtn) testGmailBtn.style.display = 'inline-block';
                } else {
                    gmailSettings.style.display = 'none';
                    if (testGmailBtn) testGmailBtn.style.display = 'none';
                }
            }

            // Set initial state
            updateGmailSettings();

            // Handle change event
            emailMethodSelect.addEventListener('change', updateGmailSettings);
        }

        // Handle Gmail API test
        if (testGmailBtn) {
            testGmailBtn.addEventListener('click', function() {
                var resultSpan = document.getElementById('emqm-gmail-test-result');
                var btn = this;

                btn.disabled = true;
                btn.textContent = 'Testing...';
                if (resultSpan) resultSpan.textContent = '';

                // Collect Gmail settings
                var data = {
                    action: 'emqm_test_gmail',
                    nonce: (typeof emqm_ajax !== 'undefined') ? emqm_ajax.nonce : '',
                    client_id: document.getElementById('emqm_gmail_client_id').value,
                    client_secret: document.getElementById('emqm_gmail_client_secret').value,
                    refresh_token: document.getElementById('emqm_gmail_refresh_token').value,
                    from_email: document.getElementById('emqm_gmail_from_email').value,
                    from_name: document.getElementById('emqm_gmail_from_name').value
                };

                // nếu có trường dữ liệu trống thì báo lỗi luôn
                if (!data.client_id || !data.client_secret || !data.refresh_token || !data.from_email) {
                    alert('Please fill in all required Gmail API fields.');
                    return;
                }

                var xhr = new XMLHttpRequest();
                xhr.open('POST', (typeof emqm_ajax !== 'undefined') ? emqm_ajax.ajaxurl : '/wp-admin/admin-ajax.php');
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        btn.disabled = false;
                        btn.textContent = 'Test Gmail API Connection';

                        if (xhr.status === 200) {
                            try {
                                var response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    if (resultSpan) {
                                        resultSpan.style.color = 'green';
                                        resultSpan.textContent = '✓ ' + response.data.message +
                                            (response.data.email ? ' (' + response.data.email + ')' : '');
                                    }
                                } else {
                                    if (resultSpan) {
                                        resultSpan.style.color = 'red';
                                        resultSpan.textContent = '✗ ' + (response.data || 'Test failed');
                                    }
                                }
                            } catch (e) {
                                if (resultSpan) {
                                    resultSpan.style.color = 'red';
                                    resultSpan.textContent = '✗ Invalid response';
                                }
                            }
                        } else {
                            if (resultSpan) {
                                resultSpan.style.color = 'red';
                                resultSpan.textContent = '✗ Request failed';
                            }
                        }
                    }
                };

                var params = Object.keys(data).map(function(key) {
                    return encodeURIComponent(key) + '=' + encodeURIComponent(data[key]);
                }).join('&');

                xhr.send(params);
            });
        }

        // Form validation
        form.addEventListener('submit', function(e) {
            var start = parseInt(document.getElementById('emqm_active_hour_start').value, 10);
            var end = parseInt(document.getElementById('emqm_active_hour_end').value, 10);
            if (end > 0 && start > 0 && end < start) {
                alert('Giờ kết thúc phải lớn hơn hoặc bằng giờ bắt đầu!');
                e.preventDefault();
            }
        });
    });
</script>