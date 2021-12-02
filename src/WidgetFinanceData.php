<?php

declare(strict_types=1);

namespace WpFinance;

use WpFinance\Plugin;
use WP_Widget;

class WidgetFinanceData extends WP_Widget
{
    //Ajax action name for calls
    public const AJAX_ACTION = 'update_exchange_chart';

    //Cache keys
    private const CACHE_LAST_EXCHANGE_RATES = 'last_exchange_rates';
    private const CACHE_HISTORICAL_EXCHANGE_RATES = 'historical_exchange_rates';

    public function __construct()
    {
        parent::__construct(
            'wp_finance_data',
            __('Widget Finance Data', 'wp-finance-data'),
            [
                'customize_selective_refresh' => true,
            ]
        );

        add_action('wp_ajax_' . self::AJAX_ACTION, [$this, 'updateRatesChart']);
        add_action('wp_ajax_nopriv_' . self::AJAX_ACTION, [$this, 'updateRatesChart']);
    }

    /**
     * Creating widget Backend
     */
    public function form($instance)
    {
        // Set widget defaults
        $defaults = [
            'title' => 'Finance Data',
            'base_currency' => 'USD',
            'symbols' => 'EUR,ARS,CAD,MXN,AUD',
        ];

        // Parse current settings with defaults
        extract(wp_parse_args((array)$instance, $defaults)); ?>

        <?php // Widget Title ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php __('Widget Title', 'wp-finance-data'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
                    name="<?php echo esc_attr($this->get_field_name('title')); ?>" 
                    type="text" value="<?php echo esc_attr($title); ?>" />
        </p>

        <?php // Base Currency ?>
        <p>
            <label for="<?php echo $this->get_field_id('base_currency'); ?>"><?php _e('Primary Currency', 'wp-finance-data' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('base_currency')); ?>" 
                    name="<?php echo esc_attr($this->get_field_name('base_currency')); ?>" 
                    type="text" value="<?php echo esc_attr($base_currency); ?>" />
        </p>

        <?php // Symbols ?>
        <p>
            <label for="<?php echo $this->get_field_id('symbols'); ?>"><?php _e( 'Cross Currencies', 'wp-finance-data'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('symbols')); ?>" 
                    name="<?php echo esc_attr($this->get_field_name('symbols')); ?>" 
                    type="text" value="<?php echo esc_attr($symbols); ?>" />
        </p>       
        <?php
    }
    /**
     * Updating widget replacing old instances with new
     */
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = isset($new_instance['title'] ) ? wp_strip_all_tags($new_instance['title']) : '';
        $instance['base_currency'] = isset($new_instance['base_currency'] ) ? wp_strip_all_tags($new_instance['base_currency']) : '';
        $instance['symbols'] = isset($new_instance['symbols']) ? wp_strip_all_tags($new_instance['symbols']) : '';

