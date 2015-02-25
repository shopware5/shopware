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

use Shopware\Components\DependencyInjection\Container;

/**
 * @category  Shopware
 * @package   Shopware\Components\DependencyInjection\Bridge
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Plugins
{
    /**
     * @param Container $container
     * @param \Enlight_Loader $loader
     * @param \Enlight_Event_EventManager $eventManager
     * @param \Shopware $application
     * @param array $config
     * @return \Enlight_Plugin_PluginManager
     */
    public function factory(
        Container $container,
        \Enlight_Loader $loader,
        \Enlight_Event_EventManager $eventManager,
        \Shopware $application,
        array $config
    ) {
        $pluginManager = new \Enlight_Plugin_PluginManager($application);
        $container->load('Table');

        if (!isset($config['namespaces'])) {
            $config['namespaces'] = array('Core', 'Frontend', 'Backend');
        }

        foreach ($config['namespaces'] as $namespace) {
            $namespace = new \Shopware_Components_Plugin_Namespace($namespace);
            $pluginManager->registerNamespace($namespace);
            $eventManager->registerSubscriber($namespace->Subscriber());
        }

        foreach (array('Local', 'Community', 'Default', 'Commercial') as $dir) {
            $loader->registerNamespace('Shopware_Plugins', Shopware()->AppPath('Plugins_' . $dir));
        }

        return $pluginManager;
    }
}
