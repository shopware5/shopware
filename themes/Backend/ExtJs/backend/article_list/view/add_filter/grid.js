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

//{namespace name=backend/article_list/main}
//{block name="backend/article_list/view/add_filter/grid"}
Ext.define('Shopware.apps.ArticleList.view.AddFilter.Grid', {
    extend: 'Ext.grid.Panel',

    alias: 'widget.multi-edit-add-filter-grid',

    style: 'border: 0;',

    /**
     * Constructor of the Ext.grid.Panel
     */
    initComponent: function() {
        var me = this;

        me.tbar = me.getToolbar();
        me.columns = me.getColumns();
        me.bbar = me.getBottomToolbar();

        me.setupRowEditor();

        me.addEvents(
            /**
             * Called when a row was edited
             */
            'editRow',

            /**
             * Called when a row is deleted
             */
            'deleteRow',

            /**
             * Called when an editor needs to be set
             */
            'setEditor'
        );

        me.callParent(arguments);
    },

    /**
     * Setup the rowEditing plugin
     */
    setupRowEditor: function() {
        var me = this;

        me.rowEditing = Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 1
        });
        me.plugins = me.rowEditing;

        // Do not edit the row if the actioncolumn was clicked
        me.rowEditing.on('beforeedit', function(editor, context, eOpts) {
            if (context.colIdx > 2) {
                context.cancel = true;
                return;
            }
        });

        me.rowEditing.on('edit', function(editor, context, eOpts) {
            me.fireEvent('editRow', context.rowIdx);
        });

        // Set the operations before edit
        me.rowEditing.on('beforeedit', function(editor, context, eOpts) {
            var me = this,
                column = context.record.get('column'),
                colRecord = me.columnStore.findRecord('name', column);

            if (!colRecord) {
                colRecord = me.columnStore.getAt(0);
            }

            me.setOperatorsForCurrentRecord(colRecord, me.operatorStore);

            me.fireEvent('setEditorBeforeEdit', me.columns, context.record);
        }, me);
    },

    /**
     * Creates the grid toolbar
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar: function() {
        var me = this, buttons = [];

        buttons.push(
            Ext.create('Ext.button.Button', {
                text: '{s name=add}Add{/s}',
                action: 'addSimpleFilter',
                name: 'addSimpleFilter',
                iconCls:'sprite-plus-circle-frame',
                handler: function() {
                    me.fireEvent('addSimpleFilter');
                }
            })
        );


        buttons.push('->');

        buttons.push({
            xtype: 'button',
            text: '{s name=addFilter/run}Execute{/s}',
            name: 'run-button-simple',
            iconCls: 'sprite-magnifier--arrow',
            tooltip: '{s name=runFilter}Immediately show matching articles{/s}',
            disabled: true,
            handler: function () {
                me.fireEvent('filter');
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: buttons
        });
    },

    /**
     * Creates the grid botoom toolbar
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getBottomToolbar: function() {
        var me = this, buttons = [];

        buttons.push(
            Ext.create('Ext.form.Label', {
                name: 'status-label-simple',
                text: ''
            })
        );

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: buttons
        });
    },

    /**
     * Return columns for the grid panel
     *
     * @returns Array
     */
    getColumns: function()  {
        var me = this;

        return [{
            flex: 1,
            header: 'Column',
            editor: {
                xtype: 'combo',
                store: me.columnStore,
                displayField: 'name',
                forceSelection: true,
                editable: false,
                queryMode: 'local',
                listeners: {
                    'select': { fn:function(combo, records, e) {
                        me.fireEvent('setEditor', me.columns, records[0], 'column');

                        me.setOperatorsForCurrentRecord(records[0], me.operatorStore);
                    }, scope: this }
                }
            },
            dataIndex: 'column',
            menuDisabled: true,
            sortable: false
        },{
            flex: 1,
            header: 'Operator',
            editor: me.getDefaultOperatorEditor(),
            dataIndex: 'operator',
            menuDisabled: true,
            sortable: false
        },{
            flex: 1,
            editor: me.getDefaultValueEditor(),
            header: 'Value',
            dataIndex: 'value',
            menuDisabled: true,
            sortable: false
        }, {
            /**
             * Special column type which provides
             * clickable icons in each row
             */
            xtype: 'actioncolumn',
            width: 25,
            items: [
                {
                    iconCls: 'sprite-minus-circle-frame',
                    action: 'deleteFilter',
                    tooltip: '{s name=deleteFilter}Delete filter{/s}',
                    handler: function (view, rowIndex, colIndex, item, e) {
                        me.fireEvent('deleteRow', rowIndex);
                    }
                }
            ]
        }];
    },

    /**
     * Returns the default editor for the operator column
     *
     * @returns Ext.form.field.Combo
     */
    getDefaultOperatorEditor: function() {
        var me = this;

        return Ext.create('Ext.form.field.ComboBox', {
            xtype: 'combo',
            queryMode: 'local',
            editable: false,
            store: me.operatorStore,
            displayField: 'name',
            forceSelection: true,
            listeners: {
                'select': { fn:function(combo, records, e) {
                    me.fireEvent('setEditor', me.columns, records[0], 'operator');
                }, scope: this }
            }
        });
    },

    /**
     * Returns the default editor for the value column
     *
     * @returns Ext.form.field.Text
     */
    getDefaultValueEditor: function() {
        var me = this;

        Ext.define('Post', {
            extend: 'Ext.data.Model',
            fields: [
                { name: 'id' },
                { name: 'title'},
                { name: 'addQuotes', defaultValue: false}
            ]
        });

        me.valueStore = Ext.create('Ext.data.Store', {
            pageSize: 10,
            model: 'Post',
            remoteFilter: true,
            proxy: {
                type: 'ajax',
                url: '{url controller="ArticleList" action="getValues"}',
                reader: {
                    type: 'json',
                    root:'data',
                    totalProperty: 'total'
                }
            }
        });

        return Ext.create('Ext.form.field.ComboBox', {
            queryMode: 'remote',
            queryCaching: true,
            store: me.valueStore,
            displayField: 'title',
            valueField: 'title',
            pageSize: 10,

            listeners: {
                beforequery: function(e) {
                    var attribute = e.combo.ownerCt.form._fields.items[0].rawValue;
                    var operator = e.combo.ownerCt.form._fields.items[1].rawValue;

                    e.combo.store.getProxy().extraParams = { resource: 'product', attribute: attribute, operator: operator};
                }
            }
        });
    },

    /**
     * Show operators depending on the current column
     *
     * @param record
     * @param operatorStore
     */
    setOperatorsForCurrentRecord: function(record, operatorStore) {
        var me = this,
            i, operators;

        operatorStore.removeAll();

        operators = me.filterableColumns[record.get('name')];
        Ext.each(operators, function(operator) {
            if (operator != 'IN' && operator != 'NOT IN' ) {
                operatorStore.add({ name: operator });
            }
        });
    }

});
//{/block}
