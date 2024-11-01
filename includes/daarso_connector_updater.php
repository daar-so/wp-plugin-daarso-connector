<?php

namespace daarso\includes;


use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

class daarso_connector_updater {

	public static function run_updater( $forceerCheck = false ): void {

		$myUpdateChecker = PucFactory::buildUpdateChecker(
			'https://github.com/daar-so/wp-plugin-daarso-connector',
			DAARSO_CONNECTOR_ROOT . 'daarso_connector.php',
			'daarso-connector'
		);

		$myUpdateChecker->debugMode = true;
		$myUpdateChecker->setBranch( 'release' );

	}

}
