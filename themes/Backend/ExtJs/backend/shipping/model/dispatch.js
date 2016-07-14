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
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model - Shipping
 *
 * todo@all: Documentation
 */
//{block name="backend/shipping/model/dispatch"}
Ext.define('Shopware.apps.Shipping.model.Dispatch', {
    /**
     * Extends the standard ExtJS 4
     * @string
     */
    extend : 'Shopware.apps.Shipping.model.DispatchList',
     /**
     * Configure the data communication
     * @object
     */
    proxy : {
        type : 'ajax',
        api : {
            read    : '{url controller="shipping" action="getShippingCosts"}',
            create  : '{url controller="shipping" action="createDispatch"}',
            update  : '{url controller="shipping" action="updateDispatch"}',
            destroy : '{url controller="shipping" action="delete"  targetField=dispatches}'
        },

        reader : {
            type : 'json',
            root : 'data'
        }
    },

     /**
     * Define the associations of the dispatch model.
     * One dispatch has one or many allowed means of payment, blocked categories, allowed countries and holidays
     * @array
     */
    associations:[
        {
            type:'hasMany',
            model:'Shopware.apps.Base.model.Payment',
            name:'getPayments',
            associationKey:'payments'
        },
        {
            type:'hasMany',
            model:'Shopware.apps.Base.model.Category',
            name:'getCategories',
            associationKey:'categories'
        },
        {
            type:'hasMany',
            model:'Shopware.apps.Base.model.Country',
            name:'getCountries',
            associationKey:'countries'
        } , {
            type:'hasMany',
            model:'Shopware.apps.Shipping.model.Holiday',
            name:'getHolidays',
            associationKey:'holidays'
        }
    ]
});
//{/block}
