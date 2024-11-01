<?php

/**
 * Provide a admin report detail area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://www.innoria.com
 * @since      1.0.0
 *
 * @package    Woo_Flow
 * @subpackage Woo_Flow/admin/partials
 */
?>

<?php
$post = get_post($_GET["post"]);
$from = isset($_GET['from']) ? $_GET['from'] : NULL;
$to = isset($_GET['to']) ? $_GET['to'] : NULL;
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<!-- Create a header in the default WordPress 'wrap' container -->
<div class="wrap">

    <h1><?php _e($post->post_title .' Page Tracking Report Detail', 'woo-flow'); ?></h1>

    <hr/>

    <?php
    $custom_tb = new Woo_Flow_Report($post);
    $custom_tb->view_page_report_detail('page_view', $from, $to);
    $custom_tb->view_page_report_detail('leave_page', $from, $to);
    ?>

</div><!-- /.wrap -->
