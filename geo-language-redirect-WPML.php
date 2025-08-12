<?php
/**
 * Plugin Name: Geo Language Redirect WPML
 * Description: Force English for all countries except Saudi Arabia (Arabic) using WPML.
 * Version: 1.0.0
 * Author: Saint Hossam
 * Author URI: https://github.com/SaintHossam/
 */

if (!defined('ABSPATH')) exit;

add_action('template_redirect', function () {
    if (is_admin() || wp_doing_ajax() || is_customize_preview() || is_feed() || (defined('REST_REQUEST') && REST_REQUEST)) return;
    if (strpos($_SERVER['REQUEST_URI'] ?? '', '/wp-login.php') !== false) return;
    if (!defined('ICL_SITEPRESS_VERSION')) return;
    if (!empty($_COOKIE['geo_lang_lock'])) return;

    // Current Language
    $current = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : apply_filters('wpml_current_language', null);

    //Is the visit the result of a "language switch"? (Referrer is from the same domain and has a different language)
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    $ref_host = $ref ? parse_url($ref, PHP_URL_HOST) : '';
    $this_host = $_SERVER['HTTP_HOST'] ?? '';
    if ($ref && $ref_host && strcasecmp($ref_host, $this_host) === 0) {
        $ref_path = parse_url($ref, PHP_URL_PATH) ?: '/';
        $ref_is_en = (bool) preg_match('~^/en(?:/|$)~i', rtrim($ref_path, '/'));
        $curr_is_en = ($current === 'en');

        // If ref is in a different language than the current one, then this is a manual switch → force lock
        if ($ref_is_en !== $curr_is_en) {
            setcookie('geo_lang_lock', '1', time() + 365 * DAY_IN_SECONDS, '/', $this_host, is_ssl(), true);
            return;
        }
    }

    // State disclosure
    $cc = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? '';
    if (!$cc && function_exists('get_user_country_code')) {
        $cc = get_user_country_code() ?: '';
    }
    $cc = strtoupper($cc);

    $desired = ($cc === 'SA') ? 'ar' : 'en';
    if ($current === $desired) return;

    $scheme = is_ssl() ? 'https' : 'http';
    $here   = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '/');
    $target = apply_filters('wpml_permalink', $here, $desired);

    if (!$target) return;
    if (rtrim($target, '/') === rtrim($here, '/')) return;

    wp_safe_redirect($target, 302);
    exit;
}, 10);
