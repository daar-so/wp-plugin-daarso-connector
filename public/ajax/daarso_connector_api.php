<?php

namespace daarso\public\ajax;

final class daarso_connector_api {
	private mixed $messsageCoder;

	/** @noinspection PhpPropertyOnlyWrittenInspection */
	public function __construct( private readonly string $plugin_name, private readonly string $version ) {
		add_action( 'wp_ajax_nopriv_wpmanager_api_entrance', [ $this, 'wpManagerApiEntrance' ] );
		add_action( 'wp_ajax_wpmanager_api_entrance', [ $this, 'wpManagerApiEntrance' ] );
		add_action( 'init', [ $this, 'adminSso' ], 0 );
	}

	public function wpManagerApiEntrance(): void {
		$protocol_version = $_POST['protocol_version'] ?? '0';

		switch ( $protocol_version ) {
			case '1':
				$this->messsageCoder = new Daarso_Api_Message_Coder_V1();
				break;
			default:
				wp_send_json_error( [ 'error' => 'Protocol version missmatch.' ], 400 );
				wp_die();
		}

		if ( isset( $_POST['message'] ) ) {
			$message      = $this->messsageCoder->decode( $_POST['message'] );

			if ( false !== $message ) {
				switch ( $message['action'] ) {
					case 'admin_sso':
						$credentials = $this->generateSsoCredentials();
						if ( false !== $credentials ) {
							$this->sendSuccessResponse( $protocol_version, $credentials );
						}
						break;
					case 'get_update_info':
						$data = $this->getUpdateInformation();
						$this->sendSuccessResponse( $protocol_version, $data );
						break;
					default:
						wp_send_json_error( [ 'error' => 'Action missmatch.' ], 400 );
				}
			}
		}
		wp_die();
	}

	private function sendSuccessResponse( $protocol_version, $unEncodedData ): void {
		$response = $this->messsageCoder->encodeSuccess( $unEncodedData );
		if ( false !== $response ) {
			wp_send_json_success( [ 'protocol_version' => $protocol_version, 'response' => $response ] );
		} else {
			$this->sendInternalErrorResponse( $protocol_version );
		}
	}

	private function sendInternalErrorResponse( $protocol_version ): void {
		wp_send_json_error(
			[ 'error' => 'Internal plugin error' ], 500
		);
	}

	private function generateSsoCredentials(): false|array {
		$id     = substr(
			str_shuffle(
				str_repeat(
					$x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil( 20 / strlen( $x ) )
				)
			), 1, 20
		);
		$key    = substr(
			str_shuffle(
				str_repeat(
					$x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil( 20 / strlen( $x ) )
				)
			), 1, 20
		);
		$result = set_transient( "daarso_sso_" . $id, $key, 60 );
		if ( false !== $result ) {
			return [ 'id' => $id, 'key' => $key ];
		}

		return false;
	}

	private function getUpdateInformation(): array {
		$themes = [];
		foreach ( wp_get_themes() as $id => $theme ) {
			$themes[ $id ] = $theme->Name;
		}

		return [
			'plugin_versions' => get_site_transient( 'update_plugins' ),
			'plugins'         => get_plugins(),
			'theme_versions'  => get_site_transient( 'update_themes' ),
			'core'            => get_site_transient( 'update_core' ),
			'themes'          => $themes,
		];
	}

	public function adminSso(): void {
		if ( isset( $_GET['ssoLogin'], $_GET['id'], $_GET['key'] ) ) {
			$key = get_transient( "daarso_sso_" . $_GET['id'] );
			if ( $key !== false && $key === $_GET['key'] ) {
				$this->enter_from_manager();
				delete_transient( 'daarso_sso_' . $_GET['id'] );
				wp_safe_redirect( admin_url() );
				exit();
			}
		}
	}

	private function enter_from_manager(): void {
		if ( ! is_user_logged_in() ) {
			$users = get_users( [ 'meta_key' => 'daarso_sso', 'meta_value' => 1, ] );
			if ( ! $users ) {
				return;
			}
			$user = $users[0];
			if ( ! is_object( $user ) || empty( $user->ID ) ) {
				return;
			}
			wp_set_current_user( $user->ID, $user->user_login );
			wp_set_auth_cookie( $user->ID );
			do_action( 'wp_login', $user->user_login, $user );
		}
	}
}
