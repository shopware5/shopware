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
 * @package    Analytics
 * @subpackage Main
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * todo@all: Documentation
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/controller/main"}
Ext.define('Shopware.apps.Analytics.controller.Main', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend:'Enlight.app.Controller',

    /**
     * References to specific elements in the module
     * @array
     */
    refs:[
        { ref:'panel', selector:'analytics-panel' },
        { ref:'layoutButton', selector:'analytics-toolbar button[action=layout]' },
        { ref:'fromField', selector:'analytics-toolbar datefield[name=from_date]' },
        { ref:'toField', selector:'analytics-toolbar datefield[name=to_date]' }
    ],


    /**
     * Contains the currently displayed mode
     * @default null
     * @string
     */
    selectedType: null,

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the subapplication
     *
     * @return void
     */
    init:function () {
        var me = this;

        me.shopStore = me.subApplication.getStore('Shop').load({
            callback:function () {
                me.dataStore = Ext.widget('analytics-store-data', { shopStore:this });
            }
        });
        me.navigationStore = me.subApplication.getStore('Navigation');
        me.mainWindow = me.getView('main.Window').create({
            shopStore:me.shopStore,
            navigationStore:me.navigationStore
        }).show();

        me.control({
            'analytics-navigation':{

                /**
                 * Select an item in navigation
                 * @param tree
                 * @param record
                 */
                select:function (tree, record) {
                    // Cache the selected data type
                    if(record.data.id) {
                        me.selectedType = record.data.id;
                    }

                    // If a custom store is defined ...
                    if (record.data.store) {
                        // Create a custom store, defined in navigation store
                        var store = Ext.widget(record.data.store, { shopStore:me.shopStore });
                        me.customStore = store;
                        me.customStoreEnabled = true; // Enable flag to refresh correct store in shop-select event listeners
                    } else {
                        // Use default store
                        var store = me.dataStore;
                        me.customStoreEnabled = false;
                        store.removeAll(true);
                    }

                    me.renderDataOutput(store, record);
                }
            },
            'analytics-toolbar button[action=layout]':{
                change:function (button, item) {
                    me.getPanel().getLayout().setActiveItem(item.layout == 'table' ? 0 : 1);
                }
            },
            'analytics-toolbar datefield':{
                change:me.onChangeDate
            },
            'analytics-toolbar combobox':{
                change:me.onChangeShop,
                blur:me.onBlurShop
            }
        });


        var overview = me.navigationStore.getNodeById('overview'),
            store = Ext.widget(overview.data.store, { shopStore: me.shopStore });

        me.selectedType = overview.data.id;
        me.customStore = store;
        me.customStoreEnabled = true;

        me.renderDataOutput(store, overview);
    },
    /**
     * Load chart and table for a certain statistic
     * @param store
     * @param panel
     * @param record
     * @param layout
     */
    renderDataOutput:function (store, record) {
        var me = this,
            chartId = 'widget.analytics-chart-' + record.data.id,
            tableId = 'widget.analytics-table-' + record.data.id,
            panel = me.getPanel(),
            layout = true,
            fromValue = me.getFromField().value,
            toValue = me.getToField().value;

        // Remove all previous inserted charts / tables
        Ext.suspendLayouts();
        panel.removeAll(true);
        panel.setLoading(true);

        Ext.apply(store.getProxy().extraParams, {
            node: 'root'
        });
        if (Ext.typeOf(fromValue) == 'date') {
            store.getProxy().extraParams.fromDate = fromValue;
        }
        if (Ext.typeOf(toValue) == 'date') {
            store.getProxy().extraParams.toDate = toValue;
        }

        store.load({
            callback:function () {
                if (Ext.ClassManager.getNameByAlias(chartId)) {
                    var chart = Ext.create(chartId, {
                        store:store,
                        shopStore:me.shopStore
                    });
                    panel.add(chart);
                } else {
                    layout = false;
                }

                if (Ext.ClassManager.getNameByAlias(tableId)) {
                    var table = Ext.create(tableId, {
                        store:store,
                        shopStore:me.shopStore
                    });
                    panel.add(table);
                } else {
                    layout = false;
                }

                var activeItem;
                if (!layout) {
                    me.getLayoutButton().hide();
                    activeItem = 0;
                } else {
                    me.getLayoutButton().show();
                    activeItem = me.getLayoutButton().getActiveItem();

                    activeItem = activeItem.layout == 'table' ? 0 : 1;
                }

                panel.getLayout().setActiveItem(activeItem);

                panel.setLoading(false);
                Ext.resumeLayouts(true);
            }
        });
    },

    /**
     * Event listener method which is fired when the user change
     * the to date field to filter the order chart data.
     * The to date field is placed on top of the chart.
     *
     * @param [Ext.form.Field.Date] - The date field which changed
     * @param [Ext.Date] - The new value
     * @return void
     */
    onChangeDate:function (field, value) {
        var me = this;
        if (Ext.typeOf(value) != 'date') {
            return;
        }

        // Support custom stores
        var store = (me.customStoreEnabled) ? me.customStore : me.dataStore;

        // If we're having a store, return here
        if(!store) {
            return false;
        }

        // Special directive for month charts
        if(me.selectedType === 'month') {
                var from = me.getFromField().getValue(),
                to = me.getToField().getValue();

            if(to.getFullYear() == from.getFullYear() && (to.getMonth() - from.getMonth()) <= 0) {
                Ext.Msg.alert('{s name=alert/time_range_too_short_title}Time range too short{/s}', '{s name=alert/time_range_too_short}Your selected time range is too short.{/s}');
                return false;
            }
        }

        store.getProxy().extraParams[(field.name == 'from_date' ? 'fromDate' : 'toDate')] = value;
        store.load();
    },

    /**
     * Event listener which is be fired when the user changed the shop combobox
     *
     * @param field
     * @param value
     */
    onChangeShop:function (field, value) {
        var me = this,
            store = (me.customStoreEnabled) ? me.customStore : me.dataStore;

        store.getProxy().extraParams.selectedShops = value.toString();
    },

    /**
     * reloads the store after the shop combobox loses focus
     */
    onBlurShop:function () {
        var me = this,
            store = (me.customStoreEnabled) ? me.customStore : me.dataStore,
            gridPanel = me.getPanel().getLayout().getActiveItem();

        if(!gridPanel){
            return;
        }

        var columns = gridPanel.getColumns(),
            selectedShopIds = store.getProxy().extraParams.selectedShops;

        if(me.getPanel().shopColumnName) {
            //the table uses multiple shop columns so add the selected ones
            if(!Ext.isEmpty(selectedShopIds)) {
                selectedShopIds = selectedShopIds.split(",");
                me.shopStore.each(function (shop) {
                    if(Ext.Array.indexOf(selectedShopIds, shop.get('id')) != -1) {
                        columns[columns.length] = {
                            xtype: 'gridcolumn',
                            dataIndex: 'amount' + shop.data.id,
                            text: Ext.String.format(gridPanel.shopColumnName, shop.data.name)
                        };
                    }
                });
            } else {
                //the user didn't select any shop so add all shop columns
                me.shopStore.each(function (shop) {
                    if(Ext.Array.indexOf(selectedShopIds, shop.get('id')) != -1) {
                        columns[columns.length] = {
                            xtype: 'gridcolumn',
                            dataIndex: 'amount' + shop.data.id,
                            text: Ext.String.format(gridPanel.shopColumnName, shop.data.name)
                        };
                    }
                });
            }
            gridPanel.reconfigure(null, columns);
        }
        store.load();
    }
});
//{/block}