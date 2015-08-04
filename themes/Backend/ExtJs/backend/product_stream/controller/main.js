
Ext.define('Shopware.apps.ProductStream.controller.Main', {
    extend: 'Enlight.app.Controller',

    refs: [
        { ref: 'conditionPanel', selector: 'product-stream-condition-panel' },
        { ref: 'previewGrid', selector: 'product-stream-preview-grid' },
        { ref: 'settingsPanel', selector: 'product-stream-settings' },
        { ref: 'shopCombo', selector: 'product-stream-preview-grid combo[name=shop]' },
        { ref: 'currencyCombo', selector: 'product-stream-preview-grid combo[name=currency]' },
        { ref: 'customerGroupCombo', selector: 'product-stream-preview-grid combo[name=customerGroup]' },
    ],

    init: function() {
        var me = this;

        me.control({
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
                Shopware.Notification.createGrowlMessage('Product stream', 'Stream saved');
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
