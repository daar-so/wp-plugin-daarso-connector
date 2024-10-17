<?php
/** @noinspection AutoloadingIssuesInspection */
/** @noinspection PhpUnused */

/*
 * Een build script om de installeerbare plugin (zip-bestand) te bouwen.
 *
 * Stap 1: zorg ervoor dat alle bestanden en directories die GEEN onderdeel zijn van de release zijn opgenomen in het
 * bestand: /build/exclude.
 *
 * Stap 2: zorg ervoor dat de verplicht Wordpress plugin header (te vinden inL: /daarso_connector.php) is bijgewerkt.
 *
 * Stap 3: voor dit PHP-script uit. Dit script heeft geen commandline parameters.
 *
 * Stap 4: zoek naar het zip-bestand in /build/builds.
 *
 * Bestandsnaam:
 * <slug>_php<php versie>_<plugin versie>.php
 * voorbeeld: daarsoNL-connector_php8.0-0.0.0.php
 *
 * slug: wordt afgeleid van de plugin naam in de header.
 * - alle niet alfanumerieke tekens behalve spaties worden verwijderd uit de naam
 * - alles spaties worden vervangen door: "-"
 * - de naam wordt omgezet in lower case.
 *
 * php versie: de minimaal vereiste PHP-versie voor deze plugin
 *
 * plugin versie: de versie van de plugin (deze moet je zelf elke keer aanpassen).
 */

class pluginBuilder {
	private string $projectRoot;
	private string $tempDir;
	private string $excludeFile;
	private string $buildsDir;

	public function __construct() {
		// Instellingen
		$this->projectRoot = dirname( __DIR__ );                       // Root van het project (een niveau boven de build directory)
		$this->tempDir     = $this->projectRoot . '/build/temp';            // Tijdelijke directory
		$this->excludeFile = $this->projectRoot . '/build/.exclude';        // Exclude lijst
		$this->buildsDir   = $this->projectRoot . '/build/builds';          // Directory voor het zip-bestand
	}

	public function build(): void {
		$this->initialize();
		$excludes = $this->getExcludes();

		$this->copyFiles( $this->projectRoot, $this->tempDir, $excludes );
		$zipPath = $this->maakNaamZipBestand();
		$this->createZip( $this->tempDir, $zipPath );
		$this->deleteTempDir();

		echo "ZIP-bestand '$this->zipFileName' is succesvol aangemaakt in de 'builds' directory.\n";
	}
	private function initialize(): void {
		if ( ! is_dir( $this->tempDir ) && ! mkdir( $this->tempDir, 0777, true ) && ! is_dir( $this->tempDir ) ) {
			throw new RuntimeException( sprintf( 'Directory "%s" kon niet worden aangemaakt', $this->tempDir ) );
		}
		if ( ! is_dir( $this->buildsDir ) && ! mkdir( $this->buildsDir, 0777, true ) && ! is_dir( $this->buildsDir ) ) {
			throw new RuntimeException( sprintf( 'Directory "%s" kon niet worden aangemaakt', $this->buildsDir ) );
		}
	}

	private function getPluginInfo(): array {
		$mainFile = glob( $this->projectRoot . '/daarso_connector.php' )[0] ?? null;
		if ( ! $mainFile ) {
			die( "Geen hoofd PHP-bestand gevonden in de plugin directory.\n" );
		}

		$data = file_get_contents( $mainFile );
		preg_match( '/Plugin Name:\s*(.+)$/m', $data, $pluginName );
		preg_match( '/Version:\s*(.+)$/m', $data, $pluginVersion );
		preg_match( '/Requires PHP:\s*(.+)$/m', $data, $phpRequirement );

		if ( ! $pluginName || ! $pluginVersion || ! $phpRequirement ) {
			die( "Plugin Name, Version of PHP vereiste niet gevonden in het hoofd bestand.\n" );
		}

		// Slug genereren: behoud spaties, verwijder overige niet-alfanumerieke tekens
		$slug = preg_replace( '/[^a-zA-Z0-9\s]/', '', trim( $pluginName[1] ) );
		// Vervang spaties door streepjes en zet naar lowercase
		$slug = strtolower( str_replace( ' ', '-', $slug ) );

		$version    = trim( $pluginVersion[1] );
		$phpVersion = trim( $phpRequirement[1] );

		return [ "slug" => $slug, "version" => $version, "php_version" => $phpVersion ];
	}

