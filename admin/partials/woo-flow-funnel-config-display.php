<?php

/**
 * Provide a admin Funnel Config area view for the plugin
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

<?php
$wf = new Woo_Flow_Config();

$hidden_field_name = 'wf_config_submit_hidden';
$form_name = 'frmWFFunnelConfig';
$config_type = 'wf_funnel_config';
$section_num = 3;

$page_list = get_pages( array(
    'post_type'     => 'page',
    'post_status'   => 'publish'
) );

$product_page = array(
    'ID'            =>  -1,
    'post_name'     =>  'wc_products',
    'post_title'    =>  'WC Products Pages',
    'post_type'     =>  'product'
);

array_unshift($page_list, (object) $product_page );

if (isset($_POST[$hidden_field_name]) && $_POST[$hidden_field_name] == 'Y') {

    if (isset($_POST['cancel'])) {

    } else {

        for ( $pos = 1; $pos <= $section_num; $pos++ ) {
            if ( isset($_POST['section'. $pos]) ) {
                $selected_pages = sanitize_text_field($_POST['section'. $pos]);
                $wf->woo_flow_update_funnel_config($config_type, 'section'. $pos, $selected_pages);
            }
        }

        // Put a "settings saved" message on the screen
        ?>
        <div class="updated"><p><strong><?php _e('Funnel Configuration saved', 'woo-flow'); ?></strong></p></div>
        <?php
    }
}

?>

<h2 class="title"><?php _e('Funnel Configuration', 'woo-flow'); ?></h2>

<p><?php _e("Select the first, second and third stages of your checkout flow e.g. WC Products Pages, Cart, Checkout", 'woo-flow') ?></p>

<form name="<?php echo $form_name; ?>" id="<?php echo $form_name; ?>" method="post" action="">
    <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

    <table class="form-table">
        <?php
        for ( $pos = 1; $pos <= $section_num; $pos++ ) {
            ?>
            <tr class="row-<?php echo $pos; ?>">
                <td class="label-col">
                    <label for="cars"><?php _e("Sections ". $pos .":"); ?></label>
                </td>
                <td>
                    <fieldset>
                        <select name="section<?php echo $pos; ?>">
                            <?php
                            foreach ($page_list as $post) :
                                $selected = $wf->is_config_page($config_type, 'section'. $pos, $post->post_name);
                                ?>
                                <option <?php echo $selected ? 'selected' : ''; ?> id="<?php echo $post->post_name; ?>" value="<?php echo $post->post_name; ?>">
                                    <?php echo $post->post_title; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </fieldset>
                </td>
            </tr>
            <?php
        }
        ?>
    </table>

    <p class="submit">
        <input type="submit" name="submit" class="button-primary"
               value="<?php esc_attr_e('Save') ?>"/>
        <input type="submit" name="cancel" class="button-secondary"
               value="<?php esc_attr_e('Cancel') ?>"/>
    </p>

</form>