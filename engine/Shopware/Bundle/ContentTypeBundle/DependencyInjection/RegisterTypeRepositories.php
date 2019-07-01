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

namespace Shopware\Bundle\ContentTypeBundle\DependencyInjection;

use Shopware\Bundle\ContentTypeBundle\Services\Repository;
use Shopware\Bundle\ContentTypeBundle\Services\TypeFieldResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\Expression;

class RegisterTypeRepositories implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach (array_keys($container->getParameter('shopware.bundle.content_type.types')) as $name) {
            $def = new Definition(Repository::class);

            $def->setArguments([
                new Reference('dbal_connection'),
                new Expression('service("Shopware\\\\Bundle\\\\ContentTypeBundle\\\\Services\\\\TypeProvider").getType("' . $name . '")'),
                new Reference('translation'),
                new Reference('shopware_storefront.context_service'),
                new Reference(TypeFieldResolver::class),
            ]);

            $container->setDefinition('shopware.bundle.content_type.' . $name, $def);
        }
    }
}
