<?php

namespace daarso\includes;

class daarso_connector_tracing {
	public static function write_to_tracelog( $message ): void {
		$date = date_create( 'now' )->format( 'Y-m-d H:i:s' );

		$rawMessage = sprintf( '%s %s %s', getmypid(), $date, $message ) . PHP_EOL;
		file_put_contents( ABSPATH . 'daarso_connector_core-plugin-trace.log', $rawMessage, FILE_APPEND );
	}

	public static function dump_loaded_classes(): void {
		$classes = get_declared_classes();
		foreach ( $classes as $class_name ) {
			echo "$class_name" . PHP_EOL;
		}
	}
}
