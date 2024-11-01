<?php

/**
 * Register Woo Flow config and setting method
 *
 * @link       http://www.innoria.com
 * @since      1.0.0
 *
 * @package    Woo_Flow
 * @subpackage Woo_Flow/includes
 */

/**
 * Register Woo Flow config and setting method
 *
 * @package    Woo_Flow
 * @subpackage Woo_Flow/includes
 * @author     Innoria Solutions <nhule@innoria.com>
 */
class Woo_Flow_Config extends Woo_Flow_Data_Helper {

    /**
     * The name of setting table to save setting.
     *
     * @var     string $woo_flow_setting The name of setting table to save setting.
     */
    private $woo_flow_track_setting;

    /**
     * The name of setting table to save general config.
     *
     * @var     string $woo_flow_setting The name of setting table to save setting.
     */
    private $woo_flow_setting;

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

        $wf_install = new Woo_Flow_Install();
        $this->woo_flow_track_setting = $wf_install->get_table_woo_flow_track_setting();
        $this->woo_flow_analysis = $wf_install->get_table_woo_flow_analysis();
        $this->woo_flow_setting = $wf_install->get_table_woo_flow_setting();

    }

    public function woo_flow_update_track_setting($track_key, $post) {

        $track_setting = $this->woo_flow_find_setting($track_key);

        if ( sizeof($track_setting) > 0) {

            $update_setting = array(
                "post_id"       => $post->ID,
                "post_type"     => $post->post_type
            );
            $this->update( $this->woo_flow_track_setting, $update_setting, array( "track_key" => $track_key) );

        } else {

            $insert_setting = array(
                "track_key"     => $track_key,
                "post_id"       => $post->ID,
                "post_type"     => $post->post_type
            );
            $this->insert( $this->woo_flow_track_setting, $insert_setting );
        }
    }

    public function woo_flow_delete_track_setting() {

        $this->delete_all( $this->woo_flow_track_setting );

    }

    public function woo_flow_reset_all_track_data( $delete_all, $from = NULL, $to = NULL ) {

        $deleted = false;

        if ($delete_all) {
            $deleted = $this->delete_all( $this->woo_flow_analysis );
        } else {
            if ( $from && $to ) {

                $delete_sql = "DELETE FROM `". $this->woo_flow_analysis ."` 
                    WHERE STR_TO_DATE(`session_time`,'%Y-%m-%d') >= STR_TO_DATE('$from','%Y-%m-%d')
                    AND STR_TO_DATE(`session_time`,'%Y-%m-%d') <= STR_TO_DATE('$to','%Y-%m-%d')";
                $deleted = $this->get_query($delete_sql);

            } else {
                if ( $from ) {

                    $delete_sql = "DELETE FROM `". $this->woo_flow_analysis ."` 
                    WHERE STR_TO_DATE(`session_time`,'%Y-%m-%d') >= STR_TO_DATE('$from','%Y-%m-%d')";
                    $deleted = $this->get_query($delete_sql);

                }
                if ( $to ) {

                    $delete_sql = "DELETE FROM `". $this->woo_flow_analysis ."` 
                    WHERE STR_TO_DATE(`session_time`,'%Y-%m-%d') <= STR_TO_DATE('$to','%Y-%m-%d')";
                    $deleted = $this->get_query($delete_sql);

                }
            }
        }

        return $deleted;

    }

    public function woo_flow_find_setting($track_key) {
        return $this->get_by($this->woo_flow_track_setting, array('track_key' => $track_key), '=');
    }

    public function woo_flow_is_setting_page($track_key) {

        $result = false;

        $track_list = $this->get_all($this->woo_flow_track_setting);
        foreach ( $track_list as $item ) {
            if ( $item->track_key === $track_key ) {
                $result = true;
            }
        }

        return $result;
    }

    //Config functions
    public function woo_flow_update_funnel_config($type, $name, $value) {

        $setting = array(
            'setting_type'      => $type,
            'setting_name'      => $name
        );

        $is_set = $this->is_config_page($type, $name);

        if ( sizeof($is_set) > 0) {

            $update_setting = array(
                "setting_value" => $value
            );
            $this->update( $this->woo_flow_setting, $update_setting, $setting );

        } else {

            $insert_setting = array(
                "setting_type"  => $type,
                "setting_name"  => $name,
                "setting_value" => $value
            );
            $this->insert( $this->woo_flow_setting, $insert_setting );
        }
    }

    public function is_config_page($type, $name, $value = NULL) {
        $setting = array(
            'setting_type'      => $type,
            'setting_name'      => $name
        );

        if ($value) {
            $setting['setting_value'] = $value;
        }

        return $this->get_by($this->woo_flow_setting, $setting, '=');
    }

    public function get_funnel_config() {
        return $this->get_by($this->woo_flow_setting, array( 'setting_type' => 'wf_funnel_config'), '=');
    }

    public function get_funnel_value() {

        $report = new Woo_Flow_Report();

        $funnel_sections = array();
        $funnel_config = $this->get_funnel_config();

        if (sizeof($funnel_config) === 0) {
            return $funnel_config;
        } else {
            foreach ( $funnel_config as $key => $config ) {

                $config_page = (object) $report->get_funnel_data($config, $funnel_config);
                array_push($funnel_sections, $config_page);
            }
        }

        return $funnel_sections;
    }

}