<?php

declare(strict_types=1);

namespace WpFinance;

class Settings
{
    public const OPTIONS_PAGE_SLUG = 'users_page_slug';
    public const OPTIONS_PAGE_SLUG_DEFAULT_VALUE = 'users-list';

    public function init()
    {
        add_action('admin_menu', [$this, 'addMenu']);
        add_action('admin_init', [ $this, 'pluginRegisterSettings' ]);
    }

     /**
     * Add the menu entry
     */
    public function addMenu(): void
    {
        $hook = add_options_page(
            __('Finance Wiget Settings', 'wp-finance-data'),
            __('Finance Data', 'wp-finance-data'),
            'manage_options',
            'finance-data-settings',
            [$this, 'widgetSettingPage']
        );
    }

    /**
     * Register Settings
     */
    public function pluginRegisterSettings()
    {
        register_setting('option_group', self::OPTIONS_PAGE_SLUG);

        add_settings_section(
            'section_id',
            __('General Settings', 'wp-finance-data'),
            [
                $this,
                'renderGeneralSettingsSection',
            ],
            'finance-data-settings'
        );

        add_settings_field(
            'users_page_slug',
            __('Users Page Slug:', 'wp-finance-data'),
            [
                $this,
                'renderUsersSlugField',
            ],
            'finance-data-settings',
            'section_id'
        );
    }

    /**
     * Render General Settings Section
     */
    public function renderGeneralSettingsSection()
    {
        ?>
        <h2><?php __('General Settings', 'wp-finance-data'); ?></h2>
        <?php
    }

    /**
     * Render Users Page Slug Field
     */
    public function renderUsersSlugField()
    {
        $storedOption = get_option(self::OPTIONS_PAGE_SLUG);

        ?>
        <input type="text" name="users_page_slug" 
               value="<?php echo esc_attr(sanitize_title($storedOption)); ?>" maxlenght="80" />
        <?php
            $usersFullUrl = home_url(sanitize_title($storedOption));
        ?>
        <p>Test the URL for Users: <a href="<?php echo esc_url($usersFullUrl); ?>"
                                      target="_blank"><?php echo esc_url($usersFullUrl); ?>        
        <?php
    }

    /**
     * Register Settings Page
     */
    public function widgetSettingPage()
    {
        ?>
        <div class="wrap" id="finance-data-container">
        <h2>
            <?php
            printf(
                '%s',
                esc_html__('Inpsyde Users Settings', 'wp-finance-data')
            );
            ?>
        </h2>
        <div class="box-container">
            <div id="inpsyde-endpoint" class="metabox-holder postbox">
                <div class="inside">                    
                    <form action="options.php" method="post">
                    <?php
                        do_settings_sections('finance-data-settings');
                        settings_fields('option_group');
                        submit_button(__('Save', 'wp-finance-data'));
                    ?>
                    </form>                
                </div>
            </div>
            <div id="inpsyde-support" class="metabox-holder postbox">
                <div class="inside">
                    <h3>Support</h3>
                    <p>
                        <?php esc_html_e(
                            'If you have questions please contact “Alejandro Narmona” directly',
                            'wp-finance-data'
                        ); ?>
                        <br/>
                        <?php esc_html_e('via +54 9 11 5959 8606 or ', 'wp-finance-data'); ?>
                        <a href="mailto:alejandro.narmona@gmail.com">alejandro.narmona@gmail.com</a>
                    </p>
                </div>
            </div>   
        </div>
        <?php
    }
}