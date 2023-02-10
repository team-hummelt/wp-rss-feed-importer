<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://wwdh.de
 * @since      1.0.0
 *
 * @package    Wp_Rss_Feed_Importer
 * @subpackage Wp_Rss_Feed_Importer/includes
 */

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use WPRSSFeddImporter\Endpoint\Srv_Api_Endpoint;
use WPRSSFeddImporter\FeedImporter\Import_RSS_Cronjob;
use WPRSSFeddImporter\FeedImporter\RSS_Import_Execute;
use WPRSSFeddImporter\FeedImporter\RSS_Importer_Helper;
use WPRSSFeddImporter\SrvApi\Endpoint\Make_Remote_Exec;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wp_Rss_Feed_Importer
 * @subpackage Wp_Rss_Feed_Importer/includes
 * @author     Jens Wiecker <wordpress@wwdh.de>
 */
class Wp_Rss_Feed_Importer {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wp_Rss_Feed_Importer_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected Wp_Rss_Feed_Importer_Loader $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected string $plugin_name;

	/**
	 * The Public API ID_RSA.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $id_rsa plugin API ID_RSA.
	 */
	private string $id_rsa;

	/**
	 * The PLUGIN API ID_RSA.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $id_plugin_rsa plugin API ID_RSA.
	 */
	private string $id_plugin_rsa;

	/**
	 * The PLUGIN API ID_RSA.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      object $plugin_api_config plugin API ID_RSA.
	 */
	private object $plugin_api_config;


	/**
	 * The Public API DIR.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $api_dir plugin API DIR.
	 */
	private string $api_dir;

	/**
	 * The plugin Slug Path.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $srv_api_dir plugin Slug Path.
	 */
	private string $srv_api_dir;

	/**
	 * Store plugin main class to allow public access.
	 *
	 * @since    1.0.0
	 * @var object The main class.
	 */
	public object $main;

	/**
	 * The plugin Slug Path.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_slug plugin Slug Path.
	 */
	private string $plugin_slug;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected string $version = '';

	/**
	 * The current database version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $db_version The current database version of the plugin.
	 */
	protected string $db_version;

	/**
	 * TWIG autoload for PHP-Template-Engine
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Environment $twig TWIG autoload for PHP-Template-Engine
	 */
	private Environment $twig;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @throws LoaderError
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = WP_RSS_FEED_IMPORTER_BASENAME;
		$this->plugin_slug = WP_RSS_FEED_IMPORTER_SLUG_PATH;
		$this->main        = $this;

		/**
		 * Currently plugin version.
		 * Start at version 1.0.0 and use SemVer - https://semver.org
		 * Rename this for your plugin and update it as you release new versions.
		 */
		$plugin = get_file_data( plugin_dir_path( dirname( __FILE__ ) ) . $this->plugin_name . '.php', array( 'Version' => 'Version' ), false );
		if ( ! $this->version ) {
			$this->version = $plugin['Version'];
		}

		if ( defined( 'WP_RSS_FEED_IMPORTER_DB_VERSION' ) ) {
			$this->db_version = WP_RSS_FEED_IMPORTER_DB_VERSION;
		} else {
			$this->db_version = '1.0.0';
		}

		$this->plugin_name = 'wp-rss-feed-importer';

		$this->check_dependencies();
		$this->load_dependencies();

		$twigAdminDir = plugin_dir_path( dirname( __FILE__ ) ) . 'admin' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR;
		$twig_loader  = new FilesystemLoader( $twigAdminDir );
		$twig_loader->addPath( $twigAdminDir . 'Templates', 'templates' );
		$this->twig = new Environment( $twig_loader );
		$language   = new TwigFilter( '__', function ( $value ) {
			return __( $value, 'wp-rss-feed-importer' );
		} );
		$this->twig->addFilter( $language );

		//JOB SRV API
		$this->srv_api_dir = plugin_dir_path( dirname( __FILE__ ) ) . 'admin' . DIRECTORY_SEPARATOR . 'srv-api' . DIRECTORY_SEPARATOR;

		if ( is_file( $this->srv_api_dir . 'id_rsa' . DIRECTORY_SEPARATOR . $this->plugin_name . '_id_rsa' ) ) {
			$this->id_plugin_rsa = base64_encode( $this->srv_api_dir . DIRECTORY_SEPARATOR . 'id_rsa' . $this->plugin_name . '_id_rsa' );
		} else {
			$this->id_plugin_rsa = '';
		}
		if ( is_file( $this->srv_api_dir . 'config' . DIRECTORY_SEPARATOR . 'config.json' ) ) {
			$this->plugin_api_config = json_decode( file_get_contents( $this->srv_api_dir . 'config' . DIRECTORY_SEPARATOR . 'config.json' ) );
		} else {
			$this->plugin_api_config = (object) [];
		}

		$this->set_locale();

