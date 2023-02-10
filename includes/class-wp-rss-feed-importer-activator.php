<?php

/**
 * Fired during plugin activation
 *
 * @link       https://wwdh.de
 * @since      1.0.0
 *
 * @package    Wp_Rss_Feed_Importer
 * @subpackage Wp_Rss_Feed_Importer/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wp_Rss_Feed_Importer
 * @subpackage Wp_Rss_Feed_Importer/includes
 * @author     Jens Wiecker <wordpress@wwdh.de>
 */
class Wp_Rss_Feed_Importer_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		self::register_wp_rss_feed_news_post_type();
		self::register_wp_rss_feed_news_taxonomy();
		flush_rewrite_rules();

		$srvLog = self::plugin_dir() . 'log';
		if (!is_dir($srvLog)) {
			mkdir($srvLog, 0777, true);
		}
		if (!is_file($srvLog . '/.htaccess')) {
			$htaccess = 'Require all denied';
			file_put_contents($srvLog . DIRECTORY_SEPARATOR . '.htaccess', $htaccess);
		}
		self::activated_api_plugin();
	}

	private static function activated_api_plugin()
	{
		$idRsa = self::plugin_dir() . 'id_rsa/public_id_rsa';
		if (is_file($idRsa)) {
			$idRsa = base64_encode(file_get_contents($idRsa));

			self::get_srv_api_data($idRsa);
		}
	}

	private static function get_srv_api_data($idRsa)
	{
		$url = 'https://start.hu-ku.com/theme-update/api/v2/public/token/' . $idRsa;
		$args = [
			'method' => 'GET',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'sslverify' => true,
			'blocking' => true,
			'body' => []
		];

		$response = wp_remote_get($url, $args);
		if (is_wp_error($response)) {
			$message = 'error|'.date('d.m.Y H:i:s', current_time('timestamp')).'|' . $response->get_error_message()."\n";
			file_put_contents(self::plugin_dir() . 'log' . DIRECTORY_SEPARATOR . 'api.log', $message);
			return;
		}

		if (isset($response['body'])) {
			$response = json_decode($response['body']);
			if($response->access_token){
				self::send_api_plugin_aktiviert($response->access_token);
			}
		}
	}

	private static function send_api_plugin_aktiviert($token)
	{
		$log = '';
		$plugin = get_file_data(plugin_dir_path(dirname(__FILE__)) . WP_RSS_FEED_IMPORTER_BASENAME . '.php', array('Version' => 'Version'), false);
		$l = self::plugin_dir() . 'log' . DIRECTORY_SEPARATOR . 'api.log';
		if(is_file($l)){
			$log = file($l);
			$log = json_encode($log);
		}

		$body = [
			'basename' => WP_RSS_FEED_IMPORTER_BASENAME,
			'type' => 'activates',
			'site_url' => site_url(),
			'version' => $plugin['Version'],
			'command' => 'plugin_aktiviert',
			'log' => $log
		];
		$args = [
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'sslverify' => true,
			'headers' => [
				'Content-Type' => 'application/x-www-form-urlencoded',
				'Authorization' => "Bearer $token"
			],
			'body' => $body
		];
		$response = wp_remote_post('https://start.hu-ku.com/theme-update/api/v2/public', $args);
		if (is_wp_error($response)) {
			$message = 'error|'.date('d.m.Y H:i:s', current_time('timestamp')).'|' . $response->get_error_message()."\n";
			file_put_contents(self::plugin_dir() . 'log' . DIRECTORY_SEPARATOR . 'api.log', $message);
			return;
		}
		if (isset($response['body'])) {
			$response = json_decode($response['body']);
			if($response->status){
				$message = 'aktiviert|'.date('d.m.Y H:i:s', current_time('timestamp'))."\n";
				file_put_contents(self::plugin_dir() . 'log' . DIRECTORY_SEPARATOR . 'api.log', $message, FILE_APPEND);
			}
		}
	}

	public static function register_wp_rss_feed_news_post_type()
	{
		register_post_type(
			'rss_news',
			array(
				'labels' => array(
					'name' => __('News', 'wp-rss-feed-importer'),
					'singular_name' => __('News', 'wp-rss-feed-importer'),
					'menu_name' => __('News', 'wp-rss-feed-importer'),
					'parent_item_colon' => __('Parent Item:', 'wp-rss-feed-importer'),
					'edit_item' => __('Edit', 'wp-rss-feed-importer'),
					'update_item' => __('Actualize', 'wp-rss-feed-importer'),
					'all_items' => __('All News', 'wp-rss-feed-importer'),
					'items_list_navigation' => __('News Posts navigation', 'wp-rss-feed-importer'),
					'add_new_item' => __('Add new post', 'wp-rss-feed-importer'),
					'archives' => __('News Posts Archives', 'wp-rss-feed-importer'),
				),
				'public' => true,
				'publicly_queryable' => true,
				'show_in_rest' => true,
				'show_ui' => true,
				'show_in_menu' => true,
				'has_archive' => true,
				'query_var' => true,
				'show_in_nav_menus' => true,
				'exclude_from_search' => false,
				'hierarchical' => false,
				'capability_type' => 'post',
				'menu_icon' => WP_RSS_FEED_IMPORTER_PLUGIN_URL.'admin/icons/news.svg',
				'menu_position' => 5,
				'can_export' => true,
				'show_in_admin_bar' => true,
				'supports' => array(
					'title', 'excerpt', 'page-attributes', 'editor', 'thumbnail', 'custom-fields'
				),
				'taxonomies' => array('rss_news_category'),
			)
		);
	}
	public static function register_wp_rss_feed_news_taxonomy()
	{
		$labels = array(
			'name' => __('News categories', 'wp-rss-feed-importer'),
			'singular_name' => __('News category', 'wp-rss-feed-importer'),
			'search_items' => __('Search News Category', 'wp-rss-feed-importer'),
			'all_items' => __('All news categories', 'wp-rss-feed-importer'),
			'parent_item' => __('Parent category', 'wp-rss-feed-importer'),
			'parent_item_colon' => __('Parent category:', 'wp-rss-feed-importer'),
			'edit_item' => __('Edit News Category', 'wp-rss-feed-importer'),
			'update_item' => __('News category update', 'wp-rss-feed-importer'),
			'add_new_item' => __('Add new news category', 'wp-rss-feed-importer'),
			'new_item_name' => __('New News Category', 'wp-rss-feed-importer'),
			'menu_name' => __('News category', 'wp-rss-feed-importer'),
		);

		$args = array(
			'labels' => $labels,
			'hierarchical' => true,
			'public' => false,
			'show_ui' => true,
			'sort' => true,
			'show_in_rest' => true,
			'query_var' => true,
			'args' => array('orderby' => 'term_order'),
			'show_admin_column' => true,
			'publicly_queryable' => true,
			'show_in_nav_menus' => true,
		);
		register_taxonomy('rss_news_category', array('attachment', 'rss_news'), $args);

		$terms = [
			'0' => [
				'name' => __('General', 'wp-rss-feed-importer'),
				'slug' => __('general', 'wp-rss-feed-importer')
			]
		];

		foreach ($terms as $term) {
			if (!term_exists($term['name'], 'rss_news_category')) {
				wp_insert_term(
					$term['name'],
					'rss_news_category',
					array(
						'description' => __('News category', 'wp-rss-feed-importer'),
						'slug' => $term['slug']
					)
				);
			}
		}
	}

	private static function plugin_dir():string
	{
		return plugin_dir_path(__DIR__) . 'admin' . DIRECTORY_SEPARATOR . 'srv-api' . DIRECTORY_SEPARATOR;
	}
}
