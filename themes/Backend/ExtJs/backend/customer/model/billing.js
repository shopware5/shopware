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
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model - Customer list backend module.
 * The billing model represents a single data row of the s_user_billingaddress or
 * the Shopware\Models\Customer\Billing.
 */
//{block name="backend/customer/model/billing"}
Ext.define('Shopware.apps.Customer.model.Billing', {

    /**
     * Extends the standard Ext Model
     * @string
     */
    extend:'Shopware.apps.Base.model.BillingAddress',

    /**
     * The fields used for this model
     * @array
     */
    fields:[
		//{block name="backend/customer/model/billing/fields"}{/block}
        { name:'birthday', type:'date', dateFormat:'d.m.Y' },
        { name:'stateId', type:'int', useNull:true }
    ]
});
//{/block}

