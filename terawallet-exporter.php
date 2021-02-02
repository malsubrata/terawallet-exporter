<?php

/**
 * Plugin Name: TeraWallet Exporter
 * Plugin URI: https://wordpress.org/plugins/woo-wallet/
 * Description: Export terawallet transaction as CSV file.
 * Author: WCBeginner
 * Author URI: https://wcbeginner.com/
 * Version: 1.0.0
 * Requires at least: 4.4
 * Tested up to: 5.1
 * WC requires at least: 3.0
 * WC tested up to: 3.6
 * 
 * Text Domain: terawallet-exporter
 * Domain Path: /languages/
 * 
 */
if (!defined('ABSPATH')) {
    exit;
}

final class TeraWallet_Exporter {

    /**
     * The single instance of the class.
     *
     * @var TeraWallet_Exporter
     * @since 1.0.0
     */
    protected static $_instance = null;

    /**
     * Main instance
     * @return class object
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_action('admin_menu', array($this, 'admin_menu'), 50);
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('wp_ajax_terawallet_user_search', array($this, 'terawallet_user_search'));
        add_action('admin_init', array($this, 'download_export_file'));
        add_action('wp_ajax_terawallet_do_ajax_transaction_export', array($this, 'terawallet_do_ajax_transaction_export'));
        
    }

    public function download_export_file() {
        if (isset($_GET['action'], $_GET['nonce']) && wp_verify_nonce(wp_unslash($_GET['nonce']), 'terawallet-transaction-csv') && 'download_export_csv' === wp_unslash($_GET['action'])) { // WPCS: input var ok, sanitization ok.
            include_once dirname(__FILE__) . '/' . 'includes/class-terawallet-csv-exporter.php';
            $exporter = new TeraWallet_CSV_Exporter();
            if (!empty($_GET['filename'])) { // WPCS: input var ok.
                $exporter->set_filename(wp_unslash($_GET['filename'])); // WPCS: input var ok, sanitization ok.
            }
            $exporter->export();
        }
    }

    public function terawallet_do_ajax_transaction_export() {
        check_ajax_referer('terawallet-exporter-script', 'security');
        include_once dirname(__FILE__) . '/' . 'includes/class-terawallet-csv-exporter.php';
        $step = isset($_POST['step']) ? absint($_POST['step']) : 1; // WPCS: input var ok, sanitization ok.

        $exporter = new TeraWallet_CSV_Exporter();

        $exporter->set_step($step);

        if (!empty($_POST['selected_columns'])) { // WPCS: input var ok.
            $exporter->set_columns_to_export(wp_unslash($_POST['selected_columns'])); // WPCS: input var ok, sanitization ok.
        }

        if (!empty($_POST['selected_users'])) { // WPCS: input var ok.
            $exporter->set_users_to_export(wp_unslash($_POST['selected_users'])); // WPCS: input var ok, sanitization ok.
        }

        if (!empty($_POST['start_date'])) {
            $exporter->set_start_date(wp_unslash($_POST['start_date']));
        }

        if (!empty($_POST['end_date'])) {
            $exporter->set_end_date(wp_unslash($_POST['end_date']));
        }

        if (!empty($_POST['filename'])) { // WPCS: input var ok.
            $exporter->set_filename(wp_unslash($_POST['filename'])); // WPCS: input var ok, sanitization ok.
        }
        $exporter->write_to_csv();
        $query_args = array(
            'nonce' => wp_create_nonce('terawallet-transaction-csv'),
            'action' => 'download_export_csv',
            'filename' => $exporter->get_filename(),
        );
        if ($exporter->get_percent_complete() >= 100) {
            wp_send_json_success(
                    array(
                        'step' => 'done',
                        'percentage' => 100,
                        'url' => add_query_arg($query_args, admin_url('admin.php?page=terawallet-exporter')),
                    )
            );
        } else {
            wp_send_json_success(
                    array(
                        'step' => ++$step,
                        'percentage' => $exporter->get_percent_complete(),
                        'columns' => '',
                    )
            );
        }
    }

    public function terawallet_user_search() {
        $return = array();
        if (isset($_REQUEST['site_id'])) {
            $id = absint($_REQUEST['site_id']);
        } else {
            $id = get_current_blog_id();
        }

        $users = get_users(array(
            'blog_id' => $id,
            'search' => '*' . $_REQUEST['term'] . '*',
            'search_columns' => array('user_login', 'user_nicename', 'user_email'),
        ));

        foreach ($users as $user) {
            $return[] = array(
                /* translators: 1: user_login, 2: user_email */
                'label' => sprintf(_x('%1$s (%2$s)', 'user autocomplete result', 'woo-wallet'), $user->user_login, $user->user_email),
                'value' => $user->ID,
            );
        }
        wp_send_json($return);
    }

    public function admin_scripts() {
        $screen = get_current_screen();
        $screen_id = $screen ? $screen->id : '';
        // register styles
        wp_register_style('exporter-style', self::plugin_url() . '/assets/css/exporter-style.css', array(), '1.0.0');

        // register scripts
        wp_register_script('terawallet-exporter-script', self::plugin_url() . '/assets/js/exporter-js.js', array('jquery'), '1.0.0');
        wp_localize_script(
                'terawallet-exporter-script',
                'terawallet_export_params',
                array(
                    'i18n' => array(
                        'inputTooShort' => __('Please enter 3 or more characters', 'woo-wallet'),
                        'no_resualt' => __('No results found', 'woo-wallet'),
                        'searching' => __('Searching…', 'woo-wallet'),
                    ),
                    'export_nonce' => wp_create_nonce('terawallet-exporter-script'),
                    'export_url' => '',
                    'export_button_title' => __('Export', 'terawallet-exporter')
                )
        );
        
        wp_register_script('terawallet_export_transaction_admin', untrailingslashit(plugins_url('/', __FILE__)) . '/assets/js/admin.js', array('jquery'), '1.0.0', true);
        wp_localize_script('terawallet_export_transaction_admin', 'terawallet_export_transaction_admin', array('url' => add_query_arg(array('page' => 'terawallet-exporter'), admin_url('admin.php')), 'title' => __('Export', 'terawallet-exporter')));

        if (in_array($screen_id, array('admin_page_terawallet-exporter'))) {
            wp_enqueue_style('select2');
            wp_enqueue_style('exporter-style');
        }
        
        if (in_array($screen_id, array('toplevel_page_woo-wallet'))) {
            wp_enqueue_script('terawallet_export_transaction_admin');
        }
    }

    public static function plugin_url() {
        return untrailingslashit(plugins_url('/', __FILE__));
    }

    public function admin_menu() {
        add_submenu_page(null, null, null, get_wallet_user_capability(), 'terawallet-exporter', array($this, 'terawallet_exporter_page'));
    }

    public function terawallet_exporter_page() {
        include_once dirname(__FILE__) . '/' . 'includes/class-terawallet-csv-exporter.php';
        include_once dirname(__FILE__) . '/views/html-exporter.php';
    }

}

TeraWallet_Exporter::instance();
