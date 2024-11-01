<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://tweakdigital.co.uk/
 * @since             1.0.1
 * @package           Woo_Flow
 *
 * @wordpress-plugin
 * Plugin Name:       Woo Flow
 * Plugin URI:        https://tweakdigital.co.uk/
 * Description:       The plugin to track user behaviour in your Woocommerce site and show analytics reports in admin dashboard
 * Version:           1.0.1
 * Author:            Tweak Digital
 * Author URI:        https://profiles.wordpress.org/tweakdigital
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woo-flow
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woo-flow-activator.php
 */
function activate_woo_flow() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-flow-activator.php';
	Woo_Flow_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woo-flow-deactivator.php
 */
function deactivate_woo_flow() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-flow-deactivator.php';
	Woo_Flow_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woo_flow' );
register_deactivation_hook( __FILE__, 'deactivate_woo_flow' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woo-flow.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woo_flow() {

	$plugin = new Woo_Flow();
	$plugin->run();

}
run_woo_flow();