        return $instance;
    }


    /**
     * Creating widget front-end 
     */
    public function widget($args, $instance)
    {
        extract($args);

        // Check the widget options
        $title = isset($instance['title']) ? apply_filters('widget_title', $instance['title']) : '';
        $base_currency = isset($instance['base_currency']) ? wp_strip_all_tags($instance['base_currency']) : '';
        $symbols = isset($instance['symbols']) ? wp_strip_all_tags($instance['symbols']) : '';

        // WordPress core before_widget hook (always include )
        echo $before_widget;

       // Display the widget
       echo '<div class="widget finance-data-widget">';

        // Display widget title if defined
        if ($title) {
            echo $before_title . $title . $after_title;
        }
        //Chart rates
        //$historicalRates = $this->retrieveHistoricalRates('2021-11-23', '2021-11-30');
        //echo $this->createHistoricalChart($historicalRates, $symbols);
        echo '<div class="chart-container">';
        echo '  <canvas id="chart-rates" height="350"></canvas>';
        echo '</div>';

        //Table rates
        $lastRates = $this->retrieveExchangeRates($symbols);
        echo $this->createExchangeRatesTable($lastRates, $symbols);

        echo '</div>';

        // WordPress core after_widget hook (always include )
        echo $after_widget;
    }
    
    /**
     * Creates HTML table with latest rates
     */
    public function createExchangeRatesTable($rates, $symbols): string
    {
        $ratesRows = '';
        $currencies = explode(",", $symbols);

        $arrRates = (array)$rates->data;
        foreach ($currencies as $currency) {
            $ratesRows .= $this->createRateRow($rates->query->base_currency, $currency, $arrRates[$currency]);
        }
        $html = sprintf('
            <table class="table">
            <thead>
            <tr>
                <th scope="col">Currencies</th>                
                <th scope="col">Rate</th>
            </tr>
            </thead>
            <tbody>
            %s
            </tbody>
        </table>', $ratesRows);

        return $html;
    }

    /**
     * Get the last extranche rates endpoiont
     */
    public function getLatestRatesEndpoint($symbols): string
    {
        //$symbolsStr = implode(",", $this->currencies());
        //By default base currency is EUR, and cannot be changed because is restricted by FREE API.
        $baseEndpoint = Plugin::API_ENDPOINT . 'latest?appkey=' . Plugin::API_KEY;

        return $baseEndpoint . '&symbols=' . $symbols;
    }

    /**
     * Get the last extranche rates endpoiont
     */
    public function getHistoricalRatesEndpoint($startDate, $endDate): string
    {
        //$symbolsStr = implode(",", $this->currencies());
        //By default base currency is EUR, and cannot be changed because is restricted by FREE API.
        $baseEndpoint = Plugin::API_ENDPOINT . 'historical?appkey=' . Plugin::API_KEY;

        return $baseEndpoint . '&date_from=' . $startDate . '&date_to=' . $endDate;
    }

    /*
     * Get Latest Exchange Rates
     */
    public function retrieveExchangeRates($symbols)
    {
        $exchangeRates = get_transient(self::CACHE_LAST_EXCHANGE_RATES);

        if ($exchangeRates === false) {
            $apiResponse = wp_remote_get($this->getLatestRatesEndpoint($symbols), ['timeout' => 30]);

            if (
                is_wp_error($apiResponse) ||
                '200' !== (string)wp_remote_retrieve_response_code($apiResponse)
            ) {
                return null;
            }

            $result = wp_remote_retrieve_body($apiResponse);

            $exchangeRates = json_decode($result);
            set_transient(self::CACHE_LAST_EXCHANGE_RATES, $exchangeRates, 60 * 60);
        }

        return $exchangeRates;
    }

    /*
     * Get Historical Exchange Rates
     */
    public function retrieveHistoricalRates($startDate, $endDate)
    {
        $exchangeRates = get_transient(self::CACHE_HISTORICAL_EXCHANGE_RATES);

        if ($exchangeRates === false) {
            $apiResponse = wp_remote_get($this->getHistoricalRatesEndpoint($startDate, $endDate), ['timeout' => 30]);

            if (
                is_wp_error($apiResponse) ||
                '200' !== (string)wp_remote_retrieve_response_code($apiResponse)
            ) {
                return null;
            }

            $result = wp_remote_retrieve_body($apiResponse);

            $exchangeRates = json_decode($result);
            set_transient(self::CACHE_HISTORICAL_EXCHANGE_RATES, $exchangeRates, 60 * 60);
        }
        
        return $exchangeRates;
    }
    
    /*
     * Update chart based on time interval
     */
    public function updateRatesChart()
    {
        
        check_admin_referer(self::AJAX_ACTION, 'nonce');

        $selectedInterval = (int) filter_input(INPUT_POST, 'selectedInterval', FILTER_SANITIZE_NUMBER_INT);

        $historicalRates = $this->retrieveHistoricalRates('2021-11-23', '2021-11-30');

        try {
            //Prepare data
            $labels = [];
            $data = [];
            $historicalData = (array)$historicalRates->data;
            $labels = array_keys($historicalData);
            
            foreach($historicalData as $currency=>$rate) {
                $data[] = $rate->EUR;
            }

            wp_send_json_success([
                                'nonce' => wp_create_nonce(self::AJAX_ACTION),
                                'labels' => $labels,
                                'data' => $data,
                            ]);
        } catch (Exception $exception) {
            wp_send_json_error(['message' => $exception->getMessage()]);
        }
    }

     /**
     * Create User Details table row based on user info from API
     *
     * @param $user
     *
     * @return string HTML TR with User details
     */
    private function createRateRow($baseCurrency, $crossCurrency, $rateVal): string
    {
        $htmlRow = sprintf(
            '<tr class="row-rate" data-base-currency="%s" data-cross-currency="%s">
                <td>%s/%s</td>
                <td>%s</td>
            </tr>',
            $baseCurrency,
            $crossCurrency,
            $baseCurrency,
            $crossCurrency,
            $rateVal
        );

        return $htmlRow;
    }
}