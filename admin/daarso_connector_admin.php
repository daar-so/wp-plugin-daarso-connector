<?php

namespace daarso\admin;

class daarso_connector_admin {

	/** @noinspection PhpPropertyOnlyWrittenInspection */
	public function __construct( private string $plugin_name, private string $version ) {
		// Een admin sectie voor WP CLI, immers alleen een beheerder komt op de command line en wil mogelijk toegang tot
		// WP CLI.
	}


	public function initialize(): void {
		if ( get_site_option( 'permalink_structure' ) === '' ) {
			add_action( 'admin_notices', [ $this, 'requirePrettyPermalinksNotice' ] );
		}
	}

	public function requirePrettyPermalinksNotice(): void {
		echo wp_kses_post(
			'<div id="message" class="error"><p>' . sprintf(
				'Om Varnish correct te kunnen gebruiken moet de permalink structuur ingesteld worden. Ga naar <a href="%s">Permalink opties</a> om dit in te stellen.',
				esc_url( admin_url( 'options-permalink.php' ) )
			) . '</p></div>'
		);
	}
}
