<?php

namespace daarso\admin;

class daarso_plugins_manager {
	public static function guard_autoupdate( string $plugin ): void {
		// 1. Haalt de huidige lijst van plugins op waarvoor automatische updates zijn ingeschakeld.
		$auto_updates = (array) get_site_option( 'auto_update_plugins', [] );

		// 2. Voegt de opgegeven plugin toe aan deze lijst.
		$auto_updates[] = $plugin;

		// 3. Verwijdert eventuele duplicaten uit de lijst, zodat elke plugin slechts één keer voorkomt.
		$auto_updates = array_unique( $auto_updates );

		// 4. Slaat de bijgewerkte lijst van plugins op in de 'auto_update_plugins' site-optie.
		update_site_option( 'auto_update_plugins', $auto_updates );
	}
}
