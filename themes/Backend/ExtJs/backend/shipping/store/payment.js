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
 * @package    Shipping
 * @subpackage Store
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Store - Shipping
 *
 * todo@all: Documentation
 */
//{block name="backend/shipping/store/shop"}
Ext.define('Shopware.apps.Shipping.store.Payment', {
    /**
     * Extend for the standard ExtJS 4
     * @string
     */
    extend: 'Ext.data.Store',
    /**
     * Auto load the store after the component
     * is initialized
     * @boolean
     */
    autoLoad: false,
    /**
     * Amount of data loaded at once
     * @integer
     */
    pageSize: 250,
    /**
     * enables the remote filter system
     * @boolean
     */
    remoteFilter: true,

    /**
     * Define the used model for this store
     * @string
     */
    model: 'Shopware.apps.Base.model.Payment',
    /**
     * Configure the data communication
     * @object
     */
    proxy: {
        type: 'ajax',
        /**
         * Configure the url mapping for the different
         * store operations based on
         * @object
         */
        url: '{url controller="shipping" action="getPayments"}',

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
