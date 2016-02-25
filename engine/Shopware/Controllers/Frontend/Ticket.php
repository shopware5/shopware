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
 * Shopware Frontend Controller for the form module
 */
class Shopware_Controllers_Frontend_Ticket extends Shopware_Controllers_Frontend_Forms
{
    /**
     * Check licence first
     *
     * @return void
     */
    public function preDispatch()
    {
        return $this->forward($this->Request()->getActionName(), 'forms');
    }

    /**
     * Show Ticket formular
     * @deprecated
     * @return void
     */
    public function indexAction()
    {
    }

    /**
     * Show ticket history
     * @deprecated
     * @return void
     */
    public function listingAction()
    {
    }

    /**
     * Open new ticket mask
     * @deprecated
     * @return void
     */
    public function requestAction()
    {
    }

    /**
     * Show ticket details
     * @deprecated
     * @return void
     */
    public function detailAction()
    {
    }

    /**
     * Show ticket direct link
     * @deprecated
     * @return void
     */
    public function directAction()
    {
    }

    /**
     * Save new ticket into database
     * @deprecated
     * @return void
     */
    public function commitForm()
    {
    }
}
