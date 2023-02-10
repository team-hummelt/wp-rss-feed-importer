<?php

class Register_RSS_Importer_Gutenberg_Tools
{

    protected Wp_Rss_Feed_Importer $main;

    public function __construct(Wp_Rss_Feed_Importer $main)
    {
        $this->main = $main;
    }


    public function rss_importer_gutenberg_register_sidebar(): void
    {
        $plugin_asset = require WP_RSS_FEED_IMPORTER_PLUGIN_DIR . '/includes/Gutenberg/Sidebar/build/index.asset.php';
        wp_register_script(
            'rss-importer-sidebar',
	        WP_RSS_FEED_IMPORTER_PLUGIN_URL . '/includes/Gutenberg/Sidebar/build/index.js',
            $plugin_asset['dependencies'], $plugin_asset['version'], true
        );

        wp_register_script('rss-importer-gutenberg-localize', '', [], $plugin_asset['version'], true);
        wp_enqueue_script('rss-importer-gutenberg-localize');
        wp_localize_script('rss-importer-gutenberg-localize',
            'rssRestObj',
            array(
                'url' => esc_url_raw(rest_url('rss-importer/v1/')),
                'nonce' => wp_create_nonce('wp_rest')
            )
        );
    }

    public function rss_importer_sidebar_script_enqueue()
    {
	    $plugin_asset = require WP_RSS_FEED_IMPORTER_PLUGIN_DIR . '/includes/Gutenberg/Sidebar/build/index.asset.php';
        wp_enqueue_script('rss-importer-sidebar');
        wp_enqueue_style('rss-importer-sidebar-style');
        wp_enqueue_style(
            'rss-importer-sidebar-style',
	        WP_RSS_FEED_IMPORTER_PLUGIN_URL . '/includes/Gutenberg/Sidebar/build/index.css', array(), $plugin_asset['version']);
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
