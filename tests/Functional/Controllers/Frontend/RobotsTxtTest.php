<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

class Shopware_Tests_Controllers_Frontend_RobotsTxtTest extends Enlight_Components_Test_Controller_TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Shopware()->Container()->get('dbal_connection')->beginTransaction();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Shopware()->Container()->get('dbal_connection')->rollBack();
    }

    /**
     * Test case method
     */
    public function testRobotsTxtTwoShops(): void
    {
        Shopware()->Db()->query('
        SET FOREIGN_KEY_CHECKS = 0;
        DELETE FROM s_core_shops;
        INSERT INTO s_core_shops (id, name, position, host, base_url, hosts, secure, template_id, document_template_id, category_id, locale_id, currency_id, customer_group_id, customer_scope, `default`, active)
        VALUES (1, "Deutsch", 0, ?, "/de", "", 0, 23, 23, 3, 1, 1, 1, 0, 1, 1);
        INSERT INTO s_core_shops (id, main_id, name, title, position, base_url, hosts, secure, category_id, locale_id, currency_id, customer_group_id, customer_scope, `default`, active)
        VALUES (2, 1, "English", "English", 0, "/en", "", 0, 39, 2, 1, 1, 0, 0, 1);
        SET FOREIGN_KEY_CHECKS = 1;
        ', [
            Shopware()->Shop()->getHost(),
        ]);

        $this->dispatch('/de/robots.txt');
        $robotsTxt = $this->formatRobotsTxt();
        $this->assertArrayHasKey('Disallow: /de/compare', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /de/checkout', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /de/register', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /de/account', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /de/address', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /de/note', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /de/widgets', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /de/listing', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /de/ticket', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /en/compare', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /en/checkout', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /en/register', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /en/account', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /en/address', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /en/note', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /en/widgets', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /en/listing', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /en/ticket', $robotsTxt);

        $this->sitemapTest($this->Response()->getBody());
    }

    public function testRobotsTxtOneShop(): void
    {
        Shopware()->Db()->query('
        SET FOREIGN_KEY_CHECKS = 0;
        DELETE FROM s_core_shops;
        INSERT INTO s_core_shops (id, name, position, host, base_url, hosts, secure, template_id, document_template_id, category_id, locale_id, currency_id, customer_group_id, customer_scope, `default`, active)
        VALUES (1, "Deutsch", 0, ?, "/de", "", 0, 23, 23, 3, 1, 1, 1, 0, 1, 1);
        SET FOREIGN_KEY_CHECKS = 1;
        ', [
            Shopware()->Shop()->getHost(),
        ]);

        $this->dispatch('/robots.txt');

        $robotsTxt = $this->formatRobotsTxt();

        $this->assertArrayHasKey('Disallow: /de/compare', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /de/checkout', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /de/register', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /de/account', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /de/address', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /de/note', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /de/widgets', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /de/listing', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /de/ticket', $robotsTxt);

        $this->sitemapTest($this->Response()->getBody());
    }

    public function testRobotsTxtBasePathOneShop(): void
    {
        Shopware()->Db()->query('
        SET FOREIGN_KEY_CHECKS = 0;
        DELETE FROM s_core_shops;
        INSERT INTO s_core_shops (id, name, position, host, base_path, base_url, hosts, secure, template_id, document_template_id, category_id, locale_id, currency_id, customer_group_id, customer_scope, `default`, active)
        VALUES (1, "Deutsch", 0, ?, "/foo", "/foo/de", "", 0, 23, 23, 3, 1, 1, 1, 0, 1, 1);
        SET FOREIGN_KEY_CHECKS = 1;
        ', [
            Shopware()->Shop()->getHost(),
        ]);

        $this->dispatch('/foo/robots.txt');

        $robotsTxt = $this->formatRobotsTxt();

        $this->assertArrayHasKey('Disallow: /foo/de/compare', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /foo/de/checkout', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /foo/de/register', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /foo/de/account', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /foo/de/address', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /foo/de/note', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /foo/de/widgets', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /foo/de/listing', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /foo/de/ticket', $robotsTxt);

        $this->sitemapTest($this->Response()->getBody());
    }

    public function testRobotsTxtBasePathTwoShops(): void
    {
        Shopware()->Db()->query('
        SET FOREIGN_KEY_CHECKS = 0;
        DELETE FROM s_core_shops;
        INSERT INTO s_core_shops (id, name, position, host, base_path, base_url, hosts, secure, template_id, document_template_id, category_id, locale_id, currency_id, customer_group_id, customer_scope, `default`, active)
        VALUES (1, "Deutsch", 0, ?, "/foo", "/foo/de", "", 0, 23, 23, 3, 1, 1, 1, 0, 1, 1);
        INSERT INTO s_core_shops (id, main_id, name, title, position, base_url, hosts, secure, category_id, locale_id, currency_id, customer_group_id, customer_scope, `default`, active)
        VALUES (2, 1, "English", "English", 0, "/en", "", 0, 39, 2, 1, 1, 0, 0, 1);
        SET FOREIGN_KEY_CHECKS = 1;
        ', [
            Shopware()->Shop()->getHost(),
        ]);

        $this->dispatch('/foo/robots.txt');

        $robotsTxt = $this->formatRobotsTxt();

        $this->assertArrayHasKey('Disallow: /foo/de/compare', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /foo/de/checkout', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /foo/de/register', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /foo/de/account', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /foo/de/address', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /foo/de/note', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /foo/de/widgets', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /foo/de/listing', $robotsTxt);
        $this->assertArrayHasKey('Disallow: /foo/de/ticket', $robotsTxt);

        $this->sitemapTest($this->Response()->getBody());
    }

    public function sitemapTest($response): void
    {
        $re = '/^\s*Sitemap:\s*(?<url>http.*)$/m';
        preg_match_all($re, $response, $matches, PREG_SET_ORDER, 0);

        $expected = '<sitemapindex';

        foreach ($matches as $match) {
            $url = parse_url($match['url'], PHP_URL_PATH);

            $this->reset();

            $this->dispatch($url);

            $this->assertContains($expected, $this->Response()->getBody());
        }
    }

    private function formatRobotsTxt(): array
    {
        $rows = explode("\n", $this->Response()->getBody());

        return array_flip(array_filter(array_map('trim', $rows)));
    }
}
