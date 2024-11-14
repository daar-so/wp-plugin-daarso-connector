<?php

namespace daarso\includes;
use WP_User;

class daarso_connector_activator {

	public static function activate(): void {
		self::upgrade();

		$options = daarso_connector_options::get_instance();
		$options->validateExistenceOptions();

		$websiteGuid   =  $options->get_message_website_guid();
		$wpmanagerGuid = $options->get_message_wpmanager_guid();
		$openSslKey    = $options->get_message_openssl_key();

		if ( empty($websiteGuid) || empty($wpmanagerGuid) || empty($openSslKey)) {
			// Eén van de opties is leeg, dus we halen alle benodigde waarden op van WPManager
			$newOptions = self::request_values_from_wpmanager();
			// Stel de opties in op de opgehaalde waarden, of op een lege string als ze niet aanwezig zijn
			$websiteGuid   = $newOptions['websiteGuid'] ?? '';
			$wpmanagerGuid = $newOptions['wpmanagerGuid'] ?? '';
			$openSslKey    = $newOptions['openSslKey'] ?? '';

			// Sla de opgehaalde waarden op in de WordPress-opties
			$options->update_option( daarso_connector_options::REQUEST_CONNECTOR_MESSAGE_WEBSITE_GUID, $websiteGuid);
			$options->update_option( daarso_connector_options::REQUEST_CONNECTOR_MESSAGE_WPMANAGER_GUID, $wpmanagerGuid);
			$options->update_option(daarso_connector_options::REQUEST_CONNECTOR_MESSAGE_OPENSSL_KEY, $openSslKey);
		}

		// Roep de functie aan om de gebruiker aan te maken of bij te werken
		self::daarso_force_user();
	}

	// Functie om de benodigde waarden op te halen van WPManager via een enkele API-call
	private static function request_values_from_wpmanager(): array {
		$site_url = home_url();
		$parsed_url = parse_url($site_url, PHP_URL_HOST);  // Haalt alleen de host (domeinnaam) op

		$domain = preg_replace('/^www\./', '', $parsed_url);

		$options = daarso_connector_options::get_instance();
		$url = sprintf('%s/connection-parameters/%s', $options->get_daarso_entrance_url(), urlencode($domain));

		$response = wp_remote_get( $url);

		if (is_wp_error($response)) {
			return []; // Return een lege array als er een fout is
		}

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);
		$decoded = base64_decode($data['data']);
		if($decoded === false) {
			return [];
		}

		$hash = md5($decoded);

		if($hash !== $data['check-hash']) {
			return [];
		}

		$parameters = json_decode($decoded, true);
		return is_array($parameters) ? [
			'websiteGuid' => $parameters['website-guid'] ?? '',
			'wpmanagerGuid' => $parameters['wpmanager-guid'] ?? '',
			'openSslKey' => $parameters['public-key'] ?? ''
		] : [];
	}
	private static function daarso_force_user(): void {
		$username = 'daarso';
		$email = 'info@daar-so.nl';
		$nice_name = 'daarso';
		$meta_key = 'daarso_sso';
		$meta_value = 1;

		if (username_exists($username)) {
			$user_id = username_exists($username);
			wp_update_user(array(
				               'ID' => $user_id,
				               'user_nicename' => $nice_name,
				               'user_email' => $email,
				               'user_pass' => wp_generate_password(40, true, true) // Willekeurig wachtwoord genereren
			               ));

			$user = new WP_User($user_id);
			$user->set_role('administrator');

		} else {
			// Gebruiker bestaat niet, we voegen een nieuwe gebruiker toe
			$random_password = wp_generate_password(40, true, true); // Willekeurig wachtwoord genereren
			$user_id = wp_create_user($username, $random_password, $email);

			if (!is_wp_error($user_id)) {
				wp_update_user(array(
					               'ID' => $user_id,
					               'user_nicename' => $nice_name
				               ));
			}

			$user = new WP_User($user_id);
			$user->set_role('administrator');
		}

		if ($user_id) {
			update_user_meta($user_id, $meta_key, $meta_value);
		}
	}

	private static function upgrade(): void {
		// Bedoel voor eventuele database inhoudelijke opruimacties etc.
		delete_option('daarso_request_message_origin_guid');
		delete_option('daarso_request_message_target_guid');
	}
}
