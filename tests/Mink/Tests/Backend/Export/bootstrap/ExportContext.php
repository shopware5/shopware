<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Mink\Tests\Backend\Export\bootstrap;

use Behat\Gherkin\Node\TableNode;
use Doctrine\DBAL\Connection;
use Exception;
use InvalidArgumentException;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;
use Shopware\Tests\Mink\Tests\General\Helpers\SubContext;
use SimpleXMLElement;

class ExportContext extends SubContext
{
    /**
     * Some config values necessary for the interpretation of the results.
     */
    protected array $mappings = [
        'xml' => ['id' => 4713, 'file' => 'export.xml'],
        'csv' => ['id' => 4711, 'delimiter' => ';', 'file' => 'export.csv'],
        'txt tab' => ['id' => 4712, 'delimiter' => "\t", 'file' => 'export.txt'],
        'txt pipe' => ['id' => 4714, 'delimiter' => '|', 'file' => 'export.txt'],
    ];

    /**
     * Used for the creation of the test subshop
     */
    private string $subShopDomain = 'exporttestshop.test';

    /**
     * Fetches the  feed from the server
     *
     * @Given I export the feed in :format format
     */
    public function iExportTheFeedFor(string $format): void
    {
        $this->getSession()->visit($this->pathTo($this->mappings[$format]));
    }

    /**
     * @param string $subshop Anything else than "subshop" will be treated as "shop"
     *
     * @throws Exception
     *
     * @Then /^I should see the feed "(?P<name>[^"]*)" with format "(?P<format>[^"]*)" in the "(?P<subshop>[^"]*)" export:$/
     */
    public function iShouldSeeFeedWithFormatInTheExport(string $name, string $format, string $subshop, TableNode $entries): void
    {
        switch ($format) {
            case 'xml':
                $export = $this->parseXml($this->getSession()->getPage()->getContent());
                break;

            case 'csv':
                $export = $this->parseCsv($this->getSession()->getPage()->getText(), $this->mappings[$name]['delimiter']);
                break;

            default:
                throw new InvalidArgumentException("Unknown output format '$format'");
        }

        $this->validate($entries, $export, $subshop);
    }

