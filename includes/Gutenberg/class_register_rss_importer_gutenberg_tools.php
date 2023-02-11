<?php

class Register_RSS_Importer_Gutenberg_Tools
{

    protected Wp_Rss_Feed_Importer $main;

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
    protected string $version;

    public function __construct(string $version,string $basename,Wp_Rss_Feed_Importer $main)
    {
        $this->main = $main;
        $this->version = $version;
        $this->basename = $basename;
    }

    public function fn_register_gutenberg_object()
    {
        wp_register_script('rss-importer-gutenberg-localize', '', [], $this->version, true);
        wp_enqueue_script('rss-importer-gutenberg-localize');
        wp_localize_script('rss-importer-gutenberg-localize',
            'RSSEndpoint',
            array(
                'url' => esc_url_raw(rest_url('rss-importer/v1/')),
                'nonce' => wp_create_nonce('wp_rest')
            )
        );
    }

    public function rss_importer_gutenberg_register_sidebar(): void
    {
        //$plugin_asset = require WP_RSS_FEED_IMPORTER_PLUGIN_DIR . '/includes/Gutenberg/Sidebar/build/index.asset.php';
        /* wp_register_script(
             'rss-importer-sidebar',
             WP_RSS_FEED_IMPORTER_PLUGIN_URL . '/includes/Gutenberg/Sidebar/build/index.js',
             $plugin_asset['dependencies'], $plugin_asset['version'], true
         );*/


    }

    public function rss_importer_sidebar_script_enqueue()
    {
	    $plugin_asset = require WP_RSS_FEED_IMPORTER_PLUGIN_DIR . '/includes/Gutenberg/RssBlock/build/index.asset.php';

       /// wp_enqueue_script('rss-importer-sidebar');
        wp_enqueue_style('rss-importer-block-style');
        wp_enqueue_style(
            'rss-importer-block-style',
	        WP_RSS_FEED_IMPORTER_PLUGIN_URL . '/includes/Gutenberg/RssBlock/build/index.css', array(), $plugin_asset['version']);


    }

    /**
     * Register TAM MEMBERS REGISTER GUTENBERG BLOCK TYPE
     *
     * @since    1.0.0
     */
    public function register_rss_importer_block_type()
    {
        global $registerThemeCallback;
        register_block_type('rss/importer-block', array(
            'render_callback' => [$registerThemeCallback, 'callback_rss_importer_block_type'],
            'editor_script' => 'rss-importer-gutenberg-block',
        ));
        add_filter('gutenberg_block_rss_importer_callback', array($registerThemeCallback, 'gutenberg_block_rss_importer_filter'), 10, 5);
        //add_filter('gutenberg_block_rss_importer_callback', 'gutenberg_block_rss_importer_filter', 10, 5);
    }

    public function rss_importer_block_type_scripts(): void
    {
        $plugin_asset = require WP_RSS_FEED_IMPORTER_PLUGIN_DIR . '/includes/Gutenberg/RssBlock/build/index.asset.php';

        wp_enqueue_script(
            'rss-importer-gutenberg-block',
            WP_RSS_FEED_IMPORTER_PLUGIN_URL . '/includes/Gutenberg/RssBlock/build/index.js',
            $plugin_asset['dependencies'], $plugin_asset['version'], true
        );

        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations('rss-importer-gutenberg-block', 'wp-rss-feed-importer', WP_RSS_FEED_IMPORTER_PLUGIN_DIR . '/languages');
        }

        wp_localize_script('rss-importer-gutenberg-block',
            'RSSEndpoint',
            array(
                'url' => esc_url_raw(rest_url('rss-importer/v1/')),
                'nonce' => wp_create_nonce('wp_rest')
            )
        );

        wp_enqueue_style(
            'rss-importer-gutenberg-block',
            WP_RSS_FEED_IMPORTER_PLUGIN_URL . '/includes/Gutenberg/RssBlock/build/index.css', array(), $plugin_asset['version']);
    }


	public function fn_rss_posts_meta_fields(): void
	{
		register_meta(
			'post',
			'_rss_import_pubDate',
			array(
				'type' => 'string',
				//'object_subtype' => 'immo',
				'single' => true,
				'show_in_rest' => true,
				'default' => '',
				'auth_callback' => array($this, 'rss_post_permissions_check')
			)
		);
		register_meta(
			'post',
			'_rss_import_guid',
			array(
				'type' => 'string',
				//'object_subtype' => 'immo',
				'single' => true,
				'show_in_rest' => true,
				'default' => '',
				'auth_callback' => array($this, 'rss_post_permissions_check')
			)
		);
		register_meta(
			'post',
			'_rss_import_title',
			array(
				'type' => 'string',
				//'object_subtype' => 'immo',
				'single' => true,
				'show_in_rest' => true,
				'default' => '',
				'auth_callback' => array($this, 'rss_post_permissions_check')
			)
		);
		register_meta(
			'post',
			'_rss_import_link',
			array(
				'type' => 'string',
				//'object_subtype' => 'immo',
				'single' => true,
				'show_in_rest' => true,
				'default' => '',
				'auth_callback' => array($this, 'rss_post_permissions_check')
			)
		);
		register_meta(
			'post',
			'_rss_import_category',
			array(
				'type' => 'string',
				//'object_subtype' => 'immo',
				'single' => true,
				'show_in_rest' => true,
				'default' => '',
				'auth_callback' => array($this, 'rss_post_permissions_check')
			)
		);
		register_meta(
			'post',
			'_rss_import_id',
			array(
				'type' => 'string',
				//'object_subtype' => 'immo',
				'single' => true,
				'show_in_rest' => true,
				'default' => '',
				'auth_callback' => array($this, 'rss_post_permissions_check')
			)
		);
		register_meta(
			'post',
			'_rss_import_bezeichnung',
			array(
				'type' => 'string',
				//'object_subtype' => 'immo',
				'single' => true,
				'show_in_rest' => true,
				'default' => '',
				'auth_callback' => array($this, 'rss_post_permissions_check')
			)
		);
	}

	/**
	 * Check if a given request has access.
	 *
	 * @return bool
	 */
	public function rss_post_permissions_check(): bool
	{
		return current_user_can('edit_posts');
	}


}
