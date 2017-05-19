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

/**
 * Shopware Application
 */
class Shopware_Controllers_Widgets_Checkout extends Enlight_Controller_Action
{
    /**
     * @var sBasket
     */
    public $module;

    /**
     * Reference to Shopware session object (Shopware()->Session)
     *
     * @var Zend_Session_Namespace
     */
    protected $session;

    /**
     * Pre dispatch method
     */
    public function preDispatch()
    {
        $this->module = Shopware()->Modules()->Basket();
        $this->session = Shopware()->Session();
    }

    public function infoAction()
    {
        $view = $this->View();

        $config = $this->container->get('config');
        if ($config->get('useSltCookie')) {
            if (!isset($this->session->userInfo)) {
                $this->session->userInfo = $this->fetchUserInfo();
            }
            $view->userInfo = $this->session->userInfo;
        }

        $view->sBasketQuantity = isset($this->session->sBasketQuantity) ? $this->session->sBasketQuantity : 0;
        $view->sBasketAmount = isset($this->session->sBasketAmount) ? $this->session->sBasketAmount : 0;
        $view->sNotesQuantity = isset($this->session->sNotesQuantity) ? $this->session->sNotesQuantity : $this->module->sCountNotes();
        $view->sUserLoggedIn = !empty(Shopware()->Session()->sUserId);
    }

    private function fetchUserInfo()
    {
        $userId = $this->session->offsetGet('sUserId');
        if (!$userId) {
            $userId = $this->session->offsetGet('auto-user');
        }

        if (!$userId) {
            return null;
        }

        $connection = Shopware()->Container()->get('dbal_connection');

        $userInfo = $connection->fetchAssoc(
            'SELECT firstname, lastname, email, salutation, title, birthday FROM s_user WHERE id = :id',
            [':id' => $userId]
        );

        return $userInfo;
    }
}
