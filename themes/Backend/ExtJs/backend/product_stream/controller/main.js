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
 * @package    ProductStream
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/product_stream/main}

Ext.define('Shopware.apps.ProductStream.controller.Main', {
    extend: 'Enlight.app.Controller',

    refs: [
        { ref: 'conditionPanel', selector: 'product-stream-condition-panel' },
        { ref: 'previewGrid', selector: 'product-stream-preview-grid' },
        { ref: 'settingsPanel', selector: 'product-stream-settings' },
        { ref: 'shopCombo', selector: 'product-stream-preview-grid combo[name=shop]' },
        { ref: 'currencyCombo', selector: 'product-stream-preview-grid combo[name=currency]' },
        { ref: 'customerGroupCombo', selector: 'product-stream-preview-grid combo[name=customerGroup]' },
        { ref: 'productStreamGrid', selector: 'product-stream-listing-grid' },
    ],

    init: function() {
        var me = this;

        me.control({
            'product-stream-defined-list-window': {
                'save-defined-list': me.saveDefinedList
            },
            'product-stream-condition-panel': {
                'load-preview': me.loadPreview
            },
            'product-stream-detail-window': {
                'save-filtered-stream': me.saveFilteredStream
            },
            'product-stream-listing-grid': {
                'open-defined-list-window': me.openDefinedListWindow
            }
        });

        me.mainWindow = me.getView('list.Window').create({ }).show();
    },

    saveDefinedList: function(record) {
        var me = this;

        var settingsPanel = me.getSettingsPanel();

        if (!settingsPanel.getForm().isValid()) {
            return;
        }

        settingsPanel.getForm().updateRecord(record);
        record.set('sorting', me.getSorting());
        record.set('conditions', null);
        this.saveRecord(record);
    },

    saveFilteredStream: function(record) {
        var me = this;
        var conditionPanel = me.getConditionPanel();
        var settingsPanel = me.getSettingsPanel();

        var valid = (
            conditionPanel.getForm().isValid() == true
            && settingsPanel.getForm().isValid() == true
        );

        if (!valid) {
            return;
        }

        settingsPanel.getForm().updateRecord(record);
        record.set('sorting', me.getSorting());
        record.set('conditions', me.getConditions());

        me.saveRecord(record);
    },

    getConditions: function() {
        var me = this;
        var conditionPanel = me.getConditionPanel();
        var values = conditionPanel.getValues();
        var conditions = { };

        for (var key in values) {
            if (key.indexOf('condition.') == 0) {
                var newKey = key.replace('condition.', '');
                conditions[newKey] = values[key];
            }
        }
        return conditions;
    },

    saveRecord: function(record) {
        var me = this;
        record.save({
            callback: function() {
                var productGrid = me.getProductStreamGrid(),
                    store = productGrid.store;

                store.reload({
                    callback: function() {
                        productGrid.reconfigure(store);
                    }
                });
                Shopware.Notification.createGrowlMessage(
                    '{s name=stream_saved_title}Product stream{/s}',
                    '{s name=stream_saved_description}Stream saved{/s}'
                );
            }
        });
    },


    openDefinedListWindow: function(record) {
        var me = this;
        me.getView('defined_list.Window').create({ record: record }).show();
    },

    loadPreview: function(conditions) {
        var me = this;

        var conditionPanel = me.getConditionPanel();
        var previewGrid = me.getPreviewGrid();
        var shopCombo = me.getShopCombo();
        var currencyCombo = me.getCurrencyCombo();
        var customerGroupCombo = me.getCustomerGroupCombo();

        if (!conditions) {
            if (!conditionPanel.validateConditions()) {
                return;
            }
            conditions = me.getConditions();
        }

        var sort = me.getSorting();

        previewGrid.getStore().getProxy().extraParams = {
            sort: Ext.JSON.encode(sort),
            conditions: Ext.JSON.encode(conditions),
            shopId: shopCombo.getValue(),
            currencyId: currencyCombo.getValue(),
            customerGroupKey: customerGroupCombo.getValue()
        };

        previewGrid.getStore().load();
    },

    getSorting: function() {
        var settingsPanel = this.getSettingsPanel();

        var sort = settingsPanel.sortingCombo.getValue();
        var store = settingsPanel.sortingCombo.getStore();
        var sortModel = null;

        store.each(function(item) {
            if (item.get('key') == sort) {
                sortModel = item;
                return false;
            }
        });

        var sortData = {};
        if (!sortModel) {
            return null;
        }

        sortData[sort] = {
            direction: sortModel.get('direction')
        };

        return sortData;
    }
});
