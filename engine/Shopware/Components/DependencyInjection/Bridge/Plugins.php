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

namespace Shopware\Components\DependencyInjection\Bridge;

use Enlight_Event_EventManager;
use Enlight_Loader;
use Enlight_Plugin_PluginManager;
use Shopware;
use Shopware\Components\Plugin\ConfigReader;
use Shopware_Components_Plugin_Namespace;

class Plugins
{
    /**
     * @return Enlight_Plugin_PluginManager
     */
    public function factory(
        Enlight_Loader $loader,
        Enlight_Event_EventManager $eventManager,
        Shopware $application,
        array $pluginDirectories,
        ConfigReader $configReader
    ) {
        $pluginManager = new Enlight_Plugin_PluginManager($application);

        foreach (['Core', 'Frontend', 'Backend'] as $namespace) {
            $namespace = new Shopware_Components_Plugin_Namespace(
                $namespace,
                null,
                $pluginDirectories,
                $configReader
            );

            $pluginManager->registerNamespace($namespace);
            $eventManager->registerSubscriber($namespace->Subscriber());
        }

        foreach ($pluginDirectories as $source => $path) {
            $loader->registerNamespace(
                'Shopware_Plugins',
                $path
            );
        }

        return $pluginManager;
    }
}
