<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace Shopware\Behat\ShopwareExtension\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class KernelInitializationPass implements CompilerPassInterface
{
    /**
     * Loads kernel file.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('behat.shopware_extension.kernel.path')) {
            return;
        }
        // get base path
        $basePath = $container->getParameter('behat.paths.base');

        // find and require bootstrap
        $bootstrapPath = $container->getParameter('behat.shopware_extension.kernel.bootstrap');

        if ($bootstrapPath) {
            if (file_exists($bootstrap = $basePath.DIRECTORY_SEPARATOR.$bootstrapPath)) {
                require_once($bootstrap);
            } elseif (file_exists($bootstrapPath)) {
                require_once($bootstrapPath);
            }
        }

        // find and require kernel
        $kernelPath = $container->getParameter('behat.shopware_extension.kernel.path');
        if (file_exists($kernel = $basePath.DIRECTORY_SEPARATOR.$kernelPath)) {
            require_once($kernel);
        } elseif (file_exists($kernelPath)) {
            require_once($kernelPath);
        }

        // boot kernel
        $kernel = $container->get('behat.shopware_extension.kernel');
        $kernel->boot();
    }
}
