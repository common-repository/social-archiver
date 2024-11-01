<?php

namespace SocialArchiver;

use SocialArchiver\Cron\TwitterCron;
use SocialArchiver\PostType\TwitterPostType;

class SocialArchiver
{
    /**
     * @var \SocialArchiver\Settings
     */
    protected $settings;
    /**
     * The constructor
     *
     * @since 1.0
     */
    public function __construct()
    {
        $this->settings = new Settings();

        add_action('admin_menu',  array($this, 'add_settings_page'));
        add_action('init', array($this, 'register_post_types'));
        add_action('plugins_loaded', array($this, 'load_textdomain'));

        add_action('admin_init', array($this->settings, 'register_settings'));

        TwitterCron::add_cron_action();
    }

    /**
     * Add social archiver settings page under settings
     *
     * @since 1.0
     */
    public function add_settings_page()
    {
        add_options_page('Social Archiver', 'Social Archiver', 'manage_options', 'social_archiver', array($this->settings, 'set_up'));
    }

    /**
     * Register the post types
     *
     * @since 1.0
     */
    public function register_post_types()
    {
        new TwitterPostType();
    }

    /**
     * Load textdomain to translate the plugin
     *
     * @since 1.0
     */
    public function load_textdomain()
    {
        load_plugin_textdomain('social-archiver', false, SOCIAL_ARCHIVER_PLUGIN_DIRNAME . '/languages');
    }

    /**
     * This function is called when the plugin is activated
     *
     * @since 1.0
     */
    public function activate_plugin()
    {
        $option = get_option('social_archiver');

        // Schedule twitter cron if auto archiving has been set to on previously
        if (isset($option['twitter_auto_archiving']) && $option['twitter_auto_archiving'] == 1) {
            $twitter_cron = new TwitterCron();

            $twitter_cron->schedule_event();
        }
    }

    /**
     * This function is called when the plugin is deactivated
     *
     * @since 1.0
     */
    public function deactivate_plugin()
    {
        $option = get_option('social_archiver');

        // Unschedule twitter cron
        if (isset($option['twitter_auto_archiving']) && $option['twitter_auto_archiving'] == 1) {
            $twitter_cron = new TwitterCron();

            $twitter_cron->unschedule_event();
        }
    }
}
