<?php

namespace daarso\public\ajax;

use daarso\includes\daarso_connector_options;

final class Daarso_Api_Message_Coder_V1 {
	private string $websiteGuid;
	private string $wpmanagerGuid;
	private string $openSslKey;

	public function __construct() {
		$options = daarso_connector_options::get_instance();

		$this->websiteGuid   = $options->get_message_website_guid();
		$this->wpmanagerGuid = $options->get_message_wpmanager_guid();
		$this->openSslKey    = $options->get_message_openssl_key();
	}

	public function decode( string $codedMessage ): false|array {

		/** @noinspection UnserializeExploitsInspection */
		$result = self::sslDecrypt( unserialize( base64_decode( $codedMessage ) ), $decodedMessage, $this->openSslKey );
		if ( $result ) {
			$resultArray = json_decode( $decodedMessage, true );
			if ( isset( $resultArray['wpmanagerGuidId'], $resultArray['websiteGuidId'], $resultArray['action'], $resultArray['data'] )
			     && ( $this->websiteGuid === $resultArray['websiteGuidId'] )
			     && ( $this->wpmanagerGuid === $resultArray['wpmanagerGuidId'] ) ) {
				return [ 'action' => $resultArray['action'], 'data' => $resultArray['data'] ];
			}
		}

		return false;
	}

	public function encodeSuccess( array $message ): string {
		$resultArray['wpmanagerGuidId'] = $this->wpmanagerGuid;
		$resultArray['websiteGuidId']   = $this->websiteGuid;
		$resultArray['message']         = $message;
		$resultArray['success']         = true;
		$resultArray['salt']            = microtime( true );
		$result                         = self::sslEncrypt( json_encode( $resultArray ), $codedResponse, $this->openSslKey );
		if ( $result ) {
			return base64_encode( serialize( $codedResponse ) );
		}

		return false;
	}

	private static function sslEncrypt( $source, &$output, $key ): bool {
		$maxlength = 100;
		$output    = [];
		while ( $source ) {
			$input    = substr( $source, 0, $maxlength );
			$source   = substr( $source, $maxlength );
			$result   = openssl_public_encrypt( $input, $encrypted, $key );
			$output[] = $encrypted;
			if ( ! $result ) {
				$output = [];

				return false;
			}
		}

		return true;
	}

	private static function sslDecrypt( $source, &$output, $key ): bool {
		$maxlength = 100;
		$output    = '';
		foreach ( $source as $row ) {
			$result = openssl_public_decrypt( $row, $decrypted, $key );
			$output .= $decrypted;

			if ( ! $result ) {
				$output = '';

				return false;
			}
		}

		return true;
	}
}
