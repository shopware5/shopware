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

namespace Shopware\Tests\Functional\Components;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Components\SitePageMenu;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class SitePageMenuTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var SitePageMenu
     */
    private $sitePageMenu;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = Shopware()->Container()->get(Connection::class);
        $this->connection->executeQuery('DELETE FROM s_cms_static');
        $this->sitePageMenu = Shopware()->Container()->get(SitePageMenu::class);
    }

    public function testSiteWithoutLink(): void
    {
        $this->connection->insert('s_cms_static', ['id' => 1, 'description' => 'test', '`grouping`' => 'left']);

        $pages = $this->sitePageMenu->getTree(1, null);
        static::assertArrayHasKey('left', $pages);
        static::assertCount(1, $pages['left']);

        $page = array_shift($pages['left']);
        static::assertStringEndsWith('/custom/index/sCustom/1', $page['link']);
    }

    public function testSiteWithExternalLink(): void
    {
        $this->connection->insert(
            's_cms_static',
            ['id' => 1, 'description' => 'test', '`grouping`' => 'left', 'link' => 'http://localhost/examples']
        );

        $pages = $this->sitePageMenu->getTree(1, null);
        static::assertArrayHasKey('left', $pages);
        static::assertCount(1, $pages['left']);

        $page = array_shift($pages['left']);
        static::assertSame('http://localhost/examples', $page['link']);
    }

    public function testSiteWithInternalLink(): void
    {
        $this->connection->insert(
            's_cms_static',
            ['id' => 1, 'description' => 'test', '`grouping`' => 'left', 'link' => 'https://www.google.de']
        );

        $pages = $this->sitePageMenu->getTree(1, null);
        static::assertArrayHasKey('left', $pages);
        static::assertCount(1, $pages['left']);

        $page = array_shift($pages['left']);
        static::assertSame('https://www.google.de', $page['link']);
    }

    public function testSiteWithLinkWithoutHttp(): void
    {
        $this->connection->insert(
            's_cms_static',
            ['id' => 1, 'description' => 'test', '`grouping`' => 'left', 'link' => 'www.google.de']
        );

        $pages = $this->sitePageMenu->getTree(1, null);
        static::assertArrayHasKey('left', $pages);
        static::assertCount(1, $pages['left']);

        $page = array_shift($pages['left']);
        static::assertSame('www.google.de', $page['link']);
    }

    public function testRelativeUrl(): void
    {
        $this->connection->insert(
            's_cms_static',
            ['id' => 1, 'description' => 'test', '`grouping`' => 'left', 'link' => '/de/hoehenluft-abenteuer/']
        );

        $pages = $this->sitePageMenu->getTree(1, null);
        static::assertArrayHasKey('left', $pages);
        static::assertCount(1, $pages['left']);

        $page = array_shift($pages['left']);
        static::assertSame('/de/hoehenluft-abenteuer/', $page['link']);
    }

    public function testSiteWithOldViewport(): void
    {
        $this->connection->insert(
            's_cms_static',
            ['id' => 1, 'description' => 'test', '`grouping`' => 'left', 'link' => 'shopware.php?sViewport=cat&sCategory=300']
        );

        $pages = $this->sitePageMenu->getTree(1, null);
        static::assertArrayHasKey('left', $pages);
        static::assertCount(1, $pages['left']);

        $page = array_shift($pages['left']);
        static::assertStringEndsWith('/cat/index/sCategory/300', $page['link']);
    }
}
