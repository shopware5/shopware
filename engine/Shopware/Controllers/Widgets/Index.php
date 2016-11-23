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

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;

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
        $request = $this->Request();
        $response = $this->Response();

        /** @var Shopware_Plugins_Frontend_Statistics_Bootstrap $plugin */
        $plugin = $this->get('plugin_manager')->Frontend()->Statistics();
        $plugin->updateLog($request, $response);

        if (($articleId = $request->getParam('articleId')) !== null) {
            /** @var \Shopware_Plugins_Frontend_LastArticles_Bootstrap $plugin */
            $plugin = $this->get('plugin_manager')->Frontend()->LastArticles();
            $plugin->setLastArticleById($articleId);
        }
    }

    /**
     * Get cms menu
     */
    public function menuAction()
    {
        $this->View()->assign('sGroup', $this->Request()->getParam('group'));
        /** @var Shopware_Plugins_Core_ControllerBase_Bootstrap $plugin */
        $plugin = $this->get('plugin_manager')->Core()->ControllerBase();
        $this->View()->assign('sMenu', $plugin->getMenu());
    }

    /**
     * Get shop menu
     */
    public function shopMenuAction()
    {
        /** @var Shop $shop */
        $shop = $this->get('shop');
        $main = $shop->getMain() !== null ? $shop->getMain() : $shop;
        /** @var ModelManager $modelManager */
        $modelManager = $this->get('models');
        $modelManager->detach($main);

        $this->View()->assign('shop', $shop);

        if (!$this->Request()->getParam('hideCurrency', false)) {
            $this->View()->assign('currencies', $shop->getCurrencies());
        }

        $languages = $shop->getChildren()->toArray();
        /** @var Shop $language */
        foreach ($languages as $languageKey => $language) {
            $modelManager->detach($language);
            if (!$language->getActive()) {
                unset($languages[$languageKey]);
            }
        }

        array_unshift($languages, $main);
        $this->View()->assign('languages', $languages);
    }
}
