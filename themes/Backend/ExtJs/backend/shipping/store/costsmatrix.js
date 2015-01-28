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
//{block name="backend/shipping/store/costmatrix"}
Ext.define('Shopware.apps.Shipping.store.Costsmatrix', {
    /**
     * Extend for the standard ExtJS 4
     * @string
     */
    extend : 'Ext.data.Store',
    /**
     * An alias name for easy access
     */
    alias  : 'store.shippingCostsmatrix',
    /**
     * StoreId für StoreManager.lookup / Ext.getStore
     * Needs to be specified explicitly here, as the store is
     * is not always created in the controller and therefore the
     * storeId will not always be overwritten automatically.
     * http://docs.sencha.com/ext-js/4-1/#!/api/Ext.data.Store-cfg-storeId
     */
    storeId: 'Costsmatrix',
    /**
     * Auto load the store after the component
     * is initialized
     * @boolean
     */
    autoLoad : false,

    /**
     * Amount of data loaded at once
     * @integer
     */
    pageSize : 30,

    /**
     * Define the used model for this store
     * @string
     */
    model : 'Shopware.apps.Shipping.model.Costsmatrix',

    /**
     * to upload all selected items in one request
     * @boolean
     */
    batch:true
});
//{/block}
