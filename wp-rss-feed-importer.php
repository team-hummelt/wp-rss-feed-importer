<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wwdh.de
 * @since             1.0.0
 * @package           Wp_Rss_Feed_Importer
 *
 * @wordpress-plugin
 * Plugin Name:       RSS Feed Importer
 * Plugin URI:        https://wwdh.de/plugins/wp-rss-feed-importer
 * Description:       RSS Feed Importer is a WordPress plugin for RSS (Really Simple Syndication) feeds. It collects content from various sources and displays the feeds on your WordPress website.
 * Version:           1.0.0
 * Author:            Jens Wiecker
 * Author URI:        https://wwdh.de
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-rss-feed-importer
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Plugin Database-Version.
 */
const WP_RSS_FEED_IMPORTER_DB_VERSION = '1.0.0';
/**
 * PHP minimum requirement for the plugin.
 */
const WP_RSS_FEED_IMPORTER_MIN_PHP_VERSION = '7.4';
/**
 * WordPress minimum requirement for the plugin.
 */
const WP_RSS_FEED_IMPORTER_MIN_WP_VERSION = '5.6';

/**
 * PLUGIN ROOT PATH.
 */
define('WP_RSS_FEED_IMPORTER_PLUGIN_DIR', dirname(__FILE__));
/**
 * PLUGIN URL.
 */
define('WP_RSS_FEED_IMPORTER_PLUGIN_URL', plugins_url('wp-rss-feed-importer').'/');
/**
 * PLUGIN SLUG.
 */
define('WP_RSS_FEED_IMPORTER_SLUG_PATH', plugin_basename(__FILE__));
/**
 * PLUGIN Basename.
 */
define('WP_RSS_FEED_IMPORTER_BASENAME', plugin_basename(__DIR__));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-rss-feed-importer-activator.php
 */
function activate_wp_rss_feed_importer() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-rss-feed-importer-activator.php';
	Wp_Rss_Feed_Importer_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-rss-feed-importer-deactivator.php
 */
function deactivate_wp_rss_feed_importer() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-rss-feed-importer-deactivator.php';
	Wp_Rss_Feed_Importer_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_rss_feed_importer' );
register_deactivation_hook( __FILE__, 'deactivate_wp_rss_feed_importer' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-rss-feed-importer.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp_rss_feed_importer() {

	$plugin = new Wp_Rss_Feed_Importer();
	$plugin->run();

}
run_wp_rss_feed_importer();
