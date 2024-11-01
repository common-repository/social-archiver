<?php

namespace SocialArchiver\Settings;

use SocialArchiver\Cron\TwitterCron;
use SocialArchiver\Auth\TwitterAuth;

class TwitterSettings
{
    /**
     * Get the form elements
     *
     * @since 1.0
     */
    public function get_form_elements()
    {
        $this->add_sections()
             ->add_fields();
    }

    /**
     * Add twitter sections to settings page
     *
     * @since 1.0
     *
     * @return  TwitterSettings
     */
    protected function add_sections()
    {
        add_settings_section(
            'social_archiver_twitter_settings_section',
            __('Twitter Settings', 'social-archiver'),
            array($this, 'section_settings_description'),
            'social_archiver'
        );

        add_settings_section(
            'social_archiver_twitter_plugin_section',
            __('Plugin', 'social-archiver'),
            array($this, 'section_plugin_description'),
            'social_archiver'
        );

        add_settings_section(
            'social_archiver_twitter_manual_archiving_section',
            __('Manual archiving', 'social-archiver'),
            array($this, 'section_manual_archiving_description'),
            'social_archiver'
        );

        return $this;
    }

    /**
     * Add twitter fields to settings page
     *
     * @since 1.0
     *
     * @return  TwitterSettings
     */
    protected function add_fields()
    {
        // Twitter settings

        add_settings_field(
            'social_archiver_twitter_username',
            __('Username', 'social-archiver'),
            array($this, 'username_field'),
            'social_archiver',
            'social_archiver_twitter_settings_section',
            array('label_for' => 'social_archiver_twitter_username')
        );

        add_settings_field(
            'social_archiver_twitter_consumer_key',
            __('Consumer key', 'social-archiver'),
            array($this, 'consumer_key_field'),
            'social_archiver',
            'social_archiver_twitter_settings_section',
            array('label_for' => 'social_archiver_twitter_consumer_key')
        );

        add_settings_field(
            'social_archiver_twitter_consumer_secret',
            __('Consumer secret', 'social-archiver'),
            array($this, 'consumer_secret_field'),
            'social_archiver',
            'social_archiver_twitter_settings_section',
            array('label_for' => 'social_archiver_twitter_consumer_secret')
        );

        // Plugin settings

        add_settings_field(
            'social_archiver_twitter_auto_archiving',
            __('Enable automatic archiving', 'social-archiver'),
            array($this, 'auto_archiving_field'),
            'social_archiver',
            'social_archiver_twitter_plugin_section',
            array('label_for' => 'social_archiver_twitter_auto_archiving')
        );

        add_settings_field(
            'social_archiver_twitter_cron_interval',
            __('Cron interval', 'social-archiver'),
            array($this, 'cron_interval_field'),
            'social_archiver',
            'social_archiver_twitter_plugin_section',
            array('label_for' => 'social_archiver_twitter_cron_interval')
        );

        return $this;
    }

    /**
     * Echo a description for twitter section
     *
     * @since 1.0
     */
    public function section_settings_description()
    {
        ?>

        <p><?php printf(
            __('Go on %1$s to create a new application. Once done click on "Keys and Access Tokens" tab to get your consumer key and consumer secret.', 'social-archiver'),
            '<a href="https://apps.twitter.com" target="_blank">apps.twitter.com</a>'
        ); ?></p>

        <p><?php printf(
            __('You can set the "Access Level" to Read only by clicking on "modify app permissions" on the same tab.', 'social-archiver')
        ); ?></p>

        <?php
    }

    /**
     * Echo a description for plugin section
     *
     * @since 1.0
     */
    public function section_plugin_description()
    {
        _e('How the plugin will archive your tweets', 'social-archiver');
    }

    /**
     * Echo a description for manual archiving section
     *
     * @since 1.0
     */
    public function section_manual_archiving_description()
    {
        $url = wp_nonce_url(
            admin_url('options-general.php?page=social_archiver&action=import_all_tweets'),
            'social_archiver_import_all_tweets'
        );

        $ajax_nonce = wp_create_nonce('social_archiver_import_all_tweets');

        ?>

        <p><?php _e('To archive your tweets manually please click on the button below, please note that Twitter API only allows to retrieve the last 3200 tweets.', 'social-archiver'); ?></p>
        <p><?php _e('Manual archiving could take time, please be patient and dont\'t leave this page until the import is done.', 'social-archiver'); ?></p>
        <p><a href="<?php echo $url; ?>" class="button"><?php _e('Archive all tweets', 'social-archiver'); ?></a></p>

        <?php
    }

