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

    $cc = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? '';
    if (!$cc && function_exists('get_user_country_code')) $cc = get_user_country_code() ?: '';
    $cc = strtoupper($cc);

    $desired = ($cc === 'SA') ? 'ar' : 'en';

    $current = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : apply_filters('wpml_current_language', null);
    if ($current === $desired) return;

    $scheme = is_ssl() ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $uri  = $_SERVER['REQUEST_URI'] ?? '/';
    $here = $scheme . '://' . $host . $uri;

    $target = apply_filters('wpml_permalink', $here, $desired);
    if (!$target) return;
    if (rtrim($target, '/') === rtrim($here, '/')) return;

    wp_safe_redirect($target, 302);
    exit;
}, 10);
