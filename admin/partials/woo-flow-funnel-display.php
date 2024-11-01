<?php

/**
 * Provide a admin funnel area view for the plugin
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
$wf = new Woo_Flow_Report();

$hidden_data_range = 'wf_date_filter';
$today = new DateTime();
$last_month = new DateTime();
$last_month->modify('-1 month');

$end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : $today->format('Y-m-d');
$start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : $last_month->format('Y-m-d');

$funnel_sections = $wf->get_funnel_data($start_date, $end_date);

$total_sessions = $funnel_sections ? $funnel_sections[0]->total_view : 0;
$total_conversions = $funnel_sections ? $funnel_sections[sizeof($funnel_sections) - 1]->total_view : 0;
$total_conversions = $total_sessions ? ( $total_conversions / $total_sessions ) * 100 : 0;

$summary_report = array(
    'sestions' => array(
        'icon' => plugin_dir_url(__FILE__) . '../images/sessions.png',
        'label' => 'Sessions',
        'value' => number_format($total_sessions)
    ),
    'conversion' => array(
        'icon' => plugin_dir_url(__FILE__) . '../images/conversion.png',
        'label' => 'Conversion Rate',
        'value' => number_format($total_conversions, 2) . '%'
    )
);

?>

<div class="wrap">

    <h1><?php _e('Sales Funnel', 'woo-flow'); ?></h1>

    <hr />

    <?php if ( $funnel_sections ) :
    $chart_data = array();
    foreach ( $funnel_sections as $key => $section ) {
        array_push($chart_data, $section->total_view);
        if ( $key === sizeof($funnel_sections) - 1 ) {
            array_push($chart_data, $section->total_view - $section->total_leave);
        }
    }
    ?>
        <script>
            var funnel_data = [<?php echo '"' . implode('","', $chart_data) . '"' ?>];
        </script>

        <section class="summary-report">

            <table class="form-table funnel-table">
                <tr>
                    <?php
                    foreach ( $summary_report as $key => $report ) :
                        ?>
                        <td>
                            <div class="summary-info summary-<?php echo $key; ?>">
                                <img class="img-thumbnail wf-funnel-icon ic-<?php echo $key; ?>"
                                     src="<?php echo $report['icon']; ?>"/>
                                <div class="report-data">
                                    <p class="wf-label"><?php echo $report['label']; ?></p>
                                    <div class="wf-value"><?php echo $report['value']; ?></div>
                                </div>
                            </div>
                        </td>
                    <?php endforeach; ?>
                    <td align="right">
                        <form name="frmWooFlowReset" id="frmWooFlowReset" method="post" action="">
                            <h4 class="form-header">Date Range</h4>
                            <input type="hidden" name="<?php echo $hidden_data_range; ?>" value="Y">

                            <label for="start_date"><?php _e('From:', 'woo_flow'); ?></label>
                            <input type="search" class="custom_date regular-input date-range" name="start_date"
                                   value="<?php echo $start_date; ?>"/>

                            <label for="end_date"><?php _e('To:', 'woo_flow'); ?></label>
                            <input type="search" class="custom_date regular-input date-range" name="end_date"
                                   value="<?php echo $end_date; ?>"/>

                            <input type="submit" name="submit" class="button button-secondary"
                                   value="<?php esc_attr_e('Filter') ?>"/>
                        </form>
                    </td>
                </tr>
            </table>

        </section>

        <section class="wf-section funnel-section">

            <table class="table wf-tabel-funnel" cellpadding="16" cellspacing="0">
                <thead>
                <tr>
                    <?php
                    foreach ( $funnel_sections as $key => $section ) {
                        ?>
                        <th>
                            Funnel Stage <?php echo $key + 1; ?>
                            <div class="page-name">
                                <?php echo $section->post_title; ?>
                            </div>
                        </th>
                        <?php
                    }
                    ?>
                </tr>
                </thead>

                <tbody>
                <tr>
                    <?php
                    foreach ( $funnel_sections as $key => $section ) {
                        ?>
                        <td>
                            <input class="hidden wf-funnel-data" type="hidden"
                                   name="<?php echo $section->setting_name; ?>"
                                   id="wf-funnel-section<?php echo $key + 1; ?>">

                            <div class="session-report report-data">
                                <div class="wf-lable">Sessions</div>
                                <div class="wf-value">
                                    <?php echo number_format($section->total_view); ?>
                                </div>
                            </div>
                            <?php
                            if ( $key > 0 ) {
                                $section->conversion_rate = $funnel_sections[$key - 1]->total_view ? ( $section->total_view / $funnel_sections[$key - 1]->total_view ) * 100 : 0;
                                ?>
                                <div class="conversion-report report-data float-right">
                                    <div class="wf-lable">Conversion Rate</div>
                                    <div class="wf-value">
                                        <?php
                                        echo number_format($section->conversion_rate, 2);
                                        ?>%
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                        </td>
                        <?php
                    }
                    ?>
                </tr>
                <tr>
                    <td colspan="3" class="wf-canvas-container">
                        <canvas id="wf-funnel" class="wf-funnel-chart" width="2000" height="250"></canvas>
                    </td>
                </tr>
                <tr>
                    <?php
                    foreach ( $funnel_sections as $key => $section ) {
                        ?>
                        <td class="funnel-footer width-30" valign="top">
                            <?php
                            if ( $key < ( sizeof($funnel_sections) - 1 ) ) {
                                $section->dropoff = $section->total_view ? ( $section->total_view - $funnel_sections[$key + 1]->total_view ) / $section->total_view * 100 : 0;
                                ?>

                                <div class="funnel-circle drop-off-circle
                                <?php
                                if ($section->total_view > 0) {
                                    if ($section->dropoff === 0) {
                                        echo ' ok-icon ';
                                    } else if ($section->dropoff < 50) {
                                        echo ' normal-icon ';
                                    } else if ($section->dropoff > 0) {
                                        echo ' hot-icon ';
                                    }
                                }
                                ?>">
                                    <span class="wf-funnel-circel-icon dashicons <?php echo ($section->dropoff === 0 && $section->total_view > 0) ? 'dashicons-yes' : 'dashicons-arrow-down-alt'; ?>"></span>
                                </div>

                                <div class="leave-report report-data width-30">
                                    <div class="wf-lable">Abandoned Sessions</div>
                                    <div class="wf-value">
                                        <?php echo number_format($section->total_leave); ?>
                                    </div>
                                </div>
                                <div class="dropoff-report report-data width-30">
                                    <div class="wf-lable">Dropoff Rate</div>
                                    <div class="wf-value">
                                        <?php echo number_format($section->dropoff, 2); ?>%
                                    </div>
                                </div>

                                <?php
                            } else {
                                ?>

                                <div class="funnel-circle conversion-circle <?php echo ($total_conversions > 0) ? ' ok-icon ' : ''; ?>">
                                    <span class="wf-funnel-circel-icon dashicons dashicons-yes"></span>
                                </div>

                                <div class="leave-report report-data width-50">
                                    <div class="wf-lable">Total Conversion</div>
                                    <div class="wf-value">
                                        <?php echo number_format($total_conversions); ?>%
                                    </div>
                                </div>

                                <?php
                            }
                            ?>
                            <div class="lost-value-report report-data float-right">
                                <div class="wf-lable">Lost Value</div>
                                <div class="wf-value">
                                    $<?php echo number_format($section->lost_value, 1); ?>
                                </div>
                            </div>
                        </td>
                        <?php
                    }
                    ?>
                </tr>
                </tbody>
            </table>

        </section>

    <?php
    else:
        echo '<hr/><p>You have no config for Funnel, please add config first.</p>';
    endif;
    ?>

</div><!-- /.wrap -->
