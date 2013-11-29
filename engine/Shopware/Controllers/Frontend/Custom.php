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
 * @subpackage Custom
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     Stefan Hamann
 * @author     $Author$
 */

/**
 * Custom controller
 *
 * todo@all: Documentation
 */
class Shopware_Controllers_Frontend_Custom extends Enlight_Controller_Action
{
    /**
     * Index action method
     */
    public function indexAction()
    {
        if ($this->Request()->isXmlHttpRequest()) {
            $this->View()->loadTemplate('frontend/custom/ajax.tpl');
        }

        $staticPage = Shopware()->Modules()->Cms()->sGetStaticPage($this->Request()->sCustom);

        if (!empty($staticPage['link'])) {
            $link = Shopware()->Modules()->Core()->sRewriteLink($staticPage['link'], $staticPage['description']);
            return $this->redirect($link, array('code' => 301));
        }

        if (!empty($staticPage['html'])) {
            $this->View()->sContent = $staticPage['html'];
        }

        for ($i = 1; $i <= 3; $i++) {
            if (empty($staticPage['tpl' . $i . 'variable']) || empty($staticPage['tpl' . $i . 'path'])) {
                continue;
            }
            if (!$this->View()->templateExists($staticPage['tpl' . $i . 'path'])) {
                continue;
            }
            $this->View()->assign($staticPage['tpl' . $i . 'variable'], $this->View()->fetch($staticPage['tpl' . $i . 'path']));
        }

        $this->View()->sCustomPage = $staticPage;
    }
}
