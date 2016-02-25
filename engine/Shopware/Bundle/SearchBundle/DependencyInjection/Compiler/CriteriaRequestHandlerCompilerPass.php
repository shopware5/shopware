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

namespace Shopware\Bundle\SearchBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @package Shopware\Bundle\SearchBundleDBAL\DependencyInjection\Compiler
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class CriteriaRequestHandlerCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('shopware_search.store_front_criteria_factory')) {
            return;
        }

        $definition = $container->getDefinition('shopware_search.store_front_criteria_factory');
        $this->replaceArgument($container, $definition, 'criteria_request_handler', 2);
    }

    /**
     * @param ContainerBuilder $container
     * @param Definition       $definition
     * @param string           $tag
     * @param int              $argumentIndex
     */
    public function replaceArgument(ContainerBuilder $container, Definition $definition, $tag, $argumentIndex)
    {
        $transports = $definition->getArgument($argumentIndex);

        $taggedServices = $container->findTaggedServiceIds(
            $tag
        );

        foreach ($taggedServices as $id => $attributes) {
            $transports[] = new Reference($id);
        }

        $definition->replaceArgument($argumentIndex, $transports);
    }
}
