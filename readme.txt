# Geo Language Redirect (WPML)

A tiny WordPress plugin that forces English for all countries, and shows Arabic only for Saudi Arabia (SA).
This plugin depends on WPML to generate the correct language URL for the current page.

## What it does
- If visitor’s country ≠ SA → redirect to the English version of the same page.
- If visitor’s country = SA → keep/show the Arabic version.
- Redirects to the equivalent URL (uses `wpml_permalink`), not just the home page.
- Skips admin, AJAX, REST, feeds, and the login page.

## Requirements
- WordPress 6.x+
- PHP 7.4+
- WPML (SitePress) active
- WPML → Languages → Language URL format = “Different languages in directories”
- Default language: Arabic
- Disable “Use directory for default language” so:
  - Arabic → `/`
  - English → `/en/`

Country detection:
- Uses Cloudflare header `HTTP_CF_IPCOUNTRY` if available.
- Otherwise, calls your `get_user_country_code()` function if it exists.

## Installation
Option A (normal plugin):
- Place this file at: `wp-content/plugins/geo-language-redirect/geo-language-redirect.php`
- Activate from WordPress → Plugins.

Option B (MU-plugin, always-on):
- Place this file at: `wp-content/mu-plugins/geo-language-redirect.php`
- No activation needed.

## How it works
1) Detect country code from Cloudflare header or `get_user_country_code()`.
2) Decide desired language: `SA` → `ar`, else → `en`.
3) If current WPML language differs, redirect to the same URL in the desired language using:
   `apply_filters('wpml_permalink', $current_url, $desired_lang)`.

## Testing
- From a non-Saudi location you should land on `/en/...`.
- From Saudi Arabia you should see Arabic on `/` (and Arabic equivalents elsewhere).
- For manual tests, temporarily force `$cc = 'SA'` or `$cc = 'EG'` and reload in a private window.

## Cache/CDN notes
Ensure your cache/CDN won’t serve Arabic pages to non-Saudi visitors (and vice versa).
Consider varying cache by country or excluding redirect paths from cache.

## Author
Saint Hossam — https://github.com/SaintHossam/
*/