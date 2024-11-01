<?php
/**
 * The admin setting functionality of the plugin.
 *
 * @link       http://www.innoria.com
 * @since      1.0.0
 *
 * @package    Woo_Flow
 * @subpackage Woo_Flow/admin
 */

/**
 * The admin setting functionality of the plugin.
 *
 * @package    Woo_Flow
 * @subpackage Woo_Flow/admin
 * @author     Innoria Solutions <nhule@innoria.com>
 */
class Woo_Flow_Admin_Settings extends Woo_Flow_Data_Helper {

    /**
     * The name of setting table to save setting.
     *
     * @var     string $woo_flow_setting The name of setting table to save setting.
     */
    private $woo_flow_track_setting;

    /**
     * @var     string $woo_flow_analysis The name of analytic table to save tracking data.
     */
    private $woo_flow_analysis;

    private $woo_flow_setting;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {

        parent::__construct();

        $wf_install = new Woo_Flow_Install();
        $this->woo_flow_track_setting = $wf_install->get_table_woo_flow_track_setting();
        $this->woo_flow_analysis = $wf_install->get_table_woo_flow_analysis();
        $this->woo_flow_setting = $wf_install->get_table_woo_flow_setting();

    }

    /**
     * This function introduces the theme options into the 'Appearance' menu and into a top-level
     * 'WPPB Demo' menu.
     */
    public function setup_plugin_options_menu() {

        add_menu_page(
            __('Woo Flow', 'woo_flow'),
            'WooFlow',
            'manage_options',
            'wf_funnel',
            array($this, 'woo_flow_admin_page_content'),
            'dashicons-chart-pie',
            56
        );

        add_submenu_page(
            'wf_funnel',
            __('Woo Flow Funnel', 'woo_flow'),
            'Funnel',
            'manage_options',
            'wf_funnel',
            array($this, 'woo_flow_admin_page_content')
        );

        add_submenu_page(
            'wf_funnel',
            __('Woo Flow Reports', 'woo_flow'),
            'Reports',
            'manage_options',
            'wf_report',
            array($this, 'woo_flow_admin_page_content')
        );

        add_submenu_page(
            'wf_funnel',
            __('Woo Flow Settings', 'woo_flow'),
            'Settings',
            'manage_options',
            'wf_setting',
            array($this, 'woo_flow_admin_page_content')
        );

    }

    /**
     * Renders a simple page for Woo Flow Admin page
     */
    public function woo_flow_admin_page_content() {

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        include_once 'partials/woo-flow-admin-display.php';
    }

    /**
     * Renders a simple page for Woo Flow Funnel Page
     */
    public function woo_flow_funnel_page_content() {

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        include_once 'partials/woo-flow-funnel-display.php';
    }

    /**
     * Renders a simple page for Woo Flow Funnel Page
     */
    public function woo_flow_funnel_config_page_content() {

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        include_once 'partials/woo-flow-funnel-config-display.php';
    }

    /**
     * Renders a simple page for Woo Flow Report Page
     */
    public function woo_flow_report_page_content() {

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        include_once 'partials/woo-flow-report-display.php';
    }

    /**
     * Renders a simple page for Woo Flow Setting Page
     */
    public function woo_flow_setting_page_content() {

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        include_once 'partials/woo-flow-setting-display.php';

    }

    public function wf_dissmiss_onemonth_message() {
        $today = new DateTime();
        $have_dissmiss = $this->get_by($this->woo_flow_setting, array( 'setting_name'  => 'dissmiss_lost_message' ), '=');

        if (sizeof($have_dissmiss) > 0) {
            $update_setting = array(
                "setting_value" => $today->format('Y-m-d')
            );
            echo $this->update( $this->woo_flow_setting, $update_setting, array( 'setting_name' => 'dissmiss_lost_message') );
        } else {
            $insert_data = $insert_setting = array(
                "setting_type"  => 'wf_global_setting',
                "setting_name"  => 'dissmiss_lost_message',
                "setting_value" => $today->format('Y-m-d')
            );
            echo $this->insert($this->woo_flow_setting, $insert_data);
        }

        die();
    }

}