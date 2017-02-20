<?php

namespace Shopware\Tests\Functional\Components;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
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

    protected function setUp()
    {
        parent::setUp();
        $this->connection = Shopware()->Container()->get('dbal_connection');
        $this->connection->beginTransaction();
        $this->connection->executeQuery('DELETE FROM s_cms_static');

        $this->sitePageMenu = Shopware()->Container()->get('shop_page_menu');
    }

    protected function tearDown()
    {
        $this->connection->rollBack();
        parent::tearDown();
    }


    public function testSiteWithoutLink()
    {
        $this->connection->insert('s_core_shops', ['name' => 'test', 'host' => 'localhost']);
        $shopId = $this->connection->lastInsertId('s_core_shops');
        $this->connection->insert('s_cms_static', ['id' => 1, 'description' => 'test', 'grouping' => 'gLeft']);

        $pages = $this->sitePageMenu->getTree($shopId, null);
        $this->assertArrayHasKey('gLeft', $pages);
        $this->assertCount(1, $pages['gLeft']);

        $page = array_shift($pages['gLeft']);
        $this->assertSame('http://localhost/custom/index/sCustom/1', $page['link']);
    }

    public function testSiteWithExternalLink()
    {
        $this->connection->insert('s_core_shops', ['name' => 'test', 'host' => 'localhost']);
        $shopId = $this->connection->lastInsertId('s_core_shops');
        $this->connection->insert(
            's_cms_static',
            ['id' => 1, 'description' => 'test', 'grouping' => 'gLeft', 'link' => 'http://localhost/examples']
        );

        $pages = $this->sitePageMenu->getTree($shopId, null);
        $this->assertArrayHasKey('gLeft', $pages);
        $this->assertCount(1, $pages['gLeft']);

        $page = array_shift($pages['gLeft']);
        $this->assertSame('http://localhost/examples', $page['link']);
    }

    public function testSiteWithInternalLink()
    {
        $this->connection->insert('s_core_shops', ['name' => 'test', 'host' => 'localhost']);
        $shopId = $this->connection->lastInsertId('s_core_shops');
        $this->connection->insert(
            's_cms_static',
            ['id' => 1, 'description' => 'test', 'grouping' => 'gLeft', 'link' => 'https://www.google.de']
        );

        $pages = $this->sitePageMenu->getTree($shopId, null);
        $this->assertArrayHasKey('gLeft', $pages);
        $this->assertCount(1, $pages['gLeft']);

        $page = array_shift($pages['gLeft']);
        $this->assertSame('https://www.google.de', $page['link']);
    }

    public function testSiteWithLinkWithoutHttp()
    {
        $this->connection->insert('s_core_shops', ['name' => 'test', 'host' => 'localhost']);
        $shopId = $this->connection->lastInsertId('s_core_shops');
        $this->connection->insert(
            's_cms_static',
            ['id' => 1, 'description' => 'test', 'grouping' => 'gLeft', 'link' => 'www.google.de']
        );

        $pages = $this->sitePageMenu->getTree($shopId, null);
        $this->assertArrayHasKey('gLeft', $pages);
        $this->assertCount(1, $pages['gLeft']);

        $page = array_shift($pages['gLeft']);
        $this->assertSame('www.google.de', $page['link']);
    }


    public function testSiteWithOldViewport()
    {
        $this->connection->insert('s_core_shops', ['name' => 'test', 'host' => 'localhost']);
        $shopId = $this->connection->lastInsertId('s_core_shops');
        $this->connection->insert(
            's_cms_static',
            ['id' => 1, 'description' => 'test', 'grouping' => 'gLeft', 'link' => 'shopware.php?sViewport=cat&sCategory=300']
        );

        $pages = $this->sitePageMenu->getTree($shopId, null);
        $this->assertArrayHasKey('gLeft', $pages);
        $this->assertCount(1, $pages['gLeft']);

        $page = array_shift($pages['gLeft']);
        $this->assertSame('http://localhost/cat/index/sCategory/300', $page['link']);
    }
}
