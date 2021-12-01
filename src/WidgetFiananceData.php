<?php

declare(strict_types=1);

namespace WpFinance;

use WpFinance\Plugin;
use WP_Widget;

class WidgetFiananceData extends WP_Widget
{
    //Ajax action name for calls
    public const AJAX_ACTION = 'update_exchange_chart';

    //Cache keys
    private const CACHE_LAST_EXCHANGE_RATES = 'last_exchange_rates';

    public $currencies = [
        'USD',
        'ARS',
        'CAD',
        'MXN',
        'AUD',
    ];

    /**
     * Initialize controller for users
     */
    public function init()
    {
        //Setup actions and filters
        //Setup Ajax Calls
        //add_action('wp_ajax_' . self::AJAX_ACTION, [$this, 'updateChart']);
        //add_action('wp_ajax_nopriv_' . self::AJAX_ACTION, [$this, 'updateChart']);

        //Setup fronend actions
        //add_action('create_users_rows', [$this, 'createUsersRows']);
    }

    public function __construct()
    {
        parent::__construct(
            'wp_finance_data',
            __('Widget Finance Data', 'wp-finance-data'),
            [
                'customize_selective_refresh' => true,
            ]
        );
    }

    /**
     * Creating widget Backend
     */
    public function form($instance)
    {
        // Set widget defaults
        $defaults = [
            'title' => 'Finance Data',
            'primary_currency' => '',
            'secondary_currency' => '',
            'show_history' => '',
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

        <?php // Dropdown ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'primary_currency' ); ?>"><?php _e( 'Primary Currency', 'wp-finance-data' ); ?></label>
            <select name="<?php echo $this->get_field_name( 'primary_currency' ); ?>" id="<?php echo $this->get_field_id( 'primary_currency' ); ?>" class="widefat">
            <?php
            // Your options array
            $options = array(
                ''        => __( 'Select primary currency', 'wp-finance-data' ),
                'USD' => __( 'USD', 'wp-finance-data' ),
                'EUR' => __( 'EUR', 'wp-finance-data' ),
                'ARS' => __( 'ARS', 'wp-finance-data' ),
                'RUB' => __( 'RUB', 'wp-finance-data' ),
                'CAD' => __( 'CAD', 'wp-finance-data' ),
            );

            // Loop through options and add each one to the select dropdown
            foreach ( $options as $key => $name ) {
                echo '<option value="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" '. selected( $select, $key, false ) . '>'. $name . '</option>';

            } ?>
            </select>
        </p>

        <?php // secondary_currency ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'secondary_currency' ); ?>"><?php _e( 'Secondary Currency', 'wp-finance-data' ); ?></label>
            <select name="<?php echo $this->get_field_name( 'secondary_currency' ); ?>" id="<?php echo $this->get_field_id( 'secondary_currency'); ?>" class="widefat">
            <?php
            // Your options array
            $options = array(
                ''        => __( 'Select secondary currency', 'wp-finance-data' ),
                'USD' => __( 'USD', 'wp-finance-data' ),
                'EUR' => __( 'EUR', 'wp-finance-data' ),
                'ARS' => __( 'ARS', 'wp-finance-data' ),
                'RUB' => __( 'RUB', 'wp-finance-data' ),
                'CAD' => __( 'CAD', 'wp-finance-data' ),
            );

            // Loop through options and add each one to the select dropdown
            foreach ( $options as $key => $name ) {
                echo '<option value="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" '. selected( $select, $key, false ) . '>'. $name . '</option>';

            } ?>
            </select>
        </p>

        <?php // Checkbox ?>
        <p>
            <input id="<?php echo esc_attr($this->get_field_id('show_history')); ?>" name="<?php echo esc_attr($this->get_field_name('show_history')); ?>" type="checkbox" value="1" <?php checked( '1', $checkbox ); ?> />
            <label for="<?php echo esc_attr($this->get_field_id('show_history')); ?>"><?php __( 'Show History', 'wp-finance-data' ); ?></label>
        </p>

        <?php }
    /**
     * Updating widget replacing old instances with new
     */
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title']    = isset( $new_instance['title'] ) ? wp_strip_all_tags( $new_instance['title'] ) : '';        
        $instance['primary_currency']   = isset( $new_instance['select'] ) ? wp_strip_all_tags( $new_instance['select'] ) : '';
        $instance['secondary_currency']   = isset( $new_instance['select'] ) ? wp_strip_all_tags( $new_instance['select'] ) : '';
        $instance['show_history'] = isset( $new_instance['checkbox'] ) ? 1 : false;
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
        $primary_currency = isset($instance['primary_currency']) ? $instance['∫primary_currency'] : '';
        $secondary_currency = isset($instance['']) ? $instance['secondary_currency'] : '';
        $show_history = ! empty($instance['show_history']) ? $instance['show_history'] : false;

        // WordPress core before_widget hook (always include )
        echo $before_widget;

       // Display the widget
       echo '<div class="finance-data-widget widget-text wp_widget_plugin_box">';

            // Display widget title if defined
            if ($title) {
                echo $before_title . $title . $after_title;
            }

            $lastRates = $this->retrieveExchangeRates();
            echo $this->createExchangeRatesTable($lastRates);

            // Display select field
            if ( $primary_currency ) {
                echo '<p>' . $primary_currency . '</p>';
            }

            // Display select field
            if ( $secondary_currency ) {
                echo '<p>' . $secondary_currency . '</p>';
            }

            // Display something if checkbox is true
            if ( $show_history ) {
                echo '<p>Show history chart</p>';
            }

        echo '</div>';

        // WordPress core after_widget hook (always include )
        echo $after_widget;
    }

