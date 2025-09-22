<?php

/**
 * Gmail API Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class EMQM_Gmail_API
{
    private $client_id;
    private $client_secret;
    private $refresh_token;
    private $from_email;
    private $from_name;
    private $access_token;
    private $enable_logging;
    private $domain_prefix;

    public function __construct()
    {
        $this->domain_prefix = self::get_domain_prefix_static();
        $this->client_id = get_option($this->domain_prefix . 'emqm_gmail_client_id', '');
        $this->client_secret = get_option($this->domain_prefix . 'emqm_gmail_client_secret', '');
        $this->refresh_token = get_option($this->domain_prefix . 'emqm_gmail_refresh_token', '');
        $this->from_email = get_option($this->domain_prefix . 'emqm_gmail_from_email', '');
        $this->from_name = get_option($this->domain_prefix . 'emqm_gmail_from_name', '');
        $this->enable_logging = get_option('emqm_enable_logging', 0) > 0 ? true : false;
    }

    /**
     * Check if Gmail API is properly configured
     */
    public function is_configured()
    {
        return !empty($this->client_id) &&
            !empty($this->client_secret) &&
            !empty($this->refresh_token) &&
            !empty($this->from_email);
    }

    /**
     * Get access token using refresh token
     */
    private function get_access_token()
    {
        if (!empty($this->access_token)) {
            return $this->access_token;
        }

        $url = 'https://oauth2.googleapis.com/token';

        $data = array(
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'refresh_token' => $this->refresh_token,
            'grant_type' => 'refresh_token'
        );

        $response = wp_remote_post($url, array(
            'body' => $data,
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            if ($this->enable_logging) {
                error_log('EMQM Gmail API: Failed to get access token - ' . $response->get_error_message());
            }
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $token_data = json_decode($body, true);

        if (isset($token_data['access_token'])) {
            $this->access_token = $token_data['access_token'];
            return $this->access_token;
        }

        if ($this->enable_logging) {
            error_log('EMQM Gmail API: Invalid token response - ' . $body);
        }

        return false;
    }

    /**
     * Send email via Gmail API
     */
    public function send_email($to, $subject, $message, $headers = array())
    {
        if (!$this->is_configured()) {
            if ($this->enable_logging) {
                error_log('EMQM Gmail API: Not properly configured');
            }
            return false;
        }

        $access_token = $this->get_access_token();
        if (!$access_token) {
            return false;
        }

        // Build email message
        $email_message = $this->build_email_message($to, $subject, $message, $headers);
        if (!$email_message) {
            return false;
        }

        // Send via Gmail API
        $url = 'https://gmail.googleapis.com/gmail/v1/users/me/messages/send';

        $data = json_encode(array(
            'raw' => base64_encode($email_message)
        ));

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json'
            ),
            'body' => $data,
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            if ($this->enable_logging) {
                error_log('EMQM Gmail API: Send failed - ' . $response->get_error_message());
            }
            return false;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code === 200) {
            if ($this->enable_logging) {
                error_log('EMQM Gmail API: Email sent successfully to ' . $to);
            }
            return true;
        }

        $body = wp_remote_retrieve_body($response);
        if ($this->enable_logging) {
            error_log('EMQM Gmail API: Send failed with code ' . $response_code . ' - ' . $body);
        }

        return false;
    }

    /**
     * Build email message in RFC 2822 format
     */
    private function build_email_message($to, $subject, $message, $headers = array())
    {
        $email_lines = array();

        // From header
        $from_name_encoded = !empty($this->from_name) ? '=?UTF-8?B?' . base64_encode($this->from_name) . '?=' : '';
        $from_header = !empty($from_name_encoded) ?
            $from_name_encoded . ' <' . $this->from_email . '>' :
            $this->from_email;

        $email_lines[] = 'From: ' . $from_header;
        $email_lines[] = 'To: ' . $to;
        $email_lines[] = 'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=';

        // Process additional headers
        $content_type_set = false;
        if (!empty($headers)) {
            foreach ($headers as $header) {
                $header = trim($header);
                if (!empty($header) && strpos($header, ':') !== false) {
                    // Skip From, To, Subject as we set them above
                    if (
                        stripos($header, 'from:') === 0 ||
                        stripos($header, 'to:') === 0 ||
                        stripos($header, 'subject:') === 0
                    ) {
                        continue;
                    }

                    if (stripos($header, 'content-type:') === 0) {
                        $content_type_set = true;
                    }

                    $email_lines[] = $header;
                }
            }
        }

        // Set default content type if not specified
        if (!$content_type_set) {
            // Check if message contains HTML
            if ($message !== strip_tags($message)) {
                $email_lines[] = 'Content-Type: text/html; charset=UTF-8';
            } else {
                $email_lines[] = 'Content-Type: text/plain; charset=UTF-8';
            }
        }

        $email_lines[] = 'MIME-Version: 1.0';
        $email_lines[] = '';
        $email_lines[] = $message;

        return implode("\r\n", $email_lines);
    }

    /**
     * Test Gmail API connection by sending a test email
     */
    public function test_connection()
    {
        if (!$this->is_configured()) {
            return array(
                'success' => false,
                'message' => 'Gmail API not properly configured'
            );
        }

        $access_token = $this->get_access_token();
        if (!$access_token) {
            return array(
                'success' => false,
                'message' => 'Failed to get access token'
            );
        }

        // Get admin email for test
        $admin_email = get_option('admin_email');
        if (empty($admin_email)) {
            return array(
                'success' => false,
                'message' => 'Admin email not configured'
            );
        }

        // Prepare test email content
        $test_subject = '[Test] Gmail API Connection - ' . get_bloginfo('name');
        $test_message = 'This is a test email to verify Gmail API connection is working correctly.

Sent from: ' . get_bloginfo('name') . '
Sent at: ' . current_time('Y-m-d H:i:s') . '
Domain: ' . $_SERVER['HTTP_HOST'] . '

If you receive this email, your Gmail API configuration is working properly.';

        // Try to send test email
        $sent = $this->send_email($admin_email, $test_subject, $test_message);

        if ($sent) {
            return array(
                'success' => true,
                'message' => 'Test email sent successfully',
                'email' => $admin_email
            );
        } else {
            return array(
                'success' => false,
                'message' => 'Failed to send test email'
            );
        }
    }

    /**
     * Get domain prefix for multi-domain support (static method for use by other classes)
     */
    public static function get_domain_prefix_static()
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (empty($host)) {
            return '';
        }

        // Remove www. prefix and sanitize domain name
        $domain = preg_replace('/^www\./', '', $host);
        $domain = preg_replace('/[^a-zA-Z0-9\-\.]/', '', $domain);
        $domain = str_replace('.', '_', $domain);

        return $domain . '_';
    }
}
