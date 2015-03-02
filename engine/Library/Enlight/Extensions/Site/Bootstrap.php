<?php
/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Enlight
 * @package    Enlight_Extensions
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Enlight site extension to set the site resource available.
 *
 * It loads automatically the appropriate site for the request.
 * If an appropriate site was found for the user, it will be stored in the session.
 *
 * @category   Enlight
 * @package    Enlight_Extensions
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Extensions_Site_Bootstrap extends Enlight_Plugin_Bootstrap_Config
{
    /**
     * Install site plugin.
     * Subscribes the init resource events for the site and site manager component.
     * In additional the Enlight_Controller_Front_DispatchLoopStartup will be subscribed
     * to set the site instance into the session object and set
     * the corresponding locale and currency object.
     */
    public function install()
    {
        $this->subscribeEvent(
            'Enlight_Bootstrap_InitResource_Site',
            'onInitResourceSite'
        );

        $this->subscribeEvent(
            'Enlight_Bootstrap_InitResource_Sites',
            'onInitResourceSiteManager'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Front_DispatchLoopStartup',
            'onStartDispatch'
        );
    }

    /**
     * Handles the init resource site manager event.
     * Returns the site manager.
     *
     * @param Enlight_Event_EventArgs $args
     * @return Enlight_Components_Site_Manager
     */
    public function onInitResourceSiteManager(Enlight_Event_EventArgs $args)
    {
        return new Enlight_Components_Site_Manager($this->Config());
    }

    /**
     * Handles the init resource site event.
     * Returns the current site resource from session or
     * reads a matching site resources from the manager,
     * if no is stored in the session.
     *
     * @param Enlight_Event_EventArgs $args
     * @return Zend_Log
     */
    public function onInitResourceSite(Enlight_Event_EventArgs $args)
    {
        /** @var $session Enlight_Components_Session_Namespace */
        $session = $this->Application()->Session();

        if (!isset($session->Site)) {
            /** @var $siteManager Enlight_Components_Site_Manager */
            $siteManager = $this->Application()->Sites();
            if (isset($_SERVER['HTTP_HOST'])) {
                $session->Site = $siteManager->findOneBy('host', $_SERVER['HTTP_HOST']);
            }
            if (!isset($session->Site)) {
                $session->Site = $siteManager->getDefault();
            }
        }

        return $session->Site;
    }

    /**
     * On Route add user-agent and remote-address to log component
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onStartDispatch(Enlight_Event_EventArgs $args)
    {
        /** @var $request Enlight_Controller_Request_Request */
        $request = $args->getRequest();

        if (($site = $request->getParam('__site')) !== null) {
            /** @var $siteManager Enlight_Components_Site_Manager */
            $siteManager = $this->Application()->Sites();
            $site = $siteManager->findOneBy('id', $site);

            /** @var $log Enlight_Components_Session_Namespace */
            $session = $this->Application()->Session();
            $session->Site = $session;
        } else {
            /** @var $site Enlight_Components_Site */
            $site = $this->Application()->Site();
        }

        if (($locale = $request->getParam('__locale')) !== null) {
            $site->setLocale($locale);
        }

        if (($currency = $request->getParam('__currency')) !== null) {
            $site->setCurrency($currency);
        }
    }
}
