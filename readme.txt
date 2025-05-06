=== Sudoku120 Publisher ===
Contributors: msdevcoder
Plugin URI: https://github.com/sudoku120/sudoku120publisher
Version: 1.0.3
Stable tag: 1.0.3
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

The reverse proxy can also be used for other purposes. However, no URLs are rewritten in the returned data, and no cookies are forwarded in both directions. The admin can create custom reverse proxies and configure whether the user IP, user agent, and/or referer should be forwarded. While it is not recommended to forward the IP address, some services require it.

There are optional response filters to the reverse proxy to improve security:

json: (application/json, application/x-json, application/ld+json)

xml: (application/xml, application/rss+xml, application/atom+xml, application/xslt+xml)

txt: (text/plain)

utf8: (text/html, application/xhtml+xml, application/javascript, application/x-javascript, text/css, text/csv, application/vnd.ms-excel, application/x-yaml, text/yaml, text/markdown, application/x-httpd-php)

media: (audio/mpeg, audio/wav, audio/x-wav, audio/ogg, audio/x-ogg, audio/flac, audio/mp4, audio/x-m4a, audio/aac,
video/mp4, video/webm, video/quicktime, video/x-msvideo, video/x-matroska, video/3gpp, video/x-flv, video/mpeg, video/x-m4v,
image/png, image/jpeg, image/pjpeg, image/gif, image/webp, image/svg+xml, image/bmp, image/avif, image/apng,
image/tiff, image/x-tiff, image/vnd.microsoft.icon, image/x-icon)

For json and xml, the response is validated for correct structure.
For json, xml, txt, and utf8 types, the response is checked for valid UTF-8 and control characters.
All types are validated by MIME type.

The reverse proxy also sends the `X-Content-Type-Options: nosniff` and `X-Robots-Tag: noindex, nofollow` headers by default.

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

Der Reverse-Proxy kann auch für andere Zwecke verwendet werden. Dabei werden jedoch keine URLs in den Rückgaben umgeschrieben und es werden in beiden Richtungen keine Cookies übertragen. Der Admin kann beliebige Reverse-Proxys anlegen und dabei einstellen, ob die User-IP, der User-Agent und/oder der Referer weitergeleitet werden sollen. Die Weitergabe der IP-Adresse wird zwar nicht empfohlen, aber es gibt auch Dienste, die dies benötigen.

Es gibt aktivierbare Mime Type Filter im Reverse Proxy eingebaut, um die Sicherheit zu erhöhen:

json: (application/json, application/x-json, application/ld+json)

xml: (application/xml, application/rss+xml, application/atom+xml, application/xslt+xml)

txt: (text/plain)

utf8: (text/html, application/xhtml+xml, application/javascript, application/x-javascript, text/css, text/csv, application/vnd.ms-excel, application/x-yaml, text/yaml, text/markdown, application/x-httpd-php)

media: (audio/mpeg, audio/wav, audio/x-wav, audio/ogg, audio/x-ogg, audio/flac, audio/mp4, audio/x-m4a, audio/aac,
video/mp4, video/webm, video/quicktime, video/x-msvideo, video/x-matroska, video/3gpp, video/x-flv, video/mpeg, video/x-m4v,
image/png, image/jpeg, image/pjpeg, image/gif, image/webp, image/svg+xml, image/bmp, image/avif, image/apng,
image/tiff, image/x-tiff, image/vnd.microsoft.icon, image/x-icon)

Bei json und xml wird die Antwort auf korrektes Format geprüft.
Bei json, xml, txt und utf8 erfolgt eine Prüfung auf gültiges UTF-8 und Steuerzeichen.
Alle Formate werden anhand ihres MIME-Typs überprüft.

Der Reverse Proxy sendet außerdem standardmäßig die Header `X-Content-Type-Options: nosniff` und `X-Robots-Tag: noindex, nofollow`.

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

Das Aussehen des Sudoku kann über CSS-Variablen definiert werden. Acht Standard-Designs werden unter `uploads/sudoku120publisher/designs/` installiert und sind in den Einstellungen auswählbar. Eigene Designs, die in diesem Verzeichnis abgelegt werden, sind ebenfalls in den Einstellungen auswählbar. Da es sich um CSS-Variablen handelt, können sie auch im normalen Seiten-CSS definiert werden, was eine Integration mit dem Light-/Dark-Design der Webseite oder anderen Anpassungen ermöglicht.

Das Sudoku selbst wird durch ein Shadow DOM vollständig von der restlichen Webseite abgekapselt, sodass sich dessen interne Styles nicht mit dem CSS der Webseite überschneiden.

Das umgebende `div`-Element sowie die Links befinden sich außerhalb des Shadow DOM und können daher mit dem CSS der Webseite angepasst werden.

== External services ==

This plugin requires a free account at https://webmaster.sudoku120.com

When the plugin is activated, a folder named "sudoku120publisher" is created in the WordPress uploads directory. The following files are downloaded from https://webmaster.sudoku120.com and stored locally:
- JavaScript file for the Sudoku
- CSS file for the Sudoku
- 8 example design CSS files (only setting CSS variables)

