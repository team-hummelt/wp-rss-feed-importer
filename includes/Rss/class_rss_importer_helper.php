<?php
namespace WPRSSFeddImporter\FeedImporter;

use stdClass;
use Wp_Rss_Feed_Importer;
use WP_Term_Query;

class RSS_Importer_Helper
{
	private static $instance;
	protected Wp_Rss_Feed_Importer $main;
	use CronSettings;

	/**
	 * The ID of this Plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $basename The ID of this Plugin.
	 */
	protected string $basename;

	/**
	 * The version of this Plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this theme.
	 */
	protected string $version;

	/**
	 * @return static
	 */
	public static function instance(string $basename, string $version, Wp_Rss_Feed_Importer $main): self
	{
		if (is_null(self::$instance)) {
			self::$instance = new self($basename, $version, $main);
		}
		return self::$instance;
	}

	public function __construct(string $basename, string $version,Wp_Rss_Feed_Importer $main)
	{
		$this->main = $main;
		$this->basename = $basename;
		$this->version = $version;
	}

	public function import_get_next_cron_time(string $cron_name)
	{
		foreach (_get_cron_array() as $timestamp => $crons) {
			if (in_array($cron_name, array_keys($crons))) {
				return $timestamp - time();
			}
		}
		return false;
	}

	public function fn_get_rss_import_post_type($values)
	{
		$args = array(
			'post_type' => $values->post_type,
			'numberposts' => $values->number_posts,

		);

		$posts = get_posts($args);
	}

	public function fn_get_import_post_types($type = '', $term_id = ''): array
	{
		$args = array(
			'public' => true,
			'_builtin' => false,
		);

		$output = 'objects'; // names or objects, note names is the default
		$operator = 'and'; // 'and' or 'or'
		$post_types = get_post_types($args, $output, $operator);
		$notTypes = ['starter_header', 'starter_footer', 'hupa_design', 'page'];
		$typesArr = [];
		foreach ($post_types as $tmp) {
			if (in_array($tmp->name, $notTypes)) {
				continue;
			}
			$postItems = [
				'post_type' => $tmp->name,
				'label' => $tmp->label,
				'taxonomie' => $tmp->taxonomies[0]
			];
			$typesArr[] = $postItems;
		}
		if($type){
			$term_args = array(
				'term_id' => $term_id,
				'hide_empty' => false,
				'fields' => 'all'
			);
		} else {
			$term_args = array(
				'taxonomy' => $typesArr[0]['taxonomie'],
				'hide_empty' => false,
				'fields' => 'all'
			);
		}


		$term_query = new WP_Term_Query($term_args);
		$cats = $term_query->terms;


		$post = [
			'post_type' => 'post',
			'label' => 'Beiträge',
			'taxonomie' => 'category'
		];

		$typesArr[] = $post;
		$return = [];
		$return['post_type'] = $typesArr;
		$return['post_taxonomies'] = $cats;

		return $return;

	}

	public function fn_get_import_taxonomy($taxonomie, $post_type): array
	{
		if (!$taxonomie || !$post_type) {
			return [];
		}

		$term_args = array(
			'post_type' => $post_type,
			'taxonomy' => $taxonomie,
			'hide_empty' => false,
			'fields' => 'all'
		);

		$term_query = new WP_Term_Query($term_args);
		$taxArr = [];

		if(!$term_query->terms){
			return $taxArr;
		}

		foreach ($term_query->terms as $tmp) {
			$item = [
				'term_id' => $tmp->term_id,
				'slug' => $tmp->slug,
				'name' => $tmp->name
			];
			$taxArr[] = $item;
		}

		return $taxArr;
	}

	public function fn_get_rss_channel($feed_url):array
	{
		if (!$feed_url) {
			return [];
		}
		$feeds = file_get_contents($feed_url);
		$rss = simplexml_load_string($feeds);
		$rss = $this->object2array_recursive($rss);
		$rss['channel']['pubDate'] ? $pubDate = date('d-m-Y H:i:s', strtotime($rss['channel']['pubDate'])) : $pubDate = '';
		$rss['channel']['lastBuildDate'] ? $lastBuildDate = date('d-m-Y H:i:s', strtotime($rss['channel']['lastBuildDate'])) : $lastBuildDate = '';

		return [
			'title' => htmlspecialchars($rss['channel']['title']),
			'link' => htmlspecialchars($rss['channel']['link']),
			'language' => htmlspecialchars($rss['channel']['language']),
			'description' => $rss['channel']['description'],
			'copyright' => htmlspecialchars($rss['channel']['copyright']),
			'pubDate' => $pubDate,
			'lastBuildDate' => $lastBuildDate,
			'generator' => htmlspecialchars($rss['channel']['generator'])
		];
	}

    public function get_rss_import_post_meta($postId, $type = null):object
    {
        $return = new stdClass();
        $return->status = false;
        if(!$postId){
            return $return;
        }
        $return->_rss_import_pubDate = get_post_meta($postId, '_rss_import_pubDate', true);
        $return->_rss_import_guid = get_post_meta($postId, '_rss_import_guid', true);
        $return->_rss_import_title = get_post_meta($postId, '_rss_import_title', true);
        $return->_rss_import_link = get_post_meta($postId, '_rss_import_link', true);
        $return->_rss_import_category = get_post_meta($postId, '_rss_import_category', true);
        $return->_rss_import_id = get_post_meta($postId, '_rss_import_id', true);
        $return->_rss_import_bezeichnung = get_post_meta($postId, '_rss_import_bezeichnung', true);
        $return->status = true;
        if($type){
            return $return->$type;
        }
        return $return;
    }


	public function object2array_recursive($object)
	{
		return json_decode(json_encode($object), true);
	}
}