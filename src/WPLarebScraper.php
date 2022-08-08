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
        if (!wp_next_scheduled('wp_lareb_scraper_scrape')) {
            wp_schedule_event(time(), 'daily', 'wp_lareb_scraper_scrape');
        }

        global $wpdb;
        $this->tableName = "{$wpdb->prefix}lareb_scraper";

        add_filter('larab_scraper_get_values', [$this, 'getValues']);
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
            preg_match('/([0-9]+(\.?))+/', $paragraphOne, $matches);

            $sideEffectsValue = str_replace('.', '', $matches[0]);

            $contentNodeTwo = $xPath->query('//div[@id="CntntPage_2"]//p')->item(0);
            $paragraphTwo = $contentNodeTwo->nodeValue;
            preg_match('/([0-9]+(\.?))+/', $paragraphTwo, $matches);

            $deathsValue = str_replace('.', '', $matches[0]);

            $contentNodeThree = $xPath->query('//div[@id="accordion-corona"]//div[@class="label-first"]//b')->item(1);
            $paragraphThree = $contentNodeThree->nodeValue;

            $amountOfReports = str_replace('.', '', $paragraphThree);

            $wpdb->update($this->tableName, ['value' => $sideEffectsValue], ['name' => 'covid_side_effects']);
            $wpdb->update($this->tableName, ['value' => $deathsValue], ['name' => 'covid_deaths']);
            $wpdb->update($this->tableName, ['value' => $amountOfReports], ['name' => 'covid_reports_count']);
        }
    }

    /**
     * Get scraped values as formated array.
     *
     * @return array
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    public function getValues(): array
    {
        global $wpdb;

        $records = $wpdb->get_results("SELECT * FROM $this->tableName");
        $values = [];

        foreach ($records as $record) {
            $values[$record->name] = $record->value;
        }

        return $values;
    }
}
