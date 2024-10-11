<?php

namespace daarso\public;

class daarso_connector_updraft_extension {
	public static function add_optimize( $table, $import_table_prefix, $engine ) {
		global $updraftplus, $wpdb;
		$updraftplus->log_e( sprintf( 'daar-so: optimizing table %s', $table ) );
		$wpdb->query( sprintf( 'OPTIMIZE TABLE %s', $table ) );
	}
}

