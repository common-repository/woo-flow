<?php

/**
 * Fired during plugin activation
 *
 * @link       http://www.innoria.com
 * @since      1.0.0
 *
 * @package    Woo_Flow
 * @subpackage Woo_Flow/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Woo_Flow
 * @subpackage Woo_Flow/includes
 * @author     Innoria Solutions <nhule@innoria.com>
 */
class Woo_Flow_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

        $installation = new Woo_Flow_Install();
        $installation->woo_flow_create_tables();

	}

}
