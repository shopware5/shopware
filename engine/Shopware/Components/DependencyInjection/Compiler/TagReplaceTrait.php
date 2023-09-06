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

use SplPriorityQueue;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @deprecated in 5.6, will be removed in 5.8 without replacement
 */
trait TagReplaceTrait
{
    /**
     * Collects all services which tagged with the provided `tagName` and replaces the service constructor parameter,
     * identified by the provided argument index, with the collected services
     *
     * @param string $serviceName   di container name of the service
     * @param string $tagName       name of the tag
     * @param int    $argumentIndex index of the constructor parameter to replace
     */
    private function replaceArgumentWithTaggedServices(ContainerBuilder $container, $serviceName, $tagName, $argumentIndex)
    {
        if (!$container->hasDefinition($serviceName)) {
            return;
        }

        $taggedServices = $this->findAndSortTaggedServices($tagName, $container);

        if (empty($taggedServices)) {
            return;
        }

        $definition = $container->getDefinition($serviceName);

        $transports = $definition->getArgument($argumentIndex);

        foreach ($taggedServices as $id => $reference) {
            $transports[] = $reference;
        }

        $definition->replaceArgument($argumentIndex, $transports);
    }

    /**
     * Finds all services with the given tag name and order them by their priority.
     *
     * @param string $tagName
     *
     * @return Reference[]
     */
    private function findAndSortTaggedServices($tagName, ContainerBuilder $container)
    {
        $services = $container->findTaggedServiceIds($tagName);

        $queue = new SplPriorityQueue();

        foreach ($services as $serviceId => $tags) {
            foreach ($tags as $attributes) {
                $priority = isset($attributes['priority']) ? $attributes['priority'] : 0;
                $queue->insert(new Reference($serviceId), $priority);
            }
        }

        return iterator_to_array($queue, false);
    }
}
