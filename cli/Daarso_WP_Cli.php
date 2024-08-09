<?php

use Daarso_Options;

final class DaarsoPluginWPCLI
{
	private static $instance = null;

	private function __construct()
	{
//        var_dump(defined('WP_CLI'));
//        var_dump(WP_CLI);
//        die();
		if (defined('WP_CLI') && WP_CLI) {
			WP_CLI::add_command('Daarso', __CLASS__);
			var_dump("wpcli aanwezig");
			die();
		}
	}

	static function getInstance(): DaarsoPluginWPCLI {
		if(self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	public function setRequestMessageOrigin($args, $assoc_args)
	{
		update_option(
			Daarso_Options::REQUEST_MESSAGE_ORIGIN_GUID, $args[0]
		);
	}

	public function setRequestMessageTarget($args, $assoc_args)
	{
		update_option(
			Daarso_Options::REQUEST_MESSAGE_TARGET_GUID, $args[0]
		);
	}

	public function setRequestMessageOpenSslKey($args, $assoc_args)
	{
		$pem = trim(str_replace('\n', "\n", $args[0]), "'");
		update_option(Daarso_Options::REQUEST_MESSAGE_OPENSSL_KEY, $pem);
	}
}
