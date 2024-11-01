<?php

/**
 * Register Woo Flow tracking method
 *
 * @link       http://www.innoria.com
 * @since      1.0.0
 *
 * @package    Woo_Flow
 * @subpackage Woo_Flow/includes
 */

/**
 * Register Woo Flow tracking method
 *
 * @package    Woo_Flow
 * @subpackage Woo_Flow/includes
 * @author     Innoria Solutions <nhule@innoria.com>
 */
class Woo_Flow_Tracker extends Woo_Flow_Data_Helper {

    protected $table_woo_flow_analysis;

    protected $table_woo_flow_track_setting;

    /**
     * Initialize the collections used to maintain the actions and filters.
     *
     * @since    1.0.0
     */
    public function __construct() {

        $wf_install = new Woo_Flow_Install();
        $this->table_woo_flow_analysis = $wf_install->get_table_woo_flow_analysis();
        $this->table_woo_flow_track_setting = $wf_install->get_table_woo_flow_track_setting();
        parent::__construct();

    }

    /**
     * Function to insert pageview event to database
     *
     * @param $page_id
     * @param int $reference_id
     * @param int $page_value
     * @param string $post_type
     * @param string $session_id
     * @param string $references_key
     * @return int Insert
     */
    public function wf_pageview($page_id, $reference_id = 0, $references_key, $post_type = 'page', $page_value = 0, $session_id) {
        $current_date = date("Y-m-d H:i:s");
        $post = get_post($page_id);
        $tracking_key = 'external_site';

        if ($post) {
            if ($post->post_type === 'product') {
                $tracking_key = 'wc_products';
            } else {
                $tracking_key = $post->post_name;
            }
        }

        $insert_data = array(
            'post_id'           =>  $page_id,
            'user_id'           =>  get_current_user_id(),
            'event_name'        =>  'page_view',
            'post_references'   =>  $reference_id,
            'session_time'      =>  $current_date,
            'page_value'        =>  $page_value,
            'post_type'         =>  $post_type,
            'tracking_key'      =>  $tracking_key,
            'references_key'    =>  $references_key,
            'session_id'        =>  $session_id
        );

        $result = $this->insert($this->table_woo_flow_analysis, $insert_data);
        return $result;
    }

    /**
     * Function to insert leave page row in database
     *
     * @param $page_id
     * @param int $reference_id
     * @param string $session_id
     * @return int Insert
     */
    public function wf_pageleave($page_id, $reference_id = 0, $session_id) {

        $current_date = date("Y-m-d H:i:s");
        global $woocommerce;
        $post = get_post($page_id);
        $next_post = get_post($reference_id);
        $tracking_key = 'external_site';
        $references_key = 'external_site';
        $page_value = 0;

        if ($post) {
            if ($post->post_type === 'product') {
                $tracking_key = 'wc_products';
                $product = new WC_Product( $post->ID );
                $page_value = (float) $product->get_price();
            } else {
                $tracking_key = $post->post_name;
            }
        }
        if ($next_post) {
            if ($next_post->post_type === 'product') {
                $references_key = 'wc_products';
            } else {
                $references_key = $next_post->post_name;
            }
        }

        if ( $page_id == get_option( 'woocommerce_cart_page_id' ) || $page_id == get_option( 'woocommerce_checkout_page_id' )) {
            $page_value = $woocommerce->cart->total;
        }

        $insert_data = array(
            'post_id'           =>  $page_id,
            'user_id'           =>  get_current_user_id(),
            'event_name'        =>  'leave_page',
            'post_references'   =>  $reference_id,
            'session_time'      =>  $current_date,
            'page_value'        =>  $page_value,
            'post_type'         =>  $post ? $post->post_type : 'external_site',
            'tracking_key'      =>  $tracking_key,
            'references_key'    =>  $references_key,
            'session_id'        =>  $session_id
        );

        $result = $this->insert($this->table_woo_flow_analysis, $insert_data);

        return $result;
    }

