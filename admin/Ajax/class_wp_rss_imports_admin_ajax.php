<?php

namespace WPRSSFeddImporter\Ajax;

/**
 * The admin-specific functionality of the theme.
 *
 * @link       https://wwdh.de
 */

defined('ABSPATH') or die();

use Exception;

use Wp_Rss_Feed_Importer;
use stdClass;
use Throwable;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use WP_Term_Query;
use WPRSSFeddImporter\FeedImporter\CronSettings;

class WP_RSS_Imports_Admin_Ajax
{
    private static $admin_ajax_instance;
    private string $method;
    private object $responseJson;
    use CronSettings;

    /**
     * Store plugin main class to allow child access.
     *
     * @var Environment $twig TWIG autoload for PHP-Template-Engine
     */
    protected Environment $twig;

    protected Wp_Rss_Feed_Importer $main;

	/**
	 * The ID of this Plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $basename The ID of this theme.
	 */
	protected string $basename;

    /**
     * @return static
     */
    public static function admin_ajax_instance(string $basename, Wp_Rss_Feed_Importer $main, Environment $twig): self
    {
        if (is_null(self::$admin_ajax_instance)) {
            self::$admin_ajax_instance = new self($basename, $main, $twig);
        }
        return self::$admin_ajax_instance;
    }

    public function __construct(string $basename, Wp_Rss_Feed_Importer $main, Environment $twig)
    {
        $this->main = $main;
        $this->twig = $twig;
		$this->basename = $basename;
        $this->method = filter_input(INPUT_POST, 'method', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_HIGH);
        $this->responseJson = (object)['status' => false, 'msg' => date('H:i:s'), 'type' => $this->method];
    }

    /**
     * @throws Exception
     */
    public function admin_ajax_handle()
    {
        if (!method_exists($this, $this->method)) {
            throw new Exception("Method not found!#Not Found");
        }
        return call_user_func_array(self::class . '::' . $this->method, []);
    }

    private function import_settings():object
    {
        $this->responseJson->target =  filter_input(INPUT_POST, 'target', FILTER_UNSAFE_RAW);
        $this->responseJson->parent =  filter_input(INPUT_POST, 'parent', FILTER_UNSAFE_RAW);

        $nextTime = apply_filters( $this->basename . '/get_next_cron_time', 'rss_import_sync');
        $next_time = date('Y-m-d H:i:s', current_time('timestamp') + $nextTime);
        $next_date = date('d.m.Y', strtotime($next_time));
        $next_clock = date('H:i:s', strtotime($next_time));

        $data = [
            's' => get_option('wp_rss_importer_settings'),
            'select' => $this->get_cron_defaults(),
            'dateTime' => $next_time
        ];
        try {
            $template = $this->twig->render('@templates/rss-import-cron.html.twig', $data);
            $this->responseJson->template = $this->html_compress_template($template);
        } catch (LoaderError|SyntaxError|RuntimeError $e) {
            $this->responseJson->msg = $e->getMessage();
            return $this->responseJson;
        } catch (Throwable $e) {
            $this->responseJson->msg = $e->getMessage();
            return $this->responseJson;
        }

        $this->responseJson->next_date = $next_date;
        $this->responseJson->next_clock = $next_clock;
        $this->responseJson->next_time = $next_time;
        $this->responseJson->status = true;
        return $this->responseJson;
    }

