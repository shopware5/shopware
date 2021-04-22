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

use Shopware\Models\Shop\DetachedShop;

class Shopware_Controllers_Widgets_Index extends Enlight_Controller_Action
{
    /**
     * Pre dispatch method
     */
    public function preDispatch(): void
    {
        $this->Response()->setHeader('x-robots-tag', 'noindex');

        if (strtolower($this->Request()->getActionName()) === 'refreshstatistic') {
            $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        }
    }

    /**
     * Refresh shop statistic
     */
    public function refreshStatisticAction(): void
    {
        $request = $this->Request();
        $response = $this->Response();

        /** @var Shopware_Plugins_Frontend_Statistics_Bootstrap $plugin */
        $plugin = Shopware()->Plugins()->Frontend()->Statistics();
        $plugin->updateLog($request, $response);
    }

    /**
     * Get cms menu
     */
    public function menuAction(): void
    {
        $this->View()->assign('sGroup', $this->Request()->getParam('group'));
        $plugin = Shopware()->Plugins()->Core()->ControllerBase();
        $this->View()->assign('sMenu', $plugin->getMenu());
    }

    /**
     * Get shop menu
     */
    public function shopMenuAction(): void
    {
        $shop = Shopware()->Shop();

        if ($shop === null) {
            throw new RuntimeException('Shop needs to be set to call this action');
        }

        $main = DetachedShop::createFromShop($shop->getMain() !== null ? $shop->getMain() : $shop);

        $this->View()->assign('shop', $shop);
        if (!$this->Request()->getParam('hideCurrency', false)) {
            $this->View()->assign('currencies', $shop->getCurrencies());
        }
        $languages = $shop->getChildren()->toArray();
        foreach ($languages as $languageKey => $language) {
            $language = DetachedShop::createFromShop($language);
            if (!$language->getActive()) {
                unset($languages[$languageKey]);
            }
        }
        array_unshift($languages, $main);
        $this->View()->assign('languages', $languages);
    }
}
