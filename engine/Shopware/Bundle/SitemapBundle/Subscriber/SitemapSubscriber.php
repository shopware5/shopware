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

namespace Shopware\Bundle\SitemapBundle\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use Shopware\Bundle\SitemapBundle\Controller\SitemapIndexXml;

class SitemapSubscriber implements SubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_SitemapIndexXml' => 'registerSitemapIndexXmlController',
        ];
    }

    /**
     * @return string
     */
    public function registerSitemapIndexXmlController()
    {
        return SitemapIndexXml::class;
    }

    /**
     * Sitemaps are now generated live in \Shopware\Bundle\SitemapBundle\Controller\SitemapIndexXml::indexAction
     * when they are requested
     *
     * @deprecated Will be removed in 5.6 without replacement
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onKernelTerminate(Enlight_Event_EventArgs $args)
    {
    }
}
