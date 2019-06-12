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
 * @package    Payment
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model - Payment list backend module.
 *
 * The Payment-Model represents a single Payment-mean.
 */
//{block name="backend/payment/model/payment"}
Ext.define('Shopware.apps.Payment.model.Payment', {
    /**
     * Extends the default extjs 4 model
     * @string
     */
    extend : 'Ext.data.Model',
     /**
     * Set an alias to make the handling a bit easier
      * @string
     */
    alias : 'model.payment',
    /**
     * Defined items used by that model
     *
     * We use a reduces feature set here - just necessary fields are selected
     *
     * @array
     */
    fields : [
        //{block name="backend/payment/model/payment/fields"}{/block}
        { name : 'text', type: 'string' },
        { name : 'id', type: 'int' },
        { name : 'name', type: 'string' },
        { name : 'description', type: 'string' },
        { name : 'translatedDescription', type: 'string' },
        { name : 'template', type: 'string' },
        { name : 'class', type: 'string' },
        { name : 'table', type: 'string' },
        { name : 'hide', type: 'int' },
        { name : 'additionalDescription', type: 'string' },
        { name : 'debitPercent', type: 'string' },
        { name : 'surcharge', type: 'string' },
        { name : 'surchargeString', type: 'string' },
        { name : 'position', type: 'int' },
        { name : 'active', type: 'boolean' },
        { name : 'esdActive', type: 'boolean' },
        { name : 'mobileInactive', type: 'boolean' },
        { name : 'embedIFrame', type: 'string' },
        { name : 'hideProspect', type: 'boolean' },
        { name : 'action', type: 'string' },
        { name : 'pluginId', type: 'int' },
        { name : 'iconCls', type: 'string' },
        { name : 'surcharge', type: 'double' },
        { name : 'source', type: 'int' }
    ],

    associations: [
        { type:'hasMany', model:'Shopware.apps.Payment.model.Country', name:'getCountries', associationKey:'countries' },
        { type:'hasMany', model:'Shopware.apps.Base.model.Shop', name:'getShops', associationKey:'shops' }
    ],

    proxy : {
        type : 'ajax',
        api : {
            read : '{url controller=payment action=getPayments}',
            create : '{url controller=payment action=createPayments}',
            update : '{url controller=payment action=updatePayments}',
            destroy : '{url controller=payment action=deletePayment}'
        },
        reader : {
            type : 'json',
            root: 'data'
        }
    }


});
//{/block}
