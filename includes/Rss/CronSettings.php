<?php

namespace WPRSSFeddImporter\FeedImporter;

trait CronSettings
{
    protected array $cron_default_values;
    protected int $aktiv = 1;
    protected int $delete_duplicate = 1;
    protected string $selected_cron_sync_interval = 'twicedaily';
    protected int $selected_post_type = 0;
    protected int $max_post_sync_selected = 10;
    protected string $select_user_role = 'manage_options';

    protected function get_cron_defaults(string $args = '', $id = null): array
    {
        $this->cron_default_values = [
            'cron_settings' => [
                'aktiv' => $this->aktiv,
                'delete_duplicate' => $this->delete_duplicate,
                'selected_cron_sync_interval' => $this->selected_cron_sync_interval,
                'selected_post_type' => $this->selected_post_type,
                'max_post_sync_selected' => $this->max_post_sync_selected,
                'selected_user_role' => $this->select_user_role
            ],
            'max_post_sync' => [
                "0" => [
                    'value' => 10
                ],
                "1" => [
                    'value' => 20
                ],
                "2" => [
                    'value' => 30
                ],
                "3" => [
                    'value' => 40
                ],
                "4" => [
                    'value' => 40
                ],
                "5" => [
                    'value' => 50
                ],
            ],
            'select_api_sync_interval' => [
                "0" => [
                    "id" => 'hourly',
                    "bezeichnung" => __('Hourly', 'wp-rss-feed-importer'),
                ],
                "1" => [
                    'id' => 'twicedaily',
                    "bezeichnung" => __('Twice a day', 'wp-rss-feed-importer'),
                ],
                "3" => [
                    'id' => 'daily',
                    "bezeichnung" => __('Once a day', 'wp-rss-feed-importer'),
                ],
                "4" => [
                    'id' => 'weekly',
                    "bezeichnung" => __('Once a week', 'wp-rss-feed-importer'),
                ],
            ],
            'select_user_role' => [
                "0" => [
                    'value' => 'read',
                    'name' => __('Subscriber', 'wp-rss-feed-importer')
                ],
                "1" => [
                    'value' => 'edit_posts',
                    'name' => __('Contributor', 'wp-rss-feed-importer')
                ],
                "2" => [
                    'value' => 'publish_posts',
                    'name' => __('Author', 'wp-rss-feed-importer')
                ],
                "3" => [
                    'value' => 'publish_pages',
                    'name' => __('Editor', 'wp-rss-feed-importer')
                ],
                "4" => [
                    'value' => 'manage_options',
                    'name' => __('Administrator', 'wp-rss-feed-importer')
                ],
            ],

            'select_post_status' => [
                '0' => [
                    'id' => 1,
                    "name" => "Publish",
                    "value" => 'publish'

                ],
                '1' => [
                    'id' => 2,
                    "name" => "Draft",
                    'value' => 'draft'

                ],
            ],
            'select_post_title' => [
                '0' => [
                    'id' => 1,
                    "name" => __('RSS Title', 'wp-rss-feed-importer') ,
                    "value" => 'title'
                ],
                '1' => [
                    'id' => 2,
                    "name" => __('RSS Channel Title', 'wp-rss-feed-importer'),
                    'value' => 'channel_title'
                ],
                '2' => [
                    'id' => 3,
                    "name" => __('RSS Date', 'wp-rss-feed-importer'),
                    'value' => 'pubDate'
                ],
            ],
            'select_post_date' => [
                '0' => [
                    'id' => 1,
                    'name' => __('RSS Date', 'wp-rss-feed-importer'),
                    'type' => 'rss'
                ],
                '1' => [
                    'id' => 2,
                    'name' => __('Post Date', 'wp-rss-feed-importer'),
                    'type' => 'post'
                ],
            ],
            'select_post_content' => [
                '0' => [
                    'id' => 1,
                    'name' => __('RSS Content', 'wp-rss-feed-importer'),
                    'type' => 'content'
                ],
                '1' => [
                    'id' => 2,
                    'name' => __('RSS Description', 'wp-rss-feed-importer'),
                    'type' => 'description'
                ],
                '2' => [
                    'id' => 3,
                    'name' => __('RSS Link', 'wp-rss-feed-importer'),
                    'type' => 'link'
                ],
            ],
            'select_date_format' => [
                '0' => [
                    'name' => 'm.Y',
                    'id' => 1
                ],
                '1' => [
                    'name' => 'd.m.Y',
                    'id' => 2
                ],
                '2' => [
                    'name' => 'F j, Y',
                    'id' => 3
                ],
                '3' => [
                    'name' => 'anderes Format',
                    'id' => 4
                ],
            ],
        ];

        if ($args) {
            if($id) {
                foreach ($this->cron_default_values[$args] as $tmp){
                    if(isset($tmp['id']) && $tmp['id'] == $id) {
                      return $tmp;
                    }
                }
            }
            return $this->cron_default_values[$args];
        } else {
            return $this->cron_default_values;
        }
    }

	protected function twig_language()
	{
		$lang = [
			__('Import title', 'wp-rss-feed-importer'),
			__('Post Type', 'wp-rss-feed-importer'),
			__('Category', 'wp-rss-feed-importer'),
			__('Imported', 'wp-rss-feed-importer'),
			__('Last', 'wp-rss-feed-importer'),
			__('Next', 'wp-rss-feed-importer'),
			__('Import Status', 'wp-rss-feed-importer'),
			__('Active', 'wp-rss-feed-importer'),
			__('Import', 'wp-rss-feed-importer'),
			__('Edit', 'wp-rss-feed-importer'),
			__('Delete', 'wp-rss-feed-importer'),
			__('RSS Feed Importer', 'wp-rss-feed-importer'),
			__('Settings', 'wp-rss-feed-importer'),
			__('RSS Feed Import', 'wp-rss-feed-importer'),
			__('Overview', 'wp-rss-feed-importer'),
			__('Add import', 'wp-rss-feed-importer'),
			__('Manage imports', 'wp-rss-feed-importer'),
			__('back', 'wp-rss-feed-importer'),
			__('Channel', 'wp-rss-feed-importer'),
			__('Channel Title', 'wp-rss-feed-importer'),
			__('no data', 'wp-rss-feed-importer'),
			__('Channel Link', 'wp-rss-feed-importer'),
			__('Channel language', 'wp-rss-feed-importer'),
			__('Date of publication', 'wp-rss-feed-importer'),
			__('last publication date', 'wp-rss-feed-importer'),
			__('Copyright', 'wp-rss-feed-importer'),
			__('import new RSS feed', 'wp-rss-feed-importer'),
			__('Edit RSS feed', 'wp-rss-feed-importer'),
			__('Import name', 'wp-rss-feed-importer'),
			__('RSS Feed Url', 'wp-rss-feed-importer'),
			__('from', 'wp-rss-feed-importer'),
			__('to', 'wp-rss-feed-importer'),
			__('Filter by time period', 'wp-rss-feed-importer'),
			__('Post Taxonomy', 'wp-rss-feed-importer'),
			__('Post Status', 'wp-rss-feed-importer'),
			__('Post Title', 'wp-rss-feed-importer'),
			__('Post content', 'wp-rss-feed-importer'),
			__('Post date', 'wp-rss-feed-importer'),
			__('Import posts per update', 'wp-rss-feed-importer'),
			__('Delete automatically', 'wp-rss-feed-importer'),
			__('Number of days', 'wp-rss-feed-importer'),
			__('Useful if you want to remove obsolete articles automatically. If the entry remains empty, the imported articles will not be deleted automatically.', 'wp-rss-feed-importer'),
			__('Import active', 'wp-rss-feed-importer'),
			__('Remove duplicate entries', 'wp-rss-feed-importer'),
			__('Delete items created for this import after a specified number of days.', 'wp-rss-feed-importer'),
			__('Save', 'wp-rss-feed-importer'),
			__('Cancel', 'wp-rss-feed-importer'),
			__('Synchronization settings', 'wp-rss-feed-importer'),
			__('Cronjob active', 'wp-rss-feed-importer'),
			__('next synchronization on', 'wp-rss-feed-importer'),
			__('at', 'wp-rss-feed-importer'),
			__('Synchronization interval', 'wp-rss-feed-importer'),
			__('Minimum requirement for plugin usage', 'wp-rss-feed-importer'),
			__('Clock', 'wp-rss-feed-importer'),

		];
	}

	protected function js_language()
	{
		$jsLang = [
			'checkbox_delete_label' => __('Delete all imported posts?', 'wp-rss-feed-importer'),
			'Cancel' => __('Cancel', 'wp-rss-feed-importer'),
			'delete_title' => __('Really delete import?', 'wp-rss-feed-importer'),
			'delete_subtitle' => __('The deletion cannot be undone.', 'wp-rss-feed-importer'),
			'delete_btn_txt' => __('Delete import', 'wp-rss-feed-importer'),
		];

		return $jsLang;

	}
}