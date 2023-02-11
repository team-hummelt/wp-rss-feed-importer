<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wwdh.de
 * @since      1.0.0
 *
 * @package    Wp_Rss_Feed_Importer
 * @subpackage Wp_Rss_Feed_Importer/admin
 */

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use WPRSSFeddImporter\Ajax\WP_RSS_Imports_Admin_Ajax;
use WPRSSFeddImporter\FeedImporter\CronSettings;


/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Rss_Feed_Importer
 * @subpackage Wp_Rss_Feed_Importer/admin
 * @author     Jens Wiecker <wordpress@wwdh.de>
 */
class Wp_Rss_Feed_Importer_Admin {

	use CronSettings;

	/**
	 * Store plugin main class to allow admin access.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var Wp_Rss_Feed_Importer $main The main class.
	 */
	protected Wp_Rss_Feed_Importer $main;

	/**
	 * TWIG autoload for PHP-Template-Engine
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Environment $twig TWIG autoload for PHP-Template-Engine
	 */
	protected Environment $twig;

	/**
	 * The ID of this theme.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string $basename The ID of this theme.
	 */
	protected string $basename;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	protected $settings;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $basename The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( string $basename, string $version, Wp_Rss_Feed_Importer $main, Environment $twig ) {

		$this->basename = $basename;
		$this->version  = $version;
		$this->main     = $main;
		$this->twig     = $twig;
		$this->settings = $this->get_cron_defaults();

	}

	public function register_wp_imports_admin_menu(): void {
		if ( ! get_option( 'wp_rss_importer_settings' ) ) {
			update_option( 'wp_rss_importer_settings', $this->settings['cron_settings'] );
		}
		add_menu_page(
			__( 'RSS-Feed', 'wp-rss-feed-importer' ),
			__( 'RSS-Feed', 'wp-rss-feed-importer' ),
			get_option('wp_rss_importer_settings')['selected_user_role'],
			'wp-rss-imports',
			'',
			$this->get_svg_icons( 'rss' )
			, 22
		);

		$hook_suffix = add_submenu_page(
			'wp-rss-imports',
			__( 'RSS-Feed Settings', 'wp-rss-feed-importer' ),
			__( 'RSS-Feed Settings', 'wp-rss-feed-importer' ),
			get_option('wp_rss_importer_settings')['selected_user_role'],
			'wp-rss-imports',
			array( $this, 'wp_rss_imports_startseite' ) );

		add_action( 'load-' . $hook_suffix, array( $this, 'wp_rss_import_load_ajax_admin_options_script' ) );
	}

	public function wp_rss_imports_startseite(): void {
		$settings = $this->get_cron_defaults();

		if (!get_option('wp_rss_importer_settings')) {
			update_option('wp_rss_importer_settings', $settings['cron_settings']);
		}

        $helpExample = file_get_contents(WP_RSS_FEED_IMPORTER_PLUGIN_DIR . '/admin/partials/Templates/example-help.txt');
		$data = [
			's' => get_option('wp_rss_importer_settings'),
			'select' => $settings,
			'db' => WP_RSS_FEED_IMPORTER_DB_VERSION,
			'version' => $this->version,
            'help_example' => ($helpExample)
		];

		try {
			$template = $this->twig->render('@templates/rss-feed-html.twig', $data);
			echo $this->html_compress_template($template);
		} catch (LoaderError|SyntaxError|RuntimeError $e) {
			echo $e->getMessage();
		} catch (Throwable $e) {
			echo $e->getMessage();
		}
	}

