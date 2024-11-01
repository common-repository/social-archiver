<?php

namespace SocialArchiver\Cron;

use SocialArchiver\Import\TwitterImport;

class TwitterCron
{
    /**
     * @var string
     */
    protected static $event_name = 'sa_twitter_cron';

    /**
     * Create the hook
     *
     * @since 1.0
     */
    public static function add_cron_action()
    {
        add_action(self::$event_name, array(new TwitterImport(), 'import'));
    }

    /**
     * Schedule the cron. The unschedule method is called to cancel the cron previously scheduled
     * and reschedule it if the interval has been changed
     *
     * @since 1.0
     */
    public function schedule_event()
    {
        $option    = get_option('social_archiver');
        $interval  = isset($option['twitter_cron_interval']) ? esc_attr($option['twitter_cron_interval']) : 'hourly';
        $schedules = wp_get_schedules();

        if (!isset($schedules[$interval])) {
            return;
        }

        $this->unschedule_event();

        wp_schedule_event(time() + $schedules[$interval]['interval'], $interval, self::$event_name);
    }

    /**
     * Unschedule the event
     *
     * @since 1.0
     */
    public function unschedule_event()
    {
        $timestamp = $this->get_next_scheduled();

        if ($timestamp !== false) {
            wp_unschedule_event($timestamp, self::$event_name);
        }
    }

    /**
     * Return the timestamp of the next scheduled
     *
     * @since 1.0
     *
     * @return integer
     */
    public function get_next_scheduled()
    {
        return wp_next_scheduled(self::$event_name);
    }
}
