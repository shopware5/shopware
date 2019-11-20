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

namespace Shopware\Tests\Functional\Components\Plugin;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\MenuSynchronizer;
use Shopware\Models\Plugin\Plugin;

class MenuSynchronizerTest extends TestCase
{
    public function testIndexNotPartOfSnippet()
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
            'created' => date('Y-m-d H:i:s', time()),
            'updated' => date('Y-m-d H:i:s', time()),
            'dirty' => 0,
        ]);
    }

    public function testIndexLowerCaseIsPartOfSnippet()
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
                'created' => date('Y-m-d H:i:s', time()),
                'updated' => date('Y-m-d H:i:s', time()),
                'dirty' => 0,
            ]
        );
    }

    public function testOtherActionIsPartOfSnippet()
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
                'created' => date('Y-m-d H:i:s', time()),
                'updated' => date('Y-m-d H:i:s', time()),
                'dirty' => 0,
            ]
        );
    }

    protected function executeTestAndCheckResult(array $menu, array $expectedQueryParameters)
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('createQueryBuilder')
            ->willReturn($this->createMock(QueryBuilder::class));
        $connection->expects(static::once())
            ->method('insert')
            ->with('s_core_snippets', $expectedQueryParameters);

        $modelManagerMock = $this->createMock(ModelManager::class);
        $modelManagerMock->method('getConnection')
            ->willReturn($connection);

        $menuSynchronizer = new MenuSynchronizer($modelManagerMock);
        $menuSynchronizer->synchronize(new Plugin(), $menu);
    }
}
