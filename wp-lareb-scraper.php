<?php
/**
 * Plugin name: WP Lareb scraper
 * Description: Simple implementation of Swift Mailer for Wordpress.
 * Version: 1.1.0
 * Author: AimToFeel
 * Author URI: https://aimtofeel.com
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text domain: wp-swift-mailer
 */

use WPLarebScraper\src\WPLarebScraper;

if (!function_exists('add_action')) {
    die('Not allowed to call WP Lareb scraper directly.');
}

define('WP_SWIFT_MAILER_DIRECTORY', plugin_dir_path(__FILE__));
require_once WP_SWIFT_MAILER_DIRECTORY . 'src/WPLarebScraper.php';
require_once WP_SWIFT_MAILER_DIRECTORY . 'src/WPLarebScraperException.php';

$wpLarebScraper = new WPLarebScraper();
add_action('init', [$wpLarebScraper, 'onInit']);
