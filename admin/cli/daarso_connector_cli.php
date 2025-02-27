<?php

namespace daarso\admin\cli;

use daarso\includes\daarso_connector_activator;
use daarso\includes\daarso_connector_options;
use daarso\includes\daarso_connector_updater;
use WP_CLI;

final class daarso_connector_cli {
	public function __construct( private readonly string $plugin_name, private readonly string $version ) {
	}

	public function resetConfiguration():void {
		$options = daarso_connector_options::get_instance();
		$options->reset_all_options();
	}
	public function resetConfigurationAndActivate():void {
		$options = daarso_connector_options::get_instance();
		$options->reset_connection_credentials();
		daarso_connector_activator::activate();
	}

	public function setDaarsoEntranceUrl( $args ): void {
		update_option(
			daarso_connector_options::DAARSO_ENTRANCE_URL, rtrim($args[0], '/')
		);
	}

	public function setRequestMessageWebsiteId( $args ): void {
		update_option(
			daarso_connector_options::REQUEST_CONNECTOR_MESSAGE_WEBSITE_GUID, $args[0]
		);
	}

	public function setRequestMessageWpmanagerId( $args ): void {
		update_option(
			daarso_connector_options::REQUEST_CONNECTOR_MESSAGE_WPMANAGER_GUID, $args[0]
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

	function checkUpdates() {
		daarso_connector_updater::run_updater(true);
	}

}
