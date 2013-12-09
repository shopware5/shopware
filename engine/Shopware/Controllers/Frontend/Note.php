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
 */
class Shopware_Controllers_Frontend_Note extends Enlight_Controller_Action
{
    /**
     * Pre dispatch method
     */
    public function preDispatch()
    {
        $this->View()->setScope(Enlight_Template_Manager::SCOPE_PARENT);
    }

    public function postDispatch()
    {
        Shopware()->Session()->sNotesQuantity = Shopware()->Modules()->Basket()->sCountNotes();
    }

    public function indexAction()
    {
		$view = $this->View();
        $view->sNotes = Shopware()->Modules()->Basket()->sGetNotes();
        $view->sUserLoggedIn = Shopware()->Modules()->Admin()->sCheckUser();
    }

    public function deleteAction()
    {
        if (!empty($this->Request()->sDelete)) {
            Shopware()->Modules()->Basket()->sDeleteNote($this->Request()->sDelete);
        }
        $this->forward('index');
    }

    public function addAction()
    {
        $ordernumber = $this->Request()->ordernumber;
        if (!empty($ordernumber)) {
            $articleID = Shopware()->Modules()->Articles()->sGetArticleIdByOrderNumber($ordernumber);
            $articleName = Shopware()->Modules()->Articles()->sGetArticleNameByOrderNumber($ordernumber);
            $this->View()->sArticleName = $articleName;
            if (!empty($articleID)) {
                Shopware()->Modules()->Basket()->sAddNote($articleID, $articleName, $ordernumber);
            }
        }
        $this->forward('index');
    }
}