    /**
     * Enables exports in DB and changes encodings to UTF8.
     *
     * @BeforeScenario @productFeeds
     */
    public function enableExports(): void
    {
        $dbal = Shopware()->Container()->get(Connection::class);
        $dump = <<<'SQL'
INSERT INTO s_export VALUES (4711,'csv','2000-01-01 00:00:00',1,'4ebfa063359a73c356913df45b3fbe7f',1,0,'2017-02-27 14:10:18',0,1,'2017-02-27 14:10:18','export.csv',2,NULL,1,1,'',NULL,0,0,0,0,0,'','{strip}id{#S#}title{#S#}url{#S#}image{#S#}price{#S#}versand{#S#}währung {/strip}{#L#}','{strip}\n{$sArticle.ordernumber|escape}{#S#}\n{$sArticle.name|strip_tags|strip|truncate:80:\"...\":true|escape|htmlentities}{#S#}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{$sArticle.image|image:2}{#S#}\n{$sArticle.price|escape:\"number\"}{#S#}\nDE::DHL:{$sArticle|@shippingcost:\"prepayment\":\"de\"}{#S#}\n{$sCurrency.currency}\n{/strip}{#L#}','',0,1,1,'2000-01-01 00:00:00',0),(4712,'txt tab','2000-01-01 00:00:00',1,'4ebfa063359a73c356913df45b3fbe7f',1,0,'2017-02-27 14:10:18',0,2,'2017-02-27 14:10:18','export.txt',2,NULL,1,1,'',NULL,0,0,0,0,0,'','{strip}id{#S#}title{#S#}url{#S#}image{#S#}price{#S#}versand{#S#}währung{/strip}{#L#}','{strip}\n{$sArticle.ordernumber|escape}{#S#}\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|escape|htmlentities}{#S#}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{$sArticle.image|image:2}{#S#}\n{$sArticle.price|escape:"number"}{#S#}\nDE::DHL:{$sArticle|@shippingcost:"prepayment":"de"}{#S#}\n{$sCurrency.currency}\n{/strip}{#L#}','',0,1,1,'2000-01-01 00:00:00',0),(4713,'xml','2000-01-01 00:00:00',1,'4ebfa063359a73c356913df45b3fbe7f',1,0,'2000-01-01 00:00:00',0,3,'0000-00-00 00:00:00','export.xml',2,NULL,1,1,'',NULL,0,0,0,0,0,'','<?xml version="1.0" encoding="UTF-8" ?>\n<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">\n<channel>\n    <atom:link href="http://{$sConfig.sBASEPATH}/engine/connectors/export/{$sSettings.id}/{$sSettings.hash}/{$sSettings.filename}" rel="self" type="application/rss+xml" />\n    <title>{$sConfig.sSHOPNAME}</title>\n<link>http://{$sConfig.sBASEPATH}</link>\n    <language>{$sLanguage.isocode}-{$sLanguage.isocode}</language>\n    <image>\n        <url>http://{$sConfig.sBASEPATH}/templates/0/de/media/img/default/store/logo.gif</url>\n        <title>{$sConfig.sSHOPNAME}</title>\n        <link>http://{$sConfig.sBASEPATH}</link>\n    </image>{#L#}','<item> \n    <title>{$sArticle.name|strip_tags|htmlspecialchars_decode|strip|escape}</title>\n    <id>{$sArticle.ordernumber|escape}</id>\n    <url>{$sArticle.articleID|link:$sArticle.name}</url>\n    <description>{if $sArticle.image}\n        <a href="{$sArticle.articleID|link:$sArticle.name}" style="border:0 none;">\n            <img src="{$sArticle.image|image:0}" align="right" style="padding: 0pt 0pt 12px 12px; float: right;" />\n        </a>\n{/if}\n        {$sArticle.description_long|strip_tags|regex_replace:"/[^\\wöäüÖÄÜß .?!,&:%;\\-\\"\']/i":""|trim|truncate:900:"..."|escape}\n    </description>\n    <image>{$sArticle.image|image:2}</image>\n <price>{$sArticle.price|escape:"number"}</price><category>{$sArticle.articleID|category:">"|htmlspecialchars_decode|escape}</category>\n{if $sArticle.changed}     {assign var="sArticleChanged" value=$sArticle.changed|strtotime}<pubDate>{"r"|date:$sArticleChanged}</pubDate>{"rn"}{/if}\n</item>{#L#}','</channel>\n</rss>',0,1,1,'2000-01-01 00:00:00',0),(4714,'txt pipe','2000-01-01 00:00:00',1,'4ebfa063359a73c356913df45b3fbe7f',1,0,'2017-02-27 14:10:18',0,4,'2017-02-27 14:10:18','export.txt',2,NULL,1,1,'',NULL,0,0,0,0,0,'','{strip}id{#S#}title{#S#}url{#S#}image{#S#}price{#S#}versand{#S#}währung{/strip}{#L#}','{strip}\n{$sArticle.ordernumber|escape}{#S#}\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|escape|htmlentities}{#S#}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{$sArticle.image|image:2}{#S#}\n{$sArticle.price|escape:"number"}{#S#}\nDE::DHL:{$sArticle|@shippingcost:"prepayment":"de"}{#S#}\n{$sCurrency.currency}\n{/strip}{#L#}','',0,1,1,'2000-01-01 00:00:00',0)
SQL;

        $dbal->executeStatement($dump);
    }

    /**
     * Disables exports and sets the encoding back to latin1.
     *
     * @AfterScenario @productFeeds
     */
    public function disableExports(): void
    {
        $dbal = Shopware()->Container()->get(Connection::class);
        $dbal->executeStatement('DELETE FROM s_export WHERE id in (4711, 4712, 4713, 4714)');
    }

