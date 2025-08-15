<?php

/**
 * Settings view
 */

if (!defined('ABSPATH')) {
    exit;
}
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
                    <input type="number" id="emqm_batch_size" name="emqm_batch_size" value="<?php echo esc_attr(get_option('emqm_batch_size', 10)); ?>" min="1" max="100" />
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
                    <input type="number" id="emqm_delete_sent_after_days" name="emqm_delete_sent_after_days" value="<?php echo esc_attr(get_option('emqm_delete_sent_after_days', 30)); ?>" min="0" max="365" />
                    <p class="description"><?php _e('Automatically delete sent emails after this many days. Set to 0 to keep all emails.', 'echbay-mail-queue'); ?></p>
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
                    <input type="checkbox" id="emqm_prevent_duplicates" name="emqm_prevent_duplicates" value="1" <?php checked(get_option('emqm_prevent_duplicates', 0), 1); ?> />
                    <p class="description"><?php _e('Prevent duplicate emails with same recipient and subject within 5 minutes.', 'echbay-mail-queue'); ?></p>
                </td>
            </tr>
        </tbody>
    </table>

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
            <textarea readonly rows="10" ondblclick="this.select();" style="width: 99%;"><?php echo esc_html("<script>
document.addEventListener('DOMContentLoaded', function() {
    var script = document.createElement('script');
    script.src = window.location.origin + '/wp-content/plugins/echbay-email-queue/assets/js/frontend.js?v=' + Math.random();
    script.type = 'text/javascript';
    script.defer = true; // hoặc script.async = true;
    document.body.appendChild(script); // chèn vào cuối body (footer)
});
</script>"); ?></textarea>
        </div>

        <h4><?php _e('Server Cron (Required)', 'echbay-mail-queue'); ?></h4>
        <p><?php _e('Add this to your server crontab (runs every minute):', 'echbay-mail-queue'); ?></p>
        <code>
            * * * * * curl -s "<?php echo esc_html(str_replace(ABSPATH, get_home_url() . '/', EMQM_PLUGIN_PATH)); ?>cron-send.php?active_wp_mail=1" > /dev/null 2>&1
        </code>
        <p><em><?php _e('This plugin no longer uses WordPress cron. Please use server cron for better reliability.', 'echbay-mail-queue'); ?></em></p>
    </div>

    <?php submit_button(); ?>
</form>