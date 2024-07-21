<?php
/*
Plugin Name: Exec Developer Office Suite
Plugin URI: https://execdeveloper.com/
Description: A comprehensive office suite plugin for small business owners, including features like generating letterhead PDFs.
Version: 1.0.0
Author: Ashwin
Author URI: https://execdeveloper.com/
License: GPL2
*/

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Include the plugin functions file
require_once plugin_dir_path(__FILE__) . 'functions.php';

// Register custom template
function exec_dev_office_suite_register_template($templates) {
    $templates['admin-only-template.php'] = 'Admin Only Template';
    return $templates;
}
add_filter('theme_page_templates', 'exec_dev_office_suite_register_template');

// Load custom template
function exec_dev_office_suite_load_template($template) {
    global $post;

    if ($post && get_page_template_slug($post->ID) == 'admin-only-template.php') {
        $template = plugin_dir_path(__FILE__) . 'admin-only-template.php';
    }

    return $template;
}
add_filter('template_include', 'exec_dev_office_suite_load_template');

// Enqueue the necessary scripts and styles for the template
function exec_dev_office_suite_enqueue_template_scripts() {
    if (is_page_template('admin-only-template.php')) {
        wp_enqueue_style('exec-dev-office-suite-style', plugin_dir_url(__FILE__) . 'css/admin-style.css');
        wp_enqueue_script('exec-dev-office-suite-admin-letters', plugin_dir_url(__FILE__) . 'js/admin-script.js', array('jquery'), null, true);

        // Localize the script with nonce
        wp_localize_script('exec-dev-office-suite-admin-letters', 'execDevOfficeSuite', array(
            'apiUrl' => rest_url('exec-dev-office-suite/v1/letters'),
            'nonce' => wp_create_nonce('wp_rest')
        ));
    }
}
add_action('wp_enqueue_scripts', 'exec_dev_office_suite_enqueue_template_scripts');


// Plugin activation hook
function exec_dev_office_suite_activate() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'exec_data';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        subject text NOT NULL,
        content longtext NOT NULL,
        to_field text NOT NULL,
        address text NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'exec_dev_office_suite_activate');




// Plugin deactivation hook
function exec_dev_office_suite_deactivate() {
    // Deactivation code here
}
register_deactivation_hook(__FILE__, 'exec_dev_office_suite_deactivate');

// Initialize the plugin
function exec_dev_office_suite_init() {
    // Initialization code here
}
add_action('plugins_loaded', 'exec_dev_office_suite_init');

// Add admin menu item
function exec_dev_office_suite_admin_menu() {
    add_menu_page(
        'Exec Developer Office Suite',
        'Exec Developer Office Suite',
        'manage_options',
        'exec-developer-office-suite',
        'exec_dev_office_suite_admin_page',
        'dashicons-admin-generic'
    );
}
add_action('admin_menu', 'exec_dev_office_suite_admin_menu');

// Admin page content
function exec_dev_office_suite_admin_page() {
    ?>
    <div class="wrap">
        <h1>Exec Developer Office Suite</h1>
        <p>Welcome to the Exec Developer Office Suite plugin for small business owners.</p>
    </div>
    <?php
}

// Enqueue TinyMCE scripts
function exec_dev_office_suite_enqueue_tinymce() {
    if (is_page_template('admin-only-template.php')) {
        wp_enqueue_script('tinymce', plugin_dir_url(__FILE__) . 'tinymce/tinymce.min.js', array(), null, true);
        wp_enqueue_script('exec-dev-office-suite-tinymce', plugin_dir_url(__FILE__) . 'js/tinymce-init.js', array('tinymce', 'jquery'), null, true);
    }
}
add_action('wp_enqueue_scripts', 'exec_dev_office_suite_enqueue_tinymce');

?>
