<?php

/**
 * Provide a admin area view for the plugin
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

    <div class="wrap">

        <?php

    $is_wc_actived = in_array(
        'woocommerce/woocommerce.php',
        apply_filters('active_plugins',
            get_option('active_plugins')
        )
    );

    if ( $is_wc_actived ) {
        //WooCommerce is activated

        global $submenu;
        $wf_setting = new Woo_Flow_Admin_Settings();
        $custom_tb = new Woo_Flow_Report();
        $show_lost_message = $custom_tb->count_last_month_lost();

        $page_list = $submenu['wf_funnel'];
        $current_page = $_REQUEST['page'];

        if (isset($_REQUEST['page'])) {
        ?>

        <h2 class="nav-tab-wrapper">

            <?php
            foreach ($page_list as $admin_sub_page) {
                printf('<a href="%s" class="nav-tab %s">%s</a>',
                    menu_page_url($admin_sub_page[2], false),
                    ($current_page === $admin_sub_page[2]) ? 'nav-tab-active' : '',
                    $admin_sub_page[0]);
            }
            ?>
        </h2>

            <?php
            if ($show_lost_message) {
                ?>
                <div class="notice notice-warning wf-notice">
                    <p>
                        <strong>
                            <?php _e('You\'re losing over $2000/month in abandoned carts. 
                                Would you like to find out how to recover them?', 'woo-flow');
                            ?>
                        </strong>
                        <span class="float-right">
                            <a href="https://cartrecover.com/your-online-store-is-losing-sales/">Yes</a>
                            or
                            <a class="wf-dissmiss wf-one-month">Dismiss this notification</a>
                        </span>
                    </p>
                </div>
                <?php
            }
            ?>

        <?php
            switch ($current_page) {
                case 'wf_funnel':
                    $wf_setting->woo_flow_funnel_page_content();
                    break;
                case 'wf_report':
                    $wf_setting->woo_flow_report_page_content();
                    break;
                case 'wf_setting':
                    $wf_setting->woo_flow_setting_page_content();
                    break;
                case 'wf_funnel_config':
                    $wf_setting->woo_flow_funnel_config_page_content();
                    break;
                default:
                    $wf_setting->woo_flow_funnel_page_content();
                    break;
            }
        }

    } else {
        //WooCommerce is NOT activated
        ?>
        <div class="error">
            <p>
                <strong>
                    <?php _e('WooCommerce is NOT actived, 
                            please install <a href="' . esc_url('https://wordpress.org/plugins/woocommerce/') . '">
                            WooCommerce</a> and active it to see Woo Flow Pages.', 'woo-flow');
                    ?>
                </strong>
            </p>
        </div>
        <?php
        die();
    }
    ?>

    </div>