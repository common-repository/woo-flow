<?php

/**
 * Provide a admin report area view for the plugin
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

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<!-- Create a header in the default WordPress 'wrap' container -->

<?php

//var_dump($_POST);

if (isset($_GET["action"]) && isset($_GET["post"]) && $_GET["action"] === 'detail') {
    include_once 'woo-flow-report-detail-display.php';
} else {
?>

    <div class="wrap wf_report_page">

        <h1>
            <?php
            _e('Woo Flow Report', 'woo-flow');
            $search = ( isset( $_REQUEST['s'] ) ) ? $_REQUEST['s'] : false;
            if ($search) {
                echo '<span class="subtitle">'. sprintf('Search results for “%s”', $search) .'</span>';
            }
            ?>

        </h1>

        <?php
        $custom_tb = new Woo_Flow_Report();
        $custom_tb->report_list_page();
        ?>

    </div><!-- /.wrap -->

<?php
}
?>