    /**
     * Draw the input for twitter username
     *
     * @since 1.0
     */
    public function username_field()
    {
        $option = get_option('social_archiver');
        $value  = isset($option['twitter_username']) ? esc_attr($option['twitter_username']) : '';

        ?>

        <input type="text" id="social_archiver_twitter_username" name="social_archiver[twitter_username]" value="<?php echo $value; ?>" />

        <?php
    }

    /**
     * Draw the input for twitter consumer key
     *
     * @since 1.0
     */
    public function consumer_key_field()
    {
        $option = get_option('social_archiver');
        $value  = isset($option['twitter_consumer_key']) ? esc_attr($option['twitter_consumer_key']) : '';

        ?>

        <input type="text" id="social_archiver_twitter_consumer_key" name="social_archiver[twitter_consumer_key]" value="<?php echo $value; ?>" />

        <?php
    }

    /**
     * Draw the input for twitter consumer secret
     *
     * @since 1.0
     */
    public function consumer_secret_field()
    {
        $option = get_option('social_archiver');
        $value  = isset($option['twitter_consumer_secret']) ? esc_attr($option['twitter_consumer_secret']) : '';

        ?>

        <input type="text" id="social_archiver_twitter_consumer_secret" name="social_archiver[twitter_consumer_secret]" value="<?php echo $value; ?>" />

        <?php
    }

    /**
     * Draw the input for enable automatic archiving
     *
     * @since 1.0
     */
    public function auto_archiving_field()
    {
        $option   = get_option('social_archiver');
        $checked  = isset($option['twitter_auto_archiving']) && esc_attr($option['twitter_auto_archiving']) ? ' checked="checked"' : '';

        ?>

        <input type="checkbox" id="social_archiver_twitter_auto_archiving" name="social_archiver[twitter_auto_archiving]"<?php echo $checked; ?> value="1" />

        <?php

        if ($checked != '') {
            $twitter_cron   = new TwitterCron();
            $next_archiving = round(($twitter_cron->get_next_scheduled() - time()) / 60);

            ?><span> (<?php printf(_n('Next archiving in %1$d minute', 'Next archiving in %1$d minutes', $next_archiving, 'social-archiver'), $next_archiving); ?>)</span><?php
        }
    }

    /**
     * Draw the input for cron interval
     *
     * @since 1.0
     */
    public function cron_interval_field()
    {
        $option         = get_option('social_archiver');
        $default_value  = isset($option['twitter_cron_interval']) ? esc_attr($option['twitter_cron_interval']) : 'hourly';

        $schedules = wp_get_schedules();

        ?>

        <select id="social_archiver_twitter_cron_interval" name="social_archiver[twitter_cron_interval]"  />
        <?php
            foreach ($schedules as $key => $interval) {
                printf('<option value="%1$s"%2$s>%3$s</option>', $key, ($key == $default_value ? ' selected="selected"' : ''), $interval['display']);
            }
        ?>
        </select>

        <?php
    }

    /**
     * Validate and sanitize the settings
     *
     * @since 1.0
     *
     * @param  array $data
     * @return array
     */
    public function validate_settings($data)
    {
        if (isset($data['twitter_consumer_key']) && isset($data['twitter_consumer_secret'])) {
            $twitter_auth = new TwitterAuth();

            $token = $twitter_auth->get_token($data['twitter_consumer_key'], $data['twitter_consumer_secret']);

            if ($token !== false) {
                $data['twitter_token'] = $token;
            }
        }

        if (isset($data['twitter_auto_archiving']) && $data['twitter_auto_archiving'] == 1) {
            $twitter_cron = new TwitterCron();

            $twitter_cron->schedule_event();
        } else {
            $data['twitter_auto_archiving'] = 0;
        }

        return $data;
    }
}
