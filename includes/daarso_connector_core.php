<?php

namespace daarso\includes;

use daarso\admin\daarso_connector_admin;
use daarso\admin\daarso_plugins_manager;
use daarso\public\ajax\daarso_connector_api;
use daarso\admin\cli\daarso_connector_cli;
use daarso\public\daarso_connector_updraft_extension;
use daarso\public\daarso_connector_public;
use daarso\public\daarso_connector_upgrader_extension;
use Throwable;

class daarso_connector_core {

	protected daarso_connector_loader $loader;
	protected string                  $plugin_name;
	protected string                  $version;

	public function __construct() {
		if ( defined( 'DAARSO_CONNECTOR_VERSION' ) ) {
			$this->version = DAARSO_CONNECTOR_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'daarso_connector_core';

		$this->load_dependencies();
		$this->define_ajax_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_cli_commands();
	}

	private function load_dependencies(): void {
		$plugin_folder = $this->get_current_parent_directory();

		require_once ($plugin_folder . 'plugin-update-checker/plugin-update-checker.php');
		$this->includeRecursively( $plugin_folder . 'includes/*.php' );
		$this->includeRecursively( $plugin_folder . 'admin/*.php' );
		$this->includeRecursively( $plugin_folder . 'public/*.php' );


		$this->loader = new daarso_connector_loader();

	}

	private function get_current_parent_directory(): string {
		//Haal de directory op van dit bestand (daarso_connector_core.php).
		$file_folder = plugin_dir_path( __FILE__ );

		// Stap 1: Verwijder eventuele trailing slashes
		$plugin_folder = rtrim( $file_folder, '/' );

		// Stap 2: Haal het pad zonder de laatste subdirectory
		$plugin_folder = dirname( $plugin_folder );

		// Stap 3: Zorg ervoor dat het resultaat eindigt met een '/'
		return rtrim( $plugin_folder, '/' ) . '/';

	}

	private function includeRecursively( $pattern ): void {
		$files = glob( $pattern );
		foreach ( $files as $file ) {
			require_once( $file );
		}

		foreach ( glob( dirname( $pattern ) . '/*', GLOB_ONLYDIR | GLOB_NOSORT ) as $dir ) {
			self::includeRecursively( $dir . '/' . basename( $pattern ) );
		}
	}

	private function define_ajax_hooks(): void {
		$plugin_ajax = new daarso_connector_api( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_ajax_nopriv_wpmanager_api_entrance', $plugin_ajax, 'wpManagerApiEntrance' );
		$this->loader->add_action( 'wp_ajax_wpmanager_api_entrance', $plugin_ajax, 'wpManagerApiEntrance' );
		$this->loader->add_action( 'init', $plugin_ajax, 'adminSso', 0 );
	}

	private function define_admin_hooks(): void {
		$plugin_admin = new daarso_connector_admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init', $plugin_admin, 'initialize' );
		// Korte klap voor een class met 1 methode (en daarom static is).
		$this->loader->add_action( 'activated_plugin', daarso_plugins_manager::class, 'guard_autoupdate' );

	}

	private function define_cli_commands(): void {
		$plugin_cli = new daarso_connector_cli( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_command( 'daarso-connector api origin-id', $plugin_cli, 'setRequestMessageOrigin' );
		$this->loader->add_command( 'daarso-connector api target-id', $plugin_cli, 'setRequestMessageTarget' );
		$this->loader->add_command( 'daarso-connector api key', $plugin_cli, 'setRequestMessageOpenSslKey' );
		$this->loader->add_command( 'daarso-connector version', $plugin_cli, 'show_version' );
	}

	private function define_public_hooks(): void {

		$plugin_public = new daarso_connector_public( $this->get_plugin_name(), $this->get_version() );

		add_action( 'plugins_loaded', [daarso_connector_updater::class, 'run_updater']);
		add_action( 'updraftplus_restored_db_table', [
			daarso_connector_updraft_extension::class,
			'add_optimize',
		],          99, 3 );
		add_filter( 'parse_request', [ $plugin_public, 'handleParseRequest' ] );
		add_filter( 'rest_user_query', [ $plugin_public, 'handleRestUserQuery' ] );
		add_action( 'phpmailer_init', [ $plugin_public, 'overrideReturnPath' ] );
		add_filter( 'auto_theme_update_send_email', [ $plugin_public, 'prevent_sending_mail' ], 10, 2 );
		add_filter( 'auto_plugin_update_send_email', [ $plugin_public, 'prevent_sending_mail' ], 10, 2 );
		add_filter( 'upgrader_install_package_result', [
			daarso_connector_upgrader_extension::class,
			'install_test',
		],          9, 2 );
	}

	/**
	 * @throws Throwable
	 */
	public function run(): void {
		$this->loader->run();
	}

	public function get_plugin_name(): string {
		return $this->plugin_name;
	}

	public function get_loader(): daarso_connector_loader {
		return $this->loader;
	}

	public function get_version(): string {
		return $this->version;
	}

}
