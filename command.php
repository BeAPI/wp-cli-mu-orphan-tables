<?php
/*
Plugin Name: BEA - WP-CLI Orphan Tables
Version: 0.1.0
Plugin URI: https://github.com/BeAPI/wp-cli-mu-orphan-tables
Description: A WP-CLI command to remove orphan tables from WordPress Multi-site.
Author: Be API
Author URI: https://beapi.fr
Domain Path: languages
Network: True

----
Copyright 2018 Be API (human@beapi.fr)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

namespace BEA\WP_CLI_Mu_Orphan_Table_List;

use WP_CLI;

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

class Command {

	protected $db;

	public function __construct() {
		$this->db = $GLOBALS['wpdb'];
	}

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

		if ( ! is_multisite() ) {
			WP_CLI::error( 'This is not a multisite installation. This command is for multisite only.' );
		}


		$db_name = DB_NAME;
		$prefix  = $this->db->base_prefix;

		$existing_blog_ids = $this->db->get_col( sprintf( "SELECT blog_id FROM %s", $this->db->blogs ) );

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

		$tables_to_drop = $this->db->get_col( "SHOW TABLES 
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
				$prefix = $this->db->base_prefix;

				return $prefix . $v;
			},
			$defaults
		);
	}
}

$instance = new Command;
WP_CLI::add_command( 'orphan tables list', $instance );