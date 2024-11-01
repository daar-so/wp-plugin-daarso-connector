# Daar-so.nl Connector
Deze plugin is onderdeel van het "daarso.nl webhostings platform"™. Het verzorgt de verbinding met WP-Manager™ en de websitesite.
Hierdoor wordt de website onderdeel van het platform ongeacht waar de website wordt gehost.  

## Opzet
Folders
- admin - alle sourcecode die wordt gebruikt voor de functies die worden aangeboden via WP-Admin en WP-CLI
- build - bevat de build script die de plugin installer (9)zip-bestand) genereert
- includes - sourcecode die generiek wordt gebruikt binnen deze plugin
- plugin-update-checker - Plugin Update Checker van Yahnis Elsts 
- public - sourcecode die wordt gebruikt voor de functies die worden gebruikt voor de website zelf en WP-AJAX
- vendor - enkele composer packages die uitsluiten voor het ontwikkelen worden gebruikt.

## Startpunt
Dit is het bestand daarso_connector.php. Dit bestand bevat dan ook de verplichte Wordpress header voor een plugin.   
Eerst worden de (de)activation en (un)install hook geregistreerd. Er zijn 4 van die speciale hooks. Momenteel gebruiken
we er maar 2 (activation en uninstall)

Direct daarna volgt de kickstart. De functie die de kern van de plugin instantieert en start wordt uitgevoerd.
De kern wordt gevormd door de class daarso_connector_core.
De core laadt de overige php bestanden in en registreert alle hooks en commands. Dan is de plugin klaar voor gebruik.

## Plugin Update Checker
Auteur: Yahnis Elsts (https://github.com/YahnisElsts/plugin-update-checker.git)

Wordt vanuit daarso_connector_updater aangeroepen om te controleren of er een nieuwe versie van "Daar-so.nl Connector" is 


## Changelog

0.1.0 Omvormen daarso plugin (2.4.5) tot een platform onafhankelijke plugin.
