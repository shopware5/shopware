<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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
 * @package   Shopware\Plugins\RebuildIndex\Controllers\Backend
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Backend_SearchIndex extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Helper function to get the new seo index component with auto completion
     * @return Shopware_Components_SeoIndex
     */
    public function SearchIndex()
    {
        return Shopware()->SearchIndex();
    }

    /**
     * This controller action is used to build the search index.
     */
    public function buildAction()
    {
        @set_time_limit(1200);

        $adapter = new Shopware_Components_Search_Adapter_Default(
            Shopware()->Db(),
            Shopware()->Cache(),
            new Shopware_Components_Search_Result_Default(),
            Shopware()->Config()
        );
        $adapter->buildSearchIndex();

        $this->View()->assign(array('success' => true));
    }
}

