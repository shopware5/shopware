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

namespace Shopware\Components\Theme\EventListener;

use Enlight\Event\SubscriberInterface;
use Shopware\Models\Shop\Shop;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ConfigLoader implements SubscriberInterface
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
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatch_Frontend' => 'onDispatch',
            'Enlight_Controller_Action_PostDispatch_Widgets' => 'onDispatch',
        ];
    }

    /**
     * @throws \Exception
     */
    public function onDispatch(\Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->get('subject');

        if (!$controller->View() || !$controller->View()->hasTemplate()) {
            return;
        }

        /** @var Shop $shop */
        $shop = $this->container->get('shop');

        $inheritance = $this->container->get('theme_inheritance');

        $templateManager = $this->container->get('template');
        $templateManager->addPluginsDir(
            $inheritance->getSmartyDirectories($shop->getTemplate())
        );

        $themeSettings = $templateManager->getTemplateVars('theme');
        if (!empty($themeSettings)) {
            return;
        }

        $config = $inheritance->buildConfig(
            $shop->getTemplate(),
            $shop,
            false
        );

        $templateManager->assign('theme', $config);
    }
}
