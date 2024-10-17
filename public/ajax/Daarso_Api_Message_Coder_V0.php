<?php

namespace daarso\public\ajax;


use daarso\includes\daarso_connector_options;

final class Daarso_Api_Message_Coder_V0 {
	private string $originGuid;
	private string $targetGuid;
	private string $openSslKey;

	public function __construct() {
		$this->originGuid = get_option( daarso_connector_options::REQUEST_CONNECTOR_MESSAGE_ORIGIN_GUID );
		$this->targetGuid = get_option( daarso_connector_options::REQUEST_CONNECTOR_MESSAGE_TARGET_GUID );
		$this->openSslKey = get_option( daarso_connector_options::REQUEST_CONNECTOR_MESSAGE_OPENSSL_KEY );
	}

	public function decode( string $codedMessage ): false|array {
		if ( openssl_public_decrypt( base64_decode( $codedMessage ), $decodedMessage, $this->openSslKey ) ) {
			$resultArray = json_decode( $decodedMessage, true );
			if ( isset(
				     $resultArray['wpmanagerGuidId'],
				     $resultArray['websiteGuidId'],
				     $resultArray['action'],
				     $resultArray['data']
			     )
			     && ( $this->originGuid === $resultArray['wpmanagerGuidId'] )
			     && ( $this->targetGuid === $resultArray['websiteGuidId'] ) ) {

				return [ 'action' => $resultArray['action'], 'data' => $resultArray['data'] ];
			}
		}

		return false;
	}

	public function encodeSuccess( array $message ): string {
		$resultArray['wpmanagerGuidId'] = $this->originGuid;
		$resultArray['websiteGuidId']   = $this->targetGuid;
		$resultArray['message']         = $message;
		$resultArray['success']         = true;
		$resultArray['salt']            = microtime( true );

		if ( openssl_public_encrypt( json_encode( $resultArray ), $codedResponse, $this->openSslKey ) ) {
			return base64_encode( $codedResponse );
		}

		return false;
	}
}
