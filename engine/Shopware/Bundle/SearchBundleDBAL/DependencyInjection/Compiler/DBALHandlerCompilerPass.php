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

namespace Shopware\Bundle\SearchBundleDBAL\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @deprecated since shopware 5.7.3 and will be removed with 5.8
 */
class DBALHandlerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $taggedServicesIds = [];
        $taggedServicesIds += array_keys($container->findTaggedServiceIds(
            'condition_handler_dbal'
        ));
        $taggedServicesIds += array_keys($container->findTaggedServiceIds(
            'sorting_handler_dbal'
        ));
        $taggedServicesIds += array_keys($container->findTaggedServiceIds(
            'facet_handler_dbal'
        ));
        $taggedServicesIds += array_keys($container->findTaggedServiceIds(
            'criteria_request_handler'
        ));

        if (empty($taggedServicesIds)) {
            return;
        }

        foreach ($taggedServicesIds as $id) {
            $def = $container->getDefinition($id);
            $def->setPublic(true);
        }
    }
}
