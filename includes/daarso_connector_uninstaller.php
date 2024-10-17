<?php

namespace daarso\includes;

class daarso_connector_uninstaller {
	public static function uninstall(): void {
		delete_option( daarso_connector_options::REQUEST_CONNECTOR_MESSAGE_ORIGIN_GUID );
		delete_option( daarso_connector_options::REQUEST_CONNECTOR_MESSAGE_TARGET_GUID );
		delete_option( daarso_connector_options::REQUEST_CONNECTOR_MESSAGE_OPENSSL_KEY );
	}
}
