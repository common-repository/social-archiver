<?php

/*
 * Plugin Name: Social Archiver
 * Plugin URI:  @TODO
 * Description: Save your social life
 * Author:      Alain Pellaux
 * Author URI:  http://www.alainpellaux.me
 * Version:     1.0.0
 * License:     GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /languages
 * Text Domain: social-archiver
 *
 * Social Archiver is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
*
 * Social Archiver is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Social Archiver. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
 */

// Security check
defined('ABSPATH') || wp_die('Access forbidden !');

// Define plugin constants
defined('SOCIAL_ARCHIVER_PLUGIN_DIRNAME') || define('SOCIAL_ARCHIVER_PLUGIN_DIRNAME', dirname(plugin_basename(__FILE__)));

// Autoload
require_once(plugin_dir_path(__FILE__) . 'vendor/autoload.php');

// run
$social_archiver = new SocialArchiver\SocialArchiver();

// Activation hook
register_activation_hook(__FILE__, array($social_archiver, 'activate_plugin'));

// Deactivation hook
register_deactivation_hook(__FILE__, array($social_archiver, 'deactivate_plugin'));
