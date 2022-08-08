<?php
/**
 * Plugin name: WP Lareb scraper
 * Description: Manages cron jobs which scrapes the Lareb website for up-to-date static data.
 * Version: 1.0.0
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
add_action('wp_lareb_scraper_scrape', [$wpLarebScraper, 'scrape']);

/**
 * On plugin register.
 *
 * @return void
 *
 * @author Niek van der Velde <niek@aimtofeel.com>
 * @version 1.0.0
 */
function activate_wp_larab_scraper(): void
{
    global $wpdb;
    $tableName = "{$wpdb->prefix}lareb_scraper";
    $charsetCollate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $tableName (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        value int NOT NULL DEFAULT 0,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charsetCollate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    $wpdb->query("TRUNCATE TABLE $tableName");

    $wpdb->insert($tableName, [
        'name' => 'covid_side_effects',
        'value' => 0,
    ]);

    $wpdb->insert($tableName, [
        'name' => 'covid_deaths',
        'value' => 0,
    ]);

    $wpdb->insert($tableName, [
        'name' => 'covid_reports_count',
        'value' => 0,
    ]);
}

/**
 * On uninstall.
 *
 * @return void
 *
 * @author Niek van der Velde <niek@aimtofeel.com>
 * @version 1.0.0
 */
function deactivate_wp_larab_scraper(): void
{
    global $wpdb;
    $tableName = "{$wpdb->prefix}lareb_scraper";
    $sql = "DROP TABLE $tableName;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'activate_wp_larab_scraper');
register_deactivation_hook(__FILE__, 'deactivate_wp_larab_scraper');
