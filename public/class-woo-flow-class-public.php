<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.innoria.com
 * @since      1.0.0
 *
 * @package    Woo_Flow
 * @subpackage Woo_Flow/public
 */

/**
 *
 * Defines function for ajax process
 *
 * @package    Woo_Flow
 * @subpackage Woo_Flow/public
 * @author     Innoria Solutions <nhule@innoria.com>
 */
class Woo_Flow_Class_Public {

    public function tracking_leave_page() {

        $result = 0;

        $current_url = esc_url($_POST['current_url']);
        $next_url = esc_url($_POST['next_url']);
        $session_id = intval($_POST['session_id']);

        $post_id = url_to_postid($current_url);
        $next_page = url_to_postid($next_url);

        $tracker = new Woo_Flow_Tracker();
        $is_track = $tracker->is_current_page_in_track_list(get_post($post_id));

        if ($is_track) {
            $result = $tracker->wf_pageleave($post_id, $next_page, $session_id);
        }

        echo $result;

        die();
    }


}
