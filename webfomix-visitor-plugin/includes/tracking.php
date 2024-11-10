<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function webfomix_pvt_track_pageview() {
    if (is_admin() || wp_is_json_request()) {
        return;
    }
    
    if (webfomix_pvt_is_bot()) {
        return;
    }
    
    $page_url = esc_url_raw(home_url($_SERVER['REQUEST_URI']));
    $view_date = current_time('Y-m-d');
    
    webfomix_pvt_insert_pageview($page_url, $view_date);
}
add_action('wp', 'webfomix_pvt_track_pageview');

function webfomix_pvt_is_bot() {
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        return true;
    }

    $bot_keywords = array(
        'bot', 'crawler', 'spider', 'slurp', 'wget', 'favicon',
        'facebook', 'twitter', 'whatsapp', 'telegram',
        'linkedin', 'semrush', 'ahrefs', 'moz', 'majestic',
        'screaming frog', 'pingdom', 'uptimerobot', 'statuscake',
        'google', 'bing', 'yahoo', 'baidu', 'yandex',
        'duckduckgo', 'sogou', 'exabot', 'facebot', 'ia_archiver'
    );
    
    $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    
    foreach ($bot_keywords as $bot) {
        if (stripos($user_agent, $bot) !== false) {
            return true;
        }
    }
    
    // Check for common bot request headers
    $bot_headers = array('AdsBot-Google', 'Googlebot', 'bingbot', 'Baiduspider');
    foreach ($bot_headers as $header) {
        if (isset($_SERVER['HTTP_FROM']) && stripos($_SERVER['HTTP_FROM'], $header) !== false) {
            return true;
        }
    }
    
    return false;
}