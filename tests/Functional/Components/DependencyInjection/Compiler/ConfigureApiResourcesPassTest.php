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

namespace Shopware\Tests\Functional\Components\DependencyInjection\Compiler;

use Shopware\Components\Api\Resource\Resource;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConfigureApiResourcesPassTest extends \Enlight_Components_Test_Controller_TestCase
{
    /**
     * @param string $serviceId
     * @dataProvider provideApiResourceIds
     */
    public function testApiResourcesAreSetUpCorrect($serviceId): void
    {
        /** @var resource $service */
        $resource = Shopware()->Container()->get($serviceId);
        static::assertNotNull($resource->getManager());
    }

    public function testApiResourcesDecoration(): void
    {
        $kernel = Shopware()->Container()->get('kernel');
        /** @var ContainerBuilder $container */
        $container = \Shopware\Tests\Functional\Helper\Utils::hijackMethod($kernel, 'buildContainer');
        $container->register('api.deco1')->setClass(\Shopware\Components\DependencyInjection\Container::class)->setDecoratedService('shopware.api.article', null, 50);
        $container->compile();
        static::assertNotEmpty($container->getDefinition('api.deco1')->getTags());
        static::assertNotEmpty($container->getDefinition('api.deco1')->getTag('shopware.api_resource'));
    }

    public function provideApiResourceIds(): array
    {
        $services = array_map(function ($id) {
            return [$id];
        }, array_filter(Shopware()->Container()->getServiceIds(), function ($id) {
            return strpos($id, 'shopware.api.') === 0;
        }));

        return $services;
    }
}
