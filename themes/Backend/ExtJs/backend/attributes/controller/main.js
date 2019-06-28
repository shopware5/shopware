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
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name="backend/attributes/main"}

Ext.define('Shopware.apps.Attributes.controller.Main', {
    extend: 'Ext.app.Controller',

    refs: [
        { ref: 'window', selector: 'attributes-window' },
        { ref: 'listing', selector: 'attributes-listing' },
        { ref: 'detail', selector: 'attributes-detail' },
        { ref: 'detailForm', selector: 'attributes-window form' },
        { ref: 'detailFormNameField', selector: 'attributes-detail textfield[name="columnName"]' },
        { ref: 'detailFormTypeField', selector: 'attributes-detail combobox[name="columnType"]' }
    ],

    init: function () {
        var me = this;
        var table = 's_articles_attributes';
        if (me.subApplication.params && me.subApplication.params.table) {
            table = me.subApplication.params.table;
        }

        me.control({
            'attributes-listing': {
                'display-table-columns':             me.loadListing,
                'attributeconfig-selection-changed': me.displayColumn,
                'delete-attribute-column':           me.onDeleteClick,
                'attributeconfig-add-item':          me.displayNewColumn,
                'generate-model':                    me.onGenerateClick
            },
            'attributes-detail': {
                'reset-column': me.onResetClick
            },
            'attributes-window': {
                'save-column': me.onSaveClick
            }
        });

        me.mainWindow = me.getView('Window').create({
            disabled: true,
            table: table
        }).show();

        me.callParent(arguments);

        Ext.Function.defer(function(){
            me.mainWindow.enable();
            me.loadListing(table);
        }, 100);
    },

    deleteColumn: function(record, callback) {
        var me = this,
            window = me.getWindow();

        window.setLoading('{s name="delete_column_loading_mask"}{/s}');
        var table = record.get('tableName');

        record.destroy({
            success: function(data, operation) {
                me.displayResponseMessage(operation.response, '{s name="delete_success"}{/s}');

                me.generateModel(table, function() {
                    callback();
                });
            }
        });
    },

    deleteDependingTableColumns: function(columnName, tables, callback) {
        var me = this;
        callback = callback ? callback : Ext.emptyFn;

        if (tables.length <= 0) {
            callback();
            return;
        }
        var record = Ext.create('Shopware.model.AttributeConfig', {
            columnName: columnName,
            tableName: tables.shift()
        });

        me.deleteColumn(record, function() {
            me.deleteDependingTableColumns(columnName, tables, callback);
        });
    },



    displayResponseMessage: function(response, successMessage) {
        var data = response;

        if (data && data.hasOwnProperty('responseText')) {
            data = Ext.decode(response.responseText);
        }

        if (data && data.success) {
            Shopware.Notification.createGrowlMessage('', successMessage);
        } else if (data && data.message) {
            Shopware.Notification.createStickyGrowlMessage('', data.message);
        }
    },

    isColumnNameValid: function(record, callback) {
        var me = this;

        me.sendAjaxRequest(
            '{url controller=attributes action=columnNameExists}',
            {
                tableName: me.getCurrentTable().get('name'),
                columnName: record.get('columnName')
            },
            function(response) {
                callback(
                    (response.exists === false)
                    ||
                    (record.get('columnName') == record.get('originalName')),
                    response.table
                );
            }
        );
    },

    saveColumn: function(record, callback) {
        var me = this;
        var window = me.getWindow();
        callback = callback ? callback : Ext.emptyFn;

        window.setLoading('{s name="save_config_message"}{/s}');

        var generateRequired = me.requireGenerate(record);
        me.disableForm();

        record.save({
            callback: function (data, operation) {
                var message = Ext.String.format(
                    '{s name="save_success"}{/s}',
                    record.get('tableName'),
                    record.get('columnName')
                );
                me.displayResponseMessage(operation.response, message);

                window.setLoading(false);
                if (!generateRequired) {
                    callback();
                    return;
                }

                me.generateModel(record.get('tableName'), function () {
                    callback();
                });
            }
        });
    },

    generateModel: function(table, callback) {
        var me = this, callbackFn = Ext.emptyFn, window = me.getWindow();

        if (Ext.isFunction(callback)) {
            callbackFn = callback;
        }

        var message = Ext.String.format(
            '{s name="generate_attributes"}{/s}',
            table
        );
        window.setLoading(message);

        me.sendAjaxRequest(
            '{url controller="Attributes" action="generateModels"}',
            { tableName: table },
            function(response) {
                window.setLoading(false);
                callbackFn(response);
            }
        );
    },

    reloadListing: function(callback) {
        var me = this, callbackFn = Ext.emptyFn;

        if (Ext.isFunction(callback)) {
            callbackFn = callback;
        }

        me.getListing().getStore().load({
            callback: callbackFn
        });
    },

    disableForm: function() {
        this.getDetailForm().disable();
    },

    getCurrentTable: function() {
        var store = this.getListing().tableComboBox.store;
        var value = this.getListing().tableComboBox.getValue();
        var table = null;
        store.each(function(record) {
            if (record.get('name') == value) {
                table = record;
                return false;
            }
        });
        return table;
    },

    columnTypeChanged: function(record) {
        return (record.get('id') > 0 && record.isModified('columnType'));
    },

    requireGenerate: function(record) {
        if (record.get('id') <= 0) {
            return true;
        }

        return (record.isModified('columnType') || record.isModified('columnName'));
    },

    onGenerateClick: function() {
        var me = this;

        var table = me.getCurrentTable();
        if (!table) {
            return;
        }

        me.generateModel(table.get('name'), function(response) {
            me.displayResponseMessage(response, '{s name="generate_success"}{/s}');
        });
    },

    onDeleteClick: function(record) {
        var me = this;
        var table = me.getCurrentTable();
        var window = me.getWindow();

        Ext.Msg.show({
            title: '{s name="delete_column_title"}{/s}',
            msg: '{s name="delete_column_message"}{/s}',
            buttons: Ext.Msg.OKCANCEL,
            icon: Ext.Msg.QUESTION,
            fn: function(btn) {
                if (btn === 'ok') {
                    var name = record.get('columnName');
                    var tables = Ext.clone(table.get('dependingTables'));

                    me.deleteColumn(record, function() {
                        me.deleteDependingTableColumns(name, tables, function() {
                            window.setLoading(false);
                            me.disableForm();
                            me.reloadListing();
                        });
                    });
                }
            }
        });
    },

    onSaveClick: function() {
        var me = this;
        var form = me.getDetailForm();
        var record = form.getRecord();
        var table = me.getCurrentTable();
        var tables = Ext.clone(table.get('dependingTables'));
        var window = me.getWindow();

        if (!form.getForm().isValid()) {
            return;
        }

        form.getForm().updateRecord(record);

        me.isColumnNameValid(
            record,
            function(isValid, foundInTable) {
                window.setLoading(false);

                if (isValid == false) {
                    Shopware.Notification.createGrowlMessage(
                        '{s name="error_name_check_title"}The name already exists{/s}',
                        Ext.String.format('{s name="error_name_check_message"}The name already exists in table [0]. Please choose an unique column name.{/s}', foundInTable)
                    );
                    me.reloadListing();
                    return;
                }

                me.askForTypeChange(record, function() {
                    me.saveColumn(record, function () {
                        me.updateDependingTables(record, tables, function () {
                            me.reloadListing();
                        });
                    });
                });
            }
        );
    },

    askForTypeChange: function(record, callback) {
        var me = this;

        if (!me.columnTypeChanged(record)) {
            callback();
            return;
        }

        Ext.Msg.show({
            title: '{s name="change_column_type_title"}{/s}',
            msg: '{s name="change_column_type_message"}{/s}',
            buttons: Ext.Msg.OKCANCEL,
            icon: Ext.Msg.WARNING,
            fn: function(btn) {
                if (btn === 'ok') {
                    callback();
                } else {
                    me.reloadListing();
                }
            }
        });
    },

    updateDependingTables: function(column, tables, callback) {
        var me = this;

        if (tables.length <= 0) {
            callback();
            return;
        }

        var table = tables.shift();
        var store = Ext.create('Shopware.apps.Attributes.store.DependingTable');
        store.getProxy().extraParams.table = table;
        var name = column.get('originalName');
        if (!name) {
            name = column.get('columnName');
        }

        store.getProxy().extraParams.columnName = name;

        store.load({
            callback: function(records) {
                var record = Ext.create('Shopware.model.AttributeConfig', column.data);

                if (records.length <= 0) {
                    record.set('id', null);
                    record.set('tableName', table);
                } else {
                    record = records[0];
                }
                record.merge(column);

                me.saveColumn(record, function() {
                     me.updateDependingTables(column, tables, callback);
                });
            }
        });
    },


    onResetClick: function() {
        var me = this;
        var detailForm = me.getDetailForm();
        var record = detailForm.getRecord();

        if (!record) {
            return;
        }

        Ext.Msg.show({
            title: '{s name="reset_data_title"}{/s}',
            msg: '{s name="reset_data_message"}{/s}',
            buttons: Ext.Msg.OKCANCEL,
            icon: Ext.Msg.WARNING,
            fn: function(btn) {
                if (btn == 'ok') {
                    me.resetColumn(record);
                }
            }
        });
    },

    resetColumn: function(record) {
        var me = this, window = me.getWindow();

        window.setLoading(true);

        me.sendAjaxRequest(
            '{url controller="Attributes" action="resetData"}',
            { tableName: record.get('tableName'), columnName: record.get('columnName') },
            function(response) {
                me.displayResponseMessage(response, '{s name="reset_success"}{/s}');

                window.setLoading(false);
            }
        );
    },


    sendAjaxRequest: function(url, params, callback) {
        var me = this;
        var callbackFn = Ext.emptyFn;

        if (Ext.isFunction(callback)) {
            callbackFn = callback;
        }

        Ext.Ajax.request({
            url: url,
            method: 'POST',
            params: params,
            success: function(operation, opts) {
                var response = Ext.decode(operation.responseText);
                callbackFn(response);
            }
        });
    },

    displayNewColumn: function() {
        var table = this.getCurrentTable();

        var newRecord = Ext.create('Shopware.model.AttributeConfig', {
            columnType: 'string',
            tableName: table.get('name'),
            displayInBackend: true,
            custom: true,
            core: false,
            identifier: false
        });

        this.loadColumn(newRecord);
    },

    displayColumn: function(grid, selModel, selection) {
        var record = selection[0];
        this.loadColumn(record);
    },

    loadColumn: function(record) {
        var me = this;
        var detailForm = me.getDetailForm();
        var nameField = me.getDetailFormNameField();
        var typeField = me.getDetailFormTypeField();

        me.disableForm();

        if (!record || !record.allowConfigure()) {
            return;
        }

        nameField.disable();
        typeField.disable();

        if (record.allowNameChange()) {
            nameField.enable();
        }
        if (record.allowTypeChange()) {
            typeField.enable();
        }

        detailForm.enable();
        detailForm.loadRecord(record);
    },

    loadListing: function(table) {
        var me = this,
            listing = me.getListing(),
            store = listing.getStore();

        me.disableForm();
        store.getProxy().extraParams.table = table;
        store.load();
    }
});
