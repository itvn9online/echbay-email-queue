<?php

/**
 * Email list view
 */

if (!defined('ABSPATH')) {
    exit;
}

// Resend email link
$cron_send_url = str_replace(ABSPATH, get_home_url() . '/', EMQM_PLUGIN_PATH) . 'cron-send.php?active_wp_mail=1';

?>

<div class="emqm-queue-stats">
    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-number"><?php echo $stats['pending']; ?></div>
            <div class="stat-label"><?php _e('Pending', 'echbay-mail-queue'); ?></div>
        </div>
        <div class="stat-box">
            <div class="stat-number"><?php echo $stats['sent']; ?></div>
            <div class="stat-label"><?php _e('Sent', 'echbay-mail-queue'); ?></div>
        </div>
        <div class="stat-box">
            <div class="stat-number"><?php echo $stats['failed']; ?></div>
            <div class="stat-label"><?php _e('Failed', 'echbay-mail-queue'); ?></div>
        </div>
        <div class="stat-box">
            <div class="stat-number"><?php echo $stats['total']; ?></div>
            <div class="stat-label"><?php _e('Total', 'echbay-mail-queue'); ?></div>
        </div>
    </div>
</div>

<div class="emqm-filters">
    <form method="get">
        <input type="hidden" name="page" value="echbay-mail-queue">
        <input type="hidden" name="tab" value="queue">

        <select name="status" onchange="this.form.submit()">
            <option value="all" <?php selected($status_filter, 'all'); ?>><?php _e('All Status', 'echbay-mail-queue'); ?></option>
            <option value="pending" <?php selected($status_filter, 'pending'); ?>><?php _e('Pending', 'echbay-mail-queue'); ?></option>
            <option value="sent" <?php selected($status_filter, 'sent'); ?>><?php _e('Sent', 'echbay-mail-queue'); ?></option>
            <option value="failed" <?php selected($status_filter, 'failed'); ?>><?php _e('Failed', 'echbay-mail-queue'); ?></option>
        </select>

        <button type="button" class="button" onclick="location.reload()">
            <?php _e('Refresh', 'echbay-mail-queue'); ?>
        </button>
    </form>
</div>

<div class="emqm-email-list">
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('ID', 'echbay-mail-queue'); ?></th>
                <th><?php _e('To', 'echbay-mail-queue'); ?></th>
                <th><?php _e('Subject', 'echbay-mail-queue'); ?></th>
                <th><?php _e('Status', 'echbay-mail-queue'); ?></th>
                <th><?php _e('Attempts', 'echbay-mail-queue'); ?></th>
                <th><?php _e('Created', 'echbay-mail-queue'); ?></th>
                <th><?php _e('Sent', 'echbay-mail-queue'); ?></th>
                <th><?php _e('Actions', 'echbay-mail-queue'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($emails)): ?>
                <tr>
                    <td colspan="8" class="no-items">
                        <?php _e('No emails found.', 'echbay-mail-queue'); ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($emails as $email): ?>
                    <tr data-email-id="<?php echo esc_attr($email->id); ?>">
                        <td><?php echo $email->id; ?></td>
                        <td><?php echo $email->to_email; ?></td>
                        <td><?php echo $email->subject; ?></td>
                        <td><span class="button button-small mail-queue-status-<?php echo $email->status; ?>"><?php echo $email->status; ?></span></td>
                        <td>
                            <?php
                            echo $email->attempts . '/' . $email->max_attempts;

                            // thêm link gửi lại email khi vượt quá ngưỡng
                            if ($email->status != 'sent' && $email->attempts >= $email->max_attempts) {
                                echo ' &nbsp; <a href="' . $cron_send_url . '&emqm_id=' . $email->id . '" class="button button-small resend-email" target="_blank">' . __('Resend', 'echbay-mail-queue') . '</a>';
                            }
                            ?>
                        </td>
                        <td><?php echo $email->created_at; ?></td>
                        <td><?php echo $email->sent_at; ?></td>
                        <td class="actions">
                            <?php if ($email->status === 'failed'): ?>
                                <button type="button" class="button button-small retry-email" data-email-id="<?php echo esc_attr($email->id); ?>">
                                    <?php _e('Retry', 'echbay-mail-queue'); ?>
                                </button>
                            <?php endif; ?>

                            <button type="button" class="button button-small delete-email" data-email-id="<?php echo esc_attr($email->id); ?>">
                                <?php _e('Delete', 'echbay-mail-queue'); ?>
                            </button>

                            <button type="button" class="button button-small view-details" data-email-id="<?php echo esc_attr($email->id); ?>">
                                <?php _e('View', 'echbay-mail-queue'); ?>
                            </button>
                        </td>
                    </tr>

                    <!-- Email details row (hidden by default) -->
                    <tr class="email-details" id="details-<?php echo esc_attr($email->id); ?>" style="display: none;">
                        <td colspan="8">
                            <div class="email-details-content">
                                <h4><?php _e('Email Details', 'echbay-mail-queue'); ?></h4>
                                <p><strong><?php _e('To:', 'echbay-mail-queue'); ?></strong> <?php echo $email->to_email; ?></p>
                                <p><strong><?php _e('Subject:', 'echbay-mail-queue'); ?></strong> <?php echo $email->subject; ?></p>
                                <?php if (!empty($email->headers)): ?>
                                    <p><strong><?php _e('Headers:', 'echbay-mail-queue'); ?></strong></p>
                                    <pre><?php echo esc_html($email->headers); ?></pre>
                                <?php endif; ?>
                                <p><strong><?php _e('Message:', 'echbay-mail-queue'); ?></strong></p>
                                <div class="email-message"><?php echo wp_kses_post($email->message); ?></div>
                                <?php if (!empty($email->error_message)): ?>
                                    <p><strong><?php _e('Error:', 'echbay-mail-queue'); ?></strong></p>
                                    <div class="error-message"><?php echo esc_html($email->error_message); ?></div>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($total_pages > 1): ?>
    <div class="emqm-pagination">
        <?php
        $pagination_args = array(
            'base' => add_query_arg('paged', '%#%'),
            'format' => '',
            'prev_text' => __('&laquo;'),
            'next_text' => __('&raquo;'),
            'total' => $total_pages,
            'current' => $paged
        );
        echo paginate_links($pagination_args);
        ?>
    </div>
<?php endif; ?>