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

namespace Shopware\Tests\Functional\Components\Routing\Matchers;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\Matchers\RewriteMatcher;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class RewriteMatcherTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var RewriteMatcher
     */
    protected $matcher;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $c = Shopware()->Container();
        $this->connection = $c->get(Connection::class);
        $this->matcher = $c->get('shopware.routing.matchers.rewrite_matcher');

        Shopware()->Models()->clear();

        $this->createSeoUrls();
        parent::setUp();
    }

    /**
     * Tear down
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        Shopware()->Models()->clear();
    }

    /**
     * Provide SEO URLs to be tested
     *
     * @return array<array<int|string|array<string, mixed>>>
     */
    public function provideSeoUrls(): array
    {
        return [
            [
                1,
                'this-url-does-not-exists',
                'this-url-does-not-exists',
            ],
            [
                1,
                '/backend/this-is-a-backend-url',
                '/backend/this-is-a-backend-url',
            ],
            [
                1,
                '/api/this-is-an-api-url',
                '/api/this-is-an-api-url',
            ],
            [
                1,
                'unique-url-main',
                [
                    'module' => 'frontend',
                    'controller' => 'a',
                    'action' => 'index',
                    'param' => '1',
                    'rewriteUrl' => true,
                ],
            ],
            [
                2,
                'unique-url-main',
                [
                    'module' => 'frontend',
                    'controller' => 'a',
                    'action' => 'index',
                    'param' => '1',
                    'rewriteAlias' => true,
                ],
            ],
            [
                1,
                'unique-url-main-with-action',
                [
                    'module' => 'frontend',
                    'controller' => 'b',
                    'action' => 'foo',
                    'param' => '2',
                    'rewriteUrl' => true,
                ],
            ],
            [
                1,
                'unique-url-main-with-s-action',
                [
                    'module' => 'frontend',
                    'controller' => 'c',
                    'action' => 'bar',
                    'param' => '3',
                    'rewriteUrl' => true,
                ],
            ],
            [
                1,
                'unique-url-not-main',
                [
                    'module' => 'frontend',
                    'controller' => 'd',
                    'action' => 'index',
                    'param' => '4',
                    'rewriteAlias' => true,
                ],
            ],
            [
                1,
                'same-url-different-subshops-one-main',
                [
                    'module' => 'frontend',
                    'controller' => 'e',
                    'action' => 'index',
                    'param' => '5',
                    'rewriteAlias' => true,
                ],
            ],
            [
                'shopId' => 2,
                'same-url-different-subshops-one-main',
                [
                    'module' => 'frontend',
                    'controller' => 'f',
                    'action' => 'index',
                    'param' => '6',
                    'rewriteUrl' => true,
                ],
            ],
            [
                1,
                'unique-url-main/',
                [
                    'module' => 'frontend',
                    'controller' => 'a',
                    'action' => 'index',
                    'param' => '1',
                    'rewriteAlias' => true,
                ],
            ],
            [
                1,
                'url-with-slash',
                [
                    'module' => 'frontend',
                    'controller' => 'a',
                    'action' => 'index',
                    'param' => '1',
                    'rewriteAlias' => true,
                ],
            ],
            'Category SEO URL with trailing slash, trailing slash in DB' => [
                1,
                'Notenbuecher/',
                [
                    'module' => 'frontend',
                    'controller' => 'cat',
                    'action' => 'index',
                    'sCategory' => '76',
                    'rewriteUrl' => true,
                ],
            ],
            'Category SEO URL without trailing slash, trailing slash in DB' => [
                1,
                'Notenbuecher',
                [
                    'module' => 'frontend',
                    'controller' => 'cat',
                    'action' => 'index',
                    'sCategory' => '76',
                    'rewriteAlias' => true,
                ],
            ],
            'Note controller should be called' => [
                1,
                'note',
                'note',
            ],
            'Category SEO URL with trailing slash, trailing slash not in DB' => [
                1,
                'Notenbuch/',
                [
                    'module' => 'frontend',
                    'controller' => 'cat',
                    'action' => 'index',
                    'sCategory' => '76',
                    'rewriteAlias' => true,
                ],
            ],
            'Category SEO URL without trailing slash, trailing slash not in DB' => [
                1,
                'Notenbuch',
                [
                    'module' => 'frontend',
                    'controller' => 'cat',
                    'action' => 'index',
                    'sCategory' => '76',
                    'rewriteUrl' => true,
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideSeoUrls
     *
     * @param array{0: int, 1: string, 2: string|array<string, mixed>}|string $expected
     */
    public function testMatcherTest(int $shopId, string $path, $expected): void
    {
        $context = $this->createRoutingContext($shopId);

        static::assertSame($expected, $this->matcher->match($path, $context));
    }

    /**
     * Create demo data for the testcases
     */
    private function createSeoUrls(): void
    {
        $this->connection->executeStatement(
            "INSERT INTO s_core_rewrite_urls(path, org_path, main, subshopID)
             VALUES
             ('unique-url-main', 'sViewport=a&param=1', 1, 1),
             ('unique-url-main-with-action', 'sViewport=b&action=foo&param=2', 1, 1),
             ('unique-url-main-with-s-action', 'sViewport=c&sAction=bar&param=3', 1, 1),
             ('unique-url-not-main', 'sViewport=d&param=4', 0, 1),
             ('same-url-different-subshops-one-main', 'sViewport=e&param=5', 0, 1),
             ('same-url-different-subshops-one-main', 'sViewport=f&param=6', 1, 2),
             ('url-with-slash/', 'sViewport=a&param=1', 1, 1),
             ('Notenbuecher/', 'sViewport=cat&sCategory=76', 1, 1),
             ('Notenbuch', 'sViewport=cat&sCategory=76', 1, 1)"
        );
    }

    private function createRoutingContext(int $shopId): Context
    {
        $context = new Context();
        $context->setShopId($shopId);

        return $context;
    }
}
