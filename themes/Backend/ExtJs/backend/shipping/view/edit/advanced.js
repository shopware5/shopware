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
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

/*{namespace name=backend/shipping/view/edit/advanced}*/

/**
 * Shopware UI - Shipping Costs
 *
 * todo@all: Documentation
 */
//{block name="backend/shipping/view/edit/advanced"}
Ext.define('Shopware.apps.Shipping.view.edit.Advanced', {
    /**
     * Based on Ext.form.Panel
     * @string
     */
    extend : 'Ext.form.Panel',
    /**
     * Alias for easy creation
     * @string
     */
    alias : 'widget.shipping-view-edit-advanced',
    /**
     * Title as shown in the tab from the panel
     * @string
     */
    title : '{s name=country_selection_tab_title}Lock categories{/s}',
    /**
     * Display the the contents of this tab immediately
     * @boolean
     */
    autoShow : true,
    /**
     * enable auto scroll
     * @boolean
     */
    autoScroll: true,

    /**
     * use layout column
     *
     * @string
     */
    layout: 'column',
    /**
     * How much padding the whole window should use
     *
     * @integer
     */
    bodyPadding: 10,

    /**
     * Default formatting for the form panels.
     * @object
     */
    formDefaults: {
        labelWidth  : 155,
        minWidth : 250,
        xtype : 'container',
        layout: 'hbox',
        columnWidth : 0.4,
        anchor : '100%'
    },

    cls: 'shopware-form',

    /**
     * Initialize the controller and defines the necessary default configuration
     */
    initComponent : function() {
        var me = this;
        me.items = [
            me.createLeftSide(),
            me.createRightSide()
        ];
        me.callParent(arguments);
    },

    createLeftSide : function() {
        var me = this;
        return {
            xtype : 'container',
            columnWidth : 0.5,
            layout : 'anchor',
            items : me.getFormElementsLeft(),
            defaults: me.formDefaults,
            margin : '0 10 0 0'
        };
    },

    createRightSide : function() {
        var me = this;
        return {
            xtype: 'container',
            columnWidth: 0.5,
            layout: 'anchor',
            items : me.getFormElementsRight(),
            defaults: me.formDefaults
        };
    },

    getFormElementsLeft : function() {
        var me = this;
        return [
            {
                xtype       : 'checkbox',
                name        : 'bindLastStock',
                fieldLabel  : '{s name=bind_laststock_label}Sale products only{/s}',
                inputValue  : 1,
                uncheckedValue : 0
            }, {
                xtype       : 'combobox',
                name        : 'bindShippingFree',
                fieldLabel  : '{s name=bind_shippingfree_label}Support articles free of shipping costs{/s}',
                store : Ext.create('Ext.data.Store',{
                    fields: ['id', 'name'],
                    data : [{ id:0 , name: '{s name=bind_shippingfree_data_support}Support{/s}' },
                            { id:1 , name: '{s name=bind_shippingfree_data_not_support_lock}do not support and lock shipping type{/s}' },
                            { id:2 , name: '{s name=bind_shippingfree_data_support_calc_costs}Support but add shipping costs nevertheless.{/s}' }]
                }),
                valueField:'id',
                displayField:'name',
                mode: 'local',
                selectOnFocus:true,
                allowBlank: true,
                typeAhead: false,
                triggerAction: 'all',
                forceSelection : true,
                value: 0,
                width: 200
            }, {
                xtype       : 'combobox',
                name        : 'bindInStock',
                fieldLabel  : '{s name=bind_instock_label}Stock larger than{/s}',
                store : Ext.create('Ext.data.Store',{
                    fields: ['id', 'name'],
                    data : [
                            { id: 0, name: '{s name=bind_instock_data_no_selection}No selection{/s}' },
                            { id: 1, name: '{s name=bind_instock_data_order_quantity}Order quantity{/s}' },
                            { id: 2, name: '{s name=bind_instock_data_order_quantity_minimum}Order quantity + minimum stock{/s}' }]
                }),
                valueField:'id',
                displayField:'name',
                mode: 'local',
                emptyText:'{s name=bind_instock_data_no_selection}No selection{/s}',
                value: 0,
                selectOnFocus:true,
                allowBlank: true,
                typeAhead: false,
                width: 200
            }, {
                    items : me.getBindTime()
            },{
                    items : me.getBindWeight()
            },{
                    items : me.getBindPrice()
            }, {
                    items : me.getBindWeekday()
            }
        ];
    },
    getFormElementsRight : function() {
        var me = this,
           preselectedStore = me.record.getHolidays(),
            ids = [];

        if (preselectedStore.getCount() > 0) {
            preselectedStore.each(function(element) {
                ids.push(element.get('id'));
            });
        }

        return [
            me.getBoxSelect(ids),
            {
                xtype       : 'ace-editor',
                name        : 'bindSql',
                hidden: true,
                /*{if {acl_is_allowed privilege=sql_rule}}*/
                    hidden: false,
                /*{/if}*/
                fieldLabel  : '{s name=bind_sql_label}Own terms{/s}',
                mode: 'sql',
                height: 80,
            }, {
                xtype       : 'ace-editor',
                name        : 'calculationSql',
                hidden: true,
                /*{if {acl_is_allowed privilege=sql_rule}}*/
                    hidden: false,
                /*{/if}*/
                fieldLabel  : '{s name=bind_calculation_sql_label}Own calculations{/s}',
                mode: 'sql',
                height: 80,
            }
        ];
    },

    getBindTime : function() {
        return Ext.create('Ext.container.Container', {
            flex:1,
            layout:{
                type:'hbox',
                align:'stretch'
            },
            height : 28,
            items:[
                {
                    xtype:'timefield',
                    name:'bindTimeFrom',
                    submitFormat:'H:i',
                    fieldLabel:'{s name=bind_time_from_label}Time{/s}',
                    labelStyle:'font-weight: 700; text-align: left;',
                    labelWidth:155,
                    minWidth:80,
                    flex:2,
                    style:'margin-right: 5px'
                },
                {
                    xtype:'timefield',
                    name:'bindTimeTo',
                    submitFormat:'H:i',
                    fieldLabel:'{s name=bind_time_to_label}till{/s}',
                    labelWidth:20,
                    labelPad:0,
                    labelSeparator:'',
                    flex:1,
                    minWidth:80
                }
            ]
        });
    },

    getBindWeight : function() {
        return Ext.create('Ext.container.Container', {
            flex:1,
            layout:{
                type:'hbox',
                align:'stretch'
            },
            height : 28,
            items:[{
                xtype:'numberfield',
                name        : 'bindWeightFrom',
                fieldLabel  : '{s name=bind_weight_from_label}Weight from{/s}',
                labelStyle:'font-weight: 700; text-align: left;',
                decimalPrecision: 3,
                submitLocaleSeparator: false,
                allowDecimal: true,
                labelWidth  : 155,
                minWidth:80,
                flex:2,
                style:'margin-right: 5px'
            }, {
                xtype:'numberfield',
                name:'bindWeightTo',
                fieldLabel  : '{s name=bind_weight_to_label}to{/s}',
                labelStyle : 'font-weight: 700; text-align: left;',
                decimalPrecision: 3,
                labelWidth:20,
                labelPad:0,
                labelSeparator:'',
                flex:1,
                minWidth:80
            }]
        });
    },

    getBindPrice : function() {
        return Ext.create('Ext.container.Container', {
            flex:1,
            layout:{
                type:'hbox',
                align:'stretch'
            },
            height : 28,
            items:[{
                xtype       : 'numberfield',
                decimalPrecision: 2,
                submitLocaleSeparator: false,
                allowDecimals: true,
                minValue: 0,
                name: 'bindPriceFrom',
                fieldLabel: '{s name=bind_price_from_label}Price from{/s}',
                labelStyle: 'font-weight: 700; text-align: left;',
                labelWidth: 155,
                minWidth: 80,
                flex:2,
                style: 'margin-right: 5px'
            }, {
                xtype: 'numberfield',
                minValue: 0,
                name: 'bindPriceTo',
                decimalPrecision: 2,
                submitLocaleSeparator: false,
                fieldLabel: '{s name=bind_price_to_label}Price to{/s}',
                labelWidth: 20,
                labelPad: 0,
                labelSeparator: ' ',
                flex:1,
                labelStyle: 'font-weight: 700; text-align: left;margin-left: 0px; margin-right: 0;',
                minWidth: 80
            }]
        });
    },

    getBindWeekday : function() {
        var dayStore = [],
            counter = 0,
            dayNamesParent = Ext.Date.dayNames,
            dayNames = Ext.clone(dayNamesParent),
            sunday = dayNames.shift();

        dayNames.push(sunday);
        //add default value
        dayStore.push([0,'{s name=bind_weekday_from_none_value}no selection{/s}']);
        Ext.each(dayNames, function(name) {
            counter++;
            dayStore.push([counter, name]);
        });
        return Ext.create('Ext.container.Container', {
            flex:1,
            layout:{
                type:'hbox',
                align:'stretch'
            },
            height : 28,
            items:[{
                    xtype       : 'combobox',
                    name        : 'bindWeekdayFrom',
                    fieldLabel  : '{s name=bind_weekday_from_label}Weekdays to{/s}',
                    labelStyle : 'font-weight: 700; text-align: left;',
                    labelWidth  : 155,
                    minWidth       : 80,
                    style: 'margin-right: 5px',
                    store: new Ext.data.ArrayStore({
                            fields: ['id','name'],
                            data : dayStore
                    }),
                    displayField:'name',
                    flex:2,
                    valueField  : 'id'
                },
                {
                    xtype       : 'combobox',
                    name        : 'bindWeekdayTo',
                    fieldLabel  : '{s name=bind_weekday_to_label}to{/s}',
                    labelWidth  : 20,
                    store: new Ext.data.ArrayStore({
                            fields: ['id','name'],
                            data : dayStore
                    }),
                    displayField:'name',
                    valueField  : 'id',
                    labelPad    : 0,
                    labelSeparator: '',
                    flex:1,
                    labelStyle : 'font-weight: 700; text-align: left;margin-left: 0px; margin-right: 0;',
                    minWidth       : 80
                }]
        });
    },

     /**
     * Returns the selection box
     * //todo@js this must be replaced due an bug where this element wont scale to the right size
     * @param ids array of integers
     * @return Ext.ux.form.field.BoxSelect
     */
    getBoxSelect : function(ids) {
        var me = this;

        return {
            xtype: 'boxselect',
            name: 'holidays',
            fieldLabel  : '{s name=bind_holidays_label}Lock holidays{/s}',
            store       : me.availableHolidays,
            queryMode: 'remote',
            displayField: 'name',
            flex :  1,
            growMax : 100,
            pinList: true,
            stacked: false,
            valueField: 'id',
            value : ids
        };
    }
});
//{/block}
