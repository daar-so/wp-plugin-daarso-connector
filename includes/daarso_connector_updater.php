<?php

namespace daarso\includes;


use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

class daarso_connector_updater {

	public static function run_updater() {
		$myUpdateChecker = PucFactory::buildUpdateChecker(
			'https://wpmanager.testdomeins.nl/updateinfo.json',
			DAARSO_CONNECTOR_ROOT . 'daarso_connector.php',
			'daarso_connector'
		);

		$myUpdateChecker->debugMode = true;

		$myUpdateChecker->addQueryArgFilter( [ self::class, 'filtertest' ] );

		//var_dump($myUpdateChecker->);

//Set the branch that contains the stable release.
//		$myUpdateChecker->setBranch('master');

//Optional: If you're using a private repository, specify the access token like this:
//		$myUpdateChecker->setAuthentication('your-token-here');
	}

	public static function filtertest( $invoer ) {
		//Weet niet of dit nog problemen gaat opleveren. Want er worden een paar query parameters toegevoegd.
		return $invoer;

	}

}
