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
//{block name="backend/article_list/view/batch_process/grid"}
Ext.define('Shopware.apps.ArticleList.view.BatchProcess.Grid', {
    extend: 'Ext.grid.Panel',


    alias: 'widget.multi-edit-batch-process-grid',

    initComponent: function() {
        var me = this;

        me.operatorStore =  Ext.create('Shopware.apps.ArticleList.store.Operator').load({ params: { resource: 'product' }});

        me.tbar = me.getToolbar();
        me.columns = me.getColumns();

        me.setupRowEditor();

        me.addEvents('editRow', 'deleteRow', 'setEditor', 'addRow');

        me.callParent(arguments);
    },


    /**
     * Creates the grid toolbar
     *
     * @return { Ext.toolbar.Toolbar } grid toolbar
     */
    getToolbar: function() {
        var me = this, buttons = [];

        buttons.push(
            Ext.create('Ext.button.Button', {
                text: '{s name=add}Add{/s}',
                action: 'addRow',
                name: 'addRow',
                iconCls:'sprite-plus-circle-frame',
                handler: function() {
                    me.fireEvent('addRow');
                }
            })
        );

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: buttons
        });
    },

    /**
     * Create and configure the RowEditing feature.
     */
    setupRowEditor: function() {
        var me =this;

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

        // Fire event *after* the user edited the row
        me.rowEditing.on('edit', function(editor, context, eOpts) {
            me.fireEvent('editRow', context.rowIdx);
        });

        // Set the operations before edit
        me.rowEditing.on('beforeedit', function(editor, context, eOpts) {
            var me = this,
                column = context.record.get('column'),
                record = me.editableColumnsStore.findRecord('name', column);

            if (!record) {
                record = me.editableColumnsStore.getAt(0);
            }

            me.setOperatorsForCurrentRecord(record, me.operatorStore);

            me.fireEvent('setEditor', me.columns[2], record);
        }, me);
    },

    getColumns: function()  {
        var me = this;

        return [{
            flex: 1,
            header: '{s name="multiEdit/column"}Column{/s}',
            editor: {
                xtype: 'combo',
                store: me.editableColumnsStore,
                displayField: 'name',
                forceSelection: true,
                allowEmpty: false,
                editable: false,
                listeners: {
                    'select': { fn:function(combo, records, e) {
                        me.setOperatorsForCurrentRecord(records[0], me.operatorStore);
                        me.fireEvent('setEditor', me.columns[2], records[0]);
                    }, scope: this },
                    beforequery: function(e) {
                        e.combo.store.getProxy().extraParams = { resource: 'product'};
                    }
                }
            },
            dataIndex: 'column',
            menuDisabled: true,
            sortable: false
        },{
            flex: 1,
            header: '{s name="multiEdit/operation"}Operation{/s}',
            editor: {
                xtype: 'combo',
                store: me.operatorStore,
                queryMode: 'local',
                displayField: 'name',
                forceSelection: true,
                allowEmpty: false,
                editable: false,
                listeners: {
                }
            },
            dataIndex: 'operator',
            menuDisabled: true,
            sortable: false
        },{
            flex: 1,
            editor: {
                xtype: 'textfield'
            },
            header: '{s name="multiEdit/value"}Value{/s}',
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
     * Show operators depending on the current column
     *
     * @param record
     * @param operatorStore
     */
    setOperatorsForCurrentRecord: function(record, operatorStore) {
        var operators;

        operatorStore.removeAll();

        operators = record.getOperators();
        Ext.each(operators.data.items, function(record) {
            operatorStore.add({ id: record.get('id'), name: record.get('name') });
        });
    }
});
//{/block}
