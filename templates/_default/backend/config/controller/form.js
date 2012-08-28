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
 * @package    Shopware_Config
 * @subpackage Config
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/config/controller/main}

/**
 * Shopware Controller - Config backend module
 *
 * todo@all: Documentation
 */
//{block name="backend/config/controller/form"}
Ext.define('Shopware.apps.Config.controller.Form', {

    extend: 'Enlight.app.Controller',

    views: [
        'form.Shop',
        'form.Currency',
        'form.Locale',
        'form.Number',
        'form.Unit',
        'form.Country',
        'form.CountryArea',
        'form.CustomerGroup',
        'form.PriceGroup',
        'form.Tax',
        'form.Widget',
        'form.PageGroup',
        'form.Attribute',
        'form.Search',
        'form.CronJob',

        'shop.Detail',
        'shop.Currency',
        'shop.Page',

        'customerGroup.Detail',
        'customerGroup.Discount',

        'tax.Detail',
        'tax.Rule',

        'priceGroup.Discount'
    ],

    stores:[
        'form.Shop',
        'form.Currency',
        'form.Locale',
        'form.Number',
        'form.Unit',
        'form.Country',
        'form.CountryArea',
        'form.CustomerGroup',
        'form.PriceGroup',
        'form.Tax',
        'form.Widget',
        'form.WidgetView',
        'form.PageGroup',
        'form.Attribute',
        'form.SearchField',
        'form.CronJob',

        'detail.Shop',
        'detail.Country',
        'detail.CustomerGroup',
        'detail.PriceGroup',
        'detail.Tax',
        'detail.PageGroup',

        'base.PageGroup',
        'base.SearchTable'
    ],

    models:[
        'form.Shop',
        'form.Currency',
        'form.Locale',
        'form.Number',
        'form.Unit',
        'form.Country',
        'form.CountryArea',
        'form.CustomerGroup',
        'form.PriceGroup',
        'form.Tax',
        'form.Widget',
        'form.WidgetView',
        'form.PageGroup',
        'form.Attribute',
        'form.SearchField',
        'form.SearchTable',
        'form.CronJob'
    ],

    refs: [
        { ref: 'window', selector: 'config-main-window' },
        { ref: 'detail', selector: 'config-base-detail' },
        { ref: 'table', selector: 'config-base-table' },
        { ref: 'deleteButton', selector: 'config-base-table button[action=delete]' },
        { ref: 'taxRuleAddButton', selector: 'config-tax-rule toolbar button' }
    ],

    messages: {
        deleteEntryTitle: '{s name=form/message/delete_entry_title}Delete entry „[label]“{/s}',
        deleteEntryMessage: '{s name=form/message/delete_entry_message}Do you really want to delete the entry?{/s}',
        deleteEntrySuccess: '{s name=form/message/delete_entry_success}Entry „[label]“ was deleted.{/s}',
        deleteEntryError: '{s name=form/message/delete_entry_error}Entry „[label]“ could not be deleted.{/s}',
        saveEntryTitle: '{s name=form/message/save_entry_title}Save entry{/s}',
        saveEntrySuccess: '{s name=form/message/save_entry_success}Entry „[label]“ has been saved.{/s}',
        saveEntryError: '{s name=form/message/save_entry_error}Entry „[label]“ could not be saved.{/s}'
    },

    /**
     *
     */
    init: function () {
        var me = this;

        me.initKeyMap();

        me.control({
            'config-base-form': {
                edit: function(panel, record) {
                    me.doEditEntry(record);
                },
                delete: function(panel, record) {
                    me.doDeleteEntry(record);
                }
            },
            'config-base-table button': {
                click: function(button) {
                    switch (button.action) {
                        case 'add':
                            me.onAddEntry();
                            break;
                        case 'delete':
                            me.onDeleteEntry();
                            break;
                        case 'edit':
                            break;
                    }
                }
            },
            'config-tax-rule config-element-select': {
                change: me.onSelectCustomerGroupOnTax
            },
            'config-base-detail button[action=save]': {
                click: me.onSaveEntry
            },
            'config-base-detail button[action=reset]': {
                click: function(button, event) {
                    me.doEditEntry();
                }
            },
            'config-base-table textfield[name=searchfield]': {
                change: function(field, value) {
                    var table = me.getTable(),
                        store = table.getStore();

                    if (value.length === 0 ) {
                        store.clearFilter();
                    } else {
                        store.filters.clear();
                        store.filter(
                            table.searchField || 'name',
                            '%' + value + '%'
                        );
                    }
                }
            },
            'config-base-table': {
                selectionchange: function(table, records) {
                    var me = this,
                        deleteButton = me.getDeleteButton(),
                        formPanel = me.getDetail(),
                        record = records.length ? records[0] : null,
                        action;

                    if(record) {
                        me.doEditEntry(record);
                        if(deleteButton) {
                            action = record.get('deletable') !== false;
                            action = action ? 'enable' : 'disable';
                            deleteButton[action]();
                        }
                    } else {
                        if(deleteButton) {
                            deleteButton.disable();
                        }
                        formPanel.disable();
                    }
                }
            },
            'config-base-property button[action=add]': {
                click: me.onAddPropertyEntry
            },
            'config-base-property [name=property]': {
                select: function(field, records) {
                    var me = this,
                        table = field.up('grid'),
                        store = table.getStore(), model;
                    if(!records.length) {
                        return;
                    }
                    model = records[0];
                    if(!store.getById(model.getId())) {
                        store.add(model.data);
                    }
                    field.reset();
                }
            },
            'config-base-property [isPropertyFilter]': {
                change: function(field, value) {
                    var me = this,
                        table = field.up('grid'),
                        store = table.getStore(),
                        filter = field.getModelData();
                    store.clearFilter(true);
                    for(var key in filter) {
                        var keyFilter = new Ext.util.Filter({
                            filterFn: function(item) {
                                return item.get(key) === filter[key];
                            }
                        });
                        store.filter(keyFilter);
                    }
                }
            },
            'config-base-property': {
                delete: function(view, record) {
                    var me = this,
                        store = view.getStore();
                    store.remove(record);
                }
            }
        });

        me.callParent(arguments);
    },

    initKeyMap: function() {
        var me = this;
        me.map = new Ext.util.KeyMap(me.getWindow().getEl(), [{
            key: "s",
            ctrl: true,
            handler: me.onSaveEntry,
            scope: me,
            defaultEventAction: 'preventDefault'
        }, {
            key: Ext.EventObject.INSERT,
            alt: true,
            handler: me.onAddEntry,
            scope: me
        }, {
            key: Ext.EventObject.DELETE,
            alt: true,
            handler: me.onDeleteEntry,
            scope: me
        }]);
    },

    onAddEntry: function() {
        var me = this,
            formPanel = me.getDetail();
        if(!formPanel) {
            return;
        }
        var basicForm = formPanel.getForm(),
            table = me.getTable(),
            store = table.getStore(),
            formStore = store,
            fields, record;

        // Get the detail store, if available
        if(formPanel.store) {
            formStore = me.getStore(formPanel.store);
        }

        // Create a new model and assign it to the form
        record = formStore.createModel({ });
        me.loadEditEntry(record);

        // Focus an first field
        fields = basicForm.getFields();
        if(fields.getCount() > 0) {
            fields.first().focus();
        }
    },


    onSelectCustomerGroupOnTax: function(field, newValue, oldValue) {
        var me = this,
        addButton = me.getTaxRuleAddButton();
        addButton.setDisabled(!newValue);
    },
    
    onSaveEntry: function() {
        var me = this,
            formPanel = me.getDetail();
        if(!formPanel) {
            return;
        }

        var basicForm = formPanel.getForm(),
            record = formPanel.getRecord(),
            table = me.getTable(),
            store = table.getStore(),
            formStore = store;

        if(!basicForm.isValid()) {
            return;
        }

        if(formPanel.store) {
            formStore = me.getStore(formPanel.store);
        }
        if(!record) {
            return;
        }

        formPanel.updateRecord();
        if(!record.store) {
            formStore.add(record);
        }

        //todo@hl
		var values = formPanel.getValues();
		if(values['elements']){
			var elementFieldSet = me.getDetail().down('fieldset[name=elementFieldSet]'),
				elementComboBox = elementFieldSet.down('combo'),
				elementStore = elementComboBox.getStore(),
				activeRecord = elementStore.getById(values['elements']),
				fieldName = activeRecord.get('name');


			activeRecord.set('value', values[fieldName + '_Value']);
			activeRecord.set('style', values[fieldName + '_Style']);
		}

        formPanel.disable();
        formPanel.loadRecord();

        var message,
            title = me.messages.saveEntryTitle;

        formStore.sync({
            success :function (records, operation) {
                message = me.messages.saveEntrySuccess;
                me.createGrowlMessage(record, title, message);
                store.load();
            },
            failure:function (records, operation) {
                message = me.messages.saveEntryError;
                me.createGrowlMessage(record, title, message);
                store.load();
            }
        });
    },

    createGrowlMessage: function(record, title, message) {
        var me = this,
            win = me.getWindow(),
            data = Ext.clone(record.data);

        data.label = data.label || data.name;
        title = new Ext.Template(title).applyTemplate(data);
        message = new Ext.Template(message).applyTemplate(data);
        Shopware.Notification.createGrowlMessage(title, message, win.title);
    },

    onDeleteEntry: function() {
        var me = this,
            table = me.getTable();
        if(!table) {
            return;
        }
        var selectionModel = table.getSelectionModel(),
            record = selectionModel.getLastSelected();
        if(!record) {
            return;
        }
        me.doDeleteEntry(record);
    },

    doDeleteEntry: function(record) {
        var me = this,
            store = record.store,
            title = new Ext.Template(me.messages.deleteEntryTitle),
            message = new Ext.Template(me.messages.deleteEntryMessage);

        record.data.label = record.data.label || record.data.name;
        title = title.applyTemplate(record.data);
        message = message.applyTemplate(record.data);

        Ext.MessageBox.confirm(title, message, function (response) {
            if (response !== 'yes') {
                return false;
            }
            store.remove(record);
            store.sync({
                success: function (operation) {
                    message = me.messages.deleteEntrySuccess;
                    me.createGrowlMessage(record, title, message);
                },
                failure: function (operation) {
                    message = me.messages.deleteEntryError;
                    me.createGrowlMessage(record, title, message);
                }
            });
        });
    },

    doEditEntry: function(record) {
        var me = this,
            formPanel = me.getDetail(),
            formStore;
        if(!formPanel) {
            return;
        }
        if(formPanel.store) {
            formStore = me.getStore(formPanel.store);
        }
        if(record && formStore) {
            formStore.load({
                filters : [{
                    property: 'id',
                    value: record.data.id
                }],
                callback: function(records, operation) {
                    if (operation.success !== true || !records.length) {
                        return;
                    }
                    me.loadEditEntry(records[0]);
                }
            });
        } else {
            me.loadEditEntry(record);
        }
    },

    loadEditEntry: function(record) {
        var me = this,
            formPanel = me.getDetail(),
            basicForm = formPanel.getForm();

        if(!record) {
            record = basicForm.getRecord();
        }

        formPanel.enable();
        formPanel.loadRecord(record);

        if(record) {
            record.associations.each(function(association) {
                var store = record[association.name](),
                    associationKey = association.associationKey,
                    grid = me.getDetail().down('grid[name=' + associationKey + ']'),
                    combo = me.getDetail().down('combo[name=' + associationKey + ']'),
                    filter;

                //Bind the association store to the combobox
                if(combo && store) {
                    combo.setValue(null);
                    combo.bindStore(store);
                    record.setDirty();
                }

                if(grid && store) {
                    grid.reconfigure(store);
                    record.setDirty();
                    filter = grid.down('[isPropertyFilter]');
                    if(filter) {
                        filter = filter.getModelData();
                        for(var key in filter) {
                            var keyFilter = new Ext.util.Filter({
                                filterFn: function(item) {
                                    return item.get(key) === filter[key];
                                }
                            });
                            store.filter(keyFilter);
                        }
                    }
                }
            });
        }
    },

    onAddPropertyEntry: function(button) {
        var me = this,
            table = button.up('grid'),
            fields = table.query('[isFormField]'),
            data = { }, fieldData;
        if(!table) {
            return;
        }
        Ext.each(fields, function(field) {
            fieldData = field.getModelData();
            data = Ext.apply(data, fieldData);
        });
        var store = table.getStore(),
            record = store.add(data)[0],
            plugin = table.getPlugin('cellediting');
        plugin.startEdit(record, table.columns[0]);
    }
});
//{/block}
