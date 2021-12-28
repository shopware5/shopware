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

//{namespace name="backend/performance/sitemap"}
//{block name="backend/performance/view/tabs/settings/elements/sitemap_custom_urls"}
Ext.define('Shopware.apps.Performance.view.tabs.settings.elements.SitemapCustomUrls', {

    extend: 'Ext.grid.Panel',

    alias: 'widget.performance-tabs-settings-elements-sitemap-custom-urls',

    name: 'custom_urls',

    height: 250,

    initComponent: function () {
        this.plugins = [
            this.createRowEditingPlugin(),
            this.createHeaderToolTipPlugin()
        ];

        this.columns = this.createColumns();

        this.dockedItems = this.createDockedItems();

        this.selModel = this.createSelectionModel();

        this.callParent(arguments);
    },

    /**
     * @returns { Object[] }
     */
    createColumns: function () {
        return [
            {
                header: '{s name="customUrl/column/url"}URL{/s}',
                dataIndex: 'url',
                flex: 10,
                editor: {
                    xtype: 'textfield',
                    vtype: 'url'
                },
                tooltip: '{s name="customUrl/column/url/toolTip"}URLs have to start with http(s)://{/s}',
            },
            {
                header: '{s name="customUrl/column/priority"}Priority{/s}',
                dataIndex: 'priority',
                flex: 5,
                editor: {
                    xtype: 'numberfield',
                    minValue: 0
                }
            },
            this.createChangeFrequencyColumn(),
            {
                header: '{s name="customUrl/column/lastMod"}Last modification{/s}',
                dataIndex: 'lastMod',
                flex: 8
            },
            this.createShopColumn(),
            {
                xtype: 'actioncolumn',
                items: [ this.createRowDeleteButton() ],
                flex: 4
            }
        ];
    },

    /**
     * @return { Ext.selection.CheckboxModel }
     */
    createSelectionModel: function() {
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

    createChangeFrequencyColumn: function () {
        this.changeFrequencyEditor = {
            xtype: 'combobox',
            displayField: 'name',
            valueField: 'id',
            store: this.createChangeFrequencyStore()
        };

        return {
            header: '{s name="customUrl/column/changeFreq"}Change frequency{/s}',
            dataIndex: 'changeFreq',
            flex: 8,
            editor: this.changeFrequencyEditor,
            renderer: this.renderChangeFrequency
        };
    },

    createShopColumn: function () {
        this.shopEditor = {
            xtype: 'combobox',
            store: Ext.create('Shopware.apps.Base.store.Shop').load(),
            displayField: 'name',
            valueField: 'id',
            getSubmitData: function () {
                return this.getModelData();
            }
        };

        return {
            header: '{s name="customUrl/column/shop"}Shop{/s}',
            dataIndex: 'shopId',
            editor: this.shopEditor,
            renderer: this.renderShopId,
            flex: 6
        }
    },

    /**
     * @returns { Ext.grid.plugin.RowEditing }
     */
    createRowEditingPlugin: function () {
        return Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 1,
            keepExisting: true,
            errorSummary: false,
        })
    },

    /**
     * @returns { Shopware.grid.HeaderToolTip }
     */
    createHeaderToolTipPlugin: function() {
        return Ext.create('Shopware.grid.HeaderToolTip', {
            showIcons: true
        });
    },

    createRowDeleteButton: function () {
        var me = this;

        return {
            iconCls: 'sprite-minus-circle-frame',
            handler: function (view, rowIndex, colIndex, item, opts, record) {
                me.store.remove(record);
            }
        }
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
                    text: '{s name="customUrl/button/createNewEntry"}Create new entry{/s}',
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
        this.deleteButton = Ext.create('Ext.button.Button', {
            text: '{s name="customUrl/button/deleteSelectedEntries"}Delete selected entries{/s}',
            iconCls: 'sprite-minus-circle',
            disabled: true,
            handler: Ext.bind(this.deleteSelectedRecords, this)
        });

        return this.deleteButton;
    },

    deleteSelectedRecords: function () {
        var selectionModel = this.selModel,
            records = selectionModel.getSelection();

        if (records.length > 0) {
            this.store.remove(records);
        }
    },

    createNewEntry: function () {
        var newModel = Ext.create(this.store.model);

        this.store.add(newModel);
    },

    /**
     * @param { string } value
     * @returns { string }
     */
    renderShopId: function (value) {
        if (value === null) {
            return '{s name="renderer/all"}All{/s}';
        }

        return this.shopEditor.store.findRecord('id', value).get('name');
    },

    /**
     * @param { string } value
     * @returns { string }
     */
    renderChangeFrequency: function (value) {
        return this.changeFrequencyEditor.store.findRecord('id', value).get('name');
    },

    createChangeFrequencyStore: function () {
        return Ext.create('Ext.data.Store', {
            fields: this.getChangeFrequencyFields(),
            data: this.getChangeFrequencies(),
            allowBlank: false
        });
    },

    getChangeFrequencies: function () {
        return [
            { 'id': 'always', name: '{s name="customUrl/changeFreq/always"}Always{/s}' },
            { 'id': 'hourly', name: '{s name="customUrl/changeFreq/hourly"}Hourly{/s}' },
            { 'id': 'daily', name: '{s name="customUrl/changeFreq/daily"}Daily{/s}' },
            { 'id': 'weekly', name: '{s name="customUrl/changeFreq/weekly"}Weekly{/s}' },
            { 'id': 'monthly', name: '{s name="customUrl/changeFreq/monthly"}Monthly{/s}' },
            { 'id': 'yearly', name: '{s name="customUrl/changeFreq/yearly"}Yearly{/s}' },
            { 'id': 'never', name: '{s name="customUrl/changeFreq/never"}Never{/s}' },
        ];
    },

    /**
     * @returns { Array }
     */
    getChangeFrequencyFields: function () {
        return [
            { name: 'name', type: 'string' },
        ];
    },
});
//{/block}
