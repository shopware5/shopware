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
 * @package    ProductFeed
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/product_feed/view/feed}
/**
 * Shopware UI - product feed detail main window.
 *
 * Displays all Detail product feed Information
 */
//{block name="backend/product_feed/view/feed/detail"}
Ext.define('Shopware.apps.ProductFeed.view.feed.Detail', {
    extend:'Ext.container.Container',
    alias:'widget.product_feed-feed-detail',
    border: 0,
    bodyPadding: 10,
    layout: 'column',
    autoScroll:true,
    defaults: {
        columnWidth: 0.5
    },
    //Text for the ModusCombobox
    variantExportData:[
        [1, '{s name=detail_general/variant_export_data/no}No{/s}'],
        [2, '{s name=detail_general/variant_export_data/variant}Variants{/s}']
    ],

    /**
     * Initialize the Shopware.apps.ProductFeed.view.feed.detail and defines the necessary
     * default configuration
     */
    initComponent:function () {
        var me = this;

        me.items = [ me.createGeneralFormLeft(), me.createGeneralFormRight() ];

        me.callParent(arguments);
    },
    /**
     * creates all fields for the general form on the left side
     */
    createGeneralFormLeft:function () {
        var me = this;

        return Ext.create('Ext.container.Container', {
            layout: 'anchor',
            style: 'padding: 0 10px 0 0',
            defaults:{
                anchor:'100%',
                labelStyle:'font-weight: 700;',
                xtype:'textfield'
            },
            items:[
                {
                    fieldLabel:'{s name=detail_general/field/title}Title{/s}',
                    name:'name',
                    allowBlank:false,
                    required:true,
                    enableKeyEvents:true
                },
                {
                    fieldLabel:'{s name=detail_general/field/file_name}File name{/s}',
                    name:'fileName',
                    allowBlank:false,
                    required:true,
                    enableKeyEvents:true,
                    validator: function (value) {
                        if (value.indexOf('..') >= 0 || value.indexOf('.php') >= 0) {
                            return '{s name=invalid_filename}{/s}';
                        }

                        return true;
                    }
                },
                {
                    fieldLabel:'{s name=detail_general/field/partner_id}Partner ID{/s}',
                    name:'partnerId',
                    helpText:'{s name=detail_general/field/partner_id/help}The partner ID will be attached to the corresponding link. So when a customer will buy an article the direct connection to the partner is saved. {/s}',
                    enableKeyEvents:true
                },
                {
                    fieldLabel:'{s name=detail_general/field/hash}Hash{/s}',
                    name:'hash',
                    helpText:'{s name=detail_general/field/hash/help}The Hash will be generated automatically. This value is shown in the URL of the generated product feed file. If you change this value the price portal is not able to access the feed.{/s}',
                    enableKeyEvents:true
                },
                {
                    xtype:'checkbox',
                    fieldLabel:'{s name=detail_general/field/active}Active{/s}',
                    inputValue:1,
                    uncheckedValue:0,
                    name:'active'
                },
                {
                    xtype: 'base-element-interval',
                    name: 'interval',
                    helpText:'{s name=detail_general/field/interval/help}When to refresh feed cache. <br>- Only cron: cache is only refreshed by cron<br>- None: new feed is generated with each request{/s}',
                    allowBlank: false,
                    fieldLabel: '{s name=detail_general/field/interval}Caching interval{/s}',
                    store: [
                        [-1, '{s name=detail_general/field/interval/onlyCron}Only cron{/s}'],
                        [0, '{s name=detail_general/field/interval/empty_value}Live{/s}'],
                        [120, '{s name=detail_general/field/interval/2_minutes}2 Minutes (120 Sec.){/s}'],
                        [300, '{s name=detail_general/field/interval/5_minutes}5 Minutes (300 Sec.){/s}'],
                        [600, '{s name=detail_general/field/interval/10_minutes}10 Minutes (600 Sec.){/s}'],
                        [900, '{s name=detail_general/field/interval/15_minutes}15 Minutes (900 Sec.){/s}'],
                        [1800, '{s name=detail_general/field/interval/30_minutes}30 Minutes (1800 Sec.){/s}'],
                        [3600, '{s name=detail_general/field/interval/1_hour}1 Hour (3600 Sec.){/s}'],
                        [7200, '{s name=detail_general/field/interval/2_hours}2 Hours (7200 Sec.){/s}'],
                        [14400, '{s name=detail_general/field/interval/4_hours}4 Hours (14400 Sec.){/s}'],
                        [28800, '{s name=detail_general/field/interval/12_hours}12 Hours (28800 Sec.){/s}'],
                        [86400, '{s name=detail_general/field/interval/1_day}1 Day (86400 Sec.){/s}'],
                        [172800, '{s name=detail_general/field/interval/2_days}2 Days (172800 Sec.){/s}'],
                        [604800, '{s name=detail_general/field/interval/1_week}1 Week (604800 Sec.){/s}']
                    ]
                },
                {
                    name:'lastExport',
                    fieldLabel: '{s name=detail_general/field/lastExport}Last export{/s}',
                    xtype: 'displayfield',
                    renderer: function(value) {
                        if ( value === Ext.undefined ) {
                            return value;
                        }

                        return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, timeFormat);
                    }
                }
            ]
        });
    },

    /**
     * creates all fields for the general form on the right side
     */
    createGeneralFormRight: function () {
        var me = this;

        var currencyStore = Ext.create('Shopware.apps.Base.store.Currency').load();
        return Ext.create('Ext.container.Container', {
            layout: 'anchor',
            style: 'padding: 0 0 0 10px',
            defaults:{
                anchor:'100%',
                labelStyle:'font-weight: 700;',
                xtype:'combobox'
            },
            items:[
                {
                    name:'languageId',
                    fieldLabel:'{s name=detail_general/field/shop}Shop{/s}',
                    store: me.shopStore.load(),
                    valueField: 'id',
                    helpText:'{s name=detail_general/field/language_id/help}The export language{/s}',
                    displayField: 'name',
                    editable:false
                },
                {
                    name:'customerGroupId',
                    fieldLabel:'{s name=detail_general/field/customer_group}Customer group{/s}',
                    store: Ext.create('Shopware.store.CustomerGroup').load(),
                    valueField:'id',
                    helpText:'{s name=detail_general/field/customergroup/help}Defines the customer group the prices are taken out of{/s}',
                    displayField:'name'
                },
                {
                    name:'currencyId',
                    fieldLabel:'{s name=detail_general/field/currency}Currency{/s}',
                    store: currencyStore,
                    helpText:'{s name=detail_general/field/currency/help}The export is based on the selected currency{/s}',
                    valueField: 'id',
                    displayField: 'name'
                },
                {
                    xtype:'combotree',
                    name:'categoryId',
                    valueField: 'id',
                    forceSelection: false,
                    editable: true,
                    displayField: 'name',
                    treeField: 'categoryId',
                    fieldLabel:'{s name=detail_general/field/category}Category{/s}',
                    helpText:'{s name=detail_general/field/category/help}This will execute the export for the selected category only{/s}',
                    store: me.comboTreeCategoryStore,
                    selectedRecord : me.record
                },
                {
                    xtype:'combobox',
                    name:'variantExport',
                    fieldLabel:'{s name=detail_general/field/variantExport}Export variants{/s}',
                    store:new Ext.data.SimpleStore({
                        fields:['id', 'text'], data:this.variantExportData
                    }),
                    valueField:'id',
                    displayField:'text',
                    mode:'local',
                    allowBlank:false,
                    required:true,
                    editable:false
                },
                {
                    name: 'cacheRefreshed',
                    xtype: 'displayfield',
                    fieldLabel: '{s name=detail_general/field/cacheRefresh}Last cache refresh{/s}',
                    renderer: function(value) {
                        if ( value === Ext.undefined ) {
                            return value;
                        }

                        return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, timeFormat);
                    }
                }
            ]
        });
    }
});
//{/block}