    /**
     * Creates a new subshop to test exports for it.
     *
     * @BeforeScenario @withSubshop
     */
    public function createSubshop(): void
    {
        $dbal = Shopware()->Container()->get(Connection::class);
        $dbal->executeStatement('INSERT INTO s_core_shops
            (id, main_id, name, title, position, host, base_path, base_url, hosts, secure, template_id, document_template_id, category_id, locale_id, currency_id, customer_group_id, fallback_id, customer_scope, `default`, active) VALUES
            ("3", NULL, "Export Testshop", "Export Testshop", "0", "' . $this->subShopDomain . '", NULL, NULL, "", "0", "23", "23", "3", "1", "1", "1", "1", "0", "0", "1");');

        $dbal->executeStatement('UPDATE s_export SET languageID=3 WHERE id in (4711, 4712, 4713, 4714)');
    }

    /**
     * Removes the previously created subshop.
     *
     * @AfterScenario @withSubshop
     */
    public static function removeSubshop(): void
    {
        $dbal = Shopware()->Container()->get(Connection::class);
        $dbal->executeStatement('DELETE FROM s_core_shops WHERE id=3');
    }

    /**
     * Builds a feed-url for a given row from the s_export table.
     */
    protected function pathTo(array $feed): string
    {
        return rtrim($this->getMinkParameter('base_url') ?? '', '/') . "/backend/export/index/{$feed['file']}?feedID={$feed['id']}&hash=4ebfa063359a73c356913df45b3fbe7f";
    }

    private function parseXml(string $content): array
    {
        $dom = simplexml_load_string($content);
        if (!$dom instanceof SimpleXMLElement) {
            Helper::throwException('Could not create XML element');
        }
        $items = $dom->xpath('//item');

        $result = [];
        foreach ($items as $item) {
            $result[] = (array) $item;
        }

        return $result;
    }

    /**
     * Parses the csv content of an export into an associative array.
     */
    private function parseCsv(string $content, string $delimiter): array
    {
        $rows = str_getcsv(html_entity_decode($content), "\n");

        $header = null;
        foreach ($rows as &$row) {
            if (!\is_string($row)) {
                continue;
            }
            $row = str_getcsv($row, $delimiter);

            if (!$header) {
                $header = $row;
            } else {
                $row = array_combine($header, $row);
            }
        }

        array_shift($rows);

        return $rows;
    }

    private function validate(TableNode $entries, array $export, string $shopType): void
    {
        $baseUrl = rtrim($this->getMinkParameter('base_url') ?? '', '/');

        $basePath = trim((string) parse_url($baseUrl, PHP_URL_PATH), '/');
        $subshopBaseUrl = rtrim('http://' . $this->subShopDomain . '/' . $basePath, '/');

        foreach ($entries as $entry) {
            $id = key($entry);
            foreach ($export as $row) {
                $this->assertKeyExists($id, $row);

                if ($entry[$id] !== $row[$id]) {
                    continue;
                }

                foreach ($entry as $key => $value) {
                    $this->assertKeyExists($key, $row);

                    $expected = $entry[$key];
                    $actual = $row[$key];

                    if (\in_array($key, ['url', 'image'], true)) {
                        $prefix = ($shopType === 'subshop') ? $subshopBaseUrl : $baseUrl;
                        $expected = $prefix . '/' . ltrim($entry[$key], '/');
                    }

                    $this->assertStringsEqual($expected, $actual);
                }
            }
        }
    }

    /**
     * @param string|int $key
     */
    private function assertKeyExists($key, array $row): void
    {
        if (!\array_key_exists($key, $row)) {
            Helper::throwException("Field '$key' was not found in export row " . print_r($row, true));
        }
    }

    private function assertStringsEqual(string $expected, string $actual): void
    {
        if (strcasecmp($expected, $actual) !== 0) {
            Helper::throwException(sprintf("Content '%s' expected, found '%s' instead.", $expected, $actual));
        }
    }
}
