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
 *
 * @category   Shopware
 * @package    Order
 * @subpackage DetailBatch
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Store - Article Module
 *
 * The batch store of the order module is responsible to initial
 * all global stores of the detail page which used for the data selection.
 */
//{block name="backend/order/store/detail_batch"}
Ext.define('Shopware.apps.Order.store.DetailBatch', {
    /**
     * Extend for the standard ExtJS 4
     * @string
     */
    extend: 'Ext.data.Store',
    /**
     * to upload all selected items in one request
     * @boolean
     */
    batch: true,
    /**
     * Define the used model for this store
     * @string
     */
    model: 'Shopware.apps.Order.model.DetailBatch',
    /**
     * Configure the data communication
     * @object
     */
    proxy: {
        /**
         * Set proxy type to ajax
         * @string
         */
        type: 'ajax',

        /**
         * Configure the url mapping for the different
         * store operations based on
         * @object
         */
        url: '{url action="loadStores"}',

        /**
         * Configure the data reader
         * @object
         */
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    }
});
//{/block}
