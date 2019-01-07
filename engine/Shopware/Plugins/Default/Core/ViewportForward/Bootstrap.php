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

class Shopware_Plugins_Core_ViewportForward_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    public function install()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Front_PreDispatch',
            'onPreDispatch',
            10
        );

        return true;
    }

    public static function onPreDispatch(Enlight_Event_EventArgs $args)
    {
        $request = $args->getSubject()->Request();

        if ($request->getModuleName() && $request->getModuleName() !== 'frontend') {
            return;
        }

        switch ($request->getControllerName()) {
            case 'sViewport,sale':
                $url = $args->getRequest()->getPathInfo();
                $url = trim($url, '/');

                foreach (explode('/', $url) as $part) {
                    $part = explode(',', $part);
                    if (!empty($part[0]) && !empty($part[1])) {
                        $request->setParam($part[0], $part[1]);
                    }
                }

                if ($request->getParam('sAction') && $request->getParam('sAction') === 'doSale') {
                    $request->setControllerName('checkout')->setActionName('finish')->setDispatched(false);
                } else {
                    $request->setControllerName('checkout')->setActionName('confirm')->setDispatched(false);
                }
                break;
            case 'cat':
                $request->setControllerName('listing')->setDispatched(false);
                break;
            case 'supplier':
                $url = $args->getSubject()->Router()->assemble([
                        'controller' => 'listing',
                        'action' => 'manufacturer',
                        'sSupplier' => $request->getParam('sSupplier'),
                ]);
                $args->getSubject()->Response()->setRedirect($url, 301);
                break;
            case 'captcha':
                $request->setModuleName('widgets')->setControllerName('captcha')->setDispatched(false);
                break;
            case 'password':
            case 'login':
            case 'logout':
                $request->setActionName($request->getParam('sViewport'));
                // no break
            case 'admin':
                $request->setControllerName('account')->setDispatched(false);
                break;
            case 'registerFC':
            case 'register1':
            case 'register2':
            case 'register2shipping':
            case 'register3':
                $request->setControllerName('register')->setDispatched(false);
                break;
            case 'sViewport,basket':
            case 'basket':
                $request->setControllerName('checkout')->setActionName('cart')->setDispatched(false);
                break;
            case 'searchFuzzy':
                $request->setControllerName('search')->setActionName('index')->setDispatched(false);
                break;
            case 'newsletterListing':
                $request->setControllerName('newsletter')->setActionName('listing')->setDispatched(false);
                break;
            case 'support':
                $request->setControllerName('forms')->setActionName('index')->setDispatched(false);
                break;
            case 'ticketdirect':
                $request->setControllerName('forms')->setActionName('direct')->setDispatched(false);
                break;
            default:
                break;
        }
    }

    public function getCapabilities()
    {
        return [
            'install' => false,
            'enable' => false,
            'update' => true,
        ];
    }
}
