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

namespace Shopware\Components\DependencyInjection\Bridge;

use Enlight_Class;
use Enlight_Controller_Front;
use Enlight_Event_EventManager;
use Enlight_Plugin_PluginManager;
use Exception;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class Front
{
    /**
     * Loads the Zend resource and initials the Enlight_Controller_Front class.
     * After the front resource is loaded, the controller path is added to the
     * front dispatcher. After the controller path is set to the dispatcher,
     * the plugin namespace of the front resource is set.
     *
     * @param array<string, mixed> $options
     *
     * @throws Exception
     *
     * @return Enlight_Controller_Front
     */
    public function factory(
        Container $container,
        Enlight_Event_EventManager $eventManager,
        array $options,
        RequestStack $requestStack
    ) {
        /** @var Enlight_Controller_Front $front */
        $front = Enlight_Class::Instance('Enlight_Controller_Front', [$eventManager]);

        $front->setDispatcher($container->get('dispatcher'));

        $front->setRouter($container->get(RouterInterface::class));

        $front->setParams($options);

        $front->setRequestStack($requestStack);

        /** @var Enlight_Plugin_PluginManager $plugins */
        $plugins = $container->get('plugins');

        $plugins->registerNamespace($front->Plugins());

        if (!empty($options['throwExceptions'])) {
            $front->throwExceptions((bool) $options['throwExceptions']);
        }

        try {
            $container->load('cache');
            $container->load('db');
            $container->load('plugins');
        } catch (Exception $e) {
            if ($front->throwExceptions()) {
                throw $e;
            }
            $front->Response()->setException($e);
        }

        return $front;
    }
}
