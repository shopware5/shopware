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
 * @package    RiskManagement
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/risk_management/main}

/**
 * Shopware UI - RiskManagement view panel
 *
 * This is the main panel, which contains the different fieldsets.
 * Payment-FieldSet: Contains the combobox to choose the payment
 * Risk-FieldSet: Contains all rules and risks
 * Example-FieldSet: Contains example-usages
 */
//{block name="backend/risk_management/view/risk_management/panel"}
Ext.define('Shopware.apps.RiskManagement.view.risk_management.Panel', {

    /**
    * Extend from the standard ExtJS 4
    * @string
    */
    extend: 'Ext.panel.Panel',
    bodyPadding: 10,


    /**
    * Alias name for the view. Could be used to get an instance
    * of the view through Ext.widget('risk_management-main-panel')
    * @string
    */
    alias: 'widget.risk_management-main-panel',
    /**
    * The window uses a border layout, so we need to set
    * a region for the grid panel
    * @string
    */
    region: 'center',
    /**
    * The view needs to be scrollable
    * @string
    */
    autoScroll: true,

    /**
    * Sets up the ui component
    * @return void
    */
    initComponent: function() {
        var me = this;

        me.addEvents('onChangePayment');
        me.items = me.createFieldSets();

        me.callParent(arguments);
    },

    /**
     * Creates the different fieldSets
     * @return Array
     */
    createFieldSets: function(){
        var me = this,
            fieldSets = [],
            data = me.createData();
        me.paymentFieldSet = Ext.create('Ext.form.FieldSet', {
            title: '{s name=paymentFieldSet/title}Choose payment method{/s}',
            items: [
                {
                    xtype: 'combo',
                    store: me.paymentStore,
                    displayField: "description",
                    valueField: 'id',
                    emptyText: '{s name=paymentFieldSet/comboBox/emptyText}Please select{/s}',
                    tpl: Ext.create('Ext.XTemplate',
                        '<tpl for=".">',
                            '<div class="x-boundlist-item"',
                                '<tpl if="this.doHighlight(id)">',
                                    ' style="background-color:#f08080;"',
                                '</tpl>',
                            '>',
                            '<div class="',
                                '<tpl if="active">',
                                    'sprite-tick-small',
                                '<tpl else>',
                                    'sprite-cross-small',
                                '</tpl>',
                                '" style="width:12px;height:12px;display:inline-block;margin:0 6px 5px 0;vertical-align:middle;">&nbsp;</div>',
                                '{literal}{description}{/literal}',
                            '</div>',
                        '</tpl>',
                        {
                            doHighlight: function(id) {
                                var record = me.paymentStore.findRecord('id', id);
                                return record.getRuleSets().count() > 0;
                            }
                        }
                    ),

                    listeners: {
                        change: function(comboBox, newValue){
                            me.fireEvent('onChangePayment', me, newValue);
                        }
                    }
                }
            ]
        });
        me.riskFieldSet = Ext.create('Ext.form.FieldSet',{
            hidden: true,
            title: '{s name=riskFieldSet/title}Disable payment if{/s}'
        });

        me.exampleFieldSet = Ext.create('Ext.form.FieldSet',{
            hidden: true,
            cls: Ext.baseCSSPrefix + 'example-table',
            title: '{s name=exampleFieldSet/title}Examples{/s}',
            items: [{
                xtype: 'container',
                html: me.createHtml(data)
            }]
        });

        fieldSets.push(me.paymentFieldSet);
        fieldSets.push(me.riskFieldSet);
        fieldSets.push(me.exampleFieldSet);

        return fieldSets;
    },

    /**
     * This function creates the dynamic HTML-code
     * @param datas - Contains the array with all values for the example-fieldSet
     * @return String
     */
    createHtml: function(datas){
        var html = "<table>";
        Ext.each(datas, function(data){
            html = html + "<tr>";
            Ext.each(data, function(item){
                html = html + "<td>" + item + "</td>"
            });
            html = html + "</tr>";
        });
        html = html + "</table>";

        return html;
    },

    /**
     * This function is used to create some arrays, which contain all data for the example-fieldSet
     * @return Array
     */
    createData: function(){
        var rules = [],
            syntax = [],
            example = [],
            data = [];

        rules.push('<b> {s name=exampleFieldSet/rules/rule}Rule{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/orderValueGt}Order value >={/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/orderValueLt}Order value <={/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/customerGroupIs}Customer group IS{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/customerGroupIsNot}Customer group IS NOT{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/newCustomer}Customer IS NEW{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/zoneIs}Zone IS{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/zoneIsNot}Zone IS NOT{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/billingZoneIs}Billing Zone IS{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/billingZoneIsNot}Billing Zone IS NOT{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/countryIs}Country IS{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/countryIsNot}Country IS NOT{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/billingCountryIs}Billing Country IS{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/billingCountryIsNot}Billing Country IS NOT{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/orderPositionsGt}Order positions >={/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/dunninglevelone}Dunning level one IS TRUE{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/dunningleveltwo}Dunning level two IS TRUE{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/dunninglevelthree}Dunning level three IS TRUE{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/encashment}Encashment IS TRUE{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/lastOrderLess}No order before at least X days{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/ordersLess}Quantity orders <={/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/articleFromCategory}Article from category{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/zipCodeIs}Zip code IS{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/streetNameContains}Street name CONTAINS X{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/billingZipCodeIs}Billing Zip code IS{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/billingStreetNameContains}Billing Street name CONTAINS X{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/customerNumberIs}Customer number IS{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/lastNameContains}Last name CONTAINS X{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/subShopIs}Shop IS{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/subShopIsNot}Shop IS NOT{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/shippingAddressDifferBillingAddress}Shipping-Address != Billing-Address{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/currencyIsoIs}Currency Iso IS{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/currencyIsoIsNot}Currency Iso IS NOT{/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/articleAttributeIs}Article attribute IS (1>5){/s} </b>');
        rules.push('<b> {s name=exampleFieldSet/rules/articleAttributeIsNot}Article attribute IS NOT (1>5){/s} </b>');


        syntax.push('<b> {s name=exampleFieldSet/syntax/syntax}Syntax{/s} </b>');
        syntax.push('{s name=exampleFieldSet/syntax/numericalValue}Value numerical{/s}');
        syntax.push('{s name=exampleFieldSet/syntax/numericalValue}Value numerical{/s}');
        syntax.push('{s name=exampleFieldSet/syntax/customerGroupId}ID of the customer group{/s}');
        syntax.push('{s name=exampleFieldSet/syntax/customerGroupId}ID of the customer group{/s}');
        syntax.push('{s name=exampleFieldSet/syntax/oneOrZero}1 or 0{/s}');
        syntax.push('{s name=exampleFieldSet/syntax/zone}germany, europe, world{/s}');
        syntax.push('{s name=exampleFieldSet/syntax/zone}germany, europe, world{/s}');
        syntax.push('{s name=exampleFieldSet/syntax/zone}germany, europe, world{/s}');
        syntax.push('{s name=exampleFieldSet/syntax/zone}germany, europe, world{/s}');
        syntax.push('{s name=exampleFieldSet/syntax/countryIso}Country-Iso{/s}');
        syntax.push('{s name=exampleFieldSet/syntax/countryIso}Country-Iso{/s}');
        syntax.push('{s name=exampleFieldSet/syntax/countryIso}Country-Iso{/s}');
        syntax.push('{s name=exampleFieldSet/syntax/countryIso}Country-Iso{/s}');
        syntax.push('{s name=exampleFieldSet/syntax/numericalValue}Value numerical{/s}');
        syntax.push('');
        syntax.push('');
        syntax.push('');
        syntax.push('');
        syntax.push('{s name=exampleFieldSet/syntax/numericalValue}Value numerical{/s}');
        syntax.push('{s name=exampleFieldSet/syntax/numericalValue}Value numerical{/s}');
        syntax.push('{s name=exampleFieldSet/syntax/categoryId}ID of the category{/s}');
        syntax.push('{s name=exampleFieldSet/syntax/numericalValue}Value numerical{/s}');
        syntax.push('{s name=exampleFieldSet/syntax/numericalValue}Value numerical{/s}');
        syntax.push('{s name=exampleFieldSet/syntax/streetName}Street name or a part of the name{/s}');
        syntax.push('{s name=exampleFieldSet/syntax/streetName}Street name or a part of the name{/s}');
        syntax.push('{s name=exampleFieldSet/syntax/numericalValue}Value numerical{/s}');
        syntax.push('{s name=exampleFieldSet/syntax/lastName}Last name{/s}');
        syntax.push('{s name=exampleFieldSet/syntax/subShopId}Name of the shop{/s}');
        syntax.push('{s name=exampleFieldSet/syntax/subShopId}Name of the shop{/s}');
        syntax.push('{s name=exampleFieldSet/syntax/shippingAddressDifferBillingAddress}Shipping-Address != Billing-Address{/s}');
        syntax.push('{s name=exampleFieldSet/syntax/currencyIso}Currency-Iso{/s}');
        syntax.push('{s name=exampleFieldSet/syntax/currencyIso}Currency-Iso{/s}');
        syntax.push('{s name=exampleFieldSet/syntax/attributes}attr*1-20*|5{/s}');
        syntax.push('{s name=exampleFieldSet/syntax/attributes}attr*1-20*|5{/s}');


        example.push('<b> {s name=exampleFieldSet/example/example}Example{/s} </b>');
        example.push('500.50');
        example.push('500.50');
        example.push('{s name=exampleFieldSet/example/customerGroupId}EK for customers, H for traders{/s}');
        example.push('{s name=exampleFieldSet/example/customerGroupId}EK for customers, H for traders{/s}');
        example.push('1');
        example.push('{s name=exampleFieldSet/example/zone}germany, europe, world{/s}');
        example.push('{s name=exampleFieldSet/example/zone}germany, europe, world{/s}');
        example.push('{s name=exampleFieldSet/example/zone}germany, europe, world{/s}');
        example.push('{s name=exampleFieldSet/example/zone}germany, europe, world{/s}');
        example.push('{s name=exampleFieldSet/example/countryIso}e.g. DE for germany{/s}');
        example.push('{s name=exampleFieldSet/example/countryIso}e.g. DE for germany{/s}');
        example.push('{s name=exampleFieldSet/example/countryIso}e.g. DE for germany{/s}');
        example.push('{s name=exampleFieldSet/example/countryIso}e.g. DE for germany{/s}');
        example.push('5');
        example.push('');
        example.push('');
        example.push('');
        example.push('');
        example.push('30');
        example.push('30');
        example.push('3');
        example.push('48624');
        example.push('48624');
        example.push('{s name=exampleFieldSet/example/streetName}e.g. \'delivery\', disables the payment for every address, which contains \'delivery\'{/s}');
        example.push('{s name=exampleFieldSet/example/streetName}e.g. \'delivery\', disables the payment for every address, which contains \'delivery\'{/s}');
        example.push('12345');
        example.push('{s name=exampleFieldSet/example/lastName}Smith{/s}');
        example.push("{s name=exampleFieldSet/example/subShopId}'English' if there was defined such a shop in the basic settings{/s}");
        example.push("{s name=exampleFieldSet/example/subShopId}'English' if there was defined such a shop in the basic settings{/s}");
        example.push('{s name=exampleFieldSet/example/shippingAddressDifferBillingAddress}Shipping-Address != Billing-Address{/s}');
        example.push('{s name=exampleFieldSet/example/currencyIso}EUR{/s}');
        example.push('{s name=exampleFieldSet/example/currencyIso}USD{/s}');
        example.push('{s name=exampleFieldSet/example/attribute}attr5|2 Disable payment if there are any articles in the basket with attr5 = 2{/s}');
        example.push('{s name=exampleFieldSet/example/attribute}attr5|2 Do not disable payment if there are any articles in the basket with attr5 = 2{/s}');

        //Creates an array like this:
        //[0] => One item of each array rules/syntax/example
        for(var i=0; i<example.length; i++){
            data[i] = [rules[i], syntax[i], example[i]]
        }
        return data;
    }
});
//{/block}
