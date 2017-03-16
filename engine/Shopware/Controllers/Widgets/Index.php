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

use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

/**
 * Shopware Application
 */
class Shopware_Controllers_Widgets_Index extends Enlight_Controller_Action
{
    /**
     * Pre dispatch method
     */
    public function preDispatch()
    {
        if ($this->Request()->getActionName() == 'refreshStatistic') {
            $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        }
    }

    /**
     * Refresh shop statistic
     */
    public function refreshStatisticAction()
    {
        /** @var ShopContextInterface $context */
        $context = $this->get('shopware_storefront.context_service')->getShopContext();

        $this->get('shopware.statistics.tracer')->trace($this->Request(), $context);
    }

    /**
     * Get cms menu
     */
    public function menuAction()
    {
        $this->View()->sGroup = $this->Request()->getParam('group');
        $plugin = Shopware()->Plugins()->Core()->ControllerBase();
        $this->View()->sMenu = $plugin->getMenu();
    }

    /**
     * Get shop menu
     */
    public function shopMenuAction()
    {
        $shop = Shopware()->Shop();
        $main = $shop->getMain() !== null ? $shop->getMain() : $shop;
        Shopware()->Models()->detach($main);

        $this->View()->shop = $shop;
        if (!$this->Request()->getParam('hideCurrency', false)) {
            $this->View()->currencies = $shop->getCurrencies();
        }
        $languages = $shop->getChildren()->toArray();
        foreach ($languages as $languageKey => $language) {
            Shopware()->Models()->detach($language);
            if (!$language->getActive()) {
                unset($languages[$languageKey]);
            }
        }
        array_unshift($languages, $main);
        $this->View()->languages = $languages;
    }
}