	public function wp_rss_import_load_ajax_admin_options_script(): void {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		$title_nonce = wp_create_nonce( 'rss_import_admin_handle' );
		wp_register_script( 'rss-importer-admin-ajax-script', '', [], '', true );
		wp_enqueue_script( 'rss-importer-admin-ajax-script' );
		wp_localize_script( 'rss-importer-admin-ajax-script', 'rss_ajax_obj', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => $title_nonce,
			'data_table' => plugin_dir_url( __FILE__ ) . 'js/tools/DataTablesGerman.json',
			'js_lang' => $this->js_language()
		) );
	}

	/**
	 * @throws Exception
	 */
	public function admin_ajax_RssImporter(): void {
		check_ajax_referer( 'rss_import_admin_handle' );
		require 'Ajax/class_wp_rss_imports_admin_ajax.php';
		$adminAjaxHandle = WP_RSS_Imports_Admin_Ajax::admin_ajax_instance($this->basename, $this->main, $this->twig );
		wp_send_json( $adminAjaxHandle->admin_ajax_handle() );
	}

    /**
     * Register the Update-Checker for the Plugin.
     *
     * @since    1.0.0
     */
    public function rss_importer_update_checker()
    {

        if (get_option("{$this->basename}_update_config") && get_option($this->basename . '_update_config')->update->update_aktiv) {
            $securityHeaderUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
                get_option("{$this->basename}_update_config")->update->update_url_git,
                WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $this->basename . DIRECTORY_SEPARATOR . $this->basename . '.php',
                $this->basename
            );

            switch (get_option("{$this->basename}_update_config")->update->update_type) {
                case '1':
                    $securityHeaderUpdateChecker->getVcsApi()->enableReleaseAssets();
                    break;
                case '2':
                    $securityHeaderUpdateChecker->setBranch(get_option("{$this->basename}_update_config")->update->branch_name);
                    break;
            }
        }
    }

    /**
     * add plugin upgrade notification
     */

    public function rss_importer_show_upgrade_notification($current_plugin_metadata, $new_plugin_metadata)
    {

        if (isset($new_plugin_metadata->upgrade_notice) && strlen(trim($new_plugin_metadata->upgrade_notice)) > 0) {
            // Display "upgrade_notice".
            echo sprintf('<span style="background-color:#d54e21;padding:10px;color:#f9f9f9;margin-top:10px;display:block;"><strong>%1$s: </strong>%2$s</span>', esc_attr('Important Upgrade Notice', 'wp-security-header'), esc_html(rtrim($new_plugin_metadata->upgrade_notice)));

        }
    }


    /**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Rss_Feed_Importer_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Rss_Feed_Importer_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( 'jquery' );
		wp_enqueue_style( 'rss-importer-admin-bs-style', plugin_dir_url( __FILE__ ) . 'css/bs/bootstrap.min.css', array(), $this->version, false );
		wp_enqueue_style( 'rss-importer-animate', plugin_dir_url( __FILE__ ) . 'css/tools/animate.min.css', array(), $this->version );
		wp_enqueue_style( 'rss-importer-swal2', plugin_dir_url( __FILE__ ) . 'css/tools/sweetalert2.min.css', array(), $this->version, false );

		wp_enqueue_style( 'rss-importer-bootstrap-icons-style', WP_RSS_FEED_IMPORTER_PLUGIN_URL . 'includes/vendor/twbs/bootstrap-icons/font/bootstrap-icons.css', array(), $this->version );
		wp_enqueue_style( 'rss-importer-font-awesome-icons-style', WP_RSS_FEED_IMPORTER_PLUGIN_URL . 'includes/vendor/components/font-awesome/css/font-awesome.min.css', array(), $this->version );
		wp_enqueue_style( 'rss-importer-admin-dashboard-style', plugin_dir_url( __FILE__ ) . 'css/admin-dashboard-style.css', array(), $this->version, false );
		wp_enqueue_style( 'rss-importer-admin-data-table', plugin_dir_url( __FILE__ ) . 'css/tools/dataTables.bootstrap5.min.css', array(), $this->version, false );

		wp_enqueue_script( 'rss-importer-bs', plugin_dir_url( __FILE__ ) . 'js/bs/bootstrap.bundle.min.js', array(), $this->version, true );
		wp_enqueue_script( 'rss-importer-swal2-script', plugin_dir_url( __FILE__ ) . 'js/tools/sweetalert2.all.min.js', array(), $this->version, true );
		wp_enqueue_script( 'js-hupa-data-table', plugin_dir_url( __FILE__ ) . 'js/tools/data-table/jquery.dataTables.min.js', array(), $this->version, true );
		wp_enqueue_script( 'js-hupa-bs-data-table', plugin_dir_url( __FILE__ ) . 'js/tools/data-table/dataTables.bootstrap5.min.js', array(), $this->version, true );
		wp_enqueue_script( 'admin-rss-importer-table', plugin_dir_url( __FILE__ ) . '/js/rssTable.js', false, $this->version, true );
		wp_enqueue_script( $this->basename, plugin_dir_url( __FILE__ ) . 'js/wp-rss-feed-importer-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * @param $name
	 *
	 * @return string
	 */
	private static function get_svg_icons( $name ): string {
		$icon = '';
		switch ( $name ) {
			case'rss':
				$icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-rss" viewBox="0 0 16 16">
                         <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                         <path d="M5.5 12a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm-3-8.5a1 1 0 0 1 1-1c5.523 0 10 4.477 10 10a1 1 0 1 1-2 0 8 8 0 0 0-8-8 1 1 0 0 1-1-1zm0 4a1 1 0 0 1 1-1 6 6 0 0 1 6 6 1 1 0 1 1-2 0 4 4 0 0 0-4-4 1 1 0 0 1-1-1z"/>
                         </svg>';
				break;

			default:
		}

		return 'data:image/svg+xml;base64,' . base64_encode( $icon );

	}

	protected function html_compress_template( string $string ): string {
		if ( ! $string ) {
			return $string;
		}

		return preg_replace( [ '/<!--(.*)-->/Uis', "/[[:blank:]]+/" ], [ '', ' ' ], str_replace( [
			"\n",
			"\r",
			"\t"
		], '', $string ) );
	}

}
