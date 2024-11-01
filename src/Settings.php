<?php

namespace SocialArchiver;

use SocialArchiver\Settings\TwitterSettings;

class Settings
{
    /**
     * @var \SocialArchiver\Settings\TwitterSettings
     */
    protected $twitter_settings;

    /**
     * The constructor
     *
     * @since 1.0
     */
    public function __construct()
    {
        add_filter('cron_schedules', array($this, 'add_cron_interval'));

        $this->twitter_settings = new TwitterSettings();
    }

    /**
     * Add a custom cron interval
     *
     * @since 1.0
     *
     * @param array $schedules
     */
    public function add_cron_interval($schedules)
    {
        $schedules['fifteen_minutes'] = array(
            'interval' => 900,
            'display'  => sprintf(__('Every %1$d minutes', 'social-archiver'), 15),
        );

        return $schedules;
    }

    /**
     * set up the settings from social networks and draw the form
     *
     * @since 1.0
     */
    public function set_up()
    {
        $this->twitter_settings->get_form_elements();

        $this->draw_page();
    }

    /**
     * Register Social Archiver settings
     *
     * @since 1.0
     */
    public function register_settings()
    {
        register_setting('social_archiver', 'social_archiver', array($this, 'validate_settings'));
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
        $data = $this->twitter_settings->validate_settings($data);

        return $data;
    }

    /**
     * Draw the page. Check if an action is requested, if yes do the job, if not draw the form
     *
     * @since 1.0
     */
    protected function draw_page()
    {
        $active_tab = isset($_GET['tab']) ? esc_attr($_GET['tab']) : 'twitter';
        ?>

        <div class="wrap">
            <h2><?php echo esc_html(get_admin_page_title()); ?></h2>

            <h2 class="nav-tab-wrapper">
                <a href="?page=social_archiver&amp;tab=twitter" class="nav-tab<?php echo $active_tab == 'twitter' ? ' nav-tab-active' : ''; ?>"><?php _e('Twitter', 'social-archiver'); ?></a>
                <a href="?page=social_archiver&amp;tab=instagram" class="nav-tab<?php echo $active_tab == 'instagram' ? ' nav-tab-active' : ''; ?>"><?php _e('Instagram', 'social-archiver'); ?></a>
                <a href="?page=social_archiver&amp;tab=tumblr" class="nav-tab<?php echo $active_tab == 'tumblr' ? ' nav-tab-active' : ''; ?>"><?php _e('Tumblr', 'social-archiver'); ?></a>
            </h2>

        <?php

        if (isset($_GET['action']) && esc_attr($_GET['action']) == 'import_all_tweets') {
            $import = new \SocialArchiver\Import\TwitterImport();

            $import->import_all();
        } else {
            $this->draw_settings_form($active_tab);
        }

        ?>

        </div>

        <?php
    }

    /**
     * Draw the settings form
     *
     * @since 1.0
     *
     * @param string $active_tab
     */
    protected function draw_settings_form($active_tab)
    {
        ?><form method="post" name="social_archiver" action="options.php"><?php

            if ($active_tab == 'twitter') {
                settings_fields('social_archiver');
                do_settings_sections('social_archiver');
            } else if ($active_tab == 'instagram') {
                ?><p><?php _e('Soon', 'social-archiver'); ?></p><?php
            } else if ($active_tab == 'tumblr') {
                ?><p><?php _e('Soon', 'social-archiver'); ?></p><?php
            }

             submit_button();
        ?></form><?php
    }
}
