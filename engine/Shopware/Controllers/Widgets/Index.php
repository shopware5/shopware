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

class Shopware_Controllers_Widgets_Index extends Enlight_Controller_Action
{
    /**
     * Pre dispatch method
     */
    public function preDispatch()
    {
        $this->Response()->setHeader('x-robots-tag', 'noindex');

        if ($this->Request()->getActionName() === 'refreshStatistic') {
            $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        }
    }

    /**
     * Refresh shop statistic
     */
    public function refreshStatisticAction()
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
    public function menuAction()
    {
        $this->View()->assign('sGroup', $this->Request()->getParam('group'));
        $plugin = Shopware()->Plugins()->Core()->ControllerBase();
        $this->View()->assign('sMenu', $plugin->getMenu());
    }

    /**
     * Get shop menu
     */
    public function shopMenuAction()
    {
        $shop = Shopware()->Shop();
        $main = $shop->getMain() !== null ? $shop->getMain() : $shop;
        Shopware()->Models()->detach($main);

        $this->View()->assign('shop', $shop);
        if (!$this->Request()->getParam('hideCurrency', false)) {
            $this->View()->assign('currencies', $shop->getCurrencies());
        }
        $languages = $shop->getChildren()->toArray();
        foreach ($languages as $languageKey => $language) {
            Shopware()->Models()->detach($language);
            if (!$language->getActive()) {
                unset($languages[$languageKey]);
            }
        }
        array_unshift($languages, $main);
        $this->View()->assign('languages', $languages);
    }
}
