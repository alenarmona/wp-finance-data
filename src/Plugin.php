<?php

declare(strict_types=1);

namespace WpFinance;

use WpFinance\Settings;
use WpFinance\WidgetFinanceData;

class Plugin
{
    /**
    * Plugin Version
    *
    * @var string
    */
    public const VERSION = '0.1.0';

    /**
     * The settings object
     *
     * @var Settings
     */
    private $settings;

    /**
     * API Endpoint
     */
    public const API_ENDPOINT = 'https://freecurrencyapi.net/api/v2/';

    /**
     * API Key
     */
    //public const API_KEY = '123ee660-536c-11ec-9bc9-9fd27e959b97';
    public const API_KEY = '2c07f5c0-5389-11ec-88f3-f3b86f0fbfb0';
    
    /**
     * Init all actions and filters
     */
    public function init()
    {
        $this->addHooks();

        //Initialize settings
        $settings = $this->settings();
    }

    /**
     * Add hooks and actions
     */
    public function addHooks()
    {
        //Register scripts and styles
        if (function_exists('is_admin')) { //added for phpunit
            if (!is_admin()) {
                add_action('wp_enqueue_scripts', [$this, 'enqueueFrontendScripts']);
            }
        }

        //Setup actions and filters
        add_action('widgets_init', [$this, 'registerFinanceWidget']);
    }

    // Register and load the widget
    public function registerFinanceWidget()
    {
        register_widget(WidgetFinanceData::class);
    }

    /**
     * Get Plugin settings page
     *
     * @return Settings
     */
    public function settings(): Settings
    {
        if (null === $this->settings) {
            $this->settings = new Settings();
            $this->settings->init();
        }

        return $this->settings;
    }

    /**
     * All things must done in load
     *
     * phpcs:disable Generic.Metrics.NestingLevel.TooHigh
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
     */
    public function enqueueFrontendScripts()
    {
        //Stylesheets
        wp_enqueue_style(
            'bootstrap-css',
            plugins_url('/assets/css/vendor/bootstrap.min.css', __DIR__),
            [],
            '5.0.2',
            'all'
        );

        wp_enqueue_style(
            'wp-finance-general-css',
            plugins_url('/assets/css/style.css', __DIR__),
            [],
            Plugin::VERSION,
            'all'
        );

        //Javascript
        /*
        wp_enqueue_script(
            'bootstrap-js',
            plugins_url('/assets/js/vendor/bootstrap.min.js', __DIR__),
            ['jquery'],
            '5.0.2',
            true
        );
        */

        wp_enqueue_script(
            'chart-js',
            plugins_url('/assets/js/vendor/chart.min.js', __DIR__),
            ['jquery'],
            '3.6.1',
            true
        );

        wp_enqueue_script(
            'wp-finance-general-js',
            plugins_url('/assets/js/general.js', __DIR__),
            [
                'jquery',
            ],
            Plugin::VERSION,
            true
        );

        wp_localize_script('wp-finance-general-js', 'ajaxInfo', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'action' => WidgetFinanceData::AJAX_ACTION,
                'nonce' => wp_create_nonce(WidgetFinanceData::AJAX_ACTION),
            ]);
    }
}
