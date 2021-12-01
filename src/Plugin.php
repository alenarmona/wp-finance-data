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
    public const API_ENDPOINT = 'http://api.exchangeratesapi.io/v1/';

    /**
     * API Key
     */
    public const API_KEY = '5569796b4ba36e1ea35dd85403c32b8d';
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
        register_widget(WidgetFiananceData::class);
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
        wp_enqueue_script(
            'bootstrap-css',
            plugins_url('/assets/js/vendor/bootstrap.min.css', __DIR__),
            [],
            '5.0.2',
            true
        );

        wp_enqueue_style(
            'wp-finance-general-css',
            plugins_url('/assets/css/style.css', __DIR__),
            [],
            Plugin::VERSION,
            'all'
        );

        //Javascript
        wp_enqueue_script(
            'bootstrap-js',
            plugins_url('/assets/js/vendor/bootstrap.min.js', __DIR__),
            ['jquery'],
            '5.0.2',
            true
        );

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
                'bootstrap-js',
            ],
            Plugin::VERSION,
            true
        );
        /*
        wp_localize_script('wp-exercise-general-js', 'exerciseAjaxInfo', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'action' => UserController::AJAX_ACTION,
            'nonce' => wp_create_nonce(UserController::AJAX_ACTION),
            ]);
        */
    }
}
