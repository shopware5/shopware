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

use Shopware\Bundle\CartBundle\Infrastructure\StoreFrontCartService;

/**
 * Shopware Application
 */
class Shopware_Controllers_Widgets_Checkout extends Enlight_Controller_Action
{
    public function infoAction()
    {
        /** @var StoreFrontCartService $service */
        $service = $this->get('shopware.cart.storefront_service');

        /** @var Enlight_Components_Session_Namespace $session */
        $session = $this->get('session');

        $cart = $service->getCart();

        $this->View()->assign([
            'sBasketQuantity' => $cart->getCalculatedCart()->getCalculatedLineItems()->filterGoods()->count(),
            'sBasketAmount' => $cart->getPrice()->getTotalPrice(),
            'sUserLoggedIn' => !empty($session->get('sUserId')),
            'sNotesQuantity' => $session->get('sNotesQuantity') ?: Shopware()->Modules()->Basket()->sCountNotes(),
        ]);
    }
}
