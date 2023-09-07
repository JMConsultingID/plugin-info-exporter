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
    echo '<input type="submit" name="export_to_excel" class="button button-primary" value="Export to Excel" />';
    echo '</form>';
    echo '</div>';

    if(isset($_POST['export_to_excel'])) {
        pie_export_to_excel();
    }
}

function pie_export_to_excel() {
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    $all_plugins = get_plugins();
    $active_plugins = get_option('active_plugins');

    // Initialize PhpSpreadsheet
    require 'vendor/autoload.php';
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Headers
    $sheet->setCellValue('A1', 'Plugin Name');
    $sheet->setCellValue('B1', 'Description');
    $sheet->setCellValue('C1', 'Plugin Tag'); // Changed from "Plugin Type" to "Plugin Tag"
    $sheet->setCellValue('D1', 'Author');
    $sheet->setCellValue('E1', 'Plugin Link');
    $sheet->setCellValue('F1', 'Active Status');
    $sheet->setCellValue('G1', 'Update Available');
    $sheet->setCellValue('H1', 'Plugin Version'); // Added column for plugin version


    $row = 2;
    $update_plugins = get_site_transient('update_plugins');

    foreach($all_plugins as $plugin_path => $plugin_data) {
        $sheet->setCellValue('A' . $row, $plugin_data['Name']);
        $sheet->setCellValue('B' . $row, strip_tags($plugin_data['Description']));
        $sheet->setCellValue('C' . $row, ''); // You'll need to determine how to get "Plugin Tag"
        $sheet->setCellValue('D' . $row, $plugin_data['Author']);
        $sheet->setCellValue('E' . $row, $plugin_data['PluginURI']);
        $sheet->setCellValue('F' . $row, in_array($plugin_path, $active_plugins) ? 'Active' : 'Inactive');

        $needs_update = (isset($update_plugins->response[$plugin_path])) ? 'Yes' : 'No';
        $sheet->setCellValue('G' . $row, $needs_update);
        
        $sheet->setCellValue('H' . $row, $plugin_data['Version']); // Added plugin version

        $row++;
    }


    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $filename = 'plugin-info-' . time() . '.xlsx';

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="'. $filename .'"');
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    exit;
}
