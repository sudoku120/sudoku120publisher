[🇩🇪 Deutsche Version](README_DE.md)

# Sudoku120 Publisher

Easily embed Sudoku puzzles from Sudoku120.com into your WordPress site.

## Description

The Sudoku120 Publisher plugin allows you to easily integrate Sudokus from [webmaster.sudoku120.com](https://webmaster.sudoku120.com) into WordPress. It performs the following tasks:

- Copying the required CSS and JS files.
- Registering the necessary fonts.
- Storing the Sudoku HTML code directly in the database.
- Setting up a reverse proxy in case the user is unable to configure it themselves on the web server.
- Provides a shortcode to easily embed the Sudoku on pages and posts.

Thanks to the reverse proxy, no requests are sent to external servers from the browser, and all connections are made locally. This ensures data protection-compliant usage without external data transfers.

The reverse proxy can also be used for other purposes. However, no URLs are rewritten in the returned data, and no cookies are forwarded in both directions. The admin can create custom reverse proxies and configure whether the user IP, user agent, and/or referer should be forwarded. While it is not recommended to forward the IP address, some services require it.

There are optional response filters to the reverse proxy to improve security:

**json**: (application/json, application/x-json, application/ld+json)

**xml**: (application/xml, application/rss+xml, application/atom+xml, application/xslt+xml)

**txt**: (text/plain)

**utf8**: (text/html, application/xhtml+xml, application/javascript, application/x-javascript, text/css, text/csv, application/vnd.ms-excel, application/x-yaml, text/yaml, text/markdown, application/x-httpd-php)

**media**: (audio/mpeg, audio/wav, audio/x-wav, audio/ogg, audio/x-ogg, audio/flac, audio/mp4, audio/x-m4a, audio/aac,
video/mp4, video/webm, video/quicktime, video/x-msvideo, video/x-matroska, video/3gpp, video/x-flv, video/mpeg, video/x-m4v,
image/png, image/jpeg, image/pjpeg, image/gif, image/webp, image/svg+xml, image/bmp, image/avif, image/apng,
image/tiff, image/x-tiff, image/vnd.microsoft.icon, image/x-icon)

For json and xml, the response is validated for correct structure.
For json, xml, txt, and utf8 types, the response is checked for valid UTF-8 and control characters.
All types are validated by MIME type.

The reverse proxy also sends the `X-Content-Type-Options: nosniff` and `X-Robots-Tag: noindex, nofollow` headers by default.

There are various configuration options available for the Sudoku. The user can choose between pre-designed layouts or custom styling. Additionally, outgoing links can be enhanced with extra security features and opened in a new tab or window. The surrounding div element of the Sudoku can be customized with CSS classes, IDs, or direct style definitions.

A detailed tutorial video for setting up the plugin is available at: [YouTube Tutorial](https://www.youtube.com/watch?v=OAV-H_LYO2Y)

For error reports, please use the GitHub issue tracker: [GitHub Issues](https://github.com/sudoku120/sudoku120publisher/issues)

## Installation

1. Download the plugin from GitHub: [https://github.com/sudoku120/sudoku120publisher](https://github.com/sudoku120/sudoku120publisher)
2. Open your WordPress admin dashboard.
3. Go to “Plugins” → “Add New” → “Upload Plugin”.
4. Select the ZIP file and click “Install Now”.
5. Activate the plugin after installation.

The default settings are already optimized for most use cases.

To embed a Sudoku:

1. Visit [webmaster.sudoku120.com](https://webmaster.sudoku120.com) and create a new Sudoku.
2. Follow the setup instructions in the plugin under the “Sudoku120” menu.

## Frequently Asked Questions

### Do I need to configure a reverse proxy manually?
No. The plugin automatically sets up a local reverse proxy. Manual configuration is only necessary if you prefer server-side setup.

### Where do I get the Sudoku to embed?
Visit [webmaster.sudoku120.com](https://webmaster.sudoku120.com) to create and manage your Sudokus.

### Can I use the plugin on multiple pages or domains?
Each Sudoku can only be used on one URL. However, you can create and embed multiple Sudokus on your site. Since the Sudokus are domain-bound, if you create multiple Sudokus, they will all show the same Sudoku, which is useful for multilingual sites.

### What happens if I delete a Sudoku in WordPress?
The local HTML and the proxy configuration are removed.

### Do I need to load Google Fonts?
The plugin registers Google Fonts by default. If you prefer to host the fonts locally, you will need an additional plugin to handle local hosting.

### How can I style the embedded Sudoku?
You can assign custom CSS classes, IDs, or inline styles directly in the plugin settings.

### Is the plugin GDPR compliant?
Yes, the plugin stores CSS and JS files locally and routes API requests through a reverse proxy without forwarding user IP addresses. No third-party requests are made, ensuring GDPR compliance. For information regarding Google Fonts, please refer to the dedicated question.

## Screenshots

1. Settings page of the plugin  
   ![Settings Page](assets/screenshots/screenshot-1-settings-page.png)

2. Sudoku embedded on a page  
   ![Sudoku Embed](assets/screenshots/screenshot-2-sudoku-embed.png)

3. Plugin dashboard  
   ![Plugin Dashboard](assets/screenshots/screenshot-3-plugin-dashboard.png)

## Other Notes

### Customizability

The appearance of the Sudoku can be customized using CSS variables. Eight default designs are installed under `uploads/sudoku120publisher/designs/` and can be selected in the plugin settings. Custom designs placed in this directory are also available for selection. Since these are CSS variables, they can also be defined in the website’s main CSS, allowing for integration with the site's light/dark theme or any other design customizations.

The Sudoku itself is encapsulated in a shadow DOM, ensuring that its internal styles do not interfere with the website’s CSS.

The surrounding `div` element, as well as the links, are outside of the shadow DOM and can therefore be styled using the website’s main CSS.

## External Services

This plugin requires a free account at [webmaster.sudoku120.com](https://webmaster.sudoku120.com).

When the plugin is activated, a folder named `sudoku120publisher` is created in the WordPress uploads directory. The following files are downloaded from [webmaster.sudoku120.com](https://webmaster.sudoku120.com) and stored locally:
- JavaScript file for the Sudoku
- CSS file for the Sudoku
- 8 example design CSS files

These files are then served locally from the WordPress installation and are not loaded from the external server during normal operation.

When a new Sudoku puzzle is created, the Sudoku HTML is fetched from https://webmaster.sudoku120.com and stored in the database. The paths to JavaScript and CSS files are adjusted to point to local copies.

API calls are made when a Sudoku is loaded, a number is revealed, or the Sudoku is validated. These API calls are forwarded via a reverse proxy on the domain of the WordPress installation.

If the reverse proxy is automatically created during the Sudoku setup, only the HTTP referrer is sent to the external service. If the reverse proxy is manually configured (e.g., through web server settings), the user agent and the user's IP address (via X-Forwarded-For) may also be transmitted, depending on the configuration.

The data transmitted during these API calls includes:
- Sudoku-related gameplay data
- The domain (host) of the WordPress installation

No other user-specific or personal data is transmitted.

webmaster.sudoku120.com does not evaluate the user's IP address (X-Forwarded-For), user agent, or referrer. However, they are logged at the web server level.

Unless otherwise configured by the administrator, no personal data of users will be transmitted to webmaster.sudoku120.com. Additionally, no direct browser requests are made to https://webmaster.sudoku120.com.

In the default configuration, the plugin operates in compliance with data protection regulations and can be used without explicit user consent.

Privacy Policy: [https://webmaster.sudoku120.com/privacy-policy](https://webmaster.sudoku120.com/privacy-policy)  
Terms of Service: [https://webmaster.sudoku120.com/terms-of-service](https://webmaster.sudoku120.com/terms-of-service)
