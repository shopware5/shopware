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

    protected function setUp()
    {
        $c = Shopware()->Container();
        $this->connection = $c->get('dbal_connection');
        $this->matcher = $c->get('shopware.routing.matchers.rewrite_matcher');

        Shopware()->Models()->clear();
        $this->connection->beginTransaction();

        $this->createSeoUrls();
    }

    protected function tearDown()
    {
        parent::tearDown();
        Shopware()->Models()->clear();
        $this->connection->rollBack();
    }

    public function createSeoUrls()
    {
        $this->connection->exec("
            INSERT INTO s_core_rewrite_urls(path, org_path, main, subshopID)
            VALUES
            ('foo', 'sViewport=bar&param=1', 0, 1),
            ('foo', 'sViewport=baz&param=2', 1, 2)
        ");
    }

    public function provideSeoUrls()
    {
        return [
            [
                'shopId' => 2,
                'seoPath' => 'foo',
                'query' => [
                    'module' => 'frontend',
                    'controller' => 'baz',
                    'action' => 'index',
                    'param' => '2',
                    'rewriteUrl' => true,
                ],
            ],
            [
                'shopId' => 1,
                'seoPath' => 'foo',
                'query' => [
                    'module' => 'frontend',
                    'controller' => 'bar',
                    'action' => 'index',
                    'param' => '1',
                    'rewriteAlias' => true,
                ],
            ],
        ];
    }

    public function testMatcherTest()
    {
        foreach ($this->provideSeoUrls() as $testCase) {
            $context = $this->createRoutingContext($testCase['shopId']);

            $this->assertEquals($this->matcher->match($testCase['seoPath'], $context), $testCase['query']);
        }
    }

    public function createRoutingContext($shopId)
    {
        $context = new Context();
        $context->setShopId($shopId);

        return $context;
    }
}
