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
 *
 * @category   Shopware
 * @package    Shopware_Controllers
 * @subpackage Content
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     Stefan Hamann
 */

/**
 * todo@all: Documentation
 */
class Shopware_Controllers_Frontend_Content extends Enlight_Controller_Action
{
	public function indexAction()
	{
		if (empty($this->Request()->sContent)){
			return $this->forward('index','index');
		}

		$groupID = $this->Request()->sContent;
		$detailID = $this->Request()->sCid;

		if (!empty($detailID)){
			$this->view->loadTemplate('frontend/content/detail.tpl');
		}

        if (!empty($detailID)){
            $sContent = Shopware()->Modules()->Cms()->sGetDynamicContentById($groupID, $detailID);
            $this->view->sContentItem = $sContent['sContent'];
            $this->view->sPages = $sContent['sPages'];
        } else {
            $sContent = Shopware()->Modules()->Cms()->sGetDynamicContentByGroup($groupID, $this->Request()->sPage);
            $this->view->sContent = $sContent['sContent'];
            $this->view->sPages = $sContent['sPages'];
        }
        $this->view->sContentName = Shopware()->Modules()->Cms()->sGetDynamicGroupName($groupID);
        $this->view->sBreadcrumb = array(0=>array('name'=>$this->view->sContentName));
	}
}
