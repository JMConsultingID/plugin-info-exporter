<?php
/*
Plugin Name: Plugin Info Exporter
Description: Gathers information about installed plugins and exports them to Excel.
Version: 1.0
Author: Ardi JM-Consulting
*/

// Ensure direct access to this file is blocked
defined('ABSPATH') or die('No script kiddies please!');

// Add admin page
function pie_add_admin_page() {
    add_menu_page('Plugin Info Exporter', 'Plugin Exporter', 'manage_options', 'plugin-info-exporter', 'pie_admin_page_callback');
}
add_action('admin_menu', 'pie_add_admin_page');

function pie_admin_page_callback() {
    echo '<div class="wrap">';
    echo '<h1>Plugin Info Exporter</h1>';
    echo '<p>Click the button below to export plugin information to Excel:</p>';
    echo '<form method="post">';
    echo '<input type="submit" name="export_to_json" class="button button-primary" value="Export to JSON" />';
    echo '</form>';
    echo '</div>';

    if(isset($_POST['export_to_json'])) {
        pie_export_to_json();
    }
}

function pie_export_to_json() {
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    $all_plugins = get_plugins();
    $active_plugins = get_option('active_plugins');
    $update_plugins = get_site_transient('update_plugins');

    $plugins_data = [];

    foreach($all_plugins as $plugin_path => $plugin_data) {
        $plugin_info = [
            'Name' => $plugin_data['Name'],
            'Description' => strip_tags($plugin_data['Description']),
            'Tag' => '', // You'll need to determine how to get "Plugin Tag"
            'Author' => $plugin_data['Author'],
            'Plugin Link' => $plugin_data['PluginURI'],
            'Active Status' => in_array($plugin_path, $active_plugins) ? 'Active' : 'Inactive',
            'Update Available' => (isset($update_plugins->response[$plugin_path])) ? 'Yes' : 'No',
            'Version' => $plugin_data['Version']
        ];

        $plugins_data[] = $plugin_info;
    }

    $json_data = json_encode($plugins_data, JSON_PRETTY_PRINT);

    header('Content-Type: application/json');
    header('Content-Disposition: attachment;filename="plugin-info-' . time() . '.json"');
    header('Cache-Control: max-age=0');

    echo $json_data;
    exit;
}
