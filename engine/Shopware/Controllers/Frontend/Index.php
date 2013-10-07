<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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

/**
 * @category  Shopware
 * @package   Shopware\Controllers\Frontend
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Frontend_Index extends Enlight_Controller_Action
{
//    public function preDispatch()
//    {
//        if ($this->Request()->getActionName() != 'index') {
//            $this->forward('index'); return;
//        }
////        $this->View()->loadTemplate('frontend/home/index.tpl');
//    }

    public function indexAction()
    {
        $category = Shopware()->Shop()->get('parentID');

        $this->View()->assign('test', 123);
        $this->View()->sCategoryContent = Shopware()->Modules()->Categories()->sGetCategoryContent($category);

        if (Shopware()->Shop()->getTemplate()->getVersion() == 1) {
            $this->View()->sOffers = Shopware()->Modules()->Articles()->sGetPromotions($category);
        }
        $this->View()->sBanner = Shopware()->Modules()->Marketing()->sBanner($category);

        if ($this->Request()->getPathInfo() != '/') {
             $this->Response()->setHttpResponseCode(404);
        }
    }
}
