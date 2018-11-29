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
 * @author shopware AG
 */

// {namespace name=backend/order/main}
//{block name="backend/order/view/mail/attachment"}
Ext.define('Shopware.apps.Order.view.mail.Attachment', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.order-mail-attachment',
    height: 350,
    title: '{s name=attachment/panel/title}{/s}',

    /**
     * Constructor parameters
     *
     * @type { Shopware.apps.Order.model.Order }
     */
    order: null,

    /**
     * @type { Shopware.apps.Order.model.Receipt }
     */
    preSelectedAttachment: null,

    /**
     * @type { Shopware.apps.Order.store.Order }
     */
    listStore: null,

    /**
     * Init the attachmentGridPanel
     */
    initComponent: function() {
        var me = this;

        me.store = me.createStore();
        me.columns = me.createColumns();
        me.selModel = me.createSelectionModel();
        me.features = me.createFeatures();
        me.dockedItems = me.createDockedItems();

        me.callParent(arguments);
    },

    /**
     * After render call preselect because before the selectionModel is not ready
     */
    afterRender: function() {
        var me = this;

        me.callParent(arguments);
        me.preselect();
    },

    /**
     * Create the document store with a groupfield from the given receiptStore
     *
     * @returns { Ext.data.Store }
     */
    createStore: function() {
        var me = this,
            tmpStore = Ext.create('Shopware.apps.Order.store.DocumentRegistry');

        me.order.getReceipt().each(function(item) {
            tmpStore.add(item);
        });

        return tmpStore;
    },

    /**
     * Creates a array of column objects for the grid panel
     *
     * @returns { Array }
     */
    createColumns: function() {
        var me = this;

        return [
            {
                text: '{s name=attachment/panel/column/date}{/s}',
                dataIndex: 'date',
                flex: 1,
                renderer: me.dateColumnRenderer
            }, {
                text: '{s name=attachment/panel/column/name}{/s}',
                dataIndex: 'typeId',
                flex: 2,
                renderer: me.nameColumnRenderer
            }
        ];
    },

    /**
     * Creates and returns all docked items as a object array.
     *
     * @returns { object[] }
     */
    createDockedItems: function() {
        var me = this;

        return [
            me.createToolbar()
        ];
    },

    /**
     * Creates the top toolbar
     *
     * @returns { Ext.toolbar.Toolbar }
     */
    createToolbar: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'top',
            cls: 'shopware-toolbar',
            ui: 'shopware-ui',
            items: me.createToolbarItems()
        });
    },

    /**
     * Creates and returns all features as a array
     *
     * @returns { Array }
     */
    createFeatures: function() {
        var me = this;

        return [
            me.createGroupingFeature()
        ]
    },

    /**
     * Creates the grouping feature
     *
     * @returns { Ext.grid.feature.Grouping }
     */
    createGroupingFeature: function() {
        var me = this;

        return Ext.create('Ext.grid.feature.Grouping', {
            groupHeaderTpl: me.createGroupingTemplate()
        });
    },

    /**
     * Creates the grouping header template
     *
     * @returns { Ext.XTemplate }
     */
    createGroupingTemplate: function() {
        var me = this;

        return Ext.create('Ext.XTemplate',
            '<span>{ name:this.formatHeader }</span>',
            '<span>&nbsp;({ rows.length })</span>',
            {
                formatHeader: Ext.bind(me.groupingHeaderRenderer, me)
            }
        )
    },

    /**
     * Renders the grouping header
     *
     * @param { boolean|string } active
     * @returns { string }
     */
    groupingHeaderRenderer: function(active) {
        if (active === true || active === 'true') {
            return '{s name=attachment/panel/grouping/prefix/attached}{/s}';
        } else {
            return '{s name=attachment/panel/grouping/prefix/not/attached}{/s}';
        }
    },

    /**
     * Creates the checkbox selection model
     *
     * @returns { Ext.selection.CheckboxModel }
     */
    createSelectionModel: function() {
        var me = this;

        return Ext.create('Ext.selection.CheckboxModel', {
            checkOnly: true,
            showHeaderCheckbox: false,
            listeners: {
                selectionchange: function(selectionModel, selected) {
                    me.fireEvent('selectionModel-selection-change', me.store, selectionModel, selected);
                }
            }
        });
    },

    /**
     * Creates all toolbar items and return them in a array.
     *
     * @returns { Array }
     */
    createToolbarItems: function() {
        var me = this;

        return [
            me.createToolbarDocumentTypeSelect(),
            '->',
            me.createAddButton()
        ]
    },

    /**
     * Creates the combo box to select the document type.
     *
     * @returns { Ext.form.field.ComboBox }
     */
    createToolbarDocumentTypeSelect: function() {
        var me = this;

        me.documentTypeSelection = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel: '{s name=attachment/panel/document/type/field/label}{/s}',
            store: me.documentTypeStore,
            displayField: 'name',
            valueField: 'id',
            multiSelect: false,
            listeners: {
                change: function(typeSelection, newValue, oldValue) {
                    me.fireEvent(
                        'document-type-selected', me, typeSelection, newValue, oldValue
                    )
                }
            }
        });

        return me.documentTypeSelection;
    },

    /**
     * Creates the split button for the choice create or create and add
     *
     * @returns { Ext.button.Split }
     */
    createAddButton: function() {
        var me = this;

        return Ext.create('Ext.button.Split', {
            text: '{s name=attachment/panel/split/button/create/and/add}{/s}',
            handler: function () {
                me.fireEvent(
                    'create-and-add-document',
                    me,
                    true,
                    me.record.get('id'),
                    me.documentTypeSelection.findRecordByValue(me.documentTypeSelection.getValue()),
                    me.listStore
                );
            },
            menu: [
                {
                    text: '{s name=attachment/panel/split/button/add}{/s}',
                    handler: function() {
                        me.fireEvent(
                            'create-and-add-document',
                            me,
                            false,
                            me.record.get('id'),
                            me.documentTypeSelection.findRecordByValue(me.documentTypeSelection.getValue()),
                            me.listStore
                        );
                    }
                }, {
                    text: '{s name=attachment/panel/split/button/create/and/add}{/s}',
                    handler: function() {
                        me.fireEvent(
                            'create-and-add-document',
                            me,
                            true,
                            me.record.get('id'),
                            me.documentTypeSelection.findRecordByValue(me.documentTypeSelection.getValue()),
                            me.listStore
                        );
                    }
                }
            ]
        });
    },

    /**
     * Renders the given value to the date format
     *
     * @param { string } value
     * @returns { string }
     */
    dateColumnRenderer: function(value) {
        if (!value) {
            return '';
        }

        return Ext.util.Format.date(value);
    },

    /**
     * Renders the name column by the value.
     *
     * @param { number } value
     * @param { object } metaData
     * @param { Ext.data.Model } record
     * @returns { string }
     */
    nameColumnRenderer: function(value, metaData, record) {
        var me = this,
            helper = new Ext.dom.Helper,
            document = me.getRecord(me.documentTypeStore, value),
            name;

        if (!document) {
            return '';
        }

        name = [
            document.get('name'),
            ' ',
            record.get('documentId')
        ].join('');

        var spec = {
            tag: 'a',
            html: name,
            href: '{url action="openPdf"}?id=' + record.get('hash'),
            target: '_blank'
        };

        return helper.markup(spec);
    },

    /**
     * Checks if the record with the given id is selected
     *
     * @param { Ext.data.Model[] } selectedRecords
     * @param { number|string } id
     * @returns { boolean }
     */
    isSelected: function(selectedRecords, id) {
        var isSelected = false;

        Ext.Array.each(selectedRecords, function(selectedRecord) {
            if (selectedRecord.get('id') === id) {
                isSelected = true;
                return false;
            }
        });

        return isSelected;
    },

    /**
     * Selects all documents in the attached property
     */
    preselect: function() {
        var me = this,
            record;

        me.documentTypeSelection.select(me.documentTypeStore.getAt(0));

        if (!me.preSelectedAttachment) {
            return;
        }

        record = me.store.getDocumentById(me.preSelectedAttachment.get('id'));
        if (record) {
            record.set('active', true);
            me.selectDocument(record);
        }
    },

    /**
     * Selects a given record
     *
     * @param { Ext.data.Model } record
     */
    selectDocument: function(record) {
        var me = this;

        me.getSelectionModel().select(
            record,
            true
        );

        me.store.sort();
    },

    /**
     * Tries to find a record in a given store by a given id
     *
     * @param { Ext.data.Store } store
     * @param { number|string } modelId
     * @returns { Ext.data.Model|null }
     */
    getRecord: function(store, modelId) {
        return store.getById(modelId);
    }
});
//{/block}