		$this->register_wp_remote_exec();
		$this->register_wp_rss_importer_rest_endpoint();
		$this->wp_rss_imports_db_handle();
		$this->register_wp_rss_importer_rss_helper();
		$this->register_cron_rss_importer();
		$this->rss_import_cronjob();
		$this->register_wp_rss_importer_gutenberg_tools();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wp_Rss_Feed_Importer_Loader. Orchestrates the hooks of the plugin.
	 * - Wp_Rss_Feed_Importer_i18n. Defines internationalization functionality.
	 * - Wp_Rss_Feed_Importer_Admin. Defines all hooks for the admin area.
	 * - Wp_Rss_Feed_Importer_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-rss-feed-importer-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-rss-feed-importer-i18n.php';

		/**
		 * The code that runs during plugin activation.
		 * This action is documented in includes/class-hupa-teams-activator.php
		 */
		require_once plugin_dir_path(dirname(__FILE__ ) ) . 'includes/class-wp-rss-feed-importer-activator.php';

		/**
		 * The Settings Trait
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/Rss/CronSettings.php';

		/**
		 * The  database for the WP RSS Importer Plugin
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/Database/class_wp_rss_import_db_handle.php';


		/**
		 * Composer-Autoload
		 * Composer Vendor for Theme|Plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/vendor/autoload.php';

		/**
		 * The Helper
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/Rss/class_rss_importer_helper.php';

		/**
		 * Plugin WP_CRON_EXECUTE
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/Rss/class_rss_import_execute.php';

		/**
		 * Plugin WP_CRON
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/Rss/class_import_rss_cronjob.php';

		/**
		 * Plugin WP Gutenberg Sidebar
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/Gutenberg/class_register_rss_importer_gutenberg_tools.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-rss-feed-importer-admin.php';

		//JOB SRV API Endpoint
		/**
		 * SRV WP-Remote Exec
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/srv-api/config/class_make_remote_exec.php';

		/**
		 * SRV WP-Remote API
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/srv-api/class_srv_api_endpoint.php';

		/**
		 * RSS Importer REST-Endpoint
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/Gutenberg/class_rss_importer_rest_endpoint.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-rss-feed-importer-public.php';

		$this->loader = new Wp_Rss_Feed_Importer_Loader();

	}

	/**
	 * Check PHP and WordPress Version
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function check_dependencies(): void {
		global $wp_version;
		if ( version_compare( PHP_VERSION, WP_RSS_FEED_IMPORTER_MIN_PHP_VERSION, '<' ) || $wp_version < WP_RSS_FEED_IMPORTER_MIN_WP_VERSION ) {
			$this->maybe_self_deactivate();
		}
	}

	/**
	 * Self-Deactivate
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function maybe_self_deactivate(): void {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		deactivate_plugins( $this->plugin_slug );
		add_action( 'admin_notices', array( $this, 'self_deactivate_notice' ) );
	}

	/**
	 * Self-Deactivate Admin Notiz
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function self_deactivate_notice(): void {
		echo sprintf( '<div class="notice notice-error is-dismissible" style="margin-top:5rem"><p>' . __( 'This plugin has been disabled because it requires a PHP version greater than %s and a WordPress version greater than %s. Your PHP version can be updated by your hosting provider.', 'wp-rss-feed-importer' ) . '</p></div>', WP_RSS_FEED_IMPORTER_MIN_PHP_VERSION, WP_RSS_FEED_IMPORTER_MIN_WP_VERSION );
		exit();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wp_Rss_Feed_Importer_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wp_Rss_Feed_Importer_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$activator = new Wp_Rss_Feed_Importer_Activator();
		$this->loader->add_action('init', $activator, 'register_wp_rss_feed_news_post_type');
		$this->loader->add_action('init', $activator, 'register_wp_rss_feed_news_taxonomy');

		$plugin_admin = new Wp_Rss_Feed_Importer_Admin( $this->get_plugin_name(), $this->get_version(),$this->main, $this->twig );
		$this->loader->add_action('admin_menu', $plugin_admin, 'register_wp_imports_admin_menu');
		$this->loader->add_action('wp_ajax_nopriv_RssImporter', $plugin_admin, 'admin_ajax_RssImporter');
		$this->loader->add_action('wp_ajax_RssImporter', $plugin_admin, 'admin_ajax_RssImporter');

		$registerEndpoint = new RSS_Importer_Rest_Endpoint($this->main);
		$this->loader->add_action('rest_api_init', $registerEndpoint, 'register_rss_importer_routes');

        //JOB UPDATE CHECKER
        $this->loader->add_action('init', $plugin_admin, 'rss_importer_update_checker');
        $this->loader->add_action('in_plugin_update_message-' . WP_RSS_FEED_IMPORTER_SLUG_PATH . '/' . WP_RSS_FEED_IMPORTER_SLUG_PATH . '.php', $plugin_admin, 'rss_importer_show_upgrade_notification', 10, 2);

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Wp_Rss_Feed_Importer_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	private function wp_rss_imports_db_handle() {
		global $wpRssImportDb;
		$wpRssImportDb = WP_RSS_Import_DB_Handle::instance( $this->main, $this->db_version );
		$this->loader->add_filter( $this->plugin_name . '/get_rss_import', $wpRssImportDb, 'getRssImportsByArgs', 10, 2 );
		$this->loader->add_filter( $this->plugin_name . '/set_rss_import', $wpRssImportDb, 'setRssImport' );
		$this->loader->add_filter( $this->plugin_name . '/update_rss_import', $wpRssImportDb, 'updateRssImport' );
		$this->loader->add_filter( $this->plugin_name . '/update_rss_last_import', $wpRssImportDb, 'updateRssLastImport' );
		$this->loader->add_filter( $this->plugin_name . '/delete_rss_import', $wpRssImportDb, 'deleteRssImport' );

		$this->loader->add_action( 'init', $wpRssImportDb, 'wp_rss_importer_check_jal_install');
	}

	/**
	 * Register API SRV Rest-Api Endpoints
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function register_wp_remote_exec() {
		global $wpRemoteExec;
		$wpRemoteExec = Make_Remote_Exec::instance( $this->plugin_name, $this->get_version(), $this->main );
	}

	/**
	 * Register WP_REST ENDPOINT
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function register_wp_rss_importer_rest_endpoint() {
		global $rss_importer_public_endpoint;
		$rss_importer_public_endpoint = new Srv_Api_Endpoint( $this->plugin_name, $this->version, $this->main );
		$this->loader->add_action( 'rest_api_init', $rss_importer_public_endpoint, 'register_routes' );

	}

	/**
	 * Register RSS Helper
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function register_wp_rss_importer_rss_helper() {
		global $rss_importer_helper;
		$rss_importer_helper = RSS_Importer_Helper::instance($this->plugin_name, $this->version, $this->main);
		$this->loader->add_filter($this->plugin_name . '/get_next_cron_time', $rss_importer_helper, 'import_get_next_cron_time');
		$this->loader->add_filter($this->plugin_name . '/get_import_post_types', $rss_importer_helper, 'fn_get_import_post_types', 10,2);
		$this->loader->add_filter($this->plugin_name . '/get_import_taxonomy', $rss_importer_helper, 'fn_get_import_taxonomy',10,2);
		$this->loader->add_filter($this->plugin_name . '/get_rss_channel', $rss_importer_helper, 'fn_get_rss_channel');

		$this->loader->add_filter($this->plugin_name . '/get_rss_post_meta', $rss_importer_helper, 'fn_get_rss_post_meta', 10,2);
	}
	/**
	 * Register RSS Gutenberg Tools
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function register_wp_rss_importer_gutenberg_tools() {
		$gbTools = new Register_RSS_Importer_Gutenberg_Tools($this->main);
		/*$this->loader->add_action('init', $gbTools, 'fn_rss_posts_meta_fields');
		$this->loader->add_action('init', $gbTools, 'rss_importer_gutenberg_register_sidebar');
		$this->loader->add_action('enqueue_block_editor_assets', $gbTools, 'rss_importer_sidebar_script_enqueue');
		*/
	}

	/**
	 * Register all the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function register_cron_rss_importer()
	{
		if ($this->check_wp_cron()) {
			$rssCron = new Import_RSS_Cronjob( $this->main);
			$this->loader->add_filter($this->plugin_name . '/rss_run_schedule_task', $rssCron, 'fn_rss_run_schedule_task');
			$this->loader->add_filter($this->plugin_name . '/rss_wp_un_schedule_task', $rssCron, 'fn_rss_wp_un_schedule_task');
			$this->loader->add_filter($this->plugin_name . '/rss_wp_delete_task', $rssCron, 'fn_rss_wp_delete_task');
		}
	}

	/**
	 * Register all the hooks related to the admin area functionality
	 * @access   private
	 */
	private function rss_import_cronjob()
	{
		global $rssImportExecute;
		$rssImportExecute = RSS_Import_Execute::instance($this->plugin_name,$this->version, $this->main);
		$this->loader->add_action('rss_import_sync', $rssImportExecute, 'rss_import_synchronisation',0);
		$this->loader->add_filter($this->plugin_name . '/make_feed_import', $rssImportExecute, 'fn_make_feed_import');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name(): string {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Wp_Rss_Feed_Importer_Loader    Orchestrates the hooks of the plugin.
	 * @since     1.0.0
	 */
	public function get_loader(): Wp_Rss_Feed_Importer_Loader {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version(): string {
		return $this->version;
	}

	/**
	 * @return Environment
	 */
	public function get_twig(): Environment {
		return $this->twig;
	}

	/**
	 * Retrieve the database version number of the plugin.
	 *
	 * @return    string    The database version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_db_version(): string {
		return $this->db_version;
	}

	public function get_plugin_api_config(): object {
		return $this->plugin_api_config;
	}

	/**
	 * @return bool
	 */
	private function check_wp_cron(): bool
	{
		if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
			return false;
		} else {
			return true;
		}
	}

}
