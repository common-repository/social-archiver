<?php

// If uninstall is not called from WordPress
if (!defined('WP_UNINSTALL_PLUGIN')) {
    wp_die();
}

// Delete option
delete_option('social_archiver');

// Autoload
require_once(plugin_dir_path(__FILE__) . 'vendor/autoload.php');

// run
$social_archiver = new SocialArchiver\SocialArchiver();

// Unschedule cron
$social_archiver->deactivate_plugin();
