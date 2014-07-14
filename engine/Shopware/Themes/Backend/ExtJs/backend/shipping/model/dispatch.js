/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
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
    extend : 'Ext.data.Model',
    /**
     * The fields used for this model
     * @array
     */
    fields : [
		//{block name="backend/shipping/model/dispatch/fields"}{/block}
        { name : 'id' },
        { name : 'name' },
        { name : 'type' },
        { name : 'description' },
        { name : 'comment' },
        { name : 'active'},
        { name : 'position' },
        { name : 'calculation' },
        { name : 'surchargeCalculation' },
        { name : 'taxCalculation' },

        { name : 'shippingFree', useNull: true },
        { name : 'customerGroupId', useNull: true, defaultValue: null },
        { name : 'bindShippingFree',  useNull: true, defaultValue: null },
        { name : 'bindTimeFrom', type: 'date', dateFormat: 'H:i',  useNull:true, defaultValue: null },
        { name : 'bindTimeTo', type: 'date', dateFormat: 'H:i', useNull:true, defaultValue: null },
        {
            name : 'bindTimeFromConvert',
            type: 'integer',
            dateFormat: 'H:i',
            convert : function(value, record) {
                return record.convertTimeToInteger(record.get('bindTimeFrom'));
            }
        },
        {
            name : 'bindTimeToConvert',
            type: 'integer',
            dateFormat: 'H:i',
            convert : function(value, record) {
                return record.convertTimeToInteger(record.get('bindTimeTo'))
            }
        },
        { name : 'bindInStock', useNull:true, defaultValue: null },
        { name : 'bindLastStock', useNull:true, defaultValue: null },
        { name : 'bindWeekdayFrom', useNull:true, defaultValue: null },
        { name : 'bindWeekdayTo', useNull:true, defaultValue: null },
        { name : 'bindWeightFrom', type: 'float' },
        { name : 'bindWeightTo', type: 'float' },
        { name : 'bindPriceFrom',type: 'float' },
        { name : 'bindPriceTo', type: 'float' },
        { name : 'statusLink' , useNull:true, defaultValue: null},
        { name : 'bindSql', useNull:true, defaultValue: null },
        { name : 'calculationSql', useNull:true, defaultValue: null },

        //{ name : 'subshop' },
        { name : 'multiShopId', useNull:true, defaultValue: null },
       // { name : 'shop', useNull:true, defaultValue: null }
    ],
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
     * If the name of the field is 'id' extjs assumes automatically that
     * this field is an unique identifier.
     */
    idProperty : 'id',

     /**
     * Define the associations of the dispatch model.
     * One dispatch has one or many allowed means of payment, blocked categories, allowed countries and holidays
     * @array
     */
    associations:[
        {
            type: 'hasMany',
            model: 'Shopware.apps.Shipping.model.Attribute',
            name: 'getAttributes',
            associationKey: 'attribute'
        },
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
    ],

    convertTimeToInteger : function(myTime) {
        if (Ext.isEmpty(myTime)) {
            return myTime;
        }
        // convert hours to minutes, sum them up and convert them to seconds.
        return (60 * myTime.getHours() + myTime.getMinutes()) * 60;
    }
}
);
//{/block}