    private function import_handle():object
    {
        $this->responseJson->target =  filter_input(INPUT_POST, 'target', FILTER_UNSAFE_RAW);
        $this->responseJson->parent =  filter_input(INPUT_POST, 'parent', FILTER_UNSAFE_RAW);
        $handle = filter_input(INPUT_POST, 'handle', FILTER_UNSAFE_RAW);

        if(!$handle) {
            $this->responseJson->msg = 'Ajax Übertragungsfehler (Ajx - '.__LINE__.')';
            return $this->responseJson;
        }

        $postTypes = apply_filters($this->basename.'/get_import_post_types', '');

        $import = [];
        if($handle == 'update'){
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if(!$id){
                $this->responseJson->msg = 'Ajax Übertragungsfehler (Ajx - '.__LINE__.')';
                return $this->responseJson;
            }
            $args = sprintf('WHERE r.id=%d', $id);
            $updData = apply_filters($this->basename.'/get_rss_import', $args, false);
            if(!$updData->status){
                $this->responseJson->msg = 'Ajax Übertragungsfehler (Ajx - '.__LINE__.')';
                return $this->responseJson;
            }
            $updData = $updData->record;

            $term = get_term( $updData->post_taxonomy );
            $term_args = array(
                'taxonomy' => $term->taxonomy,
                'hide_empty' => false,
                'fields' => 'all'
            );
            $term_query = new WP_Term_Query($term_args);
            $cats = $term_query->terms;
            $postTypes['post_taxonomies'] = $cats;

            $import = json_decode(json_encode($updData), true);
            $import['rss_channel'] = json_decode($import['rss_channel'], true);
        }


        $data = [
            'handle' => $handle,
            's' => $this->get_cron_defaults(),
            'types' => $postTypes,
            'i' => $import
        ];

        try {
            $template = $this->twig->render('@templates/add-rss-import.html.twig', $data);
            $this->responseJson->template = $this->html_compress_template($template);
        } catch (LoaderError|SyntaxError|RuntimeError $e) {
            $this->responseJson->msg = $e->getMessage();
            return $this->responseJson;
        } catch (Throwable $e) {
            $this->responseJson->msg = $e->getMessage();
            return $this->responseJson;
        }

        $this->responseJson->status = true;
        return $this->responseJson;
    }

    private function get_taxonomy_select():object
    {
        $type = filter_input(INPUT_POST, 'post_type', FILTER_UNSAFE_RAW);
        $tax = explode('#', $type);
        if(!isset($tax[1]) && !isset($tax[0])){
            return $this->responseJson;
        }
        $taxonomy = apply_filters($this->basename.'/get_import_taxonomy',$tax[1], $tax[0]);

        if($taxonomy){
           $this->responseJson->status = true;
           $this->responseJson->select = $taxonomy;
        }

        return $this->responseJson;
    }

    private function get_next_sync_time():object
    {
        $this->responseJson->target =  filter_input(INPUT_POST, 'target', FILTER_UNSAFE_RAW);
        $nextTime = apply_filters( $this->basename.'/get_next_cron_time', 'rss_import_sync');
        $next_time = date('Y-m-d H:i:s', current_time('timestamp') + $nextTime);
        $next_date = date('d.m.Y', strtotime($next_time));
        $next_clock = date('H:i:s', strtotime($next_time));
        $this->responseJson->next_date = $next_date;
        $this->responseJson->next_clock = $next_clock;
        $this->responseJson->next_time = $next_time;
        $this->responseJson->status = true;
        return $this->responseJson;
    }

    private function update_cron_settings():object
    {

        filter_input(INPUT_POST, 'active', FILTER_UNSAFE_RAW) ? $active = 1 : $active = 0;
        $selected_cron_sync_interval = filter_input(INPUT_POST, 'selected_cron_sync_interval', FILTER_UNSAFE_RAW);
        $max_post_sync_selected = filter_input(INPUT_POST, 'max_post_sync_selected', FILTER_VALIDATE_INT);

        $selected_cron_sync_interval ? $cron_sync_interval = $selected_cron_sync_interval : $cron_sync_interval = 'twicedaily';
        $max_post_sync_selected ? $max_sync_selected = $max_post_sync_selected : $max_sync_selected = 10;

        if(!$active){
            wp_clear_scheduled_hook('rss_import_sync');
        }
        $settings = get_option('wp_rss_importer_settings');

        if($settings['selected_cron_sync_interval'] != $cron_sync_interval) {
            wp_clear_scheduled_hook('wp_rss_importer_settings');
            apply_filters($this->basename.'/rss_run_schedule_task', false);
        }

        $settings['aktiv'] = $active;
        $settings['selected_cron_sync_interval'] = $cron_sync_interval;
        $settings['max_post_sync_selected'] = $max_sync_selected;
        update_option('wp_rss_importer_settings', $settings);
        $this->responseJson->status = true;
        $this->responseJson->title = 'Cron Settings';
        $this->responseJson->msg = 'Synchronisation-Einstellungen erfolgreich gespeichert.';
        return  $this->responseJson;
    }