	private function maakNaamZipBestand(): string {
		$pluginInfo        = $this->getPluginInfo();
		$this->zipFileName = "{$pluginInfo['slug']}_php{$pluginInfo['php_version']}_{$pluginInfo['version']}.zip";

		return $this->buildsDir . DIRECTORY_SEPARATOR . $this->zipFileName;
	}

	private function getExcludes(): array {
		$excludes = [ "directories" => [], "files" => [] ];

		if ( file_exists( $this->excludeFile ) ) {
			$lines = array_filter( array_map( 'trim', file( $this->excludeFile ) ) );

			foreach ( $lines as $line ) {
				if ( empty( $line ) || $line[0] === '#' ) {
					continue;
				} // Sla commentaar en lege regels over

				if ( $line[0] === '/' ) {
					$excludes["directories"][] = substr( $line, 1 ); // Voeg directory toe (zonder '/')
				} elseif ( $line[0] === '-' ) {
					$excludes["files"][] = substr( $line, 1 ); // Voeg bestand toe (zonder '-')
				}
			}
		}

		return $excludes;
	}

	private function shouldExclude( $relativePath, $excludes ): bool {
		// Controleer directories
		foreach ( $excludes["directories"] as $dir ) {
			if ( str_starts_with( $relativePath, $dir ) ) { // Exacte match vanaf root
				return true;
			}
		}
		// Controleer bestanden
		foreach ( $excludes["files"] as $file ) {
			if ( $relativePath === $file ) { // Exact match vereist
				return true;
			}
		}

		return false;
	}

	private function copyFiles( $source, $dest, $excludes ): void {
		$source = realpath( $source );
		$dest   = realpath( $dest );

		$dirIterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $source, FilesystemIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $dirIterator as $item ) {
			$relativePath = str_replace( [ $source . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR ], [
				'',
				'/',
			],                           $item->getPathname() ); // Unix-achtige padnotatie

			// Sla over als het pad in de exclude lijst staat
			if ( $this->shouldExclude( $relativePath, $excludes ) ) {
				continue;
			}

			$targetPath = $dest . DIRECTORY_SEPARATOR . $relativePath;

			if ( $item->isDir() ) {
				if ( ! is_dir( $targetPath ) && ! mkdir( $targetPath, 0777, true ) && ! is_dir( $targetPath ) ) {
					throw new RuntimeException( sprintf( 'Directory "%s" was not created', $targetPath ) );
				}
			} else {
				copy( $item->getPathname(), $targetPath );
			}
		}
	}

	private function createZip( $sourceDir, $zipPath ): void {
		$zip = new ZipArchive();
		if ( $zip->open( $zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE ) === true ) {
			$sourceDir = realpath( $sourceDir );
			$files     = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $sourceDir, FilesystemIterator::SKIP_DOTS ),
				RecursiveIteratorIterator::LEAVES_ONLY
			);

			foreach ( $files as $file ) {
				$filePath     = $file->getRealPath();
				$relativePath = substr( $filePath, strlen( $sourceDir ) + 1 );

				$zip->addFile( $filePath, $relativePath );
			}

			$zip->close();
		} else {
			die( "Er is een fout opgetreden bij het aanmaken van het ZIP-bestand.\n" );
		}
	}

	private function deleteTempDir(): void {
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $this->tempDir, FilesystemIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $files as $file ) {
			$file->isDir() ? rmdir( $file ) : unlink( $file );
		}

		rmdir( $this->tempDir );
	}
}

$builder = new pluginbuilder();
$builder->build();
