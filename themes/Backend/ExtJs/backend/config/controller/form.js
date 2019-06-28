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
 * @package    Shopware_Config
 * @subpackage Config
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
        'form.CoreLicense',
        'form.CustomerGroup',
        'form.PriceGroup',
        'form.Tax',
        'form.PageGroup',
        'form.Search',
        'form.CronJob',

        'custom_search.Overview',
        'custom_search.common.Listing',

        'custom_search.facet.Listing',
        'custom_search.facet.Detail',
        'custom_search.facet.Facet',
        'custom_search.facet.classes.FacetInterface',
        'custom_search.facet.classes.CategoryFacet',
        'custom_search.facet.classes.ImmediateDeliveryFacet',
        'custom_search.facet.classes.ManufacturerFacet',
        'custom_search.facet.classes.PriceFacet',
        'custom_search.facet.classes.VariantFacet',
        'custom_search.facet.classes.PropertyFacet',
        'custom_search.facet.classes.ShippingFreeFacet',
        'custom_search.facet.classes.VoteAverageFacet',
        'custom_search.facet.classes.ProductAttributeFacet',
        'custom_search.facet.classes.CombinedConditionFacet',
        'custom_search.facet.classes.WeightFacet',
        'custom_search.facet.classes.LengthFacet',
        'custom_search.facet.classes.HeightFacet',
        'custom_search.facet.classes.WidthFacet',

        'custom_search.sorting.Listing',
        'custom_search.sorting.Detail',
        'custom_search.sorting.SortingSelection',
        'custom_search.sorting.classes.SortingInterface',
        'custom_search.sorting.classes.PriceSorting',
        'custom_search.sorting.classes.ProductNameSorting',
        'custom_search.sorting.classes.ProductNumberSorting',
        'custom_search.sorting.classes.PopularitySorting',
        'custom_search.sorting.classes.ReleaseDateSorting',
        'custom_search.sorting.classes.SearchRankingSorting',
        'custom_search.sorting.classes.ProductAttributeSorting',
        'custom_search.sorting.classes.ManualSorting',
        'custom_search.sorting.includes.CreateWindow',
        'custom_search.sorting.includes.DirectionCombo',

        'shop.Detail',
        'shop.Currency',
        'shop.Page',

        'customerGroup.Detail',
        'customerGroup.Discount',

        'tax.Detail',
        'tax.Rule',

        'priceGroup.Discount',

        'variantFilter.ExpandGroupsGrid',
        'variantFilter.DynamicVariantReader',
        'variantFilter.ExpandGroupsHiddenField'
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
        'form.PageGroup',
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
        'form.PageGroup',
        'form.SearchField',
        'form.SearchTable',
        'form.CronJob'
    ],

    refs: [
        { ref: 'window', selector: 'config-main-window' },
        { ref: 'detail', selector: 'config-base-detail' },
        { ref: 'table', selector: 'config-base-table' },
        { ref: 'deleteButton', selector: 'config-base-table button[action=delete]' },
        { ref: 'taxRuleAddButton', selector: 'config-tax-rule toolbar button' },
        { ref: 'discountAddButton', selector: 'config-pricegroup-discount toolbar button' }
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
                    me.doEditEntry(panel, record);
                },
                delete: function(panel, record) {
                    me.doDeleteEntry(record);
                }
            },
            'config-base-table button': {
                click: function(button) {
                    var baseForm = button.up('config-base-form');
                    switch (button.action) {
                        case 'add':
                            me.onAddEntry(baseForm);
                            break;
                        case 'delete':
                            me.onDeleteEntry(baseForm);
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
                click: function(button, event) {
                    var baseForm = button.up('config-base-form');
                    me.onSaveEntry(baseForm);
                }
            },
            'config-base-detail button[action=reset]': {
                click: function(button, event) {
                    var baseForm = button.up('config-base-form');
                    me.doEditEntry(baseForm);
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
                        baseForm = table.view.up('config-base-form'),
                        deleteButton = baseForm.getDeleteButton(),
                        record = records.length ? records[0] : null,
                        formPanel = baseForm.getDetail(), action;
                    if(record) {
                        me.doEditEntry(baseForm, record);
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
            },
            'config-pricegroup-discount config-element-select': {
                change: me.onSelectCustomerGroupOnPriceDiscount
            }
        });

        me.callParent(arguments);
    },

    initKeyMap: function() {
        var me = this;
        me.map = new Ext.util.KeyMap(me.getWindow().getEl(), [{
            key: "s",
            ctrl: true,
            handler: me.onKeyHandler,
            scope: me,
            defaultEventAction: 'preventDefault'
        }, {
            key: Ext.EventObject.INSERT,
            alt: true,
            handler: me.onKeyHandler,
            scope: me
        }, {
            key: Ext.EventObject.DELETE,
            alt: true,
            handler: me.onKeyHandler,
            scope: me
        }]);
    },

    onKeyHandler: function(key, event) {
        var me = this, baseForm = me.getDetail();
        switch(key) {
            case 83:
                me.onSaveEntry(baseForm);
                break;
            case Ext.EventObject.INSERT:
                me.onAddEntry(baseForm);
                break;
            case Ext.EventObject.DELETE:
                me.onDeleteEntry(baseForm);
                break;
        }
    },

    onAddEntry: function(baseForm) {
        var me = this,
            formPanel = baseForm.getDetail();

        var basicForm = formPanel.getForm(),
            table = baseForm.getTable(),
            store = table.getStore(),
            formStore = store,
            fields, record;

        // Get the detail store, if available
        if(formPanel.store) {
            formStore = me.getStore(formPanel.store);
        }

        // Create a new model and assign it to the form
        record = formStore.createModel({ });
        me.loadEditEntry(baseForm, record);

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

    onSelectCustomerGroupOnPriceDiscount: function(field, newValue, oldValue) {
        var me = this,
            addButton = me.getDiscountAddButton();

        addButton.setDisabled(!newValue);
    },

    onSaveEntry: function(baseForm) {
        var me = this,
            formPanel = baseForm.getDetail();

        var basicForm = formPanel.getForm(),
            record = formPanel.getRecord(),
            table = baseForm.getTable(),
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
            var elementFieldSet = formPanel.down('fieldset[name=elementFieldSet]'),
                elementComboBox = elementFieldSet.down('combo'),
                elementStore = elementComboBox.getStore(),
                activeRecord = elementStore.getById(values['elements']),
                fieldName = activeRecord.get('name');

            activeRecord.set('value', values[fieldName + '_Value']);
            activeRecord.set('style', values[fieldName + '_Style']);
        }

        formPanel.disable();
        formPanel.loadRecord();

        record.associations.each(function(association) {
            var store = record[association.name]();
            store.clearFilter(true);
        });

        var message,
            title = me.messages.saveEntryTitle;

        formStore.sync({
            success :function (records, operation) {
                message = me.messages.saveEntrySuccess;
                me.createGrowlMessage(record, title, message);
                store.load();
            },
            failure:function (batch) {
                message = me.messages.saveEntryError;
                if(batch.proxy.reader.rawData.message) {
                    message += '<br />' + batch.proxy.reader.rawData.message;
                }
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

    onDeleteEntry: function(baseForm) {
        var me = this,
            table = baseForm.getTable();
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
                failure: function (batch) {
                    message = me.messages.deleteEntryError;
                    if(batch.proxy.reader.rawData.message) {
                        message += '<br />' + batch.proxy.reader.rawData.message;
                    }
                    me.createGrowlMessage(record, title, message);
                }
            });
        });
    },

    doEditEntry: function(baseForm, record) {
        var me = this,
            formPanel = baseForm.getDetail(),
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
                    me.loadEditEntry(baseForm, records[0]);
                }
            });
        } else {
            me.loadEditEntry(baseForm, record);
        }
    },

    loadEditEntry: function(baseForm, record) {
        var me = this,
            formPanel = baseForm.getDetail(),
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
                    grid = formPanel.down('grid[name=' + associationKey + ']'),
                    combo = formPanel.down('combo[name=' + associationKey + ']'),
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
