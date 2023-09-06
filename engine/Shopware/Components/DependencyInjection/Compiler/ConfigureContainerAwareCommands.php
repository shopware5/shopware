<?php
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

namespace Shopware\Components\DependencyInjection\Compiler;

use Shopware\Components\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

class ConfigureContainerAwareCommands implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        foreach (array_keys($container->findTaggedServiceIds('console.command')) as $id) {
            try {
                $definition = $container->getDefinition($id);
            } catch (ServiceNotFoundException $exception) {
                // Might be an alias, we don't want to register those
                continue;
            }

            $definitionClass = $definition->getClass();
            if (!\is_string($definitionClass)) {
                continue;
            }
            if (is_a($definitionClass, ContainerAwareInterface::class, true)) {
                $definition->addMethodCall('setContainer', [new Reference('service_container')]);
            }
        }
    }
}
