<?php

/**
 * Display reset data form in setting page
 *
 * @link       http://www.innoria.com
 * @since      1.0.0
 *
 * @package    Woo_Flow
 * @subpackage Woo_Flow/admin/partials
 */
?>

<h2 class="title"><?php _e('Reset Data', 'woo-flow'); ?></h2>

<p><?php _e("Clear WooFlow tracking data", 'woo-flow') ?></p>

<form name="frmWooFlowReset" id="frmWooFlowReset" class="will-submit" method="post" action="">
    <input type="hidden" name="<?php echo $hidden_reset_field; ?>" value="Y">

    <label for="start_date"><?php _e('From:', 'woo_flow'); ?></label>
    <input type="search" class="custom_date text date-range" name="start_date" value="<?php echo $start_date; ?>"/>

    <label for="end_date"><?php _e('To:', 'woo_flow'); ?></label>
    <input type="search" class="custom_date text date-range" name="end_date" value="<?php echo $end_date; ?>"/>

    <p class="submit">
        <input type="button" name="submit" class="button button-primary btn-wf-reset-data"
               value="<?php esc_attr_e('Reset Data') ?>"/>
        <input type="submit" hidden name="submit" id="submit" class="button button-primary hidden"
               value="<?php esc_attr_e('Reset Data') ?>"/>
        <input type="button" name="submitAll" class="button button-delete btn-wf-reset-data"
               value="<?php esc_attr_e('Reset All') ?>"/>
        <input type="submit" hidden name="submitAll" id="submitAll" class="button button-delete hidden"
               value="<?php esc_attr_e('Reset All') ?>"/>
    </p>

</form>

<div id="dialog-confirm">
    <p>Are you sure?</p>
</div>