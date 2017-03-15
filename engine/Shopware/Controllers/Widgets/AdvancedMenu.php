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

use Shopware\Bundle\StoreFrontBundle\Service\AdvancedMenuServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

/**
 * Shopware AdvancedMenu Controller
 */
class Shopware_Controllers_Widgets_AdvancedMenu extends Enlight_Controller_Action
{
    public function indexAction()
    {
        /** @var ShopContextInterface $context */
        $context = $this->get('shopware_storefront.context_service')->getShopContext();

        /** @var AdvancedMenuServiceInterface $reader */
        $reader = $this->get('shopware_storefront.advanced_menu_service');

        $categories = $reader->get(
            $context,
            (int) $this->get('config')->getByNamespace('advancedMenu', 'levels')
        );

        $tree = $categories->getTree($context->getShop()->getCategory()->getId());

        $this->View()->assign([
            'advancedMenu' => json_decode(json_encode($tree), true),
            'columnAmount' => $this->get('config')->getByNamespace('advancedMenu', 'columnAmount'),
            'hoverDelay' => $this->get('config')->getByNamespace('advancedMenu', 'hoverDelay'),
        ]);
    }
}