    /**
     * Add tracking code for pageview event
     */
    function wpsites_add_tracking_code() {

        global $wp;

        $current_url = home_url( add_query_arg( array(), $wp->request ) );
        $current_post_id = url_to_postid($current_url);
        $post = get_post($current_post_id);
        $page_value = 0;

        if (is_product()) {
            $product = new WC_Product( $post->ID );
            $page_value = (float) $product->get_price();
        }
        if (is_cart() || is_checkout()) {
            global $woocommerce;
            $page_value = $woocommerce->cart->total;
        }

        if (!isset($_SERVER['HTTP_REFERER'])) {
            $previous_page = url_to_postid("");
            $previous_post['ID'] = $previous_page;
            $previous_post['post_title'] = 'External pages';
            $previous_post['post_name'] = 'external_site';
            $previous_post['post_type'] = 'external_site';
            $previous_post = (object) $previous_post;
        } else {
            $previous_page = url_to_postid($_SERVER['HTTP_REFERER']);
            $previous_post = get_post($previous_page);
        }
        if ( $this->is_tracking_page()) {
            if ( !isset($_COOKIE['wf_last_visited']) || (isset($_COOKIE['wf_last_visited']) && $_COOKIE['wf_last_visited'] != $_SERVER['REQUEST_URI']) ) {
                $session_id = isset($_COOKIE['wf_session_id']) ? $_COOKIE['wf_session_id'] : '1';

                $reference_key = $previous_post->post_name;
                if ($previous_post->post_type === 'product') {
                    $reference_key = 'wc_products';
                }
                $this->wf_pageview($post->ID, $previous_post->ID, $reference_key, $post->post_type, $page_value, $session_id);
                // $this->wf_pageview_tracker($post, $previous_post, $reference_key, $session_id, $page_value);
                // $this->wf_pageview($post->ID, $previous_post->ID, $reference_key, $post->post_type, $page_value, $session_id);
            }
        }
    }

    /**
     * Get object post by page slug
     *
     * @param $slug
     * @param string $post_type
     * @return array|null|WP_Post
     */
    public function get_page_id_by_slug($slug, $post_type = 'page') {
        $post = get_page_by_path( $slug, OBJECT, $post_type );
        return $post;
    }

    /**
     * Show tracking message in front site to test
     *
     * @param $post
     * @param $previous_post
     * @param $session_id
     * @param $reference_key
     * @param $page_value
     */
    public function wf_pageview_tracker($post, $previous_post, $reference_key, $session_id, $page_value) {

        $this->wf_pageview($post->ID, $previous_post->ID, $reference_key, $post->post_type, $page_value, $session_id);
        echo '<div class="tracking-code">Tracking page <strong>'. $post->post_title
            .'</strong> coming from '. $previous_post->post_title
            .' at '. date("Y-m-d H:i:s") .' in session '. $session_id .'</div>';
    }

    public function set_tracking_cookies() {
        if (!isset($_COOKIE['wf_last_visited'])) {
            setcookie( 'wf_last_visited', 'external_site', NULL, '/', NULL, 0);
        }
        if (!isset($_COOKIE['wf_session_id'])) {
            $session_id = $this->render_session_id();
            if ($session_id[0]->session_id === NULL) {
                $next_session = 1;
                setcookie( 'wf_session_id', $next_session, NULL, '/', NULL, 0);
            } else {
                $next_session = $session_id[0]->session_id + 1;
                setcookie( 'wf_session_id', $next_session, NULL, '/', NULL, 0);
            }
        }
    }

    public function render_session_id() {
        return $this->get_records( $this->table_woo_flow_analysis, array('MAX(session_id)' => 'session_id'));
    }

    /**
     * Check current page is in tracking list
     *
     * @return bool
     */
    public function is_tracking_page() {

        $result = false;

        $tracking_pages = $this->get_all($this->table_woo_flow_track_setting);

        foreach ($tracking_pages as $tracking_page) {
            switch ($tracking_page->track_key) {
                case 'shop':
                    $result = is_shop();
                    break;
                case 'wc_products':
                    $result = is_product();
                    break;
                case 'cart':
                    $result = is_cart();
                    break;
                case 'checkout':
                    $result = is_checkout();
                    break;
                default:
                    $post = $this->get_page_id_by_slug($tracking_page->track_key);
                    $result = is_page($post->ID);
                    break;
            }

            if ($result === true) {
                return $result;
            }
        }

        return $result;
    }

    public function is_current_page_in_track_list( $post ) {

        $result = false;

        $tracking_pages = $this->get_all($this->table_woo_flow_track_setting);

        foreach ($tracking_pages as $tracking_page) {
            switch ($tracking_page->track_key) {
                case 'wc_products':
                    if ($post->post_type === 'product') {
                        $result = true;
                    }
                    break;
                default:
                    if ($post->post_name === $tracking_page->track_key) {
                        $result = true;
                    }
                    break;
            }

            if ($result === true) {
                return $result;
            }
        }

        return $result;
    }

}