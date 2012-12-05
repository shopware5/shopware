<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Plugins
 * @subpackage Plugin
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Shopware Template Plugin
 *
 * todo@all: Documentation
 */
class Shopware_Plugins_Core_SwagMultiShop_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Installs the plugin
     * @return bool
     */
    public function install()
    {
        $this->checkLicense();
        $this->subscribeEvents();
        return true;
    }

    /**
     * @param   bool $throwException
     * @param   string|null $host
     * @throws Exception
     * @return  bool
     */
    public function checkLicense($host = null, $throwException = true)
    {
        static $r, $m = 'SwagMultiShop';
        if(!isset($r)) {
            $s = base64_decode('5Er8LvEoyF/id9Uhq8GQV4T8f1g=');
            $c = base64_decode('iIbWuyxUix6Ni9cMAHbMmeRW+MA=');
            $r = sha1(uniqid('', true), true);
            /** @var $l Shopware_Components_License */
            $l = $this->Application()->License();
            $i = $l->getLicense($m, $r, $host);
            $t = $l->getCoreLicense();
            $u = strlen($t) === 20 ? sha1($t . $s . $t, true) : 0;
            $r = $i === sha1($c. $u . $r . $host, true);
        }
        if(!$r && $throwException) {
            throw new Exception("License check for module \"$m\" with host \"$host\" has failed.");
        }
        return $r;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.0.6';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Multishop-Unterstützung';
    }

    /**
     * Creates and subscribe the events and hooks.
     */
    protected function subscribeEvents()
    {
        $this->subscribeEvent(
            'Enlight_Bootstrap_AfterRegisterResource_Shop',
            'onAfterRegisterResource',
            100
        );
        $this->subscribeEvent(
            'Enlight_Controller_Front_RouteStartup',
            'onRouteStartup',
            100
        );
        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Backend_Config',
            'onPostDispatchBackendConfig'
        );
    }

    /**
     * Event listener method
     *
     * @param Enlight_Controller_EventArgs $args
     */
    public function onRouteStartup(Enlight_Controller_EventArgs $args)
    {
        $response = $args->getResponse();
        $bootstrap = $this->Application()->Bootstrap();
        if (!$bootstrap->issetResource('Shop')) {
            return;
        }
        $shop = $this->Application()->Shop();
        if($shop->getDefault()) {
            return;
        }
        $main = $shop->getMain();
        if ($main === null) {
            /** @var $repository Shopware\Models\Shop\Repository */
            $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
            $main = $repository->getActiveById($shop->getId());
        }
        $shop->setTemplate($main->getTemplate());
        if($main->getHost()) {
            $shop->setHost($main->getHost());
        }
        $shop->setSecureHost($main->getSecureHost() ? : $main->getHost());
        try {
            $this->checkLicense($main->getHost());
        } catch(Exception $e) {
            $response->setException($e);
        }
    }

    /**
     * Event listener method
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onAfterRegisterResource(Enlight_Event_EventArgs $args)
    {

    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatchBackendConfig(Enlight_Event_EventArgs $args)
    {
        /** @var $action Shopware_Controllers_Backend_ExtJs */
        $action = $args->getSubject();
        $view = $action->View();
        if(!$view->hasTemplate()) {
            return;
        }
        $this->registerMyTemplateDir();
        $view->extendsTemplate('backend/config/view/multishop/detail.js');
    }

    /**
     *
     */
    protected function registerMyTemplateDir()
    {
        $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/', 'multishop'
        );
    }
}
