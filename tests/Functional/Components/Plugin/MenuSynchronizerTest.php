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

namespace Shopware\Tests\Functional\Components\Plugin;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\MenuSynchronizer;
use Shopware\Models\Menu\Repository;
use Shopware\Models\Plugin\Plugin;

class MenuSynchronizerTest extends TestCase
{
    public function testIndexNotPartOfSnippet(): void
    {
        $this->executeTestAndCheckResult([
            [
                'isRootMenu' => true,
                'name' => 'Menu',
                'label' => [
                    'en' => 'EN Label',
                ],
                'controller' => 'SomeController',
                'action' => 'Index', // <-- This is the important part
            ],
        ], [
            'namespace' => 'backend/index/view/main',
            'shopID' => 1,
            'localeID' => 2,
            'name' => 'SomeController',
            'value' => 'EN Label',
            'dirty' => 0,
        ]);
    }

    public function testIndexLowerCaseIsPartOfSnippet(): void
    {
        $this->executeTestAndCheckResult(
            [
                [
                    'isRootMenu' => true,
                    'name' => 'Menu',
                    'label' => [
                        'en' => 'EN Label',
                    ],
                    'controller' => 'SomeController',
                    'action' => 'index', // <-- This is the important part
                ],
            ],
            [
                'namespace' => 'backend/index/view/main',
                'shopID' => 1,
                'localeID' => 2,
                'name' => 'SomeController/index',
                'value' => 'EN Label',
                'dirty' => 0,
            ]
        );
    }

    public function testOtherActionIsPartOfSnippet(): void
    {
        $this->executeTestAndCheckResult(
            [
                [
                    'isRootMenu' => true,
                    'name' => 'Menu',
                    'label' => [
                        'en' => 'EN Label',
                    ],
                    'controller' => 'SomeController',
                    'action' => 'FooBar', // <-- This is the important part
                ],
            ],
            [
                'namespace' => 'backend/index/view/main',
                'shopID' => 1,
                'localeID' => 2,
                'name' => 'SomeController/FooBar',
                'value' => 'EN Label',
                'dirty' => 0,
            ]
        );
    }

    /**
     * @param array<array<string, mixed>> $menu
     * @param array<string, string|int>   $expectedQueryParameters
     */
    protected function executeTestAndCheckResult(array $menu, array $expectedQueryParameters): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('createQueryBuilder')->willReturn($this->createMock(QueryBuilder::class));
        $connection->expects(static::once())
            ->method('insert')
            ->with('s_core_snippets', static::callback(static function ($value) use ($expectedQueryParameters) {
                return !array_diff($expectedQueryParameters, $value);
            }));

        $modelManagerMock = $this->createMock(ModelManager::class);
        $modelManagerMock->method('getConnection')->willReturn($connection);
        $modelManagerMock->method('getRepository')->willReturn($this->createMock(Repository::class));

        $plugin = new Plugin();
        $plugin->setId(PHP_INT_MAX);

        $menuSynchronizer = new MenuSynchronizer($modelManagerMock);
        $menuSynchronizer->synchronize($plugin, $menu);
    }
}