    private function rss_import_handle() :object
    {
        $record = new stdClass();
        $handle = filter_input(INPUT_POST, 'handle', FILTER_UNSAFE_RAW);
        $bezeichnung = filter_input(INPUT_POST, 'bezeichnung', FILTER_UNSAFE_RAW);
        $source = filter_input(INPUT_POST, 'source', FILTER_VALIDATE_URL);
        $date_from = filter_input(INPUT_POST, 'date_from', FILTER_UNSAFE_RAW);
        $date_to = filter_input(INPUT_POST, 'date_to', FILTER_UNSAFE_RAW);

        $post_type = filter_input(INPUT_POST, 'post_type', FILTER_UNSAFE_RAW);

        $post_taxonomy = filter_input(INPUT_POST, 'post_taxonomy', FILTER_VALIDATE_INT);
        $post_status = filter_input(INPUT_POST, 'post_status', FILTER_VALIDATE_INT);
        $post_title = filter_input(INPUT_POST, 'post_title', FILTER_VALIDATE_INT);
        $post_content = filter_input(INPUT_POST, 'post_content', FILTER_VALIDATE_INT);
        $post_date = filter_input(INPUT_POST, 'post_date', FILTER_VALIDATE_INT);
        $max_post_sync_selected = filter_input(INPUT_POST, 'max_post_sync_selected', FILTER_VALIDATE_INT);
        $auto_delete = filter_input(INPUT_POST, 'auto_delete', FILTER_VALIDATE_INT);
        filter_input(INPUT_POST, 'aktiv', FILTER_UNSAFE_RAW) ? $record->active = 1 : $record->active = 0;
        filter_input(INPUT_POST, 'delete_double', FILTER_UNSAFE_RAW) ? $record->remove_duplicate = 1 : $record->remove_duplicate = 0;

		if($auto_delete < 1) {
			$auto_delete = 0;
		}

        $this->responseJson->title = 'FEHLER';
        if(!$handle) {
            $this->responseJson->msg = __('Ajax transmission error', 'wp-rss-feed-importer') .' (Ajx - '.__LINE__.')';
            return $this->responseJson;
        }
        if(!$bezeichnung){
            $this->responseJson->msg = __('Invalid designation', 'wp-rss-feed-importer') .'! (Ajx - '.__LINE__.')';
            return $this->responseJson;
        }
        if(!$source){
            $this->responseJson->msg = 'Ungültige RSS-Feed Url! (Ajx - '.__LINE__.')';
            return $this->responseJson;
        }
        if(!$post_type) {
            $this->responseJson->msg = 'Ungültiger Post Type! (Ajx - '.__LINE__.')';
            return $this->responseJson;
        }
        if(!$post_taxonomy){
            $this->responseJson->msg = 'Ungültige Taxonomie! (Ajx - '.__LINE__.')';
            return $this->responseJson;
        }
        if(!$post_status){
            $this->responseJson->msg = 'Ungültiger Post Status! (Ajx - '.__LINE__.')';
            return $this->responseJson;
        }
        if(!$post_title){
            $this->responseJson->msg = 'Ungültiger Post Titel! (Ajx - '.__LINE__.')';
            return $this->responseJson;
        }
        if(!$post_content) {
            $this->responseJson->msg = 'Ungültiger Post content! (Ajx - '.__LINE__.')';
            return $this->responseJson;
        }
        if(!$post_date){
            $this->responseJson->msg = 'Ungültiges Post Datum! (Ajx - '.__LINE__.')';
            return $this->responseJson;
        }
        if(!$max_post_sync_selected){
            $this->responseJson->msg = 'Ungültiger Eintrag Beiträge pro Update! (Ajx - '.__LINE__.')';
            return $this->responseJson;
        }

        $source = htmlspecialchars($source);
        if($handle == 'insert') {
            $args = sprintf('WHERE r.source="%s"', $source);
            $checkSrc = apply_filters($this->basename.'/get_rss_import', $args);
            if ($checkSrc->status) {
                $this->responseJson->msg = 'Die RSS Feed Url ist schon vorhanden! (Ajx - ' . __LINE__ . ')';
                return $this->responseJson;
            }
        }

        $channel = apply_filters($this->basename.'/get_rss_channel', $source);
        $channel ? $channelJson = json_encode($channel) : $channelJson = '';

        $pt = explode('#', $post_type);
        if(!isset($pt[0])){
            $this->responseJson->msg = 'Ungültiger Post Type! (Ajx - '.__LINE__.')';
            return $this->responseJson;
        }

        $record->bezeichnung = htmlspecialchars($bezeichnung);
        $record->source = $source;
        $record->date_from = htmlspecialchars($date_from);
        $record->date_to = htmlspecialchars($date_to);
        $record->max_cron_import = (int) $max_post_sync_selected;
        $record->post_type = htmlspecialchars($pt[0]);
        $record->post_taxonomy = (int) $post_taxonomy;
        $record->post_status = (int) $post_status;
        $record->post_title = (int) $post_title;
        $record->post_content = (int) $post_content;
        $record->post_date = (int) $post_date;
        $record->delete_old_post = (int) $auto_delete;
        $record->rss_channel = $channelJson;

        $this->responseJson->handle = $handle;
        if($handle == 'insert'){
            $insert = apply_filters($this->basename.'/set_rss_import', $record);
            $this->responseJson->title = $insert->title;
            $this->responseJson->msg = $insert->msg;
            $this->responseJson->status = $insert->status;
            return $this->responseJson;
        }
        if($handle == 'update'){
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if(!$id) {
                $this->responseJson->msg = 'Ajax Übertragungsfehler (Ajx - '.__LINE__.')';
                return $this->responseJson;
            }
            $args = sprintf('WHERE r.id=%d', $id);
            $upd = apply_filters($this->basename.'/get_rss_import', $args, false);
            if(!$upd->status){
                $this->responseJson->msg = 'Import nicht gefunden. (Ajx - '.__LINE__.')';
                return $this->responseJson;
            }
            $upd = $upd->record;
            if($source != $upd->source) {
                $args = sprintf('WHERE r.source="%s"', $source);
                $ifSrc = apply_filters($this->basename.'/get_rss_import', $args);
                if($ifSrc->status){
                    $this->responseJson->msg = 'Die RSS Feed Url ist schon vorhanden! (Ajx - ' . __LINE__ . ')';
                    return $this->responseJson;
                }
            }
            $record->id = $id;
            $update = apply_filters($this->basename.'/update_rss_import', $record);
            $this->responseJson->title = $update->title;
            $this->responseJson->status = $update->status;
            $this->responseJson->msg = $update->msg;
        }

        return $this->responseJson;
    }

