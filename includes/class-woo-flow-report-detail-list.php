<?php

/**
 * Create Woo Flow table class that will extend the WP_List_Table
 *
 * @link       http://www.innoria.com
 * @since      1.0.0
 *
 * @package    Woo_Flow
 * @subpackage Woo_Flow/includes
 */

/**
 * Create Woo Flow table class that will extend the WP_List_Table
 *
 * @package    Woo_Flow
 * @subpackage Woo_Flow/includes
 * @author     Innoria Solutions <nhule@innoria.com>
 */
class Woo_Flow_Report_Detail_List extends WP_List_Table {

    protected $post;
    protected $event;
    protected $from;
    protected $to;

    public function __construct( $post, $event, $from = NULL, $to = NULL ) {
        parent::__construct();

        $this->post = $post;
        $this->event = $event;
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items() {

        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $data = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );
        $per_page = 20;
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page
        ) );
        $data = array_slice($data, (($current_page-1) * $current_page), $per_page);
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return array Array
     */
    public function get_columns() {
        $columns = array(
            'page_title'     => 'Page Title',
            'page_slug'      => 'Page Slug',
            'session_num'     => 'No of Sessions',
            'percent'        => 'Percent'
        );
        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return array Array
     */
    public function get_hidden_columns() {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return array Array
     */
    public function get_sortable_columns() {

        $sort_columns = array (
            'page_title'     => array('page_title', false),
            'page_slug'      => array('page_slug', false),
            'session_num'    => array('session_num', false),
            'percent'        => array('percent', false)
        );
        return $sort_columns;
    }

    /**
     * Get the table data
     *
     * @return array Array
     */
    private function table_data() {

        $data = array();
        $custom_tb = new Woo_Flow_Report();

        $items = $custom_tb->get_event_report_detail($this->post->ID, $this->event, $this->from, $this->to);

        foreach ($items as $item) {
            $page_reference = get_post($item->post_references);
            $session_num = $custom_tb->count_session($this->post->ID, $this->event, $this->from, $this->to, $item->post_references)[0];
            $total_session = $custom_tb->count_session($this->post->ID, $this->event, $this->from, $this->to)[0];
            $percent = ($session_num->session_num / $total_session->session_num)*100;


            switch ($this->event) {
                case 'page_view':
                    $external_text = 'External pages (enter site)';
                    break;
                case 'leave_page':
                    $external_text = 'External pages (leave site)';
                    break;
                default:
                    $external_text = 'External pages';
                    break;
            }

            $data[] = array(
                'id'             => $item->post_id,
                'page_title'     => $page_reference ? $page_reference->post_title : $external_text,
                'page_id'        => $page_reference ? $page_reference->ID : '-',
                'page_slug'      => $page_reference ? $page_reference->post_name : "-",
                'session_num'    => $session_num->session_num,
                'percent'        => number_format($percent, 2) .'%'
            );
        }

        return $data;
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name ) {
        switch( $column_name ) {
            case 'id':
            case 'page_title':
            case 'page_slug':
            case 'session_num':
            case 'percent':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ;
        }
    }

//    public function column_page_title($item) {
//        $actions = array(
//            'edit'      => sprintf('<a href="%s">Edit</a>', get_edit_post_link($item['page_id'])),
//            'view'    => sprintf('<a href="%s">View</a>', get_permalink($item['page_id'])),
//        );
//
//        return sprintf('%1$s %2$s', $item['page_title'], $this->row_actions($actions) );
//    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b ) {
        // Set defaults
        $orderby = 'page_title';
        $order = 'asc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))  {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order'])) {
            $order = $_GET['order'];
        }
        $result = strcmp( $a[$orderby], $b[$orderby] );
        if($order === 'asc')  {
            return $result;
        }
        return -$result;
    }

}