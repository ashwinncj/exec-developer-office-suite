<?php
/*
Template Name: Admin Only Template
*/

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Check if the user is logged in and is an admin
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    // Redirect non-admin users to the login page
    auth_redirect();
    exit;
}

// Set the page to noindex
function add_noindex_meta_tag() {
    echo '<meta name="robots" content="noindex,nofollow">';
}
add_action('wp_head', 'add_noindex_meta_tag');

// Load the header
get_header();
$site_name = get_bloginfo('name');
?>

<div class="wrap exec">
    <h1 id="exec-section-title"><?php echo esc_html($site_name);?> Office Tools</h1>
    <div id="options-container">
        <p id="exec-section-subtext">Please select an option.</p>
        <a id="create-letter-button" href="#">Create New Letter</a><br>
        <a id="view-letters-button" href="#">View Saved Letters</a>
    </div>
    <div id="search-container" style="display:none;">
        <div>
            <a id="back-to-options-letters">< Back to Options</a><br>
        </div>
        <div id="seach-input-container">
            <div id="seach-input-left">
                <input type="text" id="search-input" placeholder="Search letters...">
            </div>
            <div id="search-button">
                <button>Search</button>
            </div>
        </div>
    </div>
    <div id="create-letter-container" style="display:none;">
        <a id="back-to-options">< Back to Options</a>
        <form id="create-letter-form">
            <label for="letter-to">To:</label>
            <input type="text" id="letter-to" name="to_field" required>
            <br>
            <label for="letter-address">Address:</label>
            <textarea rows="4" id="letter-address" cols="50" name="address" required></textarea>
            <br>
            <label for="letter-subject">Subject:</label>
            <input type="text" id="letter-subject" name="subject" required>
            <br>
            <label for="letter-content">Content:</label>
            <textarea id="letter-content" name="content" required></textarea>
            <br>
            <button type="submit">Generate</button>
        </form>
    </div>
    <div id="letters-container" style="display:none;">
        <button id="back-to-options-letters">Back to Options</button>
    </div>
    <div id="pagination-container" style="display:none;"></div>
    <div id="letter-dialog" style="display:none;">
        <div class="dialog-content"></div>
        <button class="close-dialog">Close</button>
    </div>
</div>

<?php
// Load the footer
get_footer();
?>
