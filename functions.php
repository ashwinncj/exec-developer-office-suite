<?php

// Register REST API endpoints
function exec_dev_office_suite_register_api_endpoints() {
    // Endpoint for fetching the list of letters
    register_rest_route('exec-dev-office-suite/v1', '/letters', array(
        'methods' => 'GET',
        'callback' => 'exec_dev_office_suite_get_letters',
        'permission_callback' => 'exec_dev_office_suite_admin_permission_callback'
    ));

    // Endpoint for fetching the full content of a specific letter
    register_rest_route('exec-dev-office-suite/v1', '/letters/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'exec_dev_office_suite_get_letter',
        'permission_callback' => 'exec_dev_office_suite_admin_permission_callback'
    ));
}
add_action('rest_api_init', 'exec_dev_office_suite_register_api_endpoints');

// Permission callback to ensure only admins can access the endpoints
function exec_dev_office_suite_admin_permission_callback() {
    return current_user_can('manage_options');
}


// Callback function for fetching the list of letters
function exec_dev_office_suite_get_letters($request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'exec_data';

    // Pagination parameters
    $page = isset($request['page']) ? intval($request['page']) : 1;
    $per_page = isset($request['per_page']) ? intval($request['per_page']) : 10;
    $offset = ($page - 1) * $per_page;

    // Fetch the list of letters ordered by date in descending order
    $letters = $wpdb->get_results($wpdb->prepare(
        "SELECT id, date, subject, to_field, address FROM $table_name ORDER BY date DESC LIMIT %d OFFSET %d",
        $per_page, $offset
    ));

    // Get the total count of letters for pagination
    $total_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

    // Prepare the response
    $response = new WP_REST_Response($letters);
    $response->header('X-WP-Total', $total_count);
    $response->header('X-WP-TotalPages', ceil($total_count / $per_page));

    return $response;
}

// Callback function for fetching the full content of a specific letter
function exec_dev_office_suite_get_letter($request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'exec_data';
    $id = intval($request['id']);

    // Fetch the letter details by ID
    $letter = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE id = %d",
        $id
    ));

    if ($letter) {
        return new WP_REST_Response($letter, 200);
    } else {
        return new WP_Error('no_letter', 'Letter not found', array('status' => 404));
    }
}

// Register WP-CLI command to add dummy data
if (defined('WP_CLI') && WP_CLI) {
    class ExecDevOfficeSuite_CLI {
        public function add_dummy_data($args, $assoc_args) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'exec_data';
            
            // Number of dummy records to add
            $num_records = isset($assoc_args['count']) ? intval($assoc_args['count']) : 10;
            
            for ($i = 1; $i <= $num_records; $i++) {
                $wpdb->insert(
                    $table_name,
                    array(
                        'date' => current_time('mysql'),
                        'subject' => "Test Subject $i",
                        'content' => "This is the content of test letter $i.",
                        'to_field' => "Recipient $i",
                        'address' => "123 Test Address $i, Test City, Test Country"
                    )
                );
            }
            
            WP_CLI::success("$num_records dummy letters added to the $table_name table.");
        }
    }

    WP_CLI::add_command('exec-dev-office-suite add-dummy-data', array('ExecDevOfficeSuite_CLI', 'add_dummy_data'));
}


// Register REST API endpoint for searching letters
function exec_dev_office_suite_register_search_endpoint() {
    register_rest_route('exec-dev-office-suite/v1', '/search-letters', array(
        'methods' => 'GET',
        'callback' => 'exec_dev_office_suite_search_letters',
        'permission_callback' => 'exec_dev_office_suite_admin_permission_callback'
    ));
}
add_action('rest_api_init', 'exec_dev_office_suite_register_search_endpoint');

function exec_dev_office_suite_search_letters($request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'exec_data';
    $search = $request->get_param('search');

    // Fetch the list of letters matching the search query and order by date in descending order
    $letters = $wpdb->get_results($wpdb->prepare(
        "SELECT id, date, subject, to_field, address FROM $table_name WHERE subject LIKE %s OR to_field LIKE %s ORDER BY date DESC",
        '%' . $wpdb->esc_like($search) . '%',
        '%' . $wpdb->esc_like($search) . '%'
    ));

    return new WP_REST_Response($letters, 200);
}


// Register REST API endpoint for adding a new letter
function exec_dev_office_suite_register_add_letter_endpoint() {
    register_rest_route('exec-dev-office-suite/v1', '/add-letter', array(
        'methods' => 'POST',
        'callback' => 'exec_dev_office_suite_add_letter',
        'permission_callback' => 'exec_dev_office_suite_admin_permission_callback'
    ));
}
add_action('rest_api_init', 'exec_dev_office_suite_register_add_letter_endpoint');

function exec_dev_office_suite_add_letter($request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'exec_data';

    $date = current_time('mysql');
    $subject = sanitize_text_field($request->get_param('subject'));
    $content = wp_kses_post($request->get_param('content'));
    $to_field = sanitize_text_field($request->get_param('to_field'));
    $address = sanitize_textarea_field($request->get_param('address'));

    $wpdb->insert($table_name, array(
        'date' => $date,
        'subject' => $subject,
        'content' => $content,
        'to_field' => $to_field,
        'address' => $address,
    ));

    return new WP_REST_Response(array('message' => 'Letter added successfully'), 200);
}
// Register REST API endpoint for updating a letter
function exec_dev_office_suite_register_update_letter_endpoint() {
    register_rest_route('exec-dev-office-suite/v1', '/letters/(?P<id>\d+)', array(
        'methods' => 'POST',
        'callback' => 'exec_dev_office_suite_update_letter',
        'permission_callback' => 'exec_dev_office_suite_admin_permission_callback'
    ));
}

add_action('rest_api_init', 'exec_dev_office_suite_register_update_letter_endpoint');

function exec_dev_office_suite_update_letter($request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'exec_data';
    $id = intval($request['id']);

    $date = current_time('mysql');
    $subject = sanitize_text_field($request->get_param('subject'));
    $content = wp_kses_post($request->get_param('content'));
    $to_field = sanitize_text_field($request->get_param('to_field'));
    $address = sanitize_textarea_field($request->get_param('address'));

    $updated = $wpdb->update($table_name, array(
        'date' => $date,
        'subject' => $subject,
        'content' => $content,
        'to_field' => $to_field,
        'address' => $address,
    ), array('id' => $id));

    if ($updated) {
        return new WP_REST_Response(array('message' => 'Letter updated successfully'), 200);
    } else {
        return new WP_Error('update_failed', 'Failed to update letter', array('status' => 500));
    }
}
