<?php

/**
 * Register Woo Flow report method
 *
 * @link       http://www.innoria.com
 * @since      1.0.0
 *
 * @package    Woo_Flow
 * @subpackage Woo_Flow/includes
 */

/**
 * Register Woo Flow report method
 *
 * @package    Woo_Flow
 * @subpackage Woo_Flow/includes
 * @author     Innoria Solutions <nhule@innoria.com>
 */
class Woo_Flow_Report extends Woo_Flow_Data_Helper {

    protected $table_name;
    protected $woo_flow_setting;

    public $post;

    /**
     * Initialize the collections used to maintain the actions and filters.
     *
     * @param    $post - Input post object for report detail
     * @since    1.0.0
     */
    public function __construct($post = NULL) {

        if ($post) {
            $this->post = $post;
        }

        $wf_install = new Woo_Flow_Install();
        $this->table_name = $wf_install->get_table_woo_flow_analysis();
        $this->woo_flow_setting = $wf_install->get_table_woo_flow_setting();

        parent::__construct();

    }

    public function all_event() {
        $result = $this->get_all( $this->table_name, NULL);
        return $result;
    }

    public function get_distinct_items() {
        $select = array(
            '*'         => ''
        );
        $conditions = array();
        $order_by = NULL;
        $group_by = array('post_id');
        $result = $this->get_records($this->table_name, $select, $conditions, $order_by, $group_by);
        return $result;
    }

    public function get_event_report( $page_id, $event_name = NULL, $from = NULL, $to = NULL ) {
        $select = array(
                '*'         => '',
                'COUNT(*)'  => 'total_session',
                'SUM(page_value)'   => 'lost_value'
            );
        if ($event_name === 'leave_page') {
            $conditions = array(
                '='         => array(
                        'post_id'           => $page_id,
                        'event_name'        => $event_name,
                        'post_references'   => 0
                )
            );
        } else {
            $conditions = array(
                '='         => array(
                    'post_id'           => $page_id,
                    'event_name'        => $event_name
                )
            );
        }
        if ($from) {
            $conditions['datetime'][">="] = array('session_time', $from);
        }
        if ($to) {
            $conditions['datetime']["<="] = array('session_time', $to);
        }
        $order_by = NULL;
        $group_by = array();
        $result = $this->get_records($this->table_name, $select, $conditions, $order_by, $group_by);
        return $result;
    }

    public function get_event_report_detail($page_id, $event_name, $from, $to) {
        $select = array(
            '*'         => ''
        );
        $conditions = array(
            '='         => array(
                'post_id'           => $page_id,
                'event_name'        => $event_name
            )
        );
        if ($from) {
            $conditions['datetime'][">="] = array('session_time', $from);
        }
        if ($to) {
            $conditions['datetime']["<="] = array('session_time', $to);
        }
        $order_by = NULL;
        $group_by = array('post_references');
        $result = $this->get_records($this->table_name, $select, $conditions, $order_by, $group_by);
        return $result;
    }

    public function count_session($page_id, $event_name, $from, $to, $reference_id = NULL) {
        $select = array(
            'COUNT(*)'         => 'session_num'
        );

        $conditions = array(
            '='         => array(
                'post_id'           => $page_id,
                'event_name'        => $event_name,
            )
        );

        if ($reference_id !== NULL) {
            $conditions['=']['post_references'] = $reference_id;
        }
        if ($from) {
            $conditions['datetime'][">="] = array('session_time', $from);
        }
        if ($to) {
            $conditions['datetime']["<="] = array('session_time', $to);
        }

        $order_by = NULL;
        $group_by = array();
        $result = $this->get_records($this->table_name, $select, $conditions, $order_by, $group_by);
        return $result;
    }

    /**
     * Display the summary report list table page
     *
     * @return Void
     */
    public function report_list_page() {
        $example_list = new Woo_Flow_Report_List();
    ?>

        <form method="post">
            <input type="hidden" name="page" value="wf_report" />
            <?php
            $example_list->prepare_items();
            $example_list->search_box('Search', 'search_id');
            $example_list->display();
            ?>
        </form>

    <?php
    }

    /**
     * Display the detail event list table by page
     *
     * @param $event_name - Get report detail by event name
     * @param $from - Filter date range from
     * @param $to - Filter date range to
     * @return Void
     */
    public function view_page_report_detail($event_name, $from = NULL, $to = NULL) {
        $example_list = new Woo_Flow_Report_Detail_List($this->post, $event_name, $from, $to);

        if ($event_name === 'page_view') {
            ?>
            <h3><?php _e('Going from page list', 'woo-flow'); ?></h3>
            <?php
        } else {
            ?>
            <h3><?php _e('Go to page list', 'woo-flow'); ?></h3>
            <?php
        }
        ?>

        <form method="post">
            <input type="hidden" name="page" value="wf_report_detail" />
            <?php
            $example_list->prepare_items();
            //$example_list->search_box('search', 'search_id');
            $example_list->display();
            ?>
        </form>

        <?php
    }

