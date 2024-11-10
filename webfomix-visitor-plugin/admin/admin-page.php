<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function webfomix_pvt_admin_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Verify nonce if there's any form submission
    if (isset($_POST['webfomix_pvt_action'])) {
        check_admin_referer('webfomix_pvt_action', 'webfomix_pvt_nonce');
    }

    try {
        $today_views = webfomix_pvt_get_today_views();
        $top_pages = webfomix_pvt_get_top_pages();
    } catch (Exception $e) {
        echo '<div class="notice notice-error"><p>Error: ' . esc_html($e->getMessage()) . '</p></div>';
        return;
    }
    
    ?>
    <div class="wrap">
        <h1>Page View Statistics</h1>
        
        <div class="card">
            <h2>Today's Total Views: <?php echo intval($today_views); ?></h2>
        </div>
        
        <div class="card">
            <h2>Top 10 Pages</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>URL</th>
                        <th>Total Views</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($top_pages as $page): ?>
                    <tr>
                        <td><?php echo esc_html($page->page_url); ?></td>
                        <td><?php echo intval($page->total_views); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}