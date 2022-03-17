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

namespace Shopware\Tests\Functional\Components\DependencyInjection\Compiler;

use Enlight_Components_Test_Controller_TestCase;
use Shopware\Components\Api\Resource\Resource;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Tests\TestReflectionHelper;

class ConfigureApiResourcesPassTest extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * @dataProvider provideApiResourceIds
     */
    public function testApiResourcesAreSetUpCorrect(string $serviceId): void
    {
        $resource = Shopware()->Container()->get($serviceId);
        static::assertInstanceOf(Resource::class, $resource);
        static::assertNotNull($resource->getManager());
    }

    public function testApiResourcesDecoration(): void
    {
        $kernel = Shopware()->Container()->get('kernel');

        $container = TestReflectionHelper::getMethod(\get_class($kernel), 'buildContainer')->invoke($kernel);

        $container
            ->register('api.deco1')
            ->setClass(Container::class)
            ->setDecoratedService('shopware.api.article', null, 50)
            ->setPublic(true);

        $container->compile();

        static::assertNotEmpty($container->getDefinition('api.deco1')->getTags());
        static::assertNotEmpty($container->getDefinition('api.deco1')->getTag('shopware.api_resource'));
    }

    /**
     * @return array<array<string>>
     */
    public function provideApiResourceIds(): array
    {
        return array_map(
            function ($id) {
                return [$id];
            },
            array_filter(Shopware()->Container()->getServiceIds(), function ($id) {
                return str_starts_with($id, 'shopware.api.');
            })
        );
    }
}
