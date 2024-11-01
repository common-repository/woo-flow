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
class Woo_Flow_Report_List extends WP_List_Table {

    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items() {

        $search = ( isset( $_REQUEST['s'] ) ) ? sanitize_text_field($_REQUEST['s']) : false;
        $from = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
        $to = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';

        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $data = $this->table_data($search, $from, $to);
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
//            'id'            => 'ID',
            'page_name'     => 'Page name',
//            'event_name'    => 'Event name',
//            'reference'     => 'Page References',
//            'session_time'  => 'Session time',
            'total_view'    => 'No of Visitors',
            'total_leave'   => 'Leaving Visitors',
            'lost_value'    => 'Lost value'
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
            'page_name'     => array('page_name', false),
            'event_name'    => array('event_name', false),
            'reference'     => array('reference', false),
            'session_time'  => array('session_time', false),
            'total_view'    => array('total_view', false),
            'total_leave'   => array('total_leave', false),
            'lost_value'    => array('lost_value', false)
        );
        return $sort_columns;
    }

    /**
     * Get the table data
     *
     * @param $search - Get data by search key
     * @param $from - Filter data in date range from
     * @param $to - Filter data in date range to
     * @return array Array
     */
    private function table_data( $search = NULL, $from = NULL, $to = NULL ) {

        $data = array();
        $custom_tb = new Woo_Flow_Report();
        $items = $custom_tb->get_distinct_items();

        foreach ($items as $item) {
            $view_items = $custom_tb->get_event_report($item->post_id, 'page_view', $from, $to);
            $view_items = (sizeof($view_items) > 0) ? $view_items[0] : NULL;
            $leave_items = $custom_tb->get_event_report($item->post_id, 'leave_page', $from, $to);
            $leave_items = (sizeof($leave_items) > 0) ? $leave_items[0] : NULL;

            $page = get_post($item->post_id);
            $page_reference = get_post($item->post_references);

            $date = date_create($item->session_time);

            if ( $view_items->total_session == 0 && $leave_items->total_session == 0) {
                //Don't add to list
            } else {
                if ($search) {

                    $found = stripos($page->post_title, $search);
                    $matched = false;

                    if ($found !== false && $found >= 0) {
                        $matched = true;
                    }
                    if ($view_items && $view_items->total_session === $search) {
                        $matched = true;
                    }
                    if ($leave_items && $leave_items->total_session === $search) {
                        $matched = true;
                    }

                    if ($matched) {
                        $data[] = array(
                            'id'             => $item->post_id,
                            'page_name'      => $page ? $page->post_title : "External pages",
                            'page_id'        => $page->ID,
                            'event_name'     => $item->event_name,
                            'reference'      => $page_reference ? $page_reference->post_title : '-',
                            'reference_id'   => $page_reference ? $page_reference->ID : 0,
                            'session_time'   => date_format($date,"Y/m/d H:i:s"),
                            'total_view'     => $view_items ? $view_items->total_session : 0,
                            'total_leave'    => $leave_items ? $leave_items->total_session : 0,
                            'lost_value'     => '$'. number_format(($leave_items && $leave_items->lost_value) ? $leave_items->lost_value : 0, 2)
                        );
                    }

                } else {
                    $data[] = array(
                        'id'             => $item->post_id,
                        'page_name'      => $page ? $page->post_title : "External pages",
                        'page_id'        => $page->ID,
                        'event_name'     => $item->event_name,
                        'reference'      => $page_reference ? $page_reference->post_title : '-',
                        'reference_id'   => $page_reference ? $page_reference->ID : 0,
                        'session_time'   => date_format($date,"Y/m/d H:i:s"),
                        'total_view'     => $view_items ? $view_items->total_session : 0,
                        'total_leave'    => $leave_items ? $leave_items->total_session : 0,
                        'lost_value'     => '$'. number_format(($leave_items && $leave_items->lost_value) ? $leave_items->lost_value : 0, 1)
                    );
                }
            }
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
            case 'page_name':
            case 'event_name':
            case 'reference':
            case 'session_time':
            case 'total_view':
            case 'total_leave':
            case 'lost_value':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ;
        }
    }

    function single_row_columns($item) {
        list($columns, $hidden) = $this->get_column_info();
        foreach ($columns as $column_name => $column_display_name) {
            $class = "class='$column_name column-$column_name'";

            $style = '';
            if (in_array($column_name, $hidden))
                $style = ' style="display:none;"';

            $attributes = "$class$style";

            if ('cb' == $column_name) {
                echo  "<td $attributes>";
                echo '<input type="checkbox" name="id[]" value="%s" />', $item['ID'];
                echo "</td>";
            }
            elseif ('page_name' == $column_name) {
                $from = isset($_POST['start_date']) ? '&from='. sanitize_text_field($_POST['start_date']) : '';
                $to = isset($_POST['end_date']) ? '&to='. sanitize_text_field($_POST['end_date']) : '';
                $edit_url = wp_nonce_url(menu_page_url('wf_report', false). '&action=detail&post='. $item['page_id'] . $from . $to, '&post='. $item['page_id'], '_wfnonce');

                echo "<td $attributes>";
                echo '<a href="'. $edit_url .'">', $item['page_name'];
                echo "</a>";

                //add action
                echo "<div class='row-actions'>";
                echo "<span class='view'>";
                echo sprintf('<a href="%s">Visit page</a>', get_permalink($item['page_id']));
                echo "</span></div>";
                echo "</td>";
            }
            else {
                echo "<td $attributes>";
                echo $this->column_default( (array) $item, $column_name );
                echo "</td>";
            }
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b ) {
        // Set defaults
        $orderby = 'page_name';
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

    public function extra_tablenav( $which ) {

        if ( $which === 'top' ) {

            $from = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
            $to = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';

            echo '<form method="post">';
            echo '<h4 class="date-range-header">Date Range</h4>';
            echo '<input type="hidden" name="wf_date_filter" value="woo_flow_date_filter" />';
            echo '<label for="start_date">From:</label>';
            echo '<input type="search" class="custom_date text date-range" name="start_date" value="'.$from.'"/>';
            echo '<label for="end_date">To:</label>';
            echo '<input type="search" class="custom_date text date-range" name="end_date" value="'.$to.'"/>';
            echo '<input type="submit" name="submit" class="button button-secondary" value="Apply Filter"/>';
            echo '</form>';

        }
    }

}