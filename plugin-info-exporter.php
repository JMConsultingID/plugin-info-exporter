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
    echo '<p>Click the button below to export plugin information to JSON:</p>';
    echo '<button id="exportToJson" class="button button-primary">Export to JSON</button>';
    echo '</div>';
}


add_action('wp_ajax_export_plugins_to_json', 'pie_export_to_json_ajax_handler');
add_action('wp_ajax_nopriv_export_plugins_to_json', 'pie_export_to_json_ajax_handler');

function pie_export_to_json_ajax_handler() {
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

    echo json_encode($plugins_data);
    wp_die(); // This is important to terminate immediately and return a proper response
}


function pie_enqueue_admin_scripts() {
    echo "
    <script type='text/javascript'>
    jQuery(document).ready(function($) {
        $('#exportToJson').click(function() {
            $.ajax({
                url: ajaxurl,
                data: {
                    'action': 'export_plugins_to_json'
                },
                success: function(data) {
                    // Create a blob and trigger a download
                    var blob = new Blob([data], {type: 'application/json'}),
                        a = document.createElement('a');
                    a.href = URL.createObjectURL(blob);
                    a.download = 'plugin-info-' + new Date().getTime() + '.json';
                    a.click();
                },
                error: function(errorThrown){
                    console.log(errorThrown);
                }
            });
        });
    });
    </script>
    ";
}
add_action('admin_footer', 'pie_enqueue_admin_scripts');
