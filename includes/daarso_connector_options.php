<?php

namespace daarso\includes;

final class daarso_connector_options {
	public const REQUEST_CONNECTOR_MESSAGE_WEBSITE_GUID   = 'daarso_request_message_website_guid';
	public const REQUEST_CONNECTOR_MESSAGE_WPMANAGER_GUID = 'daarso_request_message_wpmanager_guid';
	public const REQUEST_CONNECTOR_MESSAGE_OPENSSL_KEY    = 'daarso_request_message_openssl_key';
	public const DAARSO_ENTRANCE_URL = 'daarso_entrance_url';


	private static ?daarso_connector_options $instance = null;
	private array $cache = [];

	// Singleton access
	public static function get_instance(): daarso_connector_options {
		if (self::$instance === null) {
			self::$instance = new daarso_connector_options();
		}
		return self::$instance;
	}

	private function __construct() {}

	public function get_message_website_guid(): string {
		return $this->get_option(self::REQUEST_CONNECTOR_MESSAGE_WEBSITE_GUID);
	}

	public function get_message_wpmanager_guid(): string {
		return $this->get_option(self::REQUEST_CONNECTOR_MESSAGE_WPMANAGER_GUID);
	}

	public function get_message_openssl_key(): string {
		return $this->get_option(self::REQUEST_CONNECTOR_MESSAGE_OPENSSL_KEY);
	}

	public function get_daarso_entrance_url(): string {
		return $this->get_option(self::DAARSO_ENTRANCE_URL);
	}
	private function get_option(string $option_name) {
		if (!array_key_exists($option_name, $this->cache)) {
			$this->cache[$option_name] = get_option($option_name);
		}

		return $this->cache[$option_name];
	}

	public function update_option(string $option_name, string $option_value):?string {
		if(update_option($option_name, $option_value)) {
			$this->cache[$option_name] = $option_value;
		}

		// Als deze update functie wordt aangeroepen voor dat de get_option is aangeroepen,
		// staat deze optie nog niet in de cache. Voegen de optie zelf toe, dat scheelt weer een read op de database.
		if(!array_key_exists($option_name,$this->cache)) {
			$this->cache[$option_name] = $option_value;
		}
		return $this->cache[$option_name];
	}


	public function validateExistenceOptions()
	{
		add_option( self::REQUEST_CONNECTOR_MESSAGE_WEBSITE_GUID, '');
		add_option( self::REQUEST_CONNECTOR_MESSAGE_WPMANAGER_GUID, '');
		add_option(self::REQUEST_CONNECTOR_MESSAGE_OPENSSL_KEY, '');
		add_option(self::DAARSO_ENTRANCE_URL, 'https://wpmanager.daar-so.nl/wordpress/entrance');
	}
	/**
	 * Alleen Voor gebruik in de uninstaller.
	 */
	public function delete_all_options(): void {
		delete_option( self::REQUEST_CONNECTOR_MESSAGE_WEBSITE_GUID );
		delete_option( self::REQUEST_CONNECTOR_MESSAGE_WPMANAGER_GUID );
		delete_option( self::REQUEST_CONNECTOR_MESSAGE_OPENSSL_KEY );
		delete_option( self::DAARSO_ENTRANCE_URL );
	}

	public function reset_all_options() {
		$this->cache = [];
		$this->update_option( self::REQUEST_CONNECTOR_MESSAGE_WEBSITE_GUID, '');
		$this->update_option( self::REQUEST_CONNECTOR_MESSAGE_WPMANAGER_GUID, '');
		$this->update_option(self::REQUEST_CONNECTOR_MESSAGE_OPENSSL_KEY, '');
		$this->update_option(self::DAARSO_ENTRANCE_URL, 'https://wpmanager.daar-so.nl/wordpress/entrance');
	}

	public function reset_connection_credentials() {
		$this->update_option( self::REQUEST_CONNECTOR_MESSAGE_WEBSITE_GUID, '');
		$this->update_option( self::REQUEST_CONNECTOR_MESSAGE_WPMANAGER_GUID, '');
		$this->update_option(self::REQUEST_CONNECTOR_MESSAGE_OPENSSL_KEY, '');
	}
}
