=== Sudoku120publisher ===
Contributors: msdevcoder
Plugin URI: https://github.com/sudoku120/sudoku120publisher
Version: 1.0.1
Stable tag: 1.0.1
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.4
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Text Domain: sudoku120publisher
Domain Path: /lang

Easily embed Sudoku puzzles from Sudoku120.com into your WordPress site.

== Description ==

The Sudoku120 Publisher plugin allows you to easily integrate Sudokus from https://webmaster.sudoku120.com into WordPress. It performs the following tasks:

- Copying the required CSS and JS files.
- Registering the necessary fonts.
- Storing the Sudoku HTML code directly in the database.
- Setting up a reverse proxy in case the user is unable to configure it themselves on the web server.
- Provides a shortcode to easily embed the Sudoku on pages and posts.

Thanks to the reverse proxy, no requests are sent to external servers from the browser, and all connections are made locally. This ensures data protection-compliant usage without external data transfers.

The reverse proxy can also be used for other purposes. However, no URLs are rewritten in the returned data. To allow the transmission of the user’s IP address, user agent, and referrer, cURL must be available on the server. While it is not recommended to forward the IP address, some services require it.

There are various configuration options available for the Sudoku. The user can choose between pre-designed layouts or custom styling. Additionally, outgoing links can be enhanced with extra security features and opened in a new tab or window. The surrounding div element of the Sudoku can be customized with CSS classes, IDs, or direct style definitions.

A detailed tutorial video for setting up the plugin is available at: https://www.youtube.com/watch?v=OAV-H_LYO2Y

For error reports, please use the GitHub function: https://github.com/sudoku120/sudoku120publisher/issues


== Description de ==

Das Sudoku120 Publisher Plugin ermöglicht es, Sudokus von https://webmaster.sudoku120.com einfach in WordPress zu integrieren. Es übernimmt die folgenden Aufgaben:

- Kopieren der erforderlichen CSS- und JS-Dateien.
- Registrieren der benötigten Zeichensätze.
- Speichern des HTML-Codes für das Sudoku direkt in der Datenbank.
- Einrichten eines Reverse-Proxys für den Fall, dass der Benutzer diesen nicht selbst auf dem Webserver konfigurieren kann.
- Bietet einen Shortcode zur einfachen Einbindung des Sudokus auf Seiten und in Beiträgen.

Dank des Reverse-Proxys werden keine Anfragen vom Browser an fremde Server gesendet, sondern alle Verbindungen erfolgen lokal. Dies sorgt für eine datenschutzkonforme Nutzung ohne externe Datenübertragungen.

Der Reverse-Proxy kann auch für andere Zwecke verwendet werden. Dabei werden jedoch keine URLs in den Rückgaben umgeschrieben. Um die Übertragung der User-IP-Adresse, des User-Agents und des Referrers zu ermöglichen, ist cURL auf dem Server erforderlich. Die Weitergabe der IP-Adresse wird zwar nicht empfohlen, aber es gibt auch Dienste, die dies benötigen.

Es stehen verschiedene Einstellungsmöglichkeiten für das Sudoku zur Verfügung. So kann der Benutzer zwischen verschiedenen vorgefertigten Designs oder einer eigenen Gestaltung wählen. Weiterhin können ausgehende Links mit zusätzlichen Sicherheitsmerkmalen versehen und in einem neuen Tab oder Browserfenster geöffnet werden. Das umgebende div-Element des Sudokus kann mit benutzerdefinierten CSS-Klassen, IDs oder direkten Style-Definitionen versehen werden.

Ein ausführliches Tutorial-Video zum Setup des Plugins ist verfügbar unter: https://www.youtube.com/watch?v=OAV-H_LYO2Y

Für Fehlermeldungen bitte die GitHub-Funktionn nutzen: https://github.com/sudoku120/sudoku120publisher/issues

== Installation ==

1. Download the plugin from GitHub: https://github.com/sudoku120/sudoku120publisher
2. Open your WordPress admin dashboard.
3. Go to “Plugins” → “Add New” → “Upload Plugin”.
4. Select the ZIP file and click “Install Now”.
5. Activate the plugin after installation.

The default settings are already optimized for most use cases.

To embed a Sudoku:

1. Visit https://webmaster.sudoku120.com and create a new Sudoku.
2. Follow the setup instructions in the plugin under the “Sudoku120” menu.

== Installation de ==

1. Lade das Plugin von GitHub herunter: https://github.com/sudoku120/sudoku120publisher
2. Öffne deine WordPress-Adminoberfläche.
3. Gehe zu „Plugins“ → „Installieren“ → „Plugin hochladen“.
4. Wähle die ZIP-Datei aus und klicke auf „Jetzt installieren“.
5. Aktiviere das Plugin nach der Installation.

Die Standardeinstellungen sind bereits für die meisten Anwendungsfälle optimal gesetzt.

Um ein Sudoku einzubinden:

1. Besuche https://webmaster.sudoku120.com und erstelle dort ein Sudoku.
2. Folge den Anweisungen im Plugin unter dem Menüpunkt „Sudoku120“ zur Einrichtung.


== Frequently Asked Questions ==

== Q: Do I need to configure a reverse proxy manually? ==
A: No. The plugin automatically sets up a local reverse proxy. Manual configuration is only necessary if you prefer server-side setup.

= Q: Where do I get the Sudoku to embed? =
A: Visit https://webmaster.sudoku120.com to create and manage your Sudokus.

