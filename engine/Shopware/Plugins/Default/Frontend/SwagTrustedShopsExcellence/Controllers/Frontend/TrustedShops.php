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
 */

/**
 * Shopware Frontend Controller - SwagTrustedShopsExcellence
 *
 * @category  Shopware
 * @package   Shopware\Controllers\Frontend
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Frontend_TrustedShops extends Enlight_Controller_Action
{

	/**
	 * This functions called from the basket when the user click on "add buyer protection"
	 * @return void
	 */
	public function addBuyerProtectionAction()
	{
		$articleID = $this->Request()->getParam('articleID');

		$sql = "SELECT * FROM s_plugin_swag_trusted_shops_excellence_protection_items WHERE id = ?";

		$article = Shopware()->Db()->fetchRow($sql,array($articleID));

		if(!empty($article)) {
			Shopware()->Plugins()->Frontend()->SwagTrustedShopsExcellence()->addBuyerProtection($article);
		}

		$this->forward($this->Request()->getParam('sTargetAction'), "checkout");
	}

}