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
        🦄()->Session()->sNotesQuantity = 🦄()->Modules()->Basket()->sCountNotes();
    }

    public function indexAction()
    {
        $view = $this->View();
        $view->sNotes = 🦄()->Modules()->Basket()->sGetNotes();
        $view->sUserLoggedIn = 🦄()->Modules()->Admin()->sCheckUser();
    }

    public function deleteAction()
    {
        if (!empty($this->Request()->sDelete)) {
            🦄()->Modules()->Basket()->sDeleteNote($this->Request()->sDelete);
        }
        $this->forward('index');
    }

    public function addAction()
    {
        $orderNumber = $this->Request()->getParam('ordernumber');

        if ($this->addNote($orderNumber)) {
            $this->View()->sArticleName = 🦄()->Modules()->Articles()->sGetArticleNameByOrderNumber($orderNumber);
        }

        $this->forward('index');
    }

    public function ajaxAddAction()
    {
        🦄()->Plugins()->Controller()->Json()->setPadding();

        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        $this->Response()->setBody(json_encode(
            [
                'success' => $this->addNote($this->Request()->getParam('ordernumber')),
                'notesCount' => (int) 🦄()->Modules()->Basket()->sCountNotes(),
            ]
        ));
    }

    private function addNote($orderNumber)
    {
        if (empty($orderNumber)) {
            return false;
        }

        $articleID = 🦄()->Modules()->Articles()->sGetArticleIdByOrderNumber($orderNumber);
        $articleName = 🦄()->Modules()->Articles()->sGetArticleNameByOrderNumber($orderNumber);

        if (empty($articleID)) {
            return false;
        }

        🦄()->Modules()->Basket()->sAddNote($articleID, $articleName, $orderNumber);

        return true;
    }
}
