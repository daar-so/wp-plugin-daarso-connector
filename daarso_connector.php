<?php
/**
 * Plugin Name: Daar-so.nl Connector
 * Description: Een verplichte plugin voor websites die worden gehost op het Daar-so hosting platform.
 * Version: 0.1.1
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author: daar-so.nl®
 * Author URI: https://daar-so.nl
 * License: Proprietary
 **/

/**
 * De opzet is volgens: Wordpress-Plugin-Boillerplate
 * Dit geeft iets van een structuur die aansluit bij de wereld van Wordpress plugins
 **/
namespace daarso;

use Throwable;

function daarso_access_guard(): void {
	// If this file is called directly, abort.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
}

daarso_access_guard();

define( 'DAARSO_CONNECTOR_VERSION', '0.1.1' );
define( 'DAARSO_CONNECTOR_ROOT', plugin_dir_path( __FILE__ ) );
define( 'DAARSO_CONNECTOR_SLUG', 'daarso-connector' );
function activate_daarso_plugin(): void {
	require_once plugin_dir_path( __FILE__ ) . 'includes/daarso_connector_activator.php';
	includes\daarso_connector_activator::activate();
}

function uninstall_daarso_plugin(): void {
	require_once plugin_dir_path( __FILE__ ) . 'includes/daarso_connector_uninstaller.php';
	includes\daarso_connector_uninstaller::uninstall();
}

register_activation_hook( __FILE__, 'daarso\activate_daarso_plugin' );
register_uninstall_hook( __FILE__, 'daarso\uninstall_daarso_plugin' );
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/daarso_connector_core.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @throws Throwable
 * @since    1.0.0
 */
function run_daarso_connector(): void {

	$plugin = new includes\daarso_connector_core();
	$plugin->run();
}

run_daarso_connector();
