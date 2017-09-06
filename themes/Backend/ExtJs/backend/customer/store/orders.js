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
 * @package    Customer
 * @subpackage Store
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name=backend/customer/view/main}

/**
 * Shopware Store - Customer list backend module
 *
 * The orders store is used from the customer order grid which is displayed
 * on bottom of the order tab.
 */
// {block name="backend/customer/store/orders"}
Ext.define('Shopware.apps.Customer.store.Orders', {
    /**
     * Extend for the standard ExtJS 4
     * @string
     */
    extend: 'Ext.data.Store',
    /**
     * Disable auto loading
     * @boolean
     */
    autoLoad: false,
    /**
     * Amount of data loaded at once
     * @integer
     */
    pageSize: 10,
    /**
     * Enable remote sorting.
     * @boolean
     */
    remoteSort: true,
    /**
     * Enable remote filtering.
     * @boolean
     */
    remoteFilter: true,
    /**
     * Define the used model for this store
     * @string
     */
    model: 'Shopware.apps.Customer.model.Order'
});
// {/block}
