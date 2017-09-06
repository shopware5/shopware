<?php

namespace Shopware\Tests\Functional\Components\Plugin;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Models\Plugin\Plugin;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\MenuSynchronizer;

class MenuSynchronizerTest extends TestCase
{
    public function testIndexNotPartOfSnippet()
    {
        $this->executeTestAndCheckResult([
            [
                'isRootMenu' => true,
                'name' => 'Menu',
                'label' => [
                    'en' => 'EN Label'
                ],
                'controller' => 'SomeController',
                'action' => 'Index', // <-- This is the important part
            ]
        ], [
            'namespace' => 'backend/index/view/main',
            'shopID'    => 1,
            'localeID'  => 2,
            'name'      => 'SomeController',
            'value'     => 'EN Label',
            'created'   => date('Y-m-d H:i:s', time()),
            'updated'   => date('Y-m-d H:i:s', time()),
            'dirty'     => 0
        ]);
    }

    public function testIndexLowerCaseIsPartOfSnippet()
    {
        $this->executeTestAndCheckResult([
            [
                'isRootMenu' => true,
                'name' => 'Menu',
                'label' => [
                    'en' => 'EN Label'
                ],
                'controller' => 'SomeController',
                'action' => 'index', // <-- This is the important part
            ]
        ],
        [
            'namespace' => 'backend/index/view/main',
            'shopID'    => 1,
            'localeID'  => 2,
            'name'      => 'SomeController/index',
            'value'     => 'EN Label',
            'created'   => date('Y-m-d H:i:s', time()),
            'updated'   => date('Y-m-d H:i:s', time()),
            'dirty'     => 0
        ]);
    }

    public function testOtherActionIsPartOfSnippet()
    {
        $this->executeTestAndCheckResult([
            [
                'isRootMenu' => true,
                'name' => 'Menu',
                'label' => [
                    'en' => 'EN Label'
                ],
                'controller' => 'SomeController',
                'action' => 'FooBar', // <-- This is the important part
            ]
        ],
            [
                'namespace' => 'backend/index/view/main',
                'shopID'    => 1,
                'localeID'  => 2,
                'name'      => 'SomeController/FooBar',
                'value'     => 'EN Label',
                'created'   => date('Y-m-d H:i:s', time()),
                'updated'   => date('Y-m-d H:i:s', time()),
                'dirty'     => 0
            ]);
    }

    /**
     * @param array $menu
     * @param array $expectedQueryParameters
     */
    protected function executeTestAndCheckResult(array $menu, array $expectedQueryParameters)
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('createQueryBuilder')
            ->willReturn($this->createMock(QueryBuilder::class));
        $connection->expects($this->once())
            ->method('insert')
            ->with('s_core_snippets', $expectedQueryParameters);

        $modelManagerMock = $this->createMock(ModelManager::class);
        $modelManagerMock->method('getConnection')
            ->willReturn($connection);

        $menuSynchronizer = new MenuSynchronizer($modelManagerMock);
        $menuSynchronizer->synchronize(new Plugin(), $menu);
    }
}
