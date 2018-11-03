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
 */

/**
 * Analytics Main Controller
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/controller/main"}
Ext.define('Shopware.apps.Analytics.controller.Main', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Enlight.app.Controller',

    /**
     * References to specific elements in the module
     * @array
     */
    refs: [
        { ref: 'navigation', selector: 'analytics-navigation' },
        { ref: 'panel', selector: 'analytics-panel' },
        { ref: 'layoutButton', selector: 'analytics-toolbar button[action=layout]' },
        { ref: 'shopSelection', selector: 'analytics-toolbar combobox[name=shop_selection]' },
        { ref: 'fromField', selector: 'analytics-toolbar datefield[name=from_date]' },
        { ref: 'toField', selector: 'analytics-toolbar datefield[name=to_date]' }
    ],

    /**
     * Contains the currently displayed mode
     * @default null
     * @string
     */
    selectedType: null,

    /**
     * The current showed statistics store
     * @default null
     * @Ext.data.Store
     */
    currentStore: null,

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the subapplication
     *
     * @return void
     */
    init: function () {
        var me = this;

        // Load the shop store
        me.shopStore = me.subApplication.getStore('Shop').load({
            callback: function () {
                me.dataStore = Ext.widget('analytics-store-data', { shopStore: this });
            }
        });
        me.navigationStore = me.subApplication.getStore('Navigation');
        me.mainWindow = me.getView('main.Window').create({
            shopStore: me.shopStore,
            navigationStore: me.navigationStore
        }).show();

        me.control({
            'analytics-navigation': {

                /**
                 * Select an item in navigation
                 * @param tree
                 * @param record
                 */
                select: function (tree, record) {
                    // Cache the selected data type
                    if (record.data.id) {
                        me.selectedType = record.data.id;
                    }

                    var store = me.dataStore;
                    // If a custom store is defined ...
                    if (record.data.store) {
                        // Create a custom store, defined in navigation store
                        store = Ext.widget(record.data.store, { shopStore: me.shopStore });
                        me.customStore = store;
                        me.customStoreEnabled = true; // Enable flag to refresh correct store in shop-select event listeners
                    } else {
                        // Use default store
                        me.customStoreEnabled = false;
                        store.removeAll(true);
                    }

                    me.renderDataOutput(store, record);
                }
            },
            'analytics-toolbar': {
                /**
                 * Called when the export button in the toolbar was clicked
                 */
                exportCSV: me.onExport,

                /**
                 * Called when the refresh button in the toolbar was clicked
                 */
                refreshView: me.onRefreshView
            },
            'analytics-toolbar button[action=layout]': {
                change: function (button, item) {
                    me.getPanel().getLayout().setActiveItem(item.layout == 'table' ? 0 : 1);
                }
            }
        });


        me.getNavigation().getSelectionModel().select(
            me.navigationStore.getNodeById('overview')
        );
    },

    /**
     * Will be called when the user clicks on the export button in the toolbar.
     * Build export url together with date and shop parameter.
     * Creates a new form and sets its url to the build one.
     * Submits the form which leads to a download of a csv file.
     */
    onExport: function () {
        var me = this,
            fromField = me.getFromField(),
            toField = me.getToField();

        var url = me.currentStore.getProxy().url;
        url += '?format=csv';
        url += '&fromDate=' + Ext.Date.format(fromField.getValue(), 'Y-m-d');
        url += '&toDate=' + Ext.Date.format(toField.getValue(), 'Y-m-d');
        url += '&type=' + me.selectedType;

        if (me.getShopSelection() && me.getShopSelection().getValue()) {
            url += '&selectedShops=' + me.getShopSelection().getValue().join(',');
        }

        var form = Ext.create('Ext.form.Panel', {
            standardSubmit: true,
            target: 'iframe'
        });

        form.submit({
            method: 'POST',
            url: url
        });
    },

    /**
     * Will be called when the user clicks on the refresh button in the toolbar.
     * Calls the renderDataOutput function when both currentStore and currentNavigationStore are present.
     * The function call leads to a refresh and clean rebuild of the table/chart.
     */
    onRefreshView: function () {
        var me = this;

        if (!me.currentStore || !me.currentNavigationItem) {
            return;
        }

        me.renderDataOutput(me.currentStore, me.currentNavigationItem)
    },

    /**
     * Loads the chart and table for the selected statistic.
     * If one of the components is not present, the layout switch button will be hidden.
     * Shows/hides the shop combobox depending of the multiShop parameter of the statistic.
     *
     * @param store
     * @param record
     */
    renderDataOutput: function (store, record) {
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
        if (me.getShopSelection() && me.getShopSelection().getValue()) {
            store.getProxy().extraParams.selectedShops = me.getShopSelection().getValue().toString();
        }

        me.currentStore = store;
        me.currentNavigationItem = record;

        if (me.getNavigation()) {
            me.getNavigation().setLoading(true);
        }

        store.load({
            callback: function (result, request) {
                if (me.getNavigation()) {
                    me.getNavigation().setLoading(false);
                }
                panel.setLoading(false);

                if (request.success === false) {
                    return;
                }

                if (Ext.ClassManager.getNameByAlias(chartId)) {
                    var chart = Ext.create(chartId, {
                        store: store,
                        shopStore: me.shopStore,
                        shopSelection: me.getShopSelection().value
                    });

                    panel.add(chart);
                } else {
                    layout = false;
                }

                if (Ext.ClassManager.getNameByAlias(tableId)) {
                    var table = Ext.create(tableId, {
                        store: store,
                        shopStore: me.shopStore,
                        shopSelection: me.getShopSelection().value
                    });
                    panel.add(table);
                } else {
                    layout = false;
                }

                if (!!record.raw.multiShop) {
                    me.getShopSelection().show();
                } else {
                    me.getShopSelection().hide();
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

                Ext.resumeLayouts(true);
            }
        });
    }
});
//{/block}
