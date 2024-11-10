<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function webfomix_pvt_activate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'webfomix_pageviews';
    
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        page_url varchar(255) NOT NULL,
        view_date DATE NOT NULL,
        view_count int(11) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY page_date (page_url(191), view_date),
        KEY view_date (view_date)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Add default options
    add_option('webfomix_pvt_settings', array(
        'retention_days' => 90,
        'track_admin' => 0
    ));

    // Schedule cleanup if not already scheduled
    if (!wp_next_scheduled('webfomix_pvt_daily_cleanup')) {
        wp_schedule_event(strtotime('tomorrow 00:00:00'), 'daily', 'webfomix_pvt_daily_cleanup');
    }
}

function webfomix_pvt_deactivate() {
    wp_clear_scheduled_hook('webfomix_pvt_daily_cleanup');
}

function webfomix_pvt_insert_pageview($page_url, $view_date) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'webfomix_pageviews';

    // Sanitize inputs
    $page_url = esc_url_raw($page_url);
    $view_date = sanitize_text_field($view_date);

    // Use INSERT ... ON DUPLICATE KEY UPDATE
    $result = $wpdb->query($wpdb->prepare(
        "INSERT INTO $table_name (page_url, view_date, view_count) 
        VALUES (%s, %s, 1) 
        ON DUPLICATE KEY UPDATE view_count = view_count + 1",
        $page_url,
        $view_date
    ));

    if ($result === false) {
        error_log(sprintf(
            'Webfomix Page View Tracker: Error inserting pageview - %s (URL: %s, Date: %s)',
            $wpdb->last_error,
            $page_url,
            $view_date
        ));
        return false;
    }

    return true;
}

function webfomix_pvt_get_today_views() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'webfomix_pageviews';
    
    $cache_key = 'webfomix_pvt_today_views_' . current_time('Y-m-d');
    $cached_result = wp_cache_get($cache_key);
    
    if (false !== $cached_result) {
        return intval($cached_result);
    }

    $result = $wpdb->get_var($wpdb->prepare(
        "SELECT COALESCE(SUM(view_count), 0) 
        FROM $table_name 
        WHERE view_date = %s",
        current_time('Y-m-d')
    ));

    wp_cache_set($cache_key, $result, '', HOUR_IN_SECONDS);
    return intval($result);
}

function webfomix_pvt_get_top_pages($limit = 10) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'webfomix_pageviews';
    
    $cache_key = 'webfomix_pvt_top_pages_' . $limit;
    $cached_result = wp_cache_get($cache_key);
    
    if (false !== $cached_result) {
        return $cached_result;
    }

    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT page_url, SUM(view_count) as total_views 
        FROM $table_name 
        GROUP BY page_url 
        ORDER BY total_views DESC 
        LIMIT %d",
        absint($limit)
    ));

    wp_cache_set($cache_key, $results, '', HOUR_IN_SECONDS);
    return $results ?: array();
}

function webfomix_pvt_cleanup_old_data() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'webfomix_pageviews';
    
    $settings = get_option('webfomix_pvt_settings');
    $days = isset($settings['retention_days']) ? absint($settings['retention_days']) : 90;
    
    $wpdb->query($wpdb->prepare(
        "DELETE FROM $table_name 
        WHERE view_date < DATE_SUB(CURDATE(), INTERVAL %d DAY)",
        $days
    ));
}

// Register cleanup hook
add_action('webfomix_pvt_daily_cleanup', 'webfomix_pvt_cleanup_old_data');