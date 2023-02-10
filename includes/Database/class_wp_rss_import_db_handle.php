<?php
/**
 * The admin-specific functionality of the theme.
 *
 * @link       https://wwdh.de
 */

defined( 'ABSPATH' ) or die();

class WP_RSS_Import_DB_Handle {
	private static $instance;
	protected Wp_Rss_Feed_Importer $main;
	private string $table_rss_imports = 'rss_feeds_importer';

	/**
	 * The current version of the DB-Version.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $db_version The current version of the database Version.
	 */
	protected string $db_version;

	/**
	 * @return static
	 */
	public static function instance( Wp_Rss_Feed_Importer $main, $db_version ): self {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self( $main,$db_version );
		}

		return self::$instance;
	}

	public function __construct( Wp_Rss_Feed_Importer $main, $db_version ) {
		$this->main = $main;
		$this->db_version = $db_version;
	}

	/**
	 * @param string $args
	 * @param bool $fetchMethod
	 *
	 * @return object
	 */
	public function getRssImportsByArgs( string $args = '', bool $fetchMethod = true ): object {

		global $wpdb;
		$return         = new stdClass();
		$return->status = false;
		$return->count  = 0;
		$fetchMethod ? $fetch = 'get_results' : $fetch = 'get_row';
		$table  = $wpdb->prefix . $this->table_rss_imports;
		$result = $wpdb->$fetch( "SELECT r.* FROM $table r $args" );
		if ( ! $result ) {
			return $return;
		}
		$fetchMethod ? $count = count( $result ) : $count = 1;
		$return->count  = $count;
		$return->status = true;
		$return->record = $result;

		return $return;
	}

	public function setRssImport( $record ): object {
		$return = new stdClass();
		global $wpdb;
		$table = $wpdb->prefix . $this->table_rss_imports;
		$wpdb->insert(
			$table,
			array(
				'bezeichnung'      => $record->bezeichnung,
				'source'           => $record->source,
				'date_from'        => $record->date_from,
				'date_to'          => $record->date_to,
				'active'           => $record->active,
				'remove_duplicate' => $record->remove_duplicate,
				'max_cron_import'  => $record->max_cron_import,
				'post_type'        => $record->post_type,
				'post_taxonomy'    => $record->post_taxonomy,
				'post_status'      => $record->post_status,
				'post_title'       => $record->post_title,
				'post_content'     => $record->post_content,
				'post_date'        => $record->post_date,
				'delete_old_post'  => $record->delete_old_post,
				'rss_channel'      => $record->rss_channel,
			),
			array( '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%s' )
		);
		if ( ! $wpdb->insert_id ) {
			$return->status = false;
			$return->msg    = 'Import konnte nicht gespeichert werden.';
			$return->title  = 'Import Fehler!';

			return $return;
		}
		$return->status = true;
		$return->id     = $wpdb->insert_id;
		$return->msg    = 'RSS Import wurde erfolgreich gespeichert.';
		$return->title  = 'Einstellungen gespeichert';

		return $return;
	}

	public function updateRssImport( $record ): object {
		$return = new stdClass();
		global $wpdb;
		$wpdb->show_errors();
		$table = $wpdb->prefix . $this->table_rss_imports;
		$wpdb->update(
			$table,
			array(
				'bezeichnung'      => $record->bezeichnung,
				'source'           => $record->source,
				'date_from'        => $record->date_from,
				'date_to'          => $record->date_to,
				'active'           => $record->active,
				'remove_duplicate' => $record->remove_duplicate,
				'max_cron_import'  => $record->max_cron_import,
				'post_type'        => $record->post_type,
				'post_taxonomy'    => $record->post_taxonomy,
				'post_status'      => $record->post_status,
				'post_title'       => $record->post_title,
				'post_content'     => $record->post_content,
				'post_date'        => $record->post_date,
				'delete_old_post'  => $record->delete_old_post,
				'rss_channel'      => $record->rss_channel,
			),
			array( 'id' => $record->id ),
			array( '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%s' ),
			array( '%d' )
		);

		if ( $wpdb->last_error !== '' ) {
			$return->status = false;
			$return->msg    = 'RSS Import konnte nicht gespeichert werden.';
			$return->title  = 'Einstellungen nicht gespeichert';

			return $return;
		}

		$return->status = true;
		$return->msg    = 'RSS Import wurde erfolgreich gespeichert.';
		$return->title  = 'Einstellungen gespeichert';

		return $return;
	}

	public function updateRssLastImport( $record ): void {
		global $wpdb;
		$table = $wpdb->prefix . $this->table_rss_imports;
		$wpdb->update(
			$table,
			array(
				'last_import' => $record->last_import,
				'last_status' => $record->last_status
			),
			array( 'id' => $record->id ),
			array( '%s', '%d' ),
			array( '%d' )
		);
	}

	public function deleteRssImport( $id ): void {
		global $wpdb;
		$table = $wpdb->prefix . $this->table_rss_imports;
		$wpdb->delete(
			$table,
			array(
				'id' => $id
			),
			array( '%d' )
		);
	}

	public function wp_rss_importer_check_jal_install() {
		if ( get_option( 'jal_wp_rss_importer_db_version' ) != $this->db_version ) {
			update_option( 'jal_wp_rss_importer_db_version', $this->db_version );
			$this->wp_rss_importer_jal_install();
		}
	}

	public function wp_rss_importer_jal_install() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		global $wpdb;
		$table_name      = $wpdb->prefix . $this->table_rss_imports;
		$charset_collate = $wpdb->get_charset_collate();
		$sql             = "CREATE TABLE $table_name (
    	`id` int(11) NOT NULL AUTO_INCREMENT,
		`bezeichnung` varchar(64) NOT NULL,
        `source` varchar(255) NOT NULL,
        `date_from` varchar(16) DEFAULT NULL,
        `date_to` varchar(16) DEFAULT NULL,
        `active` tinyint(1) NOT NULL DEFAULT 1,
        `remove_duplicate` tinyint(1) NOT NULL DEFAULT 1,
        `max_cron_import` mediumint(2) NOT NULL DEFAULT 10,
        `post_type` varchar(64) NOT NULL DEFAULT 'news',
        `post_taxonomy` int(11) NOT NULL,
        `post_status` tinyint(1) NOT NULL DEFAULT 1,
        `post_title` tinyint(1) NOT NULL,
        `post_content` tinyint(1) NOT NULL,
        `post_date` tinyint(1) NOT NULL,
        `delete_old_post` int(11) DEFAULT NULL,
        `rss_channel` text DEFAULT NULL,
        `last_import` varchar(20) DEFAULT NULL,
        `last_status` tinyint(1) DEFAULT NULL,
        `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
       PRIMARY KEY (id)
     ) $charset_collate;";
		dbDelta( $sql );
	}
}