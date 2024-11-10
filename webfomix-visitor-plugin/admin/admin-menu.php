<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function webfomix_pvt_add_admin_menu() {
    add_menu_page(
        'Page View Statistics',
        'Page Views',
        'manage_options',
        'webfomix-pageview-tracker',
        'webfomix_pvt_admin_page',
        'dashicons-chart-bar'
    );
}
add_action('admin_menu', 'webfomix_pvt_add_admin_menu');