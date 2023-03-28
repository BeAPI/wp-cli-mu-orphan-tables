<?php
if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

class WP_CLI_MU_Orphan_Tables {

	/**
	 * Remove orphan tables from WordPress
	 *
	 * ## OPTIONS
	 *
	 *
	 * ## EXAMPLES
	 *
	 *     # tables to clean
	 *     $ wp orphan tables list
	 */
	public function __invoke() {
		global $wpdb;

		if ( ! is_multisite() ) {
			WP_CLI::error( 'This is not a multisite installation. This command is for multisite only.' );
		}


		$db_name = DB_NAME;
		$prefix  = $wpdb->base_prefix;

		$existing_blog_ids = $wpdb->get_col( sprintf( "SELECT blog_id FROM %s", $wpdb->blogs ) );

		if ( empty( $existing_blog_ids ) ) {
			WP_CLI::warning( 'No table found.' );

			return false;
		}

		$exclude_sql = [];

		/**
		 * When blog is listed in table blogs
		 * then it's a real blog in the network
		 */
		foreach ( $existing_blog_ids as $existing_blog_id ) {
			$exclude_sql[] = "`Tables_in_$db_name` NOT LIKE '%$prefix\_" . $existing_blog_id . "\_%'";
		}

		/**
		 * There is no use listing main tables from
		 * main blog and multisite tables
		 */
		foreach ( $this->get_default_tables() as $t ) {
			$exclude_sql[] = "`Tables_in_$db_name` != '$t'";
		}

		$tables_to_drop = $wpdb->get_col( "SHOW TABLES 
			FROM `$db_name` 
			WHERE 1 = 1
			AND " . implode( " AND ", $exclude_sql ) );


		if ( empty( $tables_to_drop ) ) {
			WP_CLI::warning( 'No table found.' );

			return false;
		}

		$count = count( $tables_to_drop );

		WP_CLI::success( sprintf( 'Found %1$d orphan %2$s to drop',
			$count, _n( 'table', 'tables', $count )
		) );

		foreach ( $tables_to_drop as $table ) {
			WP_CLI::line( "DROP TABLE IF EXISTS `$table`;" );
		}

		WP_CLI::line( "Now you might run wp db cli to access database and then run drop statement(s) listed above." );
		WP_CLI::line( "But please BE CAREFUL, a drop statement cannot be undone so please backup your database before proceeding." );
	}

	/**
	 * List tables that are part of the default sets of multisite tables
	 * @author Julien Maury
	 * @return array
	 */
	protected function get_default_tables() {
		global $wpdb;

		$defaults = [
			'commentmeta',
			'comments',
			'links',
			'options',
			'postmeta',
			'posts',
			'users',
			'usermeta',
			'termmeta',
			'term_relationships',
			'term_taxonomy',
			'terms',
			'site',
			'sitemeta',
			'blogs',
			'blog_versions',
			'signups',
			'registration_log',
		];

		return array_map(
			function ( $v ) {
				$prefix = $wpdb->base_prefix;

				return $prefix . $v;
			},
			$defaults
		);
	}
}

WP_CLI::add_command( 'orphan tables list', 'WP_CLI_MU_Orphan_Tables' );
