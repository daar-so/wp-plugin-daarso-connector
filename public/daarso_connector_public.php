<?php

namespace daarso\public;

use Throwable;

class daarso_connector_public {

	/** @noinspection PhpPropertyOnlyWrittenInspection */
	public function __construct( private string $plugin_name, private string $version ) {
	}


	public function prevent_sending_mail(): bool {
		return false;
	}

	private function bailUserEnum( $is_json ): void {
		if ( $is_json ) {
			header( 'HTTP/1.1 403 Forbidden' );
			exit;
		}
		wp_die( 'Forbidden', 'Forbidden', [ 'response' => 403 ] );
	}

	public function handleParseRequest( $query ) {
		if ( ! current_user_can( 'list_users' ) && intval( @$query->query_vars['author'] ) ) {
			$this->bailUserEnum( false );
		}

		return $query;
	}

	public function handleRestUserQuery( $prepared_args ) {
		if ( ! current_user_can( 'list_users' ) ) {
			$this->bailUserEnum( true );
		}

		return $prepared_args;
	}

	public function overrideReturnPath( $mailer ): void {
		try {
			if ( filter_var( $mailer->From, FILTER_VALIDATE_EMAIL ) !== true ) {
				$mailer->Sender = 'ict.tech.beheer@daar-so.nl';
			}
			if ( filter_var( $mailer->Sender, FILTER_VALIDATE_EMAIL ) !== true ) {
				$mailer->Sender = $mailer->From;
			}
		} catch ( Throwable $exception ) {
			// niks, silence is golden, maar we moeten er toch wat mee.
		}
	}
}
