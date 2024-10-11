<?php

namespace daarso\public;

class daarso_connector_upgrader_extension {
	public static function install_test( $arg1, $arg2 ) {
		$file = wp_tempnam( 'plugtest' );
		file_put_contents( $file, var_export( [ $arg1, $arg2 ], true ) );
	}
}
