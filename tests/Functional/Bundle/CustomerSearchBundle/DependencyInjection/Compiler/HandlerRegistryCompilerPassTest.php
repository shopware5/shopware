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

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundle\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\CustomerSearchBundleDBAL\DependencyInjection\Compiler\HandlerRegistryCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 */
class HandlerRegistryCompilerPassTest extends TestCase
{
    public function testReplacementWithPriority()
    {
        $container = new ContainerBuilder();

        $parentService = $container->register('customer_search.dbal.handler_registry');
        $parentService->addArgument([new Reference('existing_service')]);

        $services = [
            'my_service1' => ['customer_search_condition_handler' => ['priority' => 100]],
            'my_service2' => ['customer_search_condition_handler' => ['priority' => 200]],
            'my_service3' => ['customer_search_condition_handler' => ['priority' => -501]],
            'my_service4' => ['customer_search_condition_handler' => []],
            'my_service5' => ['customer_search_condition_handler' => ['priority' => -1]],
            'my_service6' => ['customer_search_condition_handler' => ['priority' => -500]],
            'my_service7' => ['customer_search_condition_handler' => ['priority' => -499]],
            'my_service8' => ['customer_search_condition_handler' => ['priority' => 1]],
            'my_service9' => ['customer_search_condition_handler' => ['priority' => -2]],
            'my_service10' => ['customer_search_condition_handler' => ['priority' => -1000]],
            'my_service11' => ['customer_search_condition_handler' => ['priority' => -1001]],
            'my_service12' => ['customer_search_condition_handler' => ['priority' => -1002]],
            'my_service13' => ['customer_search_condition_handler' => ['priority' => -1003]],
        ];
        $this->registerServices($container, $services);

        $services = [
            'my_other_service1' => ['my_other_custom_tag' => ['priority' => 100]],
        ];
        $this->registerServices($container, $services);

        $tagReplaceTraitImplementation = new HandlerRegistryCompilerPass();
        $tagReplaceTraitImplementation->process($container);

        $expected = [
            new Reference('existing_service'),
            new Reference('my_service2'),
            new Reference('my_service1'),
            new Reference('my_service8'),
            new Reference('my_service4'),
            new Reference('my_service5'),
            new Reference('my_service9'),
            new Reference('my_service7'),
            new Reference('my_service6'),
            new Reference('my_service3'),
            new Reference('my_service10'),
            new Reference('my_service11'),
            new Reference('my_service12'),
            new Reference('my_service13'),
        ];

        $this->assertEquals(
            $expected,
            $parentService->getArgument(0)
        );
    }

    /**
     * @param ContainerBuilder $container
     * @param array[]          $services
     */
    private function registerServices(ContainerBuilder $container, array $services)
    {
        foreach ($services as $id => $tags) {
            $definition = $container->register($id);
            foreach ($tags as $name => $attributes) {
                $definition->addTag($name, $attributes);
            }
        }
    }
}
