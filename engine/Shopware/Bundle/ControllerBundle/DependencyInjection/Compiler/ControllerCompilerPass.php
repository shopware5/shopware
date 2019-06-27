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

namespace Shopware\Bundle\ControllerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ControllerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $controllers = [];

        foreach ($container->findTaggedServiceIds('shopware.controller') as $id => $options) {
            $options = $options[0];

            if (!isset($options['module'])) {
                throw new \RuntimeException(sprintf('Attribute "module" is required for "shopware.controller" tagged service with id "%s"', $id));
            }

            if (!isset($options['controller'])) {
                throw new \RuntimeException(sprintf('Attribute "module" is required for "shopware.controller" tagged service with id "%s"', $id));
            }

            $controllers[strtolower(sprintf('%s_%s', $options['module'], $options['controller']))] = $id;

            $definition = $container->getDefinition($id);

            $arguments = $definition->getArguments();
            $definition->setArguments([
                new Reference('hooks'),
                $definition->getClass(),
                $arguments,
            ]);
            $definition->setFactory('Shopware\Components\DependencyInjection\ProxyFactory::getProxy');
        }

        $container->setParameter('shopware.controllers', $controllers);
    }
}
