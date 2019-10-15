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

namespace Shopware\Tests\Functional\Components\Api;

use Doctrine\DBAL\Connection;
use Shopware\Components\Routing\Context;

class RewriteMatcherTest extends \Enlight_Components_Test_TestCase
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var Shopware\Components\Routing\Matchers\RewriteMatcher
     */
    protected $matcher;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $c = Shopware()->Container();
        $this->connection = $c->get('dbal_connection');
        $this->matcher = $c->get('shopware.routing.matchers.rewrite_matcher');

        Shopware()->Models()->clear();
        $this->connection->beginTransaction();

        $this->createSeoUrls();
    }

    /**
     * Tear down
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        Shopware()->Models()->clear();
        $this->connection->rollBack();
    }

    /**
     * Create demo data for the testcases
     */
    public function createSeoUrls()
    {
        $this->connection->exec("
            INSERT INTO s_core_rewrite_urls(path, org_path, main, subshopID)
            VALUES
            ('unique-url-main', 'sViewport=a&param=1', 1, 1),
            ('unique-url-main-with-action', 'sViewport=b&action=foo&param=2', 1, 1),
            ('unique-url-main-with-s-action', 'sViewport=c&sAction=bar&param=3', 1, 1),
            ('unique-url-not-main', 'sViewport=d&param=4', 0, 1),
            ('same-url-different-subshops-one-main', 'sViewport=e&param=5', 0, 1),
            ('same-url-different-subshops-one-main', 'sViewport=f&param=6', 1, 2)
        ");
    }

    /**
     * Provide SEO URLs to be tested
     *
     * @return array
     */
    public function provideSeoUrls()
    {
        return [
            [
                'shopId' => 1,
                'path' => 'this-url-does-not-exists',
                'expected' => 'this-url-does-not-exists',
            ],
            [
                'shopId' => 1,
                'path' => '/backend/this-is-a-backend-url',
                'expected' => '/backend/this-is-a-backend-url',
            ],
            [
                'shopId' => 1,
                'path' => '/api/this-is-an-api-url',
                'expected' => '/api/this-is-an-api-url',
            ],
            [
                'shopId' => 1,
                'path' => 'unique-url-main',
                'expected' => [
                    'module' => 'frontend',
                    'controller' => 'a',
                    'action' => 'index',
                    'param' => '1',
                    'rewriteUrl' => true,
                ],
            ],
            [
                'shopId' => 2,
                'path' => 'unique-url-main',
                'expected' => [
                    'module' => 'frontend',
                    'controller' => 'a',
                    'action' => 'index',
                    'param' => '1',
                    'rewriteAlias' => true,
                ],
            ],
            [
                'shopId' => 1,
                'path' => 'unique-url-main-with-action',
                'expected' => [
                    'module' => 'frontend',
                    'controller' => 'b',
                    'action' => 'foo',
                    'param' => '2',
                    'rewriteUrl' => true,
                ],
            ],
            [
                'shopId' => 1,
                'path' => 'unique-url-main-with-s-action',
                'expected' => [
                    'module' => 'frontend',
                    'controller' => 'c',
                    'action' => 'bar',
                    'param' => '3',
                    'rewriteUrl' => true,
                ],
            ],
            [
                'shopId' => 1,
                'path' => 'unique-url-not-main',
                'expected' => [
                    'module' => 'frontend',
                    'controller' => 'd',
                    'action' => 'index',
                    'param' => '4',
                    'rewriteAlias' => true,
                ],
            ],
            [
                'shopId' => 1,
                'path' => 'same-url-different-subshops-one-main',
                'expected' => [
                    'module' => 'frontend',
                    'controller' => 'e',
                    'action' => 'index',
                    'param' => '5',
                    'rewriteAlias' => true,
                ],
            ],
            [
                'shopId' => 2,
                'path' => 'same-url-different-subshops-one-main',
                'expected' => [
                    'module' => 'frontend',
                    'controller' => 'f',
                    'action' => 'index',
                    'param' => '6',
                    'rewriteUrl' => true,
                ],
            ],
        ];
    }

    /**
     * Test case
     */
    public function testMatcherTest()
    {
        foreach ($this->provideSeoUrls() as $testCase) {
            $context = $this->createRoutingContext($testCase['shopId']);

            static::assertEquals($this->matcher->match($testCase['path'], $context), $testCase['expected']);
        }
    }

    /**
     * Creates a routing context for the given $shopId
     *
     * @param int $shopId
     *
     * @return Context
     */
    public function createRoutingContext($shopId)
    {
        $context = new Context();
        $context->setShopId($shopId);

        return $context;
    }
}
