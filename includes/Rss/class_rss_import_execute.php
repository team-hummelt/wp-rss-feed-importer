<?php

namespace WPRSSFeddImporter\FeedImporter;


use stdClass;
use Wp_Rss_Feed_Importer;

class RSS_Import_Execute
{
    private static $instance;
    protected Wp_Rss_Feed_Importer $main;
    private $settings;
    private static bool $log_aktiv = true;

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
        $this->settings = $this->get_cron_defaults();
    }


    public function rss_import_synchronisation(): void
    {
        $this->fn_make_feed_import();
    }

    public function fn_make_feed_import($id = NULL): bool
    {
        if ($id) {
            $args = sprintf('WHERE r.id=%d', $id);
        } else {
            $args = 'WHERE r.active=1';
        }

        $feeds = apply_filters($this->basename.'/get_rss_import', $args);
        if (!$feeds->status) {
            return false;
        }
        $feeds = $feeds->record;
        $feedArr = [];
        $i = 1;
        foreach ($feeds as $feed) {
            $rss = $this->get_rss_feed($feed->source);
            $channel = json_decode($feed->rss_channel, true);

            $postArr = [];
            foreach ($rss as $tmp) {
                $checkIsFeed = $this->get_check_rss_post($feed->post_type, 1, $tmp['guid'], strtotime($tmp['pubDate']));
                if ($checkIsFeed) {
                    continue;
                }

                $selectPostStatus = $this->get_cron_defaults('select_post_status', $feed->post_status);
                $selectPostStatus ? $post_status = $selectPostStatus['value'] : $post_status = 'publish';

                switch ($feed->post_title) {
                    case '1':
                        $title = $tmp['title'];
                        break;
                    case '2':
                        $channel['title'] ? $title = $channel['title'] : $title = date('Y-m-d H-i-s', current_time('timestamp'));
                        break;
                    case'3':
                        $title = $tmp['pubDate'];
                        break;
                    default:
                        $title = $tmp['title'];
                }

                $slContent = $this->get_cron_defaults('select_post_content', $feed->post_content);
                $now = current_time('mysql');
                if ($feed->post_date == 1) {
                    $post_date = date('Y-m-d H:i:s', strtotime($tmp['pubDate']));
                } else {
                    $post_date = $now;
                }

                if ($feed->delete_old_post) {
                    $days = sprintf(" -%d days", $feed->delete_old_post);
                    $oldDate = strtotime(current_time('mysql') . $days);
                    if (strtotime($tmp['pubDate']) < $oldDate) {
                        continue;
                    }
                }

                $postItem = [
                    'post_type' => $feed->post_type,
                    'term_id' => $feed->post_taxonomy,
                    'post_status' => $post_status,
                    'post_title' => $title,
                    'post_content' => $tmp[$slContent['type']],
                    'post_date' => $post_date,
                    'post_date_gmt' => $post_date,
                    '_guid' => $tmp['guid'],
                    '_pubDate' => strtotime($tmp['pubDate']),
                    '_title' => $tmp['title'],
                    '_link' => $tmp['link'],
                    '_description' => $tmp['description'],
                    '_category' => $tmp['category'],
                    '_import_id' => $feed->id,
                    '_import_bezeichnung' => $feed->bezeichnung
                ];
                $postArr[] = $postItem;
                if ($i >= $feed->max_cron_import) {
                    break;
                }
                $i++;
            }

            if ($feed->delete_old_post) {
                $days = sprintf(" -%d days", $feed->delete_old_post);
                $oldDate = strtotime(current_time('mysql') . $days);
                $this->get_old_rss_date_post($feed->post_type, -1, date('Y-m-d', $oldDate), true);
            }
            $feedArr[] = $postArr;
        }


        $insertUpdate = new stdClass();
        if ($feedArr) {
            foreach ($feedArr as $tmp) {
                foreach ($tmp as $val) {
                    $term = get_term( $val['term_id'] );
                    $args = [
                        'post_type' => $val['post_type'],
                        'post_title' => $val['post_title'],
                        'post_content' => $val['post_content'],
                        'post_status' => $val['post_status'],
                        'post_date' => $val['post_date'],
                        'post_category' => array((int)$val['term_id']),
                        'comment_status' => 'closed',
                        'post_excerpt' => $val['_description'],
                        'meta_input' => [
                            '_rss_import_pubDate' => $val['_pubDate'],
                            '_rss_import_guid' => $val['_guid'],
                            '_rss_import_title' => $val['_title'],
                            '_rss_import_link' => $val['_link'],
                            '_rss_import_category' => $val['_category'],
                            '_rss_import_id' => $val['_import_id'],
                            '_rss_import_bezeichnung' => $val['_import_bezeichnung']
                        ]
                    ];

                    $postId = wp_insert_post($args, true);
                    $insertUpdate->last_import = current_time('timestamp');
                    $insertUpdate->id = $val['_import_id'];
                    if (is_wp_error($postId)) {
                        $insertUpdate->last_status = 0;
                        $errMsg = 'import-error|' . $postId->get_error_message() . '|ID|' . $val['_import_id'] . '|line|' . __LINE__;
                        self::rss_import_log($errMsg);
                    } else {
                        $insertUpdate->last_status = true;
                        wp_set_object_terms($postId, array($term->term_id), $term->taxonomy);
                    }
                    apply_filters($this->basename.'/update_rss_last_import', $insertUpdate);
                }
            }
        }

        return true;
    }

    private function get_check_rss_post($post_type, $number_posts, $guidId, $datetime, $delete = false): array
    {
        $args = array(
            'post_type' => $post_type,
            'numberposts' => $number_posts,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_rss_import_guid',
                    'value' => $guidId,
                    'compare' => '=='
                ),
                array(
                    'key' => '_rss_import_pubDate',
                    'value' => $datetime,
                    'compare' => '=',
                )
            )
        );

        $posts = get_posts($args);
        if ($delete) {
            foreach ($posts as $tmp) {
                $this->rss_feed_delete_post($tmp->ID);
            }

            return [];
        }
        return $posts;
    }

    private function get_old_rss_date_post($post_type, $number_posts, $date, $delete = false): array
    {
        $args = array(
            'post_type' => $post_type,
            'numberposts' => $number_posts,
            'date_query' => array(
                'before' => $date
            ),
        );
        $posts = get_posts($args);
        if ($delete) {
            foreach ($posts as $tmp) {
                $this->rss_feed_delete_post($tmp->ID);
            }

            return [];
        }

        return $posts;

    }

    private function rss_feed_delete_post($postId)
    {
        wp_delete_post($postId, true);
    }

    private function get_rss_feed($feed_url): array
    {
        $feeds = file_get_contents($feed_url);
        $feeds = str_replace("<content:encoded>", "<content>", $feeds);
        $feeds = str_replace("</content:encoded>", "</content>", $feeds);
        $rss = simplexml_load_string($feeds, 'SimpleXMLElement', LIBXML_NOCDATA);

        $feedArr = [];
        foreach ($rss->channel->item as $entry) {
            $entry = $this->xml2array($entry);
            $d = date('d-m-Y H:i:s', strtotime($entry['pubDate']));
            $item = [
                'guid' => $entry['guid'],
                'pubDate' => $d,
                'title' => $entry['title'],
                'link' => $entry['link'],
                'description' => $entry['description'],
                'content' => $entry['content'],
                'category' => $entry['category']
            ];
            $feedArr[] = $item;
        }
        if ($feedArr) {
            return $feedArr;
        }
        return [];
    }

    private function xml2array($xmlObject, $out = array()): array
    {
        foreach ((array)$xmlObject as $index => $node)
            $out[$index] = (is_object($node)) ? $this->xml2array($node) : $node;

        return $out;
    }

    private function object2array_recursive($object)
    {
        return json_decode(json_encode($object), true);
    }

    public static function rss_import_log($msg, $type = 'import_error.log')
    {
        if (self::$log_aktiv) {
            $logDir = WP_RSS_FEED_IMPORTER_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR;
            if (!$logDir) {
                mkdir($logDir, 0777, true);
            }
            if(!is_file($logDir . '.htaccess')){
                $htaccess = 'Require all denied';
                file_put_contents($logDir . '.htaccess', $htaccess);
            }

            $log = 'LOG: ' . current_time('mysql') . '|' . $msg . "\r\n";
            $log .= '-------------------' . "\r\n";
            file_put_contents($logDir . $type, $log, FILE_APPEND | LOCK_EX);
        }
    }
}
