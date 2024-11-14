<?php

namespace daarso\includes;

class daarso_connector_uninstaller {
	public static function uninstall(): void {
		$options = daarso_connector_options::get_instance();
		$options->delete_all_options();
	}
}
