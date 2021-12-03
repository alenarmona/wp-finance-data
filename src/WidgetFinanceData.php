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

    private $symbols;

    public function symbols(): string
    {
        return get_option('widget_wp_finance_data')[3]['symbols'];        
    }

    public function symbolsArray(): array
    {
        return explode(",", $this->symbols());
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

        $this->symbols = $instance['symbols'];

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

        //Assign symbols globally
        $this->symbols = $symbols;

        // WordPress core before_widget hook (always include )
        echo $before_widget;

       // Display the widget
       echo '<div class="widget card finance-data-widget">';

        // Display widget title if defined
        if ($title) {
            echo '<div class="card-header">';
            echo $before_title . $title . $after_title;
            echo '</div>';
        }
        //Chart rates
        //$historicalRates = $this->retrieveHistoricalRates('2021-11-23', '2021-11-30');
        //echo $this->createHistoricalChart($historicalRates, $symbols);
        echo '<div class="card-body">';
        echo '  <div class="chart-container">';
        echo '      <canvas id="chart-rates" height="250"></canvas>';        
        echo '  </div>';
        echo '  <div class="chart-filter d-flex justify-content-between text-primary p-1"><span>Interval:</span> <ul class="d-flex justify-content-between">';
        echo '      <li><a href="#" data-interval="1m">1m</a></li>';
        echo '      <li><a href="#" data-interval="1w" class="active">1w</a></li>';
        echo '      <li><a href="#" data-interval="1d">1d</a></li>';
        echo '  </ul></div>';

        //Table rates
        $lastRates = $this->retrieveExchangeRates($symbols);
        echo $this->createExchangeRatesTable($lastRates, $symbols);

        echo '  </div>';
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
        $index = 0;
        $currencies = explode(",", $symbols);

        $arrRates = (array)$rates->data;

        foreach ($currencies as $currency) {
            $active = ($index === 0) ? true : false;
            $ratesRows .= $this->createRateRow($rates->query->base_currency, $currency, $arrRates[$currency], $active);
            $index++;
        }

        $html = sprintf('
            <table class="latest-exchange-rates table table-striped">            
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
        //By default base currency is USD, and cannot be changed because is restricted by FREE API.
        $baseEndpoint = Plugin::API_ENDPOINT . 'latest?appkey=' . Plugin::API_KEY;

        return $baseEndpoint . '&symbols=' . $symbols;
    }

    /**
     * Get the last extranche rates endpoiont
     */
    public function getHistoricalRatesEndpoint($startDate, $endDate): string
    {
        //By default base currency is USD, and cannot be changed because is restricted by FREE API.
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
    public function retrieveHistoricalRates($interval)
    {
        $exchangeRates = get_transient(self::CACHE_HISTORICAL_EXCHANGE_RATES . '_' . $interval);

        if ($exchangeRates === false) {
            $endDate = date("Y-m-d");
            $startDate = $this->getStartDate($interval);

            $apiResponse = wp_remote_get($this->getHistoricalRatesEndpoint($startDate, $endDate), ['timeout' => 30]);

            if (
                is_wp_error($apiResponse) ||
                '200' !== (string)wp_remote_retrieve_response_code($apiResponse)
            ) {
                return null;
            }

            $result = wp_remote_retrieve_body($apiResponse);

            $exchangeRates = json_decode($result);
            set_transient(self::CACHE_HISTORICAL_EXCHANGE_RATES . '_' . $interval, $exchangeRates, 60 * 60);
        }

        return $exchangeRates;
    }

    /*
     * Update chart based on time interval
     */
    public function updateRatesChart()
    {
        check_admin_referer(self::AJAX_ACTION, 'nonce');

        $selectedInterval = (string) filter_input(INPUT_POST, 'selectedInterval');

        $historicalRates = $this->retrieveHistoricalRates($selectedInterval);

        try {
            //Prepare data for charts
            $data = [];
            $labels = [];
            $datasets = [];

            if (!is_null($historicalRates)) {
                $historicalData = (array)$historicalRates->data;
                $labels = array_keys($historicalData);
                $index = 0;
                $colors = [
                    '#F98866',
                    '#FF420E',
                    '#FF420E',
                    '#80BD9E',
                    '#89DA59',
                    '#599EDA',
                ];
    
                //Create the chart datasets for each selected currency in the widget
                foreach ($this->symbolsArray() as $currency) {
                    foreach ($historicalData as $date => $rates) {
                        $allRates = (array)$rates;
                        $data[$currency][] = $allRates[$currency];
                    }
    
                    $datasets[] = [
                        'label' => $currency,
                        'data' => $data[$currency],
                        'borderColor' => $colors[$index],
                        'hidden' => ($index === 0) ? false : true,
                    ];
                    $index++;
                }
            }

            wp_send_json_success([
                                'nonce' => wp_create_nonce(self::AJAX_ACTION),
                                'labels' => $labels,
                                'datasets' => $datasets,
                            ]);
        } catch (Exception $exception) {
            wp_send_json_error(['message' => $exception->getMessage()]);
        }
    }

    /**
     * Get start date and end date for selected interval
     */
    private function getStartDate(string $interval): string
    {
        switch ($interval) {
            case "1d":
                $oneDayAgo = new \DateTime('1 day ago');
                return $oneDayAgo->format('Y-m-d');
            case "1w":
                $oneWeekAgo = new \DateTime('1 week ago');
                return $oneWeekAgo->format('Y-m-d');
            case "1m":
                $oneMonthAgo = new \DateTime('1 month ago');
                return $oneMonthAgo->format('Y-m-d');
            default:
                $oneWeekAgo = new \DateTime('1 week ago');
                return $oneWeekAgo->format('Y-m-d');
        }
    }

     /**
     * Create User Details table row based on user info from API
     *
     * @param $user
     *
     * @return string HTML TR with User details
     */
    private function createRateRow($baseCurrency, $crossCurrency, $rateVal, $active = false): string
    {
        $htmlRow = sprintf(
            '<tr class="row-rate %s">
                <td class="text-primary currency" data-base-currency="%s" 
                    data-cross-currency="%s">%s/%s</td>
                <td class="rate" data-base-currency="%s" data-cross-currency="%s">%s</td>
            </tr>',
            ($active) ? 'active' : '',
            $baseCurrency,
            $crossCurrency,
            $baseCurrency,
            $crossCurrency,
            $baseCurrency,
            $crossCurrency,
            $rateVal
        );

        return $htmlRow;
    }
}