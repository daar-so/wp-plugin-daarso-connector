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

## WP-CLI Commands ##
| Commando                                        | Doel                                                                               |
|-------------------------------------------------|------------------------------------------------------------------------------------|
| daarso api origin-id                            | Voeg de Guid-id die de website indentificeerd in Wp-manager                        |
| daarso api target-id                            | Voeg de Guid-id die de Wp-manager indentificeerd bij deze website                  |
| daarso api key                                  | Voeg public Ssh key toe                                                            |
| daarso version                                  | Versie van deze plugin                                                             |
| daarso update check                             | Handmatig controleren nieuwe update                                                |
| daarso config reset                             | Herstel de configuratie naar "factory instellingen"                                |
| daarso config renew connection                  | Vernieuw de connectoe gegevens (beide Guid-is en key                               |
| daarso config set entrance url <root url eg : https://543a-87-195-27-36.ngrok-free.app> | Voer de nieuwe url (root domein) toe waar de plugin zijn configuratie kan ophalen |

## Plugin Update Checker
Auteur: Yahnis Elsts (https://github.com/YahnisElsts/plugin-update-checker.git)

Wordt vanuit daarso_connector_updater aangeroepen om te controleren of er een nieuwe versie van "Daar-so.nl Connector" is 

## Tracer
Een utility voor ontwikkelaars om gemakkelijker te kunnen debuggen. 
In plaats van het php error log te gebruiken om trace informatie te vergaren,  schrijft de plugin deze nu naar het
bestand daarso_trace.log in de plugin root directory.

_Gebruik_: 

`Tracer::write("Een bericht met diverse paramters %s, %s, %d", $var1, $var2, $var3)`

Deze functie maakt gebruik van de PHP-functie sprintf, de parameters worden ook 1 op 1 doorgegeven aan sprintf.

Om de tracing te activeren definieer je in de wp-config.php de variabele DAARSO_TRACING.

`define('DAARSO_TRACING', true);`

Momenteel moet de waarde "true" zijn. Dit kan in de toekomst veranderen om een te gebruiken writer aan te geven.

De tracer kent nu 2 writes:
* Writer die naar het bestand daarso_trace.log schrijft (DAARSO_TRACING: true)
* Dummy writer, default, die doet verder niets (voor vergeten Tracer::write)


## Changelog

0.1.0 Omvormen daarso plugin (2.4.5) tot een platform onafhankelijke plugin.
0.1.1 Diverse fixes naar aanleiding van testen
0.1.2 Diverse fixes naar aanleiding van integratie testen
0.2.0 Automatische configuratie m.b.v. wpmanager tijdens activatie
      Tracing
      Source file loading nu m.b.v. spl_autoload_register
      Gecentraliseerde optie beheer/loading/saving
      Van enkele opties naamgeving verbeterd
      
3.0.0 Gereserveerd voor versie die uitrolbaar is. Deze plugin vervangt de oude "daarso plugin".
      De naam wijzigt wel, voor een goede werking van de Wordpress plugin installer moet de nieuwe versie wel
      hoger zijn dan de oude (2.4.5) vandaar
