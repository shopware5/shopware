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

namespace Shopware\Tests\Functional\Components;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Routing\Router;
use Shopware\Components\SitePageMenu;

class SitePageMenuTest extends TestCase
{
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
        $this->connection = Shopware()->Container()->get('dbal_connection');
        $this->connection->beginTransaction();
        $this->connection->executeQuery('DELETE FROM s_cms_static');
        $this->sitePageMenu = Shopware()->Container()->get('shop_page_menu');
    }

    protected function tearDown(): void
    {
        $this->connection->rollBack();
        parent::tearDown();
    }

    public function testSiteWithoutLink()
    {
        $this->connection->insert('s_cms_static', ['id' => 1, 'description' => 'test', '`grouping`' => 'left']);

        $pages = $this->sitePageMenu->getTree(1, null);
        static::assertArrayHasKey('left', $pages);
        static::assertCount(1, $pages['left']);

        $page = array_shift($pages['left']);
        static::assertSame($this->getPath() . '/custom/index/sCustom/1', $page['link']);
    }

    public function testSiteWithExternalLink()
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

    public function testSiteWithInternalLink()
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

    public function testSiteWithLinkWithoutHttp()
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

    public function testRelativeUrl()
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

    public function testSiteWithOldViewport()
    {
        $this->connection->insert(
            's_cms_static',
            ['id' => 1, 'description' => 'test', '`grouping`' => 'left', 'link' => 'shopware.php?sViewport=cat&sCategory=300']
        );

        $pages = $this->sitePageMenu->getTree(1, null);
        static::assertArrayHasKey('left', $pages);
        static::assertCount(1, $pages['left']);

        $page = array_shift($pages['left']);
        static::assertSame($this->getPath() . '/cat/index/sCategory/300', $page['link']);
    }

    private function getPath()
    {
        /** @var Router $router */
        $router = Shopware()->Container()->get('router');
        $path = implode('/', [
            $router->getContext()->getHost(),
            $router->getContext()->getBaseUrl(),
        ]);
        $protocol = $router->getContext()->isSecure() ? 'https' : 'http';

        return rtrim($protocol . '://' . $path, '/');
    }
}