    public function createExchangeRatesTable($rates): string
    {
        $ratesRows = '';
        
        $arrRates = (array)$rates->rates;
        foreach ($this->currencies as $currency) {
            $ratesRows .= $this->createRateRow($rates->base, $currency, $arrRates[$currency]);
        }
        $html = sprintf('
            <table class="table">
            <thead>
            <tr>
                <th scope="col">Base Currency</th>
                <th scope="col">Cross Currency</th>
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
    public function getLastExchangeRatesEndpoint(): string
    {
        $symbols = implode(",", $this->currencies);
        //By default base currency is EUR, and cannot be changed because is restricted by FREE API.
        $baseEndpoint = Plugin::API_ENDPOINT . '/v1/latest?access_key=' . Plugin::API_KEY;

        return $baseEndpoint . '&symbols=' . $symbols;
    }
    /*
     * Get Users from API
     */
    public function retrieveExchangeRates()
    {
        $exchangeRates = get_transient(self::CACHE_LAST_EXCHANGE_RATES);

        if ($exchangeRates === false) {
            $apiResponse = wp_remote_get($this->getLastExchangeRatesEndpoint(), ['timeout' => 30]);

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
     * Update chart based on time interval
     */
    public function updateChart(): void
    {
        check_admin_referer(self::AJAX_ACTION, 'nonce');

        $selectedInterval = (int) filter_input(INPUT_POST, 'selectedInterval', FILTER_SANITIZE_NUMBER_INT);

        //Find user in cached users
        //NOTE: here I and saving and specific cache for the current user,
        //because in the users details API could be more data than the all users response.
        $user = wp_cache_get($userId, self::CACHE_USERS);

        if ($user === false) {
            $endpointUrl = Plugin::API_ENDPOINT . $userId;

            $apiResponse = wp_remote_get($endpointUrl, ['timeout' => 30]);
            $result = wp_remote_retrieve_body($apiResponse);

            if ($result === null) {
                wp_send_json_error(
                    [
                        'message' => __('Cannot retrieve user details.', 'wp-finance-data'),
                    ]
                );
            }

            $user = new User($result);

            wp_cache_set($userId, $user, self::CACHE_USERS);
        }

        try {
            $htmlDetailsRow = $this->createDetailsRow($user);
            wp_send_json_success([
                                'nonce' => wp_create_nonce(self::AJAX_ACTION),
                                'userDetails' => json_encode($htmlDetailsRow),
                            ]);
        } catch (Exception $exception) {
            wp_send_json_error(['message' => $exception->getMessage()]);
        }
    }

    /*h
     * Creates HTML users table
     */
    public function createUsersRows(): void
    {
        $users = $this->retrieveAllUsers();
        $html = '';

        if (!is_null($users) && is_array($users)) {
            foreach ($users as $user) {
                echo wp_kses_post('<tr>');
                $this->createUserColumn((string)$user->id, $user->id, "col-id");
                $this->createUserColumn($user->name, $user->id, "col-name");
                $this->createUserColumn($user->username, $user->id, "col-username");
                echo wp_kses_post('</tr>');
            }

            return;
        }

        echo wp_kses_post(__('No users were found!', 'wp-finance-data'));
    }

    /*
     * Create User table column
     */
    private function createUserColumn(string $displayValue, int $userId, string $className): void
    {
        echo wp_kses_post(sprintf('<td class="%s">', $className));
        echo wp_kses_post(sprintf('<a href="#" data-user-id="%d">', $userId));
        echo wp_kses_post($displayValue);
        echo wp_kses_post('</a>');
        echo wp_kses_post('</td>');
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
            '<tr class="row-details">
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
            </tr>',
            $baseCurrency,
            $crossCurrency,
            $rateVal
        );

        return $htmlRow;
    }
}