    /**
     * Display date range filter
     *
     * @return Void
     */
    public function report_date_filter() {
        ?>

        <form method="post">
            <input type="hidden" name="wf_date_filter" value="woo_flow_date_filter" />

            <label for="start_date"><?php _e('From:', 'woo_flow'); ?></label>
            <input type="search" class="custom_date" name="start_date" value="<?php //echo $start_date; ?>"/>

            <label for="end_date"><?php _e('To:', 'woo_flow'); ?></label>
            <input type="search" class="custom_date" name="end_date" value="<?php //echo $end_date; ?>"/>

            <input type="submit" name="submit" class="button button-secondary"
                   value="<?php esc_attr_e('Apply') ?>"/>
        </form>

        <?php
    }

    function get_funnel_data($from = NULL, $to = NULL) {

        $wf_conf = new Woo_Flow_Config();

        $funnel_sections = array();
        $funnel_config = $wf_conf->get_funnel_config();
        $all_track = $this->all_event();
        $config_name = '';
        $sessions = 0;
        $conversion = 0;
        $pre_config = NULL;
        $reference = array();
        $sessions_id = array();

        if (sizeof($funnel_config) === 0) {

            return $funnel_config;

        } else {

            foreach ( $funnel_config as $key => $config ) {

                $reference_key = NULL;
                if ($key > 0) {
                    $pre_config = $funnel_config[$key - 1];
                    $reference_key = $pre_config->setting_value;
                }

                switch ($config->setting_value) {
                    case 'wc_products':
                        $config_name = 'WC Products Pages';
                        break;
                    default:
                        $post = get_page_by_path($config->setting_value);
                        $config_name = $post ? $post->post_title : '';
                        break;
                }

                $sessions = $this->count_funnel_session($config->setting_value, 'page_view', ($key > 0) ? $sessions_id : NULL, $reference_key, $from, $to);
                $conversion = $this->count_funnel_session($config->setting_value, 'leave_page', ($key > 0) ? $sessions_id : NULL, 'external_site', $from, $to);

                $sessions_id = array();
                if ($sessions) {
                    foreach ($sessions as $value) {
                        array_push($sessions_id, $value->session_id);
                    }
                }
                $sessions = sizeof($sessions);
                $lost_value = 0;
                if ($conversion) {
                    foreach ($conversion as $leave_item) {
                        $lost_value += (float)$leave_item->page_value;
                    }
                }
                $conversion = sizeof($conversion);

                $config_page = array(
                    'setting_name'      =>  $config->setting_name,
                    'setting_value'     =>  $config->setting_value,
                    'post_name'         =>  $config->setting_value,
                    'post_title'        =>  $config_name,
                    'total_view'        =>  $sessions,
                    'total_leave'       =>  $conversion,
                    'lost_value'        =>  $lost_value,
                    'dropoff'           =>  $sessions ? ($conversion / $sessions) * 100 : 0,
                    'sessions'          =>  0,
                    'conversion_rate'   =>  0
                );

                array_push($funnel_sections, (object)$config_page);
            }

        }

        return $funnel_sections;
    }

    public function count_funnel_session($tracking_key, $event_name, $session_id = NULL, $reference = NULL, $from = NULL, $to = NULL) {
        $select = array(
            '*'         => ''
        );

        $conditions = array(
            '='         => array(
                'tracking_key'      => $tracking_key,
                'event_name'        => $event_name
            )
        );
        if ($session_id !== NULL) {
            $conditions['in']['session_id'] = (sizeof($session_id) > 0) ? $session_id : array('-1');
        }

        if ($reference !== NULL) {
            $conditions['=']['references_key'] = $reference;
        }
        if ($from) {
            $conditions['datetime'][">="] = array('session_time', $from);
        }
        if ($to) {
            $conditions['datetime']["<="] = array('session_time', $to);
        }

        $order_by = NULL;
        $group_by = array();
        $result = $this->get_records($this->table_name, $select, $conditions, $order_by, $group_by);

        return $result;
    }

    public function count_last_month_lost() {
        $result = false;
        $today = new DateTime();
        $last_month = new DateTime();
        $last_month->modify('-1 month');
        $select = array(
                'SUM(page_value)' =>  'total_lost'
        );

        $conditions = array(
            'datetime'    => array(
                '>='        => array('session_time', $last_month->format('Y-m-d')),
                '<='        => array('session_time', $today->format('Y-m-d'))
            ),
            '='           =>    array(
                'event_name'        => 'leave_page',
                'references_key'    => 'external_site',
                'tracking_key'      => 'checkout'
            )
        );
        $order_by = NULL;
        $group_by = array();

        $last_message = $this->get_by($this->woo_flow_setting, array( 'setting_name'  => 'dissmiss_lost_message' ), '=');
        $lost_last_month = $this->get_records($this->table_name, $select, $conditions, $order_by, $group_by);
        $lost_last_month = (float)$lost_last_month[0]->total_lost;

        $expired_dissmiss = true;
        if (sizeof($last_message) > 0) {
            $last_dissmiss = strtotime($last_message[0]->setting_value);
            if ($last_dissmiss > strtotime($last_month->format('Y-m-d'))) {
                $expired_dissmiss = false;
            }
        }

        if ( $lost_last_month >= 2000 && $expired_dissmiss ) {
            $result = true;
        }

        return $result;
    }

}