<?php
/*
Plugin Name: Webfomix Page View Tracker
Plugin URI: https://webfomix.com
Description: Tracks real human page views and displays statistics in the admin dashboard
Version: 1.0
Author: Webfomix
Author URI: https://webfomix.com
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WEBFOMIX_PVT_PATH', plugin_dir_path(__FILE__));
define('WEBFOMIX_PVT_URL', plugin_dir_url(__FILE__));

// Include necessary files
require_once WEBFOMIX_PVT_PATH . 'includes/database.php';
require_once WEBFOMIX_PVT_PATH . 'includes/tracking.php';
require_once WEBFOMIX_PVT_PATH . 'admin/admin-menu.php';
require_once WEBFOMIX_PVT_PATH . 'admin/admin-page.php';

// Activation hook
register_activation_hook(__FILE__, 'webfomix_pvt_activate');

// Deactivation hook
register_deactivation_hook(__FILE__, 'webfomix_pvt_deactivate');

// Uninstall hook
register_uninstall_hook(__FILE__, 'webfomix_pvt_uninstall');