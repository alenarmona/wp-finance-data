<?php

declare(strict_types=1);

/*
 * The uninstall routine.
 */
use WpFinance\Plugin;

if (! defined('WP_UNINSTALL_PLUGIN')) {
    die();
}

if (! class_exists('WpFinance\Plugin')) {
    require_once __DIR__ . '/src/Plugin.php';
}

//delete_option(Plugin::OPTION_API_SECRET);
