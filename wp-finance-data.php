<?php // phpcs:ignore

/**
 * Plugin Name:  WordPress Finance Data Widget
 * Plugin URI:   https://github.com/alenarmona/wp-finance-data.git
 * Description:  WordPress Widget for display Finantial Data
 * Author:       Alejandro Narmona
 * URI:          https://www.aride.com.ar
 * Version:      0.1.0
 * Text Domain:  wp-finance-data
 * Domain Path:  /languages
 * License:      GPLv2+
 * License URI:  LICENSE
 */

use WpFinance\Plugin;

/**
 * Function for getting plugin class
 *
 * phpcs:disable NeutronStandard.Globals.DisallowGlobalFunctions.GlobalFunctions
 *
 * @return WpFinance\Plugin
 */
function wp_finance_widget()
{
    static $plugin;

    if (null !== $plugin) {
        return $plugin;
    }

    if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 70200) {
        return null;
    }

    $autoload = __DIR__ . '/vendor/autoload.php';

    if (! class_exists(Plugin::class) && file_exists($autoload)) {
        require_once $autoload;
    }

    $plugin = new Plugin(__FILE__);
    $plugin->init();

    return $plugin;
}

/**
 * Run
 */
if (function_exists('add_action')) {
    add_action('plugins_loaded', 'wp_finance_widget');
}
