<?php

/**
 * Provide a admin setting area view for the plugin
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

// variables for the field and option names
$hidden_field_name = 'wf_setting_submit_hidden';
$hidden_reset_field = 'wf_reset_submit_hidden';

$page_list = get_pages( array(
            'post_type'     => 'page',
            'post_status'   => 'publish'
        ) );

$product_page = array(
            'ID'            =>  -1,
            'post_name'     =>  'wc_products',
            'post_title'    =>  'All Products Pages',
            'post_type'     =>  'product'
        );

array_unshift($page_list, (object) $product_page );

$wc_page_list = array(
        'woocommerce_shop'          =>  get_post( get_option( 'woocommerce_shop_page_id' ) ),
        'woocommerce_cart'          =>  get_post( get_option( 'woocommerce_cart_page_id' ) ),
        'woocommerce_checkout'      =>  get_post( get_option( 'woocommerce_checkout_page_id' ) ),
        'woocommerce_pay'           =>  get_post( get_option( 'woocommerce_pay_page_id' ) ),
        'woocommerce_thanks'        =>  get_post( get_option( 'woocommerce_thanks_page_id' ) ),
        'woocommerce_myaccount'     =>  get_post( get_option( 'woocommerce_myaccount_page_id' ) ),
        'woocommerce_edit_address'  =>  get_post( get_option( 'woocommerce_edit_address_page_id' ) ),
        'woocommerce_view_order'    =>  get_post( get_option( 'woocommerce_view_order_page_id' ) ),
        'woocommerce_terms'         =>  get_post( get_option( 'woocommerce_terms_page_id' ) )
    );

// See if the user has posted us some information
// If they did, this hidden field will be set to 'Y'
if (isset($_POST[$hidden_field_name]) && $_POST[$hidden_field_name] == 'Y') {

    $wf->woo_flow_delete_track_setting();

    if ( isset($_POST['page_setting']) ) {
        $selected_pages = $_POST['page_setting'];

        foreach ( $page_list as $page ) {
            foreach ( $selected_pages as $selected ) {
                if ( $selected === $page->post_name ) {
                    $wf->woo_flow_update_track_setting($page->post_name, $page);
                    break;
                }
            }
        }
    }

    // Put a "settings saved" message on the screen
    ?>
    <div class="updated"><p><strong><?php _e('Settings saved', 'woo-flow'); ?></strong></p></div>
    <?php

}

//Reset data function
$start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
$end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';

if (isset($_POST[$hidden_reset_field]) && $_POST[$hidden_reset_field] == 'Y') {

    if (isset($_POST['submitAll'])) {
        $deleted = $wf->woo_flow_reset_all_track_data(true);
    } else {
        $deleted = $wf->woo_flow_reset_all_track_data( false, $start_date, $end_date );
    }

    $message_text = '';

    if ($deleted === false) {
        $message_text = '';
    } else if ($deleted === 0){
        $message_text = 'No row effected';
    } else {
        $message_text = 'Delete tracking data successfully';
    }

    // Put a "settings saved" message on the screen
    if ( $message_text !== '' ) {
        ?>
        <div class="updated"><p><strong><?php _e($message_text, 'woo-flow'); ?></strong></p></div>
        <?php
    }
}
?>

<!-- Create a header in the default WordPress 'wrap' container -->
<div class="wrap">

    <h1><?php _e('Woo Flow Setting', 'woo-flow'); ?></h1>

    <hr/>

    <p><?php _e("Select pages to be tracked in WooFlow", 'woo-flow') ?></p>

    <form name="frmWooFlowSetting" id="frmWooFlowSetting" method="post" action="">
        <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

        <fieldset>

            <input type="checkbox" id="allPages" name="allPages" value="Select All" />
            <label for="allPages">Select All</label>
            <br/>

            <div class="select-list">
                <?php
                foreach ($page_list as $post) :
                    $is_in_setting = $wf->woo_flow_is_setting_page($post->post_name);
                    ?>
                    <input type="checkbox" id="<?php echo $post->post_name; ?>"
                           name="page_setting[]" value="<?php echo $post->post_name; ?>"
                            <?php echo $is_in_setting ? 'checked' : ''; ?> />
                    <label for="<?php echo $post->post_name; ?>"><?php echo $post->post_title; ?></label>
                    <br/>
                <?php endforeach; ?>
            </div>

        </fieldset>

        <p class="submit">
            <input type="submit" name="submit" class="button-primary"
                   value="<?php esc_attr_e('Save Changes') ?>"/>
        </p>

    </form>

    <hr/>

    <?php
    include_once 'woo-flow-funnel-config-display.php';
    echo '<hr/>';
    include_once 'woo-flow-reset-data-form-display.php';
    ?>

    <!-- End main setting for choose page will track event -->



</div><!-- /.wrap -->
