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
        { ref: 'productStreamDetailGrid', selector: 'product-stream-selected-list-grid' }
    ],

    init: function() {
        var me = this;

        me.control({
            'product-stream-selected-list-window': {
                'save-selection-stream': me.saveSelectionStream
            },
            'product-stream-condition-panel': {
                'load-preview': me.loadPreview
            },
            'product-stream-preview-grid': {
                'load-preview': me.loadPreview
            },
            'product-stream-detail-window': {
                'save-condition-stream': me.saveConditionStream
            },
            'product-stream-listing-grid': {
                'open-selected-list-window': me.openSelectedListWindow,
                'stream-delete-item': me.onDeleteItem
            }
        });

        me.mainWindow = me.getView('list.Window').create({ }).show();
    },

    onDeleteItem: function(grid, record) {
        var message = Ext.String.format('{s name=dialog_delete_stream_message}Do you really want to delete "[0]"?{/s}', record.get('name'));
        Ext.MessageBox.confirm('{s name=dialog_delete_stream_title}Delete Prdoduct Stream{/s}', message, function (response) {
            if (response !== 'yes') {
                return false;
            }

            record.destroy({
                callback: function() {
                    grid.getStore().load();
                }
            });

        });

        return false;
    },

    saveConditionStream: function(record) {
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

        me.saveConditionStreamRecord(record);
    },

    saveConditionStreamRecord: function(record) {
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

    saveSelectionStream: function(record) {
        var me = this;

        var settingsPanel = me.getSettingsPanel();

        if (!settingsPanel.getForm().isValid()) {
            return;
        }

        settingsPanel.getForm().updateRecord(record);
        record.set('sorting', me.getSorting());
        record.set('conditions', null);
        this.saveSelectionStreamRecord(record);
    },

    saveSelectionStreamRecord: function(record) {
        var me = this;
        record.save({
            callback: function() {
                var productGrid = me.getProductStreamGrid(),
                        listStore = productGrid.store,
                        detailGrid = me.getProductStreamDetailGrid();

                detailGrid.streamId = record.get('id');

                listStore.reload({
                    callback: function() {
                        productGrid.reconfigure(listStore);
                    }
                });
                Shopware.Notification.createGrowlMessage(
                        '{s name=stream_saved_title}Product stream{/s}',
                        '{s name=stream_saved_description}Stream saved{/s}'
                );
            }
        });
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

    openSelectedListWindow: function(record) {
        var me = this;
        me.getView('selected_list.Window').create({ record: record }).show();
    },

    loadPreview: function(conditions) {
        var me = this;

        var conditionPanel = me.getConditionPanel();
        var previewGrid = me.getPreviewGrid();
        var shopCombo = me.getShopCombo();
        var currencyCombo = me.getCurrencyCombo();
        var customerGroupCombo = me.getCustomerGroupCombo();

        if (!conditions || Object.getOwnPropertyNames(conditions).length === 0) {
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
            if (item.get('value') == sort) {
                sortModel = item;
                return false;
            }
        });

        var sortData = {};
        if (!sortModel) {
            return null;
        }

        sortData[sortModel.get('key')] = {
            direction: sortModel.get('direction')
        };

        return sortData;
    }
});
