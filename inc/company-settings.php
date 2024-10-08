<?php


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

    add_submenu_page(
        'exec-developer-office-suite',
        'Settings',
        'Settings',
        'manage_options',
        'exec-developer-office-suite-settings',
        'exec_dev_office_suite_settings_page'
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

// Settings page content
function exec_dev_office_suite_settings_page() {
    ?>
    <div class="wrap">
        <h1>Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('exec_dev_office_suite_settings_group');
            do_settings_sections('exec-developer-office-suite-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings
function exec_dev_office_suite_register_settings() {
    register_setting('exec_dev_office_suite_settings_group', 'exec_dev_office_suite_company_name');
    register_setting('exec_dev_office_suite_settings_group', 'exec_dev_office_suite_company_address');
    register_setting('exec_dev_office_suite_settings_group', 'exec_dev_office_suite_phone');
    register_setting('exec_dev_office_suite_settings_group', 'exec_dev_office_suite_email');
    register_setting('exec_dev_office_suite_settings_group', 'exec_dev_office_suite_logo');
    register_setting('exec_dev_office_suite_settings_group', 'exec_dev_office_suite_reference_number_prefix');
    register_setting('exec_dev_office_suite_settings_group', 'exec_dev_office_suite_display_logo');
    register_setting('exec_dev_office_suite_settings_group', 'exec_dev_office_suite_display_company_name');

    add_settings_section(
        'exec_dev_office_suite_settings_section',
        'Company Information',
        null,
        'exec-developer-office-suite-settings'
    );

    add_settings_field(
        'exec_dev_office_suite_company_name',
        'Company Name',
        'exec_dev_office_suite_company_name_callback',
        'exec-developer-office-suite-settings',
        'exec_dev_office_suite_settings_section'
    );

    add_settings_field(
        'exec_dev_office_suite_company_address',
        'Company Address',
        'exec_dev_office_suite_company_address_callback',
        'exec-developer-office-suite-settings',
        'exec_dev_office_suite_settings_section'
    );

    add_settings_field(
        'exec_dev_office_suite_phone',
        'Phone',
        'exec_dev_office_suite_phone_callback',
        'exec-developer-office-suite-settings',
        'exec_dev_office_suite_settings_section'
    );

    add_settings_field(
        'exec_dev_office_suite_email',
        'Email',
        'exec_dev_office_suite_email_callback',
        'exec-developer-office-suite-settings',
        'exec_dev_office_suite_settings_section'
    );

    add_settings_field(
        'exec_dev_office_suite_logo',
        'Logo',
        'exec_dev_office_suite_logo_callback',
        'exec-developer-office-suite-settings',
        'exec_dev_office_suite_settings_section'
    );

    add_settings_field(
        'exec_dev_office_suite_reference_number_prefix',
        'Reference Number Prefix',
        'exec_dev_office_suite_reference_number_prefix_callback',
        'exec-developer-office-suite-settings',
        'exec_dev_office_suite_settings_section'
    );

    add_settings_field(
        'exec_dev_office_suite_display_logo',
        'Display Logo',
        'exec_dev_office_suite_display_logo_callback',
        'exec-developer-office-suite-settings',
        'exec_dev_office_suite_settings_section'
    );

    add_settings_field(
        'exec_dev_office_suite_display_company_name',
        'Display Company Name',
        'exec_dev_office_suite_display_company_name_callback',
        'exec-developer-office-suite-settings',
        'exec_dev_office_suite_settings_section'
    );
}
add_action('admin_init', 'exec_dev_office_suite_register_settings');

// Callbacks to render the input fields
function exec_dev_office_suite_company_name_callback() {
    $company_name = get_option('exec_dev_office_suite_company_name');
    echo '<input type="text" name="exec_dev_office_suite_company_name" value="' . esc_attr($company_name) . '" class="regular-text">';
}

function exec_dev_office_suite_company_address_callback() {
    $company_address = get_option('exec_dev_office_suite_company_address');
    echo '<textarea id="exec_dev_office_suite_company_address" name="exec_dev_office_suite_company_address" class="regular-text" rows="3" style="width: 100%;">' . esc_textarea($company_address) . '</textarea>';
}

function exec_dev_office_suite_phone_callback() {
    $phone = get_option('exec_dev_office_suite_phone');
    echo '<input type="text" name="exec_dev_office_suite_phone" value="' . esc_attr($phone) . '" class="regular-text">';
}

function exec_dev_office_suite_email_callback() {
    $email = get_option('exec_dev_office_suite_email');
    echo '<input type="email" name="exec_dev_office_suite_email" value="' . esc_attr($email) . '" class="regular-text">';
}

function exec_dev_office_suite_logo_callback() {
    $logo = get_option('exec_dev_office_suite_logo');
    echo '<input type="hidden" name="exec_dev_office_suite_logo" id="exec_dev_office_suite_logo" value="' . esc_url($logo) . '">';
    echo '<button type="button" class="button" id="upload_logo_button">Upload Logo</button>';
    echo '<div id="logo_preview" style="margin-top: 10px;">';
    if ($logo) {
        echo '<img src="' . esc_url($logo) . '" style="max-width: 150px;">';
    }
    echo '</div>';
}

// Enqueue admin scripts for media uploader
function exec_dev_office_suite_admin_scripts() {
    wp_enqueue_media();
    wp_enqueue_script('exec_dev_office_suite_admin_script', plugin_dir_url(__DIR__) . 'js/admin-script.js', array('jquery'), '1.0.6', true);
    wp_enqueue_style('exec_dev_office_suite_admin_style', plugin_dir_url(__DIR__) . 'css/admin-style.css');
}
add_action('admin_enqueue_scripts', 'exec_dev_office_suite_admin_scripts');

// Enqueue TinyMCE scripts
function exec_dev_office_suite_enqueue_tinymce() {
    if (is_page_template('admin-only-template.php')) {
        wp_enqueue_script('tinymce', plugin_dir_url(__DIR__) . 'tinymce/tinymce.min.js', array(), null, true);
        wp_enqueue_script('exec-dev-office-suite-tinymce', plugin_dir_url(__DIR__) . 'js/tinymce-init.js', array('tinymce', 'jquery'), null, true);
    }
}
add_action('wp_enqueue_scripts', 'exec_dev_office_suite_enqueue_tinymce');

// Add reference number prefix setting
function exec_dev_office_suite_reference_number_prefix_callback() {
    $reference_number_prefix = get_option('exec_dev_office_suite_reference_number_prefix');
    echo '<input type="text" name="exec_dev_office_suite_reference_number_prefix" value="' . esc_attr($reference_number_prefix) . '" class="regular-text">';
}

// Add display logo setting
function exec_dev_office_suite_display_logo_callback() {
    $display_logo = get_option('exec_dev_office_suite_display_logo');
    echo '<input type="checkbox" name="exec_dev_office_suite_display_logo" value="1" ' . checked(1, $display_logo, false) . '>';
}

// Add display company name setting
function exec_dev_office_suite_display_company_name_callback() {
    $display_company_name = get_option('exec_dev_office_suite_display_company_name');
    echo '<input type="checkbox" name="exec_dev_office_suite_display_company_name" value="1" ' . checked(1, $display_company_name, false) . '>';
}