	private function import_feeds_now() :object
	{

		$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
		if(!$id){
			$this->responseJson->msg = __('Ajax transmission error', 'wp-rss-feed-importer') .' (Ajx - '.__LINE__.')';
			return $this->responseJson;
		}

		$args = sprintf('WHERE r.id=%d', $id);
		$feed = apply_filters($this->basename.'/get_rss_import', $args, false);
		if(!$feed->status){
			$this->responseJson->msg = __('Ajax transmission error', 'wp-rss-feed-importer') .' (Ajx - '.__LINE__.')';
			return $this->responseJson;
		}

		$feed = $feed->record;
		$make = apply_filters($this->basename.'/make_feed_import', $feed->id);
		if(!$make){
			$this->responseJson->msg = __('Import failed', 'wp-rss-feed-importer') .' (Ajx - '.__LINE__.')';
			return $this->responseJson;
		}
		$this->responseJson->status = true;
		$this->responseJson->title = __('Imported', 'wp-rss-feed-importer');
		$this->responseJson->msg = __('Contributions were created successfully.', 'wp-rss-feed-importer');
		return  $this->responseJson;
	}

	private function delete_import_feeds() :object
	{
		$this->responseJson->title = __('Error', 'wp-rss-feed-importer');
		$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
		if(!$id){
			$this->responseJson->msg = __('Ajax transmission error', 'wp-rss-feed-importer') .' (Ajx - '.__LINE__.')';
			return $this->responseJson;
		}
		$delete_posts = filter_input(INPUT_POST, 'delete_posts', FILTER_VALIDATE_INT);
		if($delete_posts){
			$args = sprintf('WHERE r.id=%d', $id);
			$feed = apply_filters($this->basename. '/get_rss_import', $args, false);
			if(!$feed->status){
				$this->responseJson->msg = __('Ajax transmission error', 'wp-rss-feed-importer') .' (Ajx - '.__LINE__.')';
				return $this->responseJson;
			}

			$feed = $feed->record;
			$termId = $feed->post_taxonomy;
			$postType = $feed->post_type;

			$term = get_term($termId);
			$postArgs = [
				'post_type' => $postType,
				'numberposts' => -1,
				'tax_query' => array(
					array(
						'taxonomy' => $term->taxonomy,
						'field' => 'term_id',
						'terms' => $termId,
					)
				)
			];

			$delPosts = get_posts($postArgs);
			if($delPosts){
				foreach ($delPosts as $tmp){
					wp_delete_post($tmp->ID, true);
				}
			}
		}

		apply_filters($this->basename.'/delete_rss_import', $id);
		$this->responseJson->title = __('Executed', 'wp-rss-feed-importer');
		$this->responseJson->msg =  __('The RSS import was successfully deleted.', 'wp-rss-feed-importer');
		$this->responseJson->status = true;
		return $this->responseJson;
	}

