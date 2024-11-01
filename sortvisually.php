<?php

/**
 * @link              https://sortvisually.com/
 * @since             1.0.0
 * @package           Sortvisually
 *
 * @wordpress-plugin
 * Plugin Name:       Sortvisually
 * Plugin URI:        https://sortvisually.com/
 * Description:       Sortvisually Online Visual Merchandising - Wordpress Visual Merchandiser
 * Version:           1.0.0
 * Author:            Optalenty Ltd.
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sortvisually
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SORTVISUALLY_VERSION', '1.0.0' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-sortvisually.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_sortvisually() {

	$plugin = new Sortvisually();
	$plugin->run();

}
run_sortvisually();
