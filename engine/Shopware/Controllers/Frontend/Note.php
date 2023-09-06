<?php

/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

class Shopware_Controllers_Frontend_Note extends Enlight_Controller_Action
{
    /**
     * Pre dispatch method
     */
    public function preDispatch()
    {
        $this->View()->setScope(Enlight_Template_Manager::SCOPE_PARENT);
        $this->View()->assign('userInfo', $this->get('shopware_account.store_front_greeting_service')->fetch());
    }

    public function postDispatch()
    {
        Shopware()->Session()->set('sNotesQuantity', Shopware()->Modules()->Basket()->sCountNotes());
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $view = $this->View();
        $view->assign('sNotes', Shopware()->Modules()->Basket()->sGetNotes());
        $view->assign('sUserLoggedIn', Shopware()->Modules()->Admin()->sCheckUser());
        $view->assign('sOneTimeAccount', Shopware()->Session()->offsetGet('sOneTimeAccount'));
    }

    /**
     * @return void
     */
    public function deleteAction()
    {
        if (!empty($this->Request()->sDelete)) {
            Shopware()->Modules()->Basket()->sDeleteNote($this->Request()->sDelete);
        }

        $this->redirect(['action' => 'index']);
    }

    /**
     * @return void
     */
    public function addAction()
    {
        $orderNumber = (string) $this->Request()->getParam('ordernumber');

        if ($this->addNote($orderNumber)) {
            $this->View()->assign('sArticleName', Shopware()->Modules()->Articles()->sGetArticleNameByOrderNumber($orderNumber));
        }

        $this->redirect(['action' => 'index']);
    }

    /**
     * @return void
     */
    public function ajaxAddAction()
    {
        $this->Request()->setHeader('Content-Type', 'application/json');
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        $this->Response()->setContent(json_encode(
            [
                'success' => $this->addNote((string) $this->Request()->getParam('ordernumber')),
                'notesCount' => (int) Shopware()->Modules()->Basket()->sCountNotes(),
            ]
        ));
    }

    private function addNote(string $orderNumber): bool
    {
        if (empty($orderNumber)) {
            return false;
        }

        $productId = Shopware()->Modules()->Articles()->sGetArticleIdByOrderNumber($orderNumber);
        $productName = Shopware()->Modules()->Articles()->sGetArticleNameByOrderNumber($orderNumber);

        if (empty($productId) || empty($productName)) {
            return false;
        }

        Shopware()->Modules()->Basket()->sAddNote($productId, $productName, $orderNumber);

        return true;
    }
}
