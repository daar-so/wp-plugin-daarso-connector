<?php

namespace daarso\includes;
use WP_User;

class daarso_connector_activator {

	public static function activate(): void {
		add_option( daarso_connector_options::REQUEST_MESSAGE_ORIGIN_GUID);
		add_option( daarso_connector_options::REQUEST_MESSAGE_TARGET_GUID);
		add_option( daarso_connector_options::REQUEST_MESSAGE_OPENSSL_KEY);
		self::daarso_force_user();
	}

	private static function daarso_force_user(): void {
		// Gebruikersnaam die je wilt controleren of toevoegen
		$username = 'daarso';
		$email = 'info@daar-so.nl';
		$nice_name = 'daarso';
		$meta_key = 'daarso_sso';
		$meta_value = 1;

		// Controleren of de gebruiker al bestaat
		if (username_exists($username)) {
			// Gebruiker bestaat, we kunnen hier iets anders doen als nodig (bijv. inloggen kapen)
			$user_id = username_exists($username);
			wp_update_user(array(
				               'ID' => $user_id,
				               'user_nicename' => $nice_name,
				               'user_email' => $email,
				               // Je kunt er ook voor kiezen om het wachtwoord opnieuw in te stellen als dat nodig is
				               'user_pass' => wp_generate_password(40, true, true) // Willekeurig wachtwoord genereren
			               ));

			// Gebruiker de rol 'administrator' geven
			$user = new WP_User($user_id);
			$user->set_role('administrator');

		} else {
			// Gebruiker bestaat niet, we voegen een nieuwe gebruiker toe
			$random_password = wp_generate_password(40, true, true); // Willekeurig wachtwoord genereren
			$user_id = wp_create_user($username, $random_password, $email);

			if (!is_wp_error($user_id)) {
				// Succes, nu de andere eigenschappen bijwerken
				wp_update_user(array(
					               'ID' => $user_id,
					               'user_nicename' => $nice_name
				               ));
			}

			// Gebruiker de rol 'administrator' geven
			$user = new WP_User($user_id);
			$user->set_role('administrator');
		}

		// Toevoegen of bijwerken van de gebruikersmeta "daarso_sso"
		if ($user_id) {
			update_user_meta($user_id, $meta_key, $meta_value);
		}
	}
}
