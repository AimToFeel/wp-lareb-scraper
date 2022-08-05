<?php

namespace WPLarebScraper\src;

use DOMDocument;
use DOMXPath;

class WPLarebScraper
{
    private $tableName;

    /**
     * On plugin init.
     *
     * @return void
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    public function onInit(): void
    {
        wp_schedule_single_event(time(), 'wp_lareb_scraper_scrape');
        if (!wp_next_scheduled('wp_lareb_scraper_scrape')) {
            // wp_schedule_event(time(), 'daily', 'wp_lareb_scraper_scrape');
        }

        global $wpdb;
        $this->tableName = "{$wpdb->prefix}lareb_scraper";
    }

    /**
     * Start craping Lareb.
     *
     * @return void
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    public function scrape(): void
    {
        global $wpdb;

        $response = wp_remote_get('https://www.lareb.nl/coronameldingen');

        if (is_array($response) && !is_wp_error($response)) {
            $body = $response['body'];

            $document = new DOMDocument();
            $document->loadHTML($body);
            $xPath = new DOMXPath($document);

            $contentNodeOne = $xPath->query('//div[@id="CntntPage_1"]//p')->item(0);
            $paragraphOne = $contentNodeOne->nodeValue;

            var_dump($paragraphOne);

            $wpdb->update($this->tableName, ['value' => strlen($body)], ['name' => 'covid_side_effects']);
            $wpdb->update($this->tableName, ['value' => strlen($body)], ['name' => 'covid_deaths']);
        }

    }
}
