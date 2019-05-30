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
//{block name="backend/shipping/store/country"}
Ext.define('Shopware.apps.Shipping.store.Country', {
    /**
     * Extend for the standard ExtJS 4
     *
     * @string
     */
    extend: 'Ext.data.Store',

    /**
     * Auto load the store after the component
     * is initialized
     *
     * @boolean
     */
    autoLoad: false,

    /**
     * Amount of data loaded at once
     *
     * @integer
     */
    pageSize: 30,

    /**
     * Enables the remote filter system
     *
     * @boolean
     */
    remoteFilter: true,

    /**
     * Define the used model for this store
     *
     * @string
     */
    model: 'Shopware.apps.Base.model.Country',

    /**
     * Configure the data communication
     *
     * @object
     */
    proxy: {
        type:'ajax',

        /**
         * Configure the url mapping for the different
         * store operations based on
         *
         * @string
         */
        url: '{url controller="shipping" action="getCountries"}',

        /**
         * Configure the data reader
         *
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
