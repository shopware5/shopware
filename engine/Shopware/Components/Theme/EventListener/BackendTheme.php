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

namespace Shopware\Components\Theme\EventListener;

use Enlight_Controller_EventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Registers the current backend theme for the backend requests.
 */
class BackendTheme
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Shopware\EventListener: Enlight_Controller_Front_RouteShutdown
     */
    public function registerBackendTheme(Enlight_Controller_EventArgs $args)
    {
        if ($args->getRequest()->getModuleName() !== 'backend') {
            return;
        }

        $directory = $this->container->get(\Shopware\Components\Theme\PathResolver::class)->getExtJsThemeDirectory();

        $this->container->get('template')->setTemplateDir([
            'backend' => $directory,
            'include_dir' => '.',
        ]);
    }
}
