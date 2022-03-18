<?php

declare(strict_types=1);

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

namespace Shopware\Tests\Functional\Controllers\Frontend;

use Doctrine\DBAL\Connection;
use Enlight_Components_Test_Controller_TestCase as ControllerTestCase;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class RobotsTxtTest extends ControllerTestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    public function testRobotsTxtTwoShops(): void
    {
        $this->getContainer()->get(Connection::class)->executeStatement(
            <<<'SQL'
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM s_core_shops;
INSERT INTO s_core_shops (id, name, position, host, base_url, hosts, secure, template_id, document_template_id, category_id, locale_id, currency_id, customer_group_id, customer_scope, `default`, active)
VALUES (1, "Deutsch", 0, ?, "/de", "", 0, 23, 23, 3, 1, 1, 1, 0, 1, 1);
INSERT INTO s_core_shops (id, main_id, name, title, position, base_url, hosts, secure, category_id, locale_id, currency_id, customer_group_id, customer_scope, `default`, active)
VALUES (2, 1, "English", "English", 0, "/en", "", 0, 39, 2, 1, 1, 0, 0, 1);
SET FOREIGN_KEY_CHECKS = 1;
SQL,
            [$this->getContainer()->get('shop')->getHost()]
        );

        $this->dispatch('/de/robots.txt');
        $robotsTxt = $this->formatRobotsTxt();
        static::assertArrayHasKey('Disallow: /de/compare/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /de/checkout/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /de/register/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /de/account/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /de/address/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /de/note/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /de/widgets/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /de/listing/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /de/ticket/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /en/compare/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /en/checkout/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /en/register/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /en/account/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /en/address/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /en/note/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /en/widgets/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /en/listing/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /en/ticket/', $robotsTxt);
        static::assertArrayHasKey('Allow: /de/widgets/index/refreshStatistic', $robotsTxt);

        $this->sitemapTest();
    }

    public function testRobotsTxtTwoShopsButOneIsInactive(): void
    {
        $this->getContainer()->get(Connection::class)->executeStatement(
            <<<'SQL'
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM s_core_shops;
INSERT INTO s_core_shops (id, name, position, host, base_url, hosts, secure, template_id, document_template_id, category_id, locale_id, currency_id, customer_group_id, customer_scope, `default`, active)
VALUES (1, "Deutsch", 0, ?, "/de", "", 0, 23, 23, 3, 1, 1, 1, 0, 1, 1);
INSERT INTO s_core_shops (id, main_id, name, title, position, base_url, hosts, secure, category_id, locale_id, currency_id, customer_group_id, customer_scope, `default`, active)
VALUES (2, 1, "English", "English", 0, "/en", "", 0, 39, 2, 1, 1, 0, 0, 0);
SET FOREIGN_KEY_CHECKS = 1;
SQL,
            [$this->getContainer()->get('shop')->getHost()]
        );

        $this->dispatch('/de/robots.txt');
        $robotsTxt = $this->formatRobotsTxt();
        static::assertArrayHasKey('Disallow: /de/compare/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /de/checkout/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /de/register/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /de/account/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /de/address/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /de/note/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /de/widgets/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /de/listing/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /de/ticket/', $robotsTxt);
        static::assertArrayNotHasKey('Disallow: /en/compare/', $robotsTxt);
        static::assertArrayNotHasKey('Disallow: /en/checkout/', $robotsTxt);
        static::assertArrayNotHasKey('Disallow: /en/register/', $robotsTxt);
        static::assertArrayNotHasKey('Disallow: /en/account/', $robotsTxt);
        static::assertArrayNotHasKey('Disallow: /en/address/', $robotsTxt);
        static::assertArrayNotHasKey('Disallow: /en/note/', $robotsTxt);
        static::assertArrayNotHasKey('Disallow: /en/widgets/', $robotsTxt);
        static::assertArrayNotHasKey('Disallow: /en/listing/', $robotsTxt);
        static::assertArrayNotHasKey('Disallow: /en/ticket/', $robotsTxt);
        static::assertArrayHasKey('Allow: /de/widgets/index/refreshStatistic', $robotsTxt);

        $this->sitemapTest();
    }

    public function testRobotsTxtOneShop(): void
    {
        $this->getContainer()->get(Connection::class)->executeStatement(
            <<<'SQL'
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM s_core_shops;
INSERT INTO s_core_shops (id, name, position, host, base_url, hosts, secure, template_id, document_template_id, category_id, locale_id, currency_id, customer_group_id, customer_scope, `default`, active)
VALUES (1, "Deutsch", 0, ?, "/de", "", 0, 23, 23, 3, 1, 1, 1, 0, 1, 1);
SET FOREIGN_KEY_CHECKS = 1;
SQL,
            [$this->getContainer()->get('shop')->getHost()]
        );

        $this->dispatch('/robots.txt');

        $robotsTxt = $this->formatRobotsTxt();

        static::assertArrayHasKey('Disallow: /de/compare/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /de/checkout/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /de/register/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /de/account/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /de/address/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /de/note/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /de/widgets/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /de/listing/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /de/ticket/', $robotsTxt);
        static::assertArrayHasKey('Allow: /de/widgets/index/refreshStatistic', $robotsTxt);

        $this->sitemapTest();
    }

    public function testRobotsTxtBasePathOneShop(): void
    {
        $this->getContainer()->get(Connection::class)->executeStatement(
            <<<'SQL'
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM s_core_shops;
INSERT INTO s_core_shops (id, name, position, host, base_path, base_url, hosts, secure, template_id, document_template_id, category_id, locale_id, currency_id, customer_group_id, customer_scope, `default`, active)
VALUES (1, "Deutsch", 0, ?, "/foo", "/foo/de", "", 0, 23, 23, 3, 1, 1, 1, 0, 1, 1);
SET FOREIGN_KEY_CHECKS = 1;
SQL,
            [$this->getContainer()->get('shop')->getHost()]
        );

        $this->dispatch('/foo/robots.txt');

        $robotsTxt = $this->formatRobotsTxt();

        static::assertArrayHasKey('Disallow: /foo/de/compare/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/de/checkout/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/de/register/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/de/account/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/de/address/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/de/note/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/de/widgets/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/de/listing/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/de/ticket/', $robotsTxt);
        static::assertArrayHasKey('Allow: /foo/de/widgets/index/refreshStatistic', $robotsTxt);

        $this->sitemapTest();
    }

    public function testRobotsTxtBasePathTwoShops(): void
    {
        $this->getContainer()->get(Connection::class)->executeStatement(
            <<<'SQL'
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM s_core_shops;
INSERT INTO s_core_shops (id, name, position, host, base_path, base_url, hosts, secure, template_id, document_template_id, category_id, locale_id, currency_id, customer_group_id, customer_scope, `default`, active)
VALUES (1, "Deutsch", 0, ?, "/foo", "/foo/de", "", 0, 23, 23, 3, 1, 1, 1, 0, 1, 1);
INSERT INTO s_core_shops (id, main_id, name, title, position, base_url, hosts, secure, category_id, locale_id, currency_id, customer_group_id, customer_scope, `default`, active)
VALUES (2, 1, "English", "English", 0, "/en", "", 0, 39, 2, 1, 1, 0, 0, 1);
SET FOREIGN_KEY_CHECKS = 1;
SQL,
            [$this->getContainer()->get('shop')->getHost()]
        );

        $this->dispatch('/foo/robots.txt');

        $robotsTxt = $this->formatRobotsTxt();

        static::assertArrayHasKey('Disallow: /foo/de/compare/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/de/checkout/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/de/register/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/de/account/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/de/address/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/de/note/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/de/widgets/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/de/listing/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/de/ticket/', $robotsTxt);
        static::assertArrayHasKey('Allow: /foo/de/widgets/index/refreshStatistic', $robotsTxt);

        $this->sitemapTest();
    }

    public function testLanguageShopWithoutVirtualUrl(): void
    {
        $this->getContainer()->get(Connection::class)->executeStatement(
            <<<'SQL'
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM s_core_shops;
INSERT INTO s_core_shops (id, name, position, host, base_path, base_url, hosts, secure, template_id, document_template_id, category_id, locale_id, currency_id, customer_group_id, customer_scope, `default`, active)
VALUES (1, "Deutsch", 0, ?, "/foo", "/foo/de", "", 0, 23, 23, 3, 1, 1, 1, 0, 1, 1);
INSERT INTO s_core_shops (id, main_id, name, title, position, base_url, hosts, secure, category_id, locale_id, currency_id, customer_group_id, customer_scope, `default`, active)
VALUES (2, 1, "English", "English", 0, "", "", 0, 39, 2, 1, 1, 0, 0, 1);
SET FOREIGN_KEY_CHECKS = 1;
SQL,
            [$this->getContainer()->get('shop')->getHost()]
        );

        $this->dispatch('/foo/robots.txt');

        $robotsTxt = $this->formatRobotsTxt();

        static::assertArrayHasKey('Disallow: /foo/compare/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/checkout/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/register/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/account/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/address/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/note/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/widgets/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/listing/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/ticket/', $robotsTxt);
        static::assertArrayHasKey('Allow: /foo/de/widgets/index/refreshStatistic', $robotsTxt);

        $this->sitemapTest();
    }

    public function testMainShopWithPath(): void
    {
        $this->getContainer()->get(Connection::class)->executeStatement(
            <<<'SQL'
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM s_core_shops;
INSERT INTO s_core_shops (id, name, position, host, base_path, base_url, hosts, secure, template_id, document_template_id, category_id, locale_id, currency_id, customer_group_id, customer_scope, `default`, active)
VALUES (1, "Deutsch", 0, ?, "/foo", "", "", 0, 23, 23, 3, 1, 1, 1, 0, 1, 1);
SET FOREIGN_KEY_CHECKS = 1;
SQL,
            [$this->getContainer()->get('shop')->getHost()]
        );

        $this->dispatch('/foo/robots.txt');

        $robotsTxt = $this->formatRobotsTxt();

        static::assertArrayHasKey('Disallow: /foo/compare/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/checkout/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/register/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/account/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/address/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/note/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/widgets/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/listing/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/ticket/', $robotsTxt);
        static::assertArrayHasKey('Allow: /foo/widgets/index/refreshStatistic', $robotsTxt);

        $this->sitemapTest();
    }

    public function testLanguageShopWithVirtualUrlsAndRequestNormalRobotsTxt(): void
    {
        $this->getContainer()->get(Connection::class)->executeStatement(
            <<<'SQL'
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM s_core_shops;
INSERT INTO s_core_shops (id, name, position, host, base_path, base_url, hosts, secure, template_id, document_template_id, category_id, locale_id, currency_id, customer_group_id, customer_scope, `default`, active)
VALUES (1, "Deutsch", 0, ?, "/foo", "/foo/de", "", 0, 23, 23, 3, 1, 1, 1, 0, 1, 1);
INSERT INTO s_core_shops (id, main_id, name, title, position, base_url, hosts, secure, category_id, locale_id, currency_id, customer_group_id, customer_scope, `default`, active)
VALUES (2, 1, "English", "English", 0, "", "/foo/en", 0, 39, 2, 1, 1, 0, 0, 1);
SET FOREIGN_KEY_CHECKS = 1;
SQL,
            [$this->getContainer()->get('shop')->getHost()]
        );

        $this->dispatch('/robots.txt');

        $robotsTxt = $this->formatRobotsTxt();

        static::assertArrayHasKey('Disallow: /foo/de/compare/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/de/checkout/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/de/register/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/de/account/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/de/address/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/de/note/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/de/widgets/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/de/listing/', $robotsTxt);
        static::assertArrayHasKey('Disallow: /foo/de/ticket/', $robotsTxt);
        static::assertArrayHasKey('Allow: /foo/de/widgets/index/refreshStatistic', $robotsTxt);

        $this->sitemapTest();
    }

    public function sitemapTest(): void
    {
        $response = $this->Response()->getBody();
        static::assertIsString($response);

        $re = '/^\s*Sitemap:\s*(?<url>http.*)$/m';
        preg_match_all($re, $response, $matches, PREG_SET_ORDER, 0);

        $expected = '<sitemapindex';

        foreach ($matches as $match) {
            $url = parse_url($match['url'], PHP_URL_PATH);
            static::assertIsString($url);

            $this->reset();

            $this->dispatch($url);

            $response = $this->Response()->getBody();
            static::assertIsString($response);

            static::assertStringContainsString($expected, $response);
        }
    }

    /**
     * @return array<string, int>
     */
    private function formatRobotsTxt(): array
    {
        $content = $this->Response()->getBody();
        static::assertIsString($content);

        $rows = explode("\n", $content);

        return array_flip(array_filter(array_map('trim', $rows)));
    }
}
