<?php
namespace WPRSSFeddImporter\FeedImporter;
use stdClass;
use Wp_Rss_Feed_Importer;

defined( 'ABSPATH' ) or die();

class Import_RSS_Cronjob {

    /**
     * @access   private
     * @var      object $settings The FB-API Settings for this Plugin
     */
    private object $settings;

    /**
     * @access   private
     * @var Wp_Rss_Feed_Importer $main The main class.
     */
    private Wp_Rss_Feed_Importer $main;

    public function __construct(  Wp_Rss_Feed_Importer $main ) {

        $this->main = $main;
        $settings = get_option('wp_rss_importer_settings');
        if($settings){
            if($settings['aktiv']){
                if (!wp_next_scheduled('rss_import_sync')) {
                    wp_schedule_event(time(), $settings['selected_cron_sync_interval'], 'rss_import_sync');
                }
            }
        }
    }

    public function fn_rss_wp_un_schedule_task($args): void
    {
        $timestamp = wp_next_scheduled('rss_import_sync');
        wp_unschedule_event($timestamp, 'rss_import_sync');
    }

    public function fn_rss_wp_delete_task($args): void
    {
        wp_clear_scheduled_hook('rss_import_sync');
    }

    public function fn_rss_run_schedule_task($args): void
    {
        $settings = get_option('wp_rss_importer_settings');
        if($settings){
            $schedule = $settings['selected_cron_sync_interval'];
        } else {
           $schedule = 'daily';
        }
        $time = get_gmt_from_date(gmdate('Y-m-d H:i:s', current_time('timestamp')), 'U');
        $args = [
            'timestamp' => $time,
            'recurrence' => $schedule->recurrence,
            'hook' => 'rss_import_sync'
        ];

        $this->schedule_task($args);
    }

    /**
     * @param $task
     * @return void
     */
    private function schedule_task($task): void
    {

        /* Must have task information. */
        if (!$task) {
            return;
        }

        /* Set list of required task keys. */
        $required_keys = array(
            'timestamp',
            'recurrence',
            'hook'
        );

        /* Verify the necessary task information exists. */
        $missing_keys = [];
        foreach ($required_keys as $key) {
            if (!array_key_exists($key, $task)) {
                $missing_keys[] = $key;
            }
        }

        /* Check for missing keys. */
        if (!empty($missing_keys)) {
            return;
        }

        /* Task darf nicht bereits geplant sein. */
        if (wp_next_scheduled($task['hook'])) {
            wp_clear_scheduled_hook($task['hook']);
        }

        /* Schedule the task to run. */
        wp_schedule_event($task['timestamp'], $task['recurrence'], $task['hook']);
    }

}
