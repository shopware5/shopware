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

namespace Shopware\Tests\Functional\Components\Router\Generators;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Components\ContainerAwareEventManager;
use Shopware\Components\QueryAliasMapper;
use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\Generators\RewriteGenerator;
use Shopware\Models\Shop\Shop;
use Shopware\Tests\Functional\Traits\ContainerTrait;

class RewriteGeneratorTest extends TestCase
{
    use ContainerTrait;

    public function testGenerateList(): void
    {
        $list = [
            4 => [
                'controller' => 'cat',
                'module' => 'frontend',
                'sCategory' => '5',
            ],
            5 => [
                'controller' => 'cat',
                'module' => 'frontend',
                'sCategory' => '9999',
            ],
            6 => [
                'controller' => 'cat',
                'module' => 'frontend',
                'sCategory' => '9',
            ],
        ];

        $urls = $this->createGenerator()->generateList($list, $this->createShopContext());

        static::assertSame('genusswelten/', $urls[4]);
        static::assertFalse($urls[5]);
        static::assertSame('freizeitwelten/', $urls[6]);
    }

    public function testGenerateListWithOnlyNotGeneratedURLs(): void
    {
        $list = [
            5 => [
                'controller' => 'cat',
                'module' => 'frontend',
                'sCategory' => '8888',
            ],
            6 => [
                'controller' => 'cat',
                'module' => 'frontend',
                'sCategory' => '9999',
            ],
        ];

        $urls = $this->createGenerator()->generateList($list, $this->createShopContext());

        static::assertFalse($urls[5]);
        static::assertFalse($urls[6]);
    }

    private function createGenerator(): RewriteGenerator
    {
        $eventManagerMock = $this->createMock(ContainerAwareEventManager::class);
        $eventManagerMock->method('filter')->willReturnArgument(1);

        return new RewriteGenerator(
            $this->getContainer()->get(Connection::class),
            $this->getContainer()->get(QueryAliasMapper::class),
            $eventManagerMock
        );
    }

    private function createShopContext(): Context
    {
        $shop = $this->getContainer()->get('models')->find(Shop::class, 1);
        static::assertInstanceOf(Shop::class, $shop);

        return Context::createFromShop($shop, $this->getContainer()->get('config'));
    }
}