= Q: Can I use the plugin on multiple pages or domains? =
A: Each Sudoku can only be used on one URL. However, you can create and embed multiple Sudokus on your site. Since the Sudokus are domain-bound, if you create multiple Sudokus, they will all show the same Sudoku, which is useful for multilingual sites.

= Q: What happens if I delete a Sudoku in WordPress? =
A: The local HTML and the proxy configuration are removed.

= Q: Do I need to load Google Fonts? =
A: The plugin registers Google Fonts by default. If you prefer to host the fonts locally, you will need an additional plugin to handle local hosting.

= Q: How can I style the embedded Sudoku? =
A: You can assign custom CSS classes, IDs, or inline styles directly in the plugin settings.

= Q: Is the plugin GDPR compliant? =
A: Yes, the plugin stores CSS and JS files locally and routes API requests through a reverse proxy without forwarding user IP addresses. No third-party requests are made, ensuring GDPR compliance. For information regarding Google Fonts, please refer to the dedicated question.

== Frequently Asked Questions de ==

= F: Muss ich einen Reverse-Proxy manuell konfigurieren? =
A: Nein. Das Plugin richtet automatisch einen lokalen Reverse-Proxy ein. Eine manuelle Konfiguration ist nur notwendig, wenn du eine serverseitige Einrichtung bevorzugst.

= Q: Wo bekomme ich das Sudoku, das ich einbinden möchte? =
A: Besuche https://webmaster.sudoku120.com, um Sudokus zu erstellen und zu verwalten.

= Q: Kann ich das Plugin auf mehreren Seiten oder Domains verwenden? =
A: Jedes Sudoku kann nur auf einer URL verwendet werden. Du kannst jedoch mehrere Sudokus auf deiner Seite erstellen und einbinden. Da die Sudokus domainengebunden sind, werden alle erstellten Sudokus dasselbe Sudoku anzeigen, was besonders für mehrsprachige Seiten nützlich ist.

= Q: Was passiert, wenn ich ein Sudoku in WordPress lösche? =
A: Das lokale HTML und die Proxy-Konfiguration werden entfernt.

= Q: Muss ich Google Fonts laden? =
A: Das Plugin registriert standardmäßig Google Fonts. Wenn du die Schriftarten lieber lokal hosten möchtest, benötigst du ein zusätzliches Plugin, das das lokale Hosting übernimmt.

= Q: Wie kann ich das eingebettete Sudoku gestalten? =
A: Du kannst benutzerdefinierte CSS-Klassen, IDs oder Inline-Stile direkt in den Plugin-Einstellungen zuweisen.

= Q: Ist das Plugin DSGVO-konform? =
A: Ja, das Plugin speichert CSS- und JS-Dateien lokal und leitet API-Anfragen über einen Reverse-Proxy, ohne die IP-Adressen der Benutzer weiterzuleiten. Es werden keine Anfragen an Dritte gestellt, was die DSGVO-Konformität gewährleistet. Weitere Informationen zu Google Fonts findest du in der entsprechenden Frage.

== Screenshots ==

1. Settings page of the plugin
   Screenshot: assets/screenshots/screenshot-1-settings-page.png

2. Sudoku embedded on a page
   Screenshot: assets/screenshots/screenshot-2-sudoku-embed.png

3. Plugin dashboard
   Screenshot: assets/screenshots/screenshot-3-plugin-dashboard.png

== Screenshots de ==

1. Einstellungsseite des Plugins
   Screenshot: assets/screenshots/screenshot-1-settings-page.png

2. Sudoku auf einer Seite eingebunden
   Screenshot: assets/screenshots/screenshot-2-sudoku-embed.png

3. Plugin-Dashboard
   Screenshot: assets/screenshots/screenshot-3-plugin-dashboard.png

== Other Notes ==

= Customizability =

The appearance of the Sudoku can be customized using CSS variables. Eight default designs are installed under `uploads/sudoku120publisher/designs/` and can be selected in the plugin settings. Custom designs placed in this directory are also available for selection in the settings. Since these are CSS variables, they can also be defined in the website’s main CSS, allowing for integration with the site's light/dark theme or any other design customizations.

The Sudoku itself is encapsulated in a shadow DOM, ensuring that its internal styles do not interfere with the website’s CSS.

The surrounding `div` element, as well as the links, are outside of the shadow DOM and can therefore be styled using the website’s main CSS.

== Other Notes de ==

= Anpassbarkeit =

Das Aussehen des Sudokus kann über CSS-Variablen definiert werden. Acht Standard-Designs werden unter `uploads/sudoku120publisher/designs/` installiert und sind in den Einstellungen auswählbar. Eigene Designs, die in diesem Verzeichnis abgelegt werden, sind ebenfalls in den Einstellungen auswählbar. Da es sich um CSS-Variablen handelt, können sie auch im normalen Seiten-CSS definiert werden, was eine Integration mit dem Light-/Dark-Design der Webseite oder anderen Anpassungen ermöglicht.

Das Sudoku selbst wird durch ein Shadow DOM vollständig von der restlichen Webseite abgekapselt, sodass sich dessen interne Styles nicht mit dem CSS der Webseite überschneiden.

Das umgebende `div`-Element sowie die Links befinden sich außerhalb des Shadow DOM und können daher mit dem CSS der Webseite angepasst werden.


== Changelog ==

= 1.0.0 =
* Initial release of Sudoku120 Publisher plugin.

= 1.0.1 =
* Added additional sanitation functions to improve code quality and eliminate warnings in the plugin checker.
* Updated `readme.txt` to meet WordPress Plugin Directory requirements and enhance the plugin description.
