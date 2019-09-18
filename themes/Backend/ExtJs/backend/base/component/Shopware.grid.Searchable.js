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

//{namespace name="backend/base/component/searchable_grid"}
//{block name="backend/base/Shopware.grid.Searchable"}
Ext.define('Shopware.grid.Searchable', {

    extend: 'Ext.grid.Panel',

    alias: ['widget.searchablegrid', 'widget.searchgrid'],

    /**
     * Will be filled later with the loaded stores for the entity + identifier combinations.
     */
    savedStores : {},

    /**
     * Will be filled at runtime with an `Ext.button.Button` for the top toolbar.
     */
    deleteButton: null,

    listeners: {
        beforerender: function (grid) {
            this.loadStores(grid);
        }
    },

    /**
     * Delimiter to be used by both the multi-select combobox, as well as for rendering the several values.
     */
    identifierDelimiter: ',',

    /**
     * Contains the entities, which should be available here.
     */
    allowedEntities: [],

    /**
     * Maps the identifier names to the base stores and its translation
     */
    availableStores: {
        'blog': {
            translation: '{s name="entityStoreSnippets/blog"}Blogs{/s}',
            storeConfiguration: {
                model: 'Shopware.apps.Base.model.Blog',
                entity: "Shopware\\Models\\Blog\\Blog"
            },
        },
        'category': {
            translation: '{s name="entityStoreSnippets/category"}Categories{/s}',
            storeConfiguration: {
                model: 'Shopware.apps.Base.model.Category',
                entity: "Shopware\\Models\\Category\\Category"
            },
        },
        'country': {
            translation: '{s name="entityStoreSnippets/country"}Countries{/s}',
            storeConfiguration: {
                model: 'Shopware.apps.Base.model.Country',
                entity: "Shopware\\Models\\Country\\Country"
            },
        },
        'currency': {
            translation: '{s name="entityStoreSnippets/currency"}Currencies{/s}',
            storeConfiguration: {
                model: 'Shopware.apps.Base.model.Currency',
                entity: "Shopware\\Models\\Shop\\Currency"
            },
        },
        'customer': {
            translation: '{s name="entityStoreSnippets/customer"}Customers{/s}',
            storeConfiguration: {
                model: 'Shopware.apps.Base.model.User',
                entity: "Shopware\\Models\\Customer\\Customer"
            },
        },
        'customer_group': {
            translation: '{s name="entityStoreSnippets/customer_group"}Customer groups{/s}',
            storeConfiguration: {
                model: 'Shopware.apps.Base.model.CustomerGroup',
                entity: "Shopware\\Models\\Customer\\Group"
            },
        },
        'dispatch': {
            translation: '{s name="entityStoreSnippets/dispatch"}Shipping methods{/s}',
            storeConfiguration: {
                model: 'Shopware.apps.Base.model.Dispatch',
                entity: "Shopware\\Models\\Dispatch\\Dispatch"
            },
        },
        'landing_page': {
            translation: '{s name="entityStoreSnippets/landing_page"}Landingpages{/s}',
            storeConfiguration: {
                model: 'Shopware.apps.Base.model.LandingPage',
                entity: "Shopware\\Models\\Emotion\\LandingPage"
            },
        },
        'locale': {
            translation: '{s name="entityStoreSnippets/locale"}Locales{/s}',
            storeConfiguration: {
                model: 'Shopware.apps.Base.model.Locale',
                entity: "Shopware\\Models\\Shop\\Locale"
            },
        },
        'manufacturer': {
            translation: '{s name="entityStoreSnippets/supplier"}Manufacturer{/s}',
            storeConfiguration: {
                model: 'Shopware.apps.Base.model.Supplier',
                entity: "Shopware\\Models\\Article\\Supplier"
            },
        },
        'product': {
            translation: '{s name="entityStoreSnippets/product"}Products{/s}',
            storeConfiguration: {
                model: 'Shopware.apps.Base.model.Article',
                entity: "Shopware\\Models\\Article\\Article"
            },
        },
        'shop': {
            translation: '{s name="entityStoreSnippets/shop"}Shops{/s}',
            storeConfiguration: {
                model: 'Shopware.apps.Base.model.Shop',
                entity: "Shopware\\Models\\Shop\\Shop"
            },
        },
        'static': {
            translation: '{s name="entityStoreSnippets/static"}Shop pages{/s}',
            storeConfiguration: {
                model: 'Shopware.apps.Base.model.Static',
                entity: "Shopware\\Models\\Site\\Site"
            },
        },
    },

    /**
     * Data index of the entity column / property.
     */
    entityColumnDataIndex: 'entity',

    /**
     * Data index of the identifier column / property.
     */
    identifierColumnDataIndex: 'identifier',

    initComponent: function () {
        this.plugins = this.createRowEditingPlugin();

        this.entityStore = this.createEntityStore();
        this.columns = this.buildColumns();

        this.selModel = this.createSelectionModel();

        this.dockedItems = this.createDockedItems();

        this.callParent(arguments);
    },

    /**
     * Overwrite this to add your custom columns.
     *
     * @returns { Array }
     */
    getColumns: function () {
        return [];
    },

    /**
     * Override the original method to add a new converter to the identifier model field.
     * This new converter handles empty arrays as null.
     *
     * @param { Ext.data.Store } store
     */
    reconfigure: function (store) {
        this.addConverterToIdentifier(store.model);

        this.callParent(arguments);
    },

    /**
     * @param { Ext.data.Model } model
     */
    addConverterToIdentifier: function (model) {
        var me = this;

        Ext.each(model.getFields(), function (field) {
            if (field.name === me.identifierColumnDataIndex && !field.hasCustomConvert) {
                var originalConvert = field.convert;
                field.hasCustomConvert = true;

                field.convert = function (value) {
                    if ((Array.isArray(value) && value.length === 0) || value === '' || value === null) {
                        return null;
                    }

                    return originalConvert(value);
                };
            }
        });
    },

    /**
     * @returns { Ext.grid.plugin.RowEditing }
     */
    createRowEditingPlugin: function () {
        var me = this;

        return Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 1,
            keepExisting: true,
            listeners: {
                beforeedit: function (editor, options) {
                    me.changeStoreBeforeEdit(options.record);
                },
                validateedit: Ext.bind(this.loadStoresAfterEdit, this)
            }
        })
    },

    /**
     * @returns { Array }
     */
    createDockedItems: function () {
        return [ this.createToolBar() ];
    },

    /**
     * @returns { Ext.toolbar.Toolbar }
     */
    createToolBar: function () {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: [
                {
                    xtype: 'button',
                    iconCls: 'sprite-plus-circle',
                    text: '{s name="button/createNewEntry"}Create new entry{/s}',
                    handler: Ext.bind(this.createNewEntry, me)
                },
                this.createActionBarDeleteButton()
            ]
        });
    },

    /**
     * @returns { Ext.button.Button }
     */
    createActionBarDeleteButton: function () {
        this.deleteButton = Ext.create('Ext.button.Button',{
            text: '{s name="button/deleteSelectedEntries"}Delete selected entries{/s}',
            iconCls: 'sprite-minus-circle',
            disabled: true,
            handler: Ext.bind(this.deleteSelectedRecords, this)
        });

        return this.deleteButton;
    },

    /**
     * @returns { Array }
     */
    buildColumns: function () {
        var defaultColumns = this.buildDefaultColumns(),
            customColumns = this.getColumns();

        return Ext.Array.merge(defaultColumns, customColumns, {
            xtype: 'actioncolumn',
            flex: 1,
            items: [ this.createRowDeleteButton() ]
        });
    },

    /**
     * @returns { Object }
     */
    createRowDeleteButton: function () {
        var me = this;

        return {
            iconCls: 'sprite-minus-circle-frame',
            handler: function (view, rowIndex, colIndex, item, opts, record) {
                me.store.remove(record);
            }
        };
    },

    /**
     * @returns { Array }
     */
    buildDefaultColumns: function () {
        return [
            {
                header: '{s name="column/entity"}Entity{/s}',
                dataIndex: this.entityColumnDataIndex,
                flex: 3,
                editor: this.getEntityEditor(),
                renderer: this.renderEntityName
            }, {
                header: '{s name="column/identifier"}Identifier{/s}',
                dataIndex: this.identifierColumnDataIndex,
                flex: 5,
                editor: this.getIdentifierEditor(),
                renderer: this.renderIdentifier
            }
        ]
    },

    /**
     * @returns { Object }
     */
    getEntityEditor: function () {
        return {
            xtype: 'combo',
            store: this.entityStore,
            displayField: 'name',
            valueField: 'id',
            listeners: {
                select: Ext.bind(this.bindIdentifierStoreOnChange, this)
            }
        };
    },

    /**
     * @returns { Object }
     */
    getIdentifierEditor: function () {
        this.identifierEditor = Ext.create('Ext.form.field.ComboBox', {
            displayField: 'name',
            valueField: 'id',
            delimiter: this.identifierDelimiter,
            multiSelect: true,
            allowBlank: true,
            // Enables resetting this field with a null value
            getSubmitData: function () {
                return this.getModelData();
            },
            // Add more than one query parameters
            getParams: function (queryString) {
                var params = {},
                    param = this.queryParam;

                if (param) {
                    params[param] = queryString;
                    params[param] = queryString;
                }
                return params;
            }
        });

        return this.identifierEditor;
    },

    /**
     * @returns { Ext.data.Store }
     */
    createEntityStore: function () {
        return Ext.create('Ext.data.Store', {
            fields: this.getChangeFrequencyFields(),
            data: this.getEntityStoreData(),
            allowBlank: false,
            sorters: [
                {
                    property: 'name'
                }
            ]
        });
    },

    /**
     * @returns { Array }
     */
    getChangeFrequencyFields: function () {
        return [
            { name: 'id', type: 'string' },
            { name: 'name', type: 'string' },
        ];
    },

    /**
     * @returns { Array }
     */
    getEntityStoreData: function () {
        var entityStoreData = [],
            entity;

        for (entity in this.availableStores) {
            if (!this.availableStores.hasOwnProperty(entity) || !this.isEntityAllowed(entity)) {
                continue;
            }

            entityStoreData.push({ 'id': entity, 'name': this.availableStores[entity].translation });
        }

        return entityStoreData;
    },

    /**
     * @return { Ext.selection.CheckboxModel }
     */
    createSelectionModel : function() {
        var me = this;

        return Ext.create('Ext.selection.CheckboxModel', {
            listeners: {
                selectionchange: function (selectionModel, selections) {
                    me.deleteButton.setDisabled(selections.length === 0);
                }
            },
            editRenderer: null
        });
    },

    /**
     * @param { string } entity
     * @returns { string }
     */
    renderEntityName: function (entity) {
        return this.entityStore.findRecord('id', entity).get('name');
    },

    createNewEntry: function () {
        var newModel = Ext.create(this.store.model),
            entityName = this.getFirstPossibleEntityName();

        newModel.set(this.entityColumnDataIndex, entityName);
        newModel.set(this.identifierColumnDataIndex, null);

        this.store.add(newModel);
    },

    deleteSelectedRecords: function () {
        var selectionModel = this.selModel,
            records = selectionModel.getSelection();

        if (records.length > 0) {
            this.store.remove(records);
        }
    },

    /**
     * @returns { string }
     */
    getFirstPossibleEntityName: function () {
        if (!this.allowedEntities.length) {
            return Object.keys(this.availableStores)[0];
        }

        return this.allowedEntities[0];
    },

    /**
     * @param { mixed } value
     * @param { Object } colMetaData
     * @param { Ext.data.Model } record
     * @returns { string }
     */
    renderIdentifier: function (value, colMetaData, record) {
        if (!value) {
            return '{s name="renderer/all"}All{/s}';
        }

        var entity = record.get(this.entityColumnDataIndex),
            identifier = record.get(this.identifierColumnDataIndex),
            store = this.savedStores[entity][identifier],
            names = [];

        if (!store) {
            return value;
        }

        store.each(function (item) {
            names.push(item.get('name'));
        });

        return names.join(this.identifierDelimiter);
    },

    /**
     * @param { string } entity
     * @returns { Ext.data.Store }
     */
    getStoreForEntity: function (entity) {
        var storeConfiguration = this.availableStores[entity].storeConfiguration;

        return Ext.create('Shopware.store.Search', {
            model: storeConfiguration.model,
            configure: function () {
                return { entity: storeConfiguration.entity };
            }
        });
    },

    /**
     * @param { string } entity
     * @param { string } identifiers
     * @param { function } callback
     */
    loadStoreByValues: function (entity, identifiers, callback) {
        var store = this.getStoreForEntity(entity),
            identifierArray,
            params = {};

        callback = callback || function () {};
        
        if (identifiers === null) {
            return;
        }

        identifierArray = identifiers.split(this.identifierDelimiter);

        params.ids = Ext.JSON.encode(identifierArray);
        if (!Array.isArray(identifierArray)) {
            params.id = identifiers;
        }

        store.load({
            params: params,
            callback: callback
        });

        this.saveStore(entity, identifiers, store);
    },

    /**
     * @param { Ext.grid.Panel } grid
     */
    loadStores: function (grid) {
        var me = this;

        grid.getStore().each(function (record) {
            var entity = record.get(me.entityColumnDataIndex),
                identifiers = record.get(me.identifierColumnDataIndex);

            me.loadStoreByValues(entity, identifiers);
        });
    },

    /**
     * @param { string } entity
     * @param { string } identifiers
     * @param { Ext.data.Store } store
     */
    saveStore: function (entity, identifiers, store) {
        if (!this.savedStores.hasOwnProperty('entity')) {
            this.savedStores[entity] = {};
        }

        this.savedStores[entity][identifiers] = store;
    },

    /**
     * @param { Ext.data.Model } record
     */
    changeStoreBeforeEdit: function (record) {
        var me = this,
            entityStore = this.getStoreForEntity(record.get(this.entityColumnDataIndex));

        entityStore.load(function () {
            me.identifierEditor.bindStore(entityStore);

            var identifierEditorValue = me.identifierEditor.getValue();

            if (!identifierEditorValue.length) {
                return;
            }

            identifierEditorValue = identifierEditorValue[0].split(me.identifierDelimiter);
            identifierEditorValue = identifierEditorValue.map(function (element) { return parseInt(element); });
            me.identifierEditor.setValue(identifierEditorValue, true);
        });
    },

    /**
     * @param { Ext.grid.plugin.Editing } editor
     * @param { Object } options
     */
    loadStoresAfterEdit: function (editor, options) {
        var me = this,
            identifierValue = options.newValues[this.identifierColumnDataIndex];

        if (!Array.isArray(identifierValue) || identifierValue === null) {
            return;
        }

        if (!identifierValue.length) {
            return;
        }

        identifierValue = identifierValue.join(me.delimiter);

        me.loadStoreByValues(
            options.newValues[this.entityColumnDataIndex],
            identifierValue,
            function () {
                me.getView().refresh();
            }
        );
    },

    /**
     * @param { Ext.form.field.ComboBox } combo
     * @param { Ext.data.Model[] } records
     */
    bindIdentifierStoreOnChange: function (combo, records) {
        var entity = records[0].get('id'),
            entityStore = this.getStoreForEntity(entity);

        entityStore.load();
        this.identifierEditor.setValue(null);
        this.identifierEditor.bindStore(entityStore);
    },

    /**
     * @param { string } entity
     * @returns { boolean }
     */
    isEntityAllowed: function (entity) {
        if (!this.allowedEntities.length) {
            return true;
        }

        return this.allowedEntities.includes(entity);
    },
});
//{/block}