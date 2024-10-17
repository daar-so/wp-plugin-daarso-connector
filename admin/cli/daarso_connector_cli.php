<?php

namespace daarso\admin\cli;

use daarso\includes\daarso_connector_options;
use WP_CLI;

final class daarso_connector_cli {
	public function __construct( private readonly string $plugin_name, private readonly string $version ) {
	}

	public function setRequestMessageOrigin( $args ): void {
		update_option(
			daarso_connector_options::REQUEST_CONNECTOR_MESSAGE_ORIGIN_GUID, $args[0]
		);
	}

	public function setRequestMessageTarget( $args ): void {
		update_option(
			daarso_connector_options::REQUEST_CONNECTOR_MESSAGE_TARGET_GUID, $args[0]
		);
	}

	public function setRequestMessageOpenSslKey( $args ): void {
		$pem = trim( str_replace( '\n', "\n", $args[0] ), "'" );
		update_option( daarso_connector_options::REQUEST_CONNECTOR_MESSAGE_OPENSSL_KEY, $pem );
	}

	function show_version() {
		$plugin_data = get_file_data( __FILE__, [
			'name'    => 'Plugin Name',
			'version' => 'Version',
		] );

		WP_CLI::line( sprintf( '%s, versie %s', $this->plugin_name, $this->version ) );
	}
}