    private function rss_table(): object
    {
        $nextTime = apply_filters( $this->basename.'/get_next_cron_time', 'rss_import_sync');
        $next_time = date('Y-m-d H:i:s', current_time('timestamp') + $nextTime);

        $query = '';
        $columns = array(
            "r.bezeichnung",
            "r.post_type",
            "r.post_taxonomy",
            "",
            "r.last_import",
            "",
            "r.last_status",
            "r.active",
            "",
            "",
            ""
        );
        $search = (string)$_POST['search']['value'];
        if (isset($_POST['search']['value'])) {
            $query = ' WHERE r.created_at LIKE "%' . $_POST['search']['value'] . '%"
             OR r.bezeichnung LIKE "%' . $_POST['search']['value'] . '%"
             OR r.source LIKE "%' . $_POST['search']['value'] . '%"
             OR r.post_type LIKE "%' . $_POST['search']['value'] . '%"
             OR r.date_from LIKE "%' . $_POST['search']['value'] . '%"
             OR r.date_to LIKE "%' . $_POST['search']['value'] . '%"
            ';
        }


        if (isset($_POST['order'])) {
            $query .= ' ORDER BY ' . $columns[$_POST['order']['0']['column']] . ' ' . $_POST['order']['0']['dir'] . ' ';
        } else {

            $query .= ' ORDER BY  r.bezeichnung ASC';
        }

        $limit = '';
        if ($_POST["length"] != -1) {
            $limit = ' LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
        }

        $table = apply_filters($this->basename.'/get_rss_import', $query . $limit);
        $data_arr = array();
        if (!$table->status) {
            $this->responseJson->draw = $_POST['draw'];
            $this->responseJson->recordsTotal = 0;
            $this->responseJson->recordsFiltered = 0;
            $this->responseJson->data = $data_arr;
            return $this->responseJson;
        }

        foreach ($table->record as $tmp) {

            if ($tmp->active) {
                $checkActive = '<i class="text-success bi bi-check-circle"></i><small class="d-block lh-1 small-xl">'.__('Yes', 'wp-rss-feed-importer').'</small>';
            } else {
                $checkActive = '<i class="text-danger bi bi-x-circle"></i><small class="d-block lh-1 small-xl">'.__('No', 'wp-rss-feed-importer').'</small>';
            }
            if($tmp->last_status){
                $lastStatus = '<i class="text-success bi bi-check-circle"></i><small class="d-block lh-1 small-xl">'.__('Successful', 'wp-rss-feed-importer').'</small>';
            } else {
                $lastStatus = '<i class="text-danger bi bi-x-circle"></i><small class="d-block lh-1 small-xl">'.__('Failed', 'wp-rss-feed-importer').'</small>';
            }
            $tmp->last_import ? $lastTime = '<span class="d-none">'.$tmp->last_import.'</span><small class="lh-1 small">'.date('d.m.Y', $tmp->last_import).'<span class="small-lg mt-1 d-block">'.date('H:i:s', $tmp->last_import).' '.__('Clock', 'wp-rss-feed-importer').'</span></small>' : $lastTime = '<small>'.__('unknown', 'wp-rss-feed-importer').'</small>';
            $term = get_term( $tmp->post_taxonomy );
            $importCount = $term->count;
            $importCount == 1 ? $countTxt = ' '.__('Post', 'wp-rss-feed-importer') : $countTxt = ' '.__('Posts', 'wp-rss-feed-importer');
            $data_item[] = $tmp->bezeichnung;
            $data_item[] = $tmp->post_type;
            $data_item[] = $term->name;
            $data_item[] = '<span class="text-nowrap">'.$importCount . $countTxt.'</span>';
            $data_item[] = $lastTime;
            $data_item[] = '<small class="lh-1 small">' . date('d.m.Y', strtotime($next_time)) . '<span class="small-lg mt-1 d-block">' . date('H:i:s', strtotime($next_time)) . ' '.__('Clock', 'wp-rss-feed-importer').'</span> </small>';
            $data_item[] = $lastStatus;
            $data_item[] = $checkActive;
            $data_item[] = '<button data-id="'.$tmp->id.'" data-type="import_feeds_now" class="rss-action btn btn-blue btn-sm text-nowrap"><i class="bi bi-arrow-repeat d-inline-block me-1"></i> '.__('Import now', 'wp-rss-feed-importer').'</button>';
            $data_item[] = '<button data-target="#colEditImport" data-parent="#collParent" data-type="update_import_handle" data-id="'.$tmp->id.'" class="rss-action btn btn-blue-outline btn-sm">'.__('Edit', 'wp-rss-feed-importer').'</button>';
            $data_item[] = '<button data-type="delete_import_feeds" data-id="'.$tmp->id.'" class="rss-action btn btn-outline-danger btn-sm text-nowrap"><i class="bi d-inline-block"></i> '.__('Delete', 'wp-rss-feed-importer').'</button>';
            $data_arr[] = $data_item;
        }


        $this->responseJson->draw = $_POST['draw'];
        $tbCount = apply_filters($this->basename.'/get_rss_import', false);
        $this->responseJson->recordsTotal = $tbCount->count;
        $this->responseJson->data = $data_arr;
        if ($search) {
            $this->responseJson->recordsFiltered = count($table);
        } else {
            $this->responseJson->recordsFiltered = $tbCount->count;
        }
        return $this->responseJson;
    }

    private function html_compress_template(string $string): string
    {
        if (!$string) {
            return $string;
        }
        return preg_replace(['/<!--(.*)-->/Uis', "/[[:blank:]]+/"], ['', ' '], str_replace(["\n", "\r", "\t"], '', $string));
    }
}
