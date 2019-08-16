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

namespace Shopware\Components\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * This class adds to all shopware.api services the tag shopware.api_resource
 *
 * @depracted in 5.6 and will be removed with Shopware 5.8. Please use the tag `shopware.api_resource` instead.
 */
class LegacyApiResourcesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getServiceIds() as $id) {
            if (strpos($id, 'shopware.api.') === 0) {
                try {
                    $definition = $container->getDefinition($id);
                } catch (ServiceNotFoundException $exception) {
                    // Might be an alias, we don't want to register those
                    continue;
                }

                if ($definition->isAbstract()) {
                    continue;
                }

                if (count($definition->getTag('shopware.api_resource')) !== 0) {
                    continue;
                }

                $definition->addTag('shopware.api_resource');
            }
        }
    }
}