These files are then served locally from the WordPress installation and are not loaded from the external server during normal operation.

When creating a new Sudoku, the Sudoku HTML is fetched from https://webmaster.sudoku120.com and stored in the database. During this process, the paths to the JavaScript and CSS files are adjusted to point to the local copies.

API calls are made when a Sudoku is loaded, a number is revealed, or when a Sudoku is validated. These API calls are routed through a reverse proxy on the domain of the WordPress installation.
If the reverse proxy is automatically created during Sudoku configuration, only the HTTP referer is forwarded to the external service.
If the reverse proxy is manually configured (e.g., directly in the web server settings), depending on the configuration, additional headers such as the user's IP address (via X-Forwarded-For) and the user agent string may also be transmitted.

The data transmitted during these API calls includes:
- Sudoku-related gameplay data
- The domain (host) of the WordPress installation
No other user-specific or personal data is included.

webmaster.sudoku120.com does not evaluate or analyze the user IP address (via X-Forwarded-For), user agent, or referer data. However, user agent and referer information will be logged at the web server level.

Unless the site administrator explicitly configures otherwise, no personal user data is transmitted to webmaster.sudoku120.com. Additionally, no direct browser requests are made to https://webmaster.sudoku120.com.

With the default configuration, the plugin operates in a privacy-compliant manner and can be used without requiring explicit user consent.

Privacy Policy: https://webmaster.sudoku120.com/privacy-policy
Terms of Service: https://webmaster.sudoku120.com/terms-of-service

== External services de ==

Dieses Plugin erfordert ein kostenloses Konto auf https://webmaster.sudoku120.com

Beim Aktivieren des Plugins wird im Uploads-Verzeichnis der WordPress-Installation ein Ordner namens "sudoku120publisher" erstellt. Die folgenden Dateien werden von https://webmaster.sudoku120.com heruntergeladen und lokal gespeichert:
- JavaScript-Datei für das Sudoku
- CSS-Datei für das Sudoku
- 8 Beispiel-Design-CSS-Dateien (diese setzen nur CSS-Variablen)

Diese Dateien werden anschließend lokal von der WordPress-Installation ausgeliefert und nicht während des normalen Betriebs vom externen Server geladen.

Beim Anlegen eines neuen Sudokus wird das Sudoku-HTML von https://webmaster.sudoku120.com abgerufen und in der Datenbank gespeichert. Dabei werden die Pfade zu JavaScript- und CSS-Dateien auf die lokalen Kopien angepasst.

API-Aufrufe erfolgen, wenn ein Sudoku geladen, eine Zahl aufgedeckt oder ein Sudoku überprüft wird. Diese API-Aufrufe werden über einen Reverse Proxy auf der Domain der WordPress-Installation weitergeleitet.
Wird der Reverse Proxy automatisch während der Sudoku-Konfiguration erstellt, wird nur der HTTP-Referer an den externen Dienst weitergegeben.
Wird der Reverse Proxy manuell konfiguriert (z. B. direkt in den Webserver-Einstellungen), können je nach Konfiguration zusätzlich der User-Agent und die IP-Adresse des Nutzers (über X-Forwarded-For) übertragen werden.

Die bei diesen API-Aufrufen übermittelten Daten umfassen:
- Sudoku-bezogene Spieldaten
- Die Domain (Host) der WordPress-Installation
Es werden keine weiteren nutzerspezifischen oder personenbezogenen Daten übermittelt.

webmaster.sudoku120.com wertet weder die IP-Adresse des Nutzers (X-Forwarded-For), noch den User-Agent oder den Referer aus. User-Agent und Referer werden jedoch auf Webserver-Ebene protokolliert.

Sofern der Administrator der Website keine anderslautende Konfiguration vornimmt, werden keine personenbezogenen Daten der Nutzer an webmaster.sudoku120.com übermittelt. Zudem erfolgen keine direkten Browseranfragen an https://webmaster.sudoku120.com.

In der Standardkonfiguration arbeitet das Plugin datenschutzkonform und kann ohne ausdrückliche Zustimmung der Nutzer verwendet werden.

Datenschutzerklärung: https://webmaster.sudoku120.com/de/datenschutzerklaerung
Nutzungsbedingungen: https://webmaster.sudoku120.com/de/nutzungsbedingungen

== Changelog ==

= 1.0.0 =
* Initial release of Sudoku120 Publisher plugin.

= 1.0.1 =
* Added additional sanitation functions to improve code quality and eliminate warnings in the plugin checker.
* Updated `readme.txt` to meet WordPress Plugin Directory requirements and enhance the plugin description.

= 1.0.2 =
* Removed usage of cURL to improve compatibility and security.
* Added additional sanitization and escaping to meet WordPress Plugin Directory requirements.
* Revised readme.txt and added detailed information about the external service integration.

= 1.0.3 =
* Added selectable MIME type filter and content validation to the reverse proxy
* Added `X-Content-Type-Options: nosniff` and `X-Robots-Tag: noindex, nofollow` headers to reverse proxy responses
* Added daily cron job for status check of all Sudokus
