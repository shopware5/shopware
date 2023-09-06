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
 * @subpackage Store
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Store - Order list backend module
 *
 * The list store is used from the order list grid.
 */
//{block name="backend/order/store/list"}
Ext.define('Shopware.apps.Order.store.Order', {
    /**
     * Extend for the standard ExtJS 4
     * @string
     */
    extend: 'Ext.data.Store',

    /**
     * Auto load the store after the component is initialized
     * @boolean
     */
    autoLoad: false,

    /**
     * Enable remote sort.
     * @boolean
     */
    remoteSort: true,

    /**
     * Enable remote filtering
     * @boolean
     */
    remoteFilter: true,

    /**
     * Amount of data loaded at once
     * @integer
     */
    pageSize: 20,

    /**
     * to upload all selected items in one request
     * @boolean
     */
    batch: true,

    /**
     * Define the used model for this store
     * @string
     */
    model: 'Shopware.apps.Order.model.Order',

    /**
     * Default sorters
     * @array
     */
    sorters: [
        {
            property: 'orderTime',
            direction: 'DESC'
        }
    ]
});
//{/block}
