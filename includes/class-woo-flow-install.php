<?php
/**
 * The installation functions when plugin was activated.
 *
 * @link       http://www.innoria.com
 * @since      1.0.0
 *
 * @package    Woo_Flow
 * @subpackage Woo_Flow/admin
 */

/**
 * The installation functions when plugin was activated.
 *
 * @package    Woo_Flow
 * @subpackage Woo_Flow/includes
 * @author     Innoria Solutions <nhule@innoria.com>
 */
class Woo_Flow_Install extends Woo_Flow_Data_Helper {

    /**
     * The name of setting table to save setting.
     *
     * @var     string $woo_flow_setting The name of setting table to save setting.
     */
    private $woo_flow_setting;

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

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {

        parent::__construct();

        $this->get_table_name();

    }

    /**
     * Function get all table name.
     *
     */
    public function get_table_name() {
        global $wpdb;

        $this->woo_flow_setting       = $wpdb->prefix . 'woo_flow_setting';
        $this->woo_flow_analysis      = $wpdb->prefix . 'woo_flow_analysis';
        $this->woo_flow_track_setting = $wpdb->prefix . 'woo_flow_track_setting';
    }

    public function get_table_woo_flow_setting() {
        return $this->woo_flow_setting;
    }

    public function get_table_woo_flow_analysis() {
        return $this->woo_flow_analysis;
    }

    public function get_table_woo_flow_track_setting() {
        return $this->woo_flow_track_setting;
    }

    /**
     * Renders a simple page for Woo Flow Setting Page
     */
    public function woo_flow_create_tables() {

        global $wpdb;
        global $your_db_name;
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $charset_collate = $wpdb->get_charset_collate();

        // create the ECPT metabox database table
        if($wpdb->get_var("show tables like '$your_db_name'") != $your_db_name)
        {
            $sql = "";
            if ( !$this->is_table_exists($this->woo_flow_analysis)) {
                $sql = "CREATE TABLE `$this->woo_flow_analysis` (
                    id bigint(20) NOT NULL AUTO_INCREMENT,
                    post_id bigint(20) NOT NULL,
                    user_id bigint(20) DEFAULT 0 NOT NULL,
                    event_name varchar(20) NOT NULL,
                    post_references bigint(20) NOT NULL,
                    page_value DECIMAL(10, 2) DEFAULT 0 NOT NULL,
                    post_type varchar(20) NOT NULL,
                    tracking_key varchar(100) NOT NULL,
                    references_key varchar(100) NOT NULL,
                    session_time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    session_id bigint(20) NOT NULL,
                    UNIQUE KEY id (id), PRIMARY KEY (id)
                ) $charset_collate;";
            }
            if ( !$this->is_table_exists($this->woo_flow_track_setting)) {
                $sql .= "CREATE TABLE `$this->woo_flow_track_setting` (
                    track_id bigint(20) NOT NULL AUTO_INCREMENT,
                    track_key varchar(50) NOT NULL,
                    post_id bigint(20),
                    post_type varchar(20) NOT NULL,
                    UNIQUE KEY track_id (track_id, track_key), PRIMARY KEY (track_id)
                ) $charset_collate;";
            }
            if ( !$this->is_table_exists($this->woo_flow_setting)) {
                $sql .= "CREATE TABLE `$this->woo_flow_setting` (
                    setting_id bigint(20) NOT NULL AUTO_INCREMENT,
                    setting_type varchar(50) NOT NULL,
                    setting_name varchar(50) NOT NULL,
                    setting_value varchar(200) NOT NULL,
                    UNIQUE KEY setting_id (setting_id), PRIMARY KEY (setting_id)
                ) $charset_collate;";
            }
            dbDelta( $sql );

            $this->set_default_config();
        }

    }

    public function is_table_exists($table_name) {
        global $wpdb;
        $result = $wpdb->query("SHOW TABLES LIKE '$table_name'");
        return $result;
    }

    public function set_default_config() {

        $type = 'wf_funnel_config';
        $default_pages = array();

        $product_page = array(
            'ID'            =>  -1,
            'post_name'     =>  'wc_products',
            'post_title'    =>  'WC Products Pages',
            'post_type'     =>  'product'
        );
        $cart = get_post(get_option( 'woocommerce_cart_page_id' ));
        $checkout = get_post(get_option( 'woocommerce_checkout_page_id' ));

        array_push($default_pages, (object) $product_page);
        array_push($default_pages, $cart);
        array_push($default_pages, $checkout);

        foreach ($default_pages as $pos => $default_page) {
            $insert_setting = array(
                "setting_type"  => $type,
                "setting_name"  => 'section'. ($pos + 1),
                "setting_value" => $default_page->post_name
            );

            $setting = array(
                'setting_type'      => $type,
                'setting_name'      => 'section'. ($pos + 1)
            );

            $is_set =  $this->get_by($this->woo_flow_setting, $setting, '=');
            if ( sizeof($is_set) > 0) {
                //Have old setting...
            } else {
                $this->insert( $this->woo_flow_setting, $insert_setting );
            }
        }
    }

}