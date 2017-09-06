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
 * Shopware Store - Customer list backend module.
 *
 * The detail store is used for the customer detail page and allows the user
 * to edit or add customers.
 */
// {block name="backend/customer/store/detail"}
Ext.define('Shopware.apps.Customer.store.Detail', {
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
     * Define the used model for this store
     * @string
     */
    model: 'Shopware.apps.Customer.model.Customer'
});
// {/block}
