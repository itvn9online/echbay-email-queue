<?php

/**
 * Auto Updater for Echbay Mail Queue Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class EMQM_Auto_Updater
{
    private $plugin_slug;
    private $plugin_file;
    private $version;
    private $github_user;
    private $github_repo;
    private $github_version_urls;
    private $github_download_url;

    public function __construct($plugin_file)
    {
        $this->plugin_file = $plugin_file;
        $this->plugin_slug = plugin_basename($plugin_file);
        $this->version = EMQM_VERSION;
        $this->github_user = 'itvn9online';
        $this->github_repo = 'echbay-email-queue';
        $this->github_version_urls = array(
            'https://raw.githubusercontent.com/' . $this->github_user . '/' . $this->github_repo . '/refs/heads/main/VERSION',
            'https://raw.githubusercontent.com/' . $this->github_user . '/' . $this->github_repo . '/refs/heads/main/version.txt',
        );
        $this->github_download_url = 'https://github.com/' . $this->github_user . '/' . $this->github_repo . '/archive/refs/heads/main.zip';

        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks()
    {
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_update'));
        add_filter('plugins_api', array($this, 'plugin_info'), 20, 3);
        add_filter('upgrader_source_selection', array($this, 'upgrader_source_selection'), 10, 3);
        add_action('upgrader_process_complete', array($this, 'upgrader_process_complete'), 10, 2);
    }

    /**
     * Canonical plugin folder name (without GitHub archive -main suffix).
     */
    private function get_canonical_plugin_dir()
    {
        return $this->github_repo;
    }

    /**
     * Strip trailing -main from folder names (echbay-email-queue-main -> echbay-email-queue).
     */
    private function normalize_dir_name($dir_name)
    {
        $canonical = $this->get_canonical_plugin_dir();

        if ($dir_name === $canonical || $dir_name === $canonical . '-main') {
            return $canonical;
        }

        if (preg_match('/^' . preg_quote($canonical, '/') . '-main$/', $dir_name)) {
            return $canonical;
        }

        return $dir_name;
    }

    /**
     * Whether the current upgrader run targets this plugin.
     */
    private function is_updating_our_plugin($upgrader)
    {
        if (!isset($upgrader->skin->plugin)) {
            return false;
        }

        $plugin = $upgrader->skin->plugin;

        if (basename($plugin) !== basename($this->plugin_file)) {
            return false;
        }

        return $this->normalize_dir_name(dirname($plugin)) === $this->get_canonical_plugin_dir();
    }

    /**
     * Check for plugin updates
     */
    public function check_for_update($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        // Get remote version
        $remote_version = $this->get_remote_version();

        if (!$remote_version) {
            return $transient;
        }

        // Compare versions
        if (version_compare($this->version, $remote_version, '<')) {
            $transient->response[$this->plugin_slug] = (object) array(
                'slug' => dirname($this->plugin_slug),
                'plugin' => $this->plugin_slug,
                'new_version' => $remote_version,
                'url' => 'https://github.com/' . $this->github_user . '/' . $this->github_repo,
                'package' => $this->github_download_url,
                'tested' => get_bloginfo('version'),
                'compatibility' => array()
            );
        } else {
            // Plugin is up to date — clear stale update response and mark as no_update
            if (isset($transient->response[$this->plugin_slug])) {
                unset($transient->response[$this->plugin_slug]);
            }
            $transient->no_update[$this->plugin_slug] = (object) array(
                'slug' => dirname($this->plugin_slug),
                'plugin' => $this->plugin_slug,
                'new_version' => $remote_version,
                'url' => 'https://github.com/' . $this->github_user . '/' . $this->github_repo,
                'package' => '',
                'tested' => get_bloginfo('version'),
                'compatibility' => array()
            );
        }

        return $transient;
    }

    /**
     * Get remote version from GitHub (VERSION or version.txt).
     */
    private function get_remote_version()
    {
        foreach ($this->github_version_urls as $url) {
            $request = wp_remote_get($url, array(
                'timeout' => 10,
                'sslverify' => false
            ));

            if (!is_wp_error($request) && wp_remote_retrieve_response_code($request) === 200) {
                $body = trim(wp_remote_retrieve_body($request));
                if ($body !== '') {
                    return $body;
                }
            }
        }

        return false;
    }

    /**
     * Plugin information for update popup
     */
    public function plugin_info($false, $action, $response)
    {
        if ($action !== 'plugin_information') {
            return $false;
        }

        if ($this->normalize_dir_name($response->slug) !== $this->get_canonical_plugin_dir()) {
            return $false;
        }

        $remote_version = $this->get_remote_version();

        $response = (object) array(
            'name' => 'Echbay Mail Queue Manager',
            'slug' => dirname($this->plugin_slug),
            'version' => $remote_version,
            'author' => 'Dao Quoc Dai',
            'homepage' => 'https://github.com/' . $this->github_user . '/' . $this->github_repo,
            'short_description' => 'Quản lý hàng đợi email cho WordPress, tránh làm chậm website khi gửi mail.',
            'sections' => array(
                'Description' => 'Plugin quản lý hàng đợi email cho WordPress, giúp tối ưu hiệu suất website khi gửi email.',
                'Installation' => 'Upload plugin và activate. Cấu hình cronjob server để xử lý hàng đợi email.',
                'Changelog' => $this->get_changelog()
            ),
            'download_link' => $this->github_download_url,
            'trunk' => $this->github_download_url,
            'requires' => '5.0',
            'tested' => get_bloginfo('version'),
            'requires_php' => '7.4',
            'last_updated' => date_i18n('Y-m-d H:i:s'),
            'upgrade_notice' => 'Phiên bản mới có sẵn. Vui lòng cập nhật để nhận các tính năng và sửa lỗi mới nhất.'
        );

        return $response;
    }

    /**
     * Get changelog from GitHub
     */
    private function get_changelog()
    {
        $changelog_url = 'https://github.com/' . $this->github_user . '/' . $this->github_repo . '/raw/refs/heads/main/wp-content/plugins/echbay-email-queue/CHANGELOG.md';

        $request = wp_remote_get($changelog_url, array(
            'timeout' => 10,
            'sslverify' => false
        ));

        if (!is_wp_error($request) && wp_remote_retrieve_response_code($request) === 200) {
            $body = wp_remote_retrieve_body($request);
            // Convert markdown to HTML (basic)
            $body = nl2br(esc_html($body));
            return $body;
        }

        return 'Không thể tải changelog.';
    }

    /**
     * Locate plugin files inside a GitHub archive extract folder.
     */
    private function find_extracted_plugin_path($source)
    {
        global $wp_filesystem;

        $main_file = basename($this->plugin_file);
        $canonical = $this->get_canonical_plugin_dir();
        $archive_dir = $canonical . '-main';

        $candidates = array(
            trailingslashit($source) . $archive_dir . '/wp-content/plugins/' . $canonical,
            trailingslashit($source) . 'wp-content/plugins/' . $canonical,
            trailingslashit($source) . $canonical,
            trailingslashit($source) . $archive_dir,
        );

        foreach ($candidates as $path) {
            if ($wp_filesystem->is_dir($path) && $wp_filesystem->exists(trailingslashit($path) . $main_file)) {
                return $path;
            }
        }

        $source_files = $wp_filesystem->dirlist($source, false, false);
        if (!$source_files) {
            return false;
        }

        foreach ($source_files as $name => $info) {
            if ($info['type'] !== 'd') {
                continue;
            }

            if ($this->normalize_dir_name($name) !== $canonical) {
                continue;
            }

            $nested_paths = array(
                trailingslashit($source) . $name . '/wp-content/plugins/' . $canonical,
                trailingslashit($source) . $name,
            );

            foreach ($nested_paths as $path) {
                if ($wp_filesystem->is_dir($path) && $wp_filesystem->exists(trailingslashit($path) . $main_file)) {
                    return $path;
                }
            }
        }

        return false;
    }

    /**
     * Fix source directory after download; always install to echbay-email-queue (no -main).
     */
    public function upgrader_source_selection($source, $remote_source, $upgrader)
    {
        global $wp_filesystem;

        if (!$this->is_updating_our_plugin($upgrader)) {
            return $source;
        }

        $plugin_source = $this->find_extracted_plugin_path($source);
        if (!$plugin_source) {
            return $source;
        }

        $canonical = $this->get_canonical_plugin_dir();
        $desired_destination = trailingslashit($remote_source) . $canonical;
        $current_dir = dirname($this->plugin_slug);
        $current_path = trailingslashit($remote_source) . $current_dir;

        if ($current_dir !== $canonical && $wp_filesystem->is_dir($current_path)) {
            $wp_filesystem->delete($current_path, true);
        }

        if ($desired_destination !== $plugin_source && $wp_filesystem->is_dir($desired_destination)) {
            $wp_filesystem->delete($desired_destination, true);
        }

        if ($wp_filesystem->move($plugin_source, $desired_destination)) {
            $wp_filesystem->delete($source, true);
            return $desired_destination;
        }

        return $source;
    }

    /**
     * Keep plugin active after folder rename from *-main to canonical name.
     */
    public function upgrader_process_complete($upgrader, $hook_extra)
    {
        if (!isset($hook_extra['action'], $hook_extra['type']) || $hook_extra['action'] !== 'update' || $hook_extra['type'] !== 'plugin') {
            return;
        }

        if (empty($hook_extra['plugins']) || !is_array($hook_extra['plugins'])) {
            return;
        }

        $canonical_slug = $this->get_canonical_plugin_dir() . '/' . basename($this->plugin_file);

        foreach ($hook_extra['plugins'] as $plugin) {
            if (basename($plugin) !== basename($this->plugin_file)) {
                continue;
            }

            if ($this->normalize_dir_name(dirname($plugin)) !== $this->get_canonical_plugin_dir()) {
                continue;
            }

            if ($plugin === $canonical_slug) {
                continue;
            }

            $this->migrate_active_plugin_path($plugin, $canonical_slug);
        }
    }

    /**
     * Replace old plugin path in active_plugins after directory rename.
     */
    private function migrate_active_plugin_path($old_slug, $new_slug)
    {
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $was_active = is_plugin_active($old_slug);
        if (!$was_active) {
            return;
        }

        $active_plugins = get_option('active_plugins', array());
        if (!is_array($active_plugins)) {
            return;
        }

        $updated = false;
        foreach ($active_plugins as $index => $plugin) {
            if ($plugin === $old_slug) {
                $active_plugins[$index] = $new_slug;
                $updated = true;
            }
        }

        if ($updated) {
            update_option('active_plugins', $active_plugins);
        }

        if (is_multisite()) {
            $network_active = get_site_option('active_sitewide_plugins', array());
            if (isset($network_active[$old_slug])) {
                $network_active[$new_slug] = $network_active[$old_slug];
                unset($network_active[$old_slug]);
                update_site_option('active_sitewide_plugins', $network_active);
            }
        }
    }

    /**
     * Manual check for updates (for admin page)
     */
    public function manual_check_update()
    {
        $remote_version = $this->get_remote_version();

        if (!$remote_version) {
            return array(
                'success' => false,
                'message' => 'Không thể kiểm tra phiên bản mới từ GitHub.'
            );
        }

        $current_version = $this->version;
        $has_update = version_compare($current_version, $remote_version, '<');

        return array(
            'success' => true,
            'current_version' => $current_version,
            'remote_version' => $remote_version,
            'has_update' => $has_update,
            'message' => $has_update
                ? "Có phiên bản mới: {$remote_version} (hiện tại: {$current_version})"
                : "Plugin đang ở phiên bản mới nhất: {$current_version}"
        );
    }
}
