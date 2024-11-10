<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'webfomix_pageviews';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

delete_option('webfomix_pvt_settings');