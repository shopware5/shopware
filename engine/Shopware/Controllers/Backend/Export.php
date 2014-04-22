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
 * Export controller
 *
 * This controller is used by the ProductFeed modul.
 * The ProductFeed modul will call this controller to export the chosen ProductFeed with all options.
 * The controller uses the base class sExport for all export relevant methods.
 * Sets a different header to return a downloadable export file.
 */
class Shopware_Controllers_Backend_Export extends Enlight_Controller_Action
{
    /**
     * Init controller method
     *
     * Disables the authorization-checking and template renderer.
     */
    public function init()
    {
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
        $this->Front()->setParam('disableOutputBuffering', true);
        $this->Front()->returnResponse(true);
    }

    /**
     * Index action method
     *
     * Creates the export product.
     */
    public function indexAction()
    {
        /**
         * initialize the base class sExport
         */
        $export = Shopware()->Modules()->Export();
        $export->sSYSTEM = Shopware()->System();
        $export->sFeedID = (int)$this->Request()->feedID;
        $export->sHash = $this->Request()->hash;
        $export->sDB = Shopware()->Adodb();

        $export->sInitSettings();

        /**
         * initialize smarty
         */
        $export->sSmarty = $this->View()->Engine();
        $export->sInitSmarty();

        /**
         * set feed specific options to the export and sets
         * the right header
         */
        if (!empty($export->sSettings['encodingID']) && $export->sSettings['encodingID'] == 2) {
            if (!empty($export->sSettings['formatID']) && $export->sSettings['formatID'] == 3) {
                $this->Response()->setHeader('Content-Type', 'text/xml;charset=utf-8');
            } else {
                $this->Response()->setHeader('Content-Type', 'text/x-comma-separated-values;charset=utf-8');
            }
        } else {
            if (!empty($export->sSettings['formatID']) && $export->sSettings['formatID'] == 3) {
                $this->Response()->setHeader('Content-Type', 'text/xml;charset=iso-8859-1');
            } else {
                $this->Response()->setHeader('Content-Type', 'text/x-comma-separated-values;charset=iso-8859-1');
            }
        }
        $this->Response()->sendHeaders();
        $handleResource = fopen('php://output', 'w');
        $export->executeExport($handleResource);
    }
}
