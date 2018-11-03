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
 * @package    Order
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/order/main}

/**
 * Shopware UI - Order detail page
 *
 * todo@all: Documentation
 */
//{block name="backend/order/view/detail/position"}
Ext.define('Shopware.apps.Order.view.detail.Position', {

    /**
     * Define that the additional information is an Ext.panel.Panel extension
     * @string
     */
    extend: 'Ext.container.Container',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.order-position-panel',

    /**
     * An optional extra CSS class that will be added to this component's Element.
     */
    cls: Ext.baseCSSPrefix + 'position-panel',

    /**
     * Use border layout
     */
    layout: 'fit',

    /**
     * A shortcut for setting a padding style on the body element. The value can either be a number to be applied to all sides, or a normal css string describing padding.
     */
    bodyPadding: 10,

    /**
     * True to use overflow:'auto' on the components layout element and show scroll bars automatically when necessary, false to clip any overflowing content.
     */
    autoScroll: true,

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        title: '{s name=position/window_title}Positions{/s}',
        add:'{s name=position/button_add}Add{/s}',
        remove:'{s name=position/button_delete}Delete all selected{/s}'
    },


    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @return void
     */
    initComponent:function () {
        var me = this;

        me.registerEvents();
        me.items = [ me.createPositionGrid() ];
        me.title = me.snippets.title;
        me.callParent(arguments);
        me.traceGridEvents();
    },

    /**
     * I tried to trace the events in the detail controller but no event has been fired.
     * @return void
     */
    traceGridEvents: function() {
        var me = this;

        //register listener on the before edit event to set the article name and number manually into the row editor.
        me.rowEditor.on('beforeedit', function(editor, e) {
            me.fireEvent('beforeEdit', editor, e)
        });

        //register listener on the edit event to save the record and convert the price value. Without
        //this listener the insert price "10,55" would be become "1055"
        me.rowEditor.on('edit', function(editor, e) {
            me.articleNumberSearch.getDropDownMenu().hide();
            me.articleNameSearch.getDropDownMenu().hide();

            me.fireEvent('savePosition', editor, e, me.record, {
                callback: function(order) {
                    me.fireEvent('updateForms', order, me.up('window'));
                }
            })
        });

        //register listener on the canceledit event to remove new order positions.
        me.rowEditor.on('canceledit', function(grid, eOpts) {
            me.fireEvent('cancelEdit', grid, eOpts)
        });

        me.articleNumberSearch.on('valueselect', function(field, value, hiddenValue, record) {
            me.fireEvent('articleNumberSelect', me.rowEditor, value, record)
        });
        me.articleNameSearch.on('valueselect', function(field, value, hiddenValue, record) {
            me.fireEvent('articleNameSelect', me.rowEditor, value, record)
        });

        me.on('canceledit', function() {
            me.articleNumberSearch.getDropDownMenu().hide();
            me.articleNameSearch.getDropDownMenu().hide();
        }, me);
    },


    /**
     * Defines additional events which will be
     * fired from the component
     *
     * @return void
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the add button to add an order position.
             *
             * @event addPosition
             * @param [Ext.data.Model] record - The record of the detail page
             * @param [Ext.grid.Panel] grid - The order position grid of the detail page
             * @param [object] editor - Ext.grid.plugin.RowEditing
             */
            'addPosition',

            /**
             * Event will be fired when the user clicks the remove button to remove all selected order positions.
             *
             * @event deleteMultiplePositions
             * @param [Ext.data.Model] record - The record of the detail page
             * @param [Ext.grid.Panel] grid - The order position grid of the detail page
             */
            'deleteMultiplePositions',

            /**
             * Event will be fired when the user start the editing of the order position grid
             *
             * @event beforeEdit
             * @param [Ext.grid.plugin.Editing] - The row editor
             * @param [object]  - An edit event with the following properties:
             *   grid - The grid this editor is on
             *   view - The grid view
             *   store - The grid store
             *   record - The record being edited
             *   row - The grid table row
             *   column - The grid Column defining the column that initiated the edit
             *   rowIdx - The row index that is being edited
             *   colIdx - The column index that initiated the edit
             *   cancel - Set this to true to cancel the edit or return false from your handler.
             */
            'beforeEdit',

            /**
             * Event will be fired when the user adds a new order position and before he save
             * this position he clicks the cancel button.
             *
             * @event cancelEdit
             * @param [Ext.data.Store] - The position store
             * @param [Ext.data.Model] - The edited record
             */
            'cancelEdit',

            /**
             * Event will be fired when the user clicks the update button of the row editor.
             *
             * @event edit
             * @param [Ext.grid.plugin.Editing] - The row editor
             * @param [object]  - An edit event with the following properties:
             *   grid - The grid this editor is on
             *   view - The grid view
             *   store - The grid store
             *   record - The record being edited
             *   row - The grid table row
             *   column - The grid Column defining the column that initiated the edit
             *   rowIdx - The row index that is being edited
             *   colIdx - The column index that initiated the edit
             *   cancel - Set this to true to cancel the edit or return false from your handler.
             */
            'savePosition',

            /**
             * Event will be fired when the user search for an article name in the row editor
             * and selects an article in the drop down menu.
             *
             * @event articleNameSelect
             * @param [object] editor - Ext.grid.plugin.RowEditing
             * @param [string] value - Value of the Ext.form.field.Trigger
             * @param [object] record - Selected record
             */
            'articleNameSelect',

            /**
             * Event will be fired when the user search for an article number in the row editor
             * and selects an article in the drop down menu.
             *
             * @event articleNameSelect
             * @param [object] editor - Ext.grid.plugin.RowEditing
             * @param [string] value - Value of the Ext.form.field.Trigger
             * @param [object] record - Selected record
             */
            'articleNumberSelect',


            /**
             * Event will be fired when the user clicks the "Save button" button.
             *
             * @event
             * @param [Ext.data.Model]    record - The current form record
             * @param [Ext.window.window] window - The detail window
             */
            'updateForms'

        );
    },

    /**
     * Creates the position grid for the position tab panel.
     * The position grid is already defined in backend/order/view/list/position.js.
     * The grid in the position tab is an small extension of the original grid.
     *
     * @return Ext.grid.Panel
     */
    createPositionGrid: function() {
        var me = this;

        me.rowEditor = Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToMoveEditor: 2,
            autoCancel: true
        });

        me.orderPositionGrid = Ext.create('Shopware.order.position.grid', {
            name: 'order-position-grid',
            store: me.record.getPositions(),
            plugins: [me.rowEditor, {
                ptype: 'grid-attributes',
                table: 's_order_details_attributes',
                createActionColumn: false
            }],
            style: {
                borderTop: '1px solid #A4B5C0'
            },
            viewConfig: {
                enableTextSelection: false
            },
            tbar: me.createGridToolbar(),
            selModel: me.getGridSelModel(),
            getColumns: function() {
                return me.getColumns(this);
            }
        });

        return me.orderPositionGrid;
    },

    /**
     * Overrides the getColumns function of the order position grid which is defined in view/list/position.js
     */
    getColumns:function (grid) {
        var me = this;

        me.articleNumberSearch = me.createArticleSearch('number', 'name', 'articleNumber');
        me.articleNameSearch = me.createArticleSearch('name', 'number', 'articleName');
        grid.taxStore = me.taxStore;
        grid.statusStore = me.statusStore;

        return [
            {
                header: grid.snippets.articleNumber,
                dataIndex: 'articleNumber',
                flex:2,
                editor: me.articleNumberSearch
            },
            {
                header: grid.snippets.articleName,
                dataIndex: 'articleName',
                flex:2,
                editor: me.articleNameSearch
            },
            {
                header: grid.snippets.quantity,
                dataIndex: 'quantity',
                flex:1,
                editor: {
                    xtype: 'numberfield',
                    allowBlank: false,
                    minValue: 0
                }
            },
            {
                header: grid.snippets.price,
                dataIndex: 'price',
                flex:1,
                renderer: grid.priceColumn,
                editor: {
                    xtype: 'numberfield',
                    allowBlank: false,
                    decimalPrecision: 2
                }
            },
            {
                header: grid.snippets.total,
                dataIndex: 'total',
                flex:1,
                renderer: grid.totalColumn
            },
            {
                header: grid.snippets.status,
                dataIndex: 'statusId',
                flex: 2,
                renderer: me.statusColumn,
                editor: {
                    allowBlank: false,
                    editable: false,
                    xtype: 'combobox',
                    queryMode: 'local',
                    store: grid.statusStore ,
                    displayField: 'description',
                    valueField: 'id'
                }
            },
            {
                header: grid.snippets.tax,
                dataIndex: 'taxId',
                flex:1,
                renderer: me.taxColumn,
                editor: {
                    xtype: 'combobox',
                    editable: false,
                    queryMode: 'local',
                    allowBlank: false,
                    store: grid.taxStore,
                    displayField: 'name',
                    valueField: 'id'
                }
            },
            {
                header: grid.snippets.inStock,
                dataIndex: 'inStock',
                flex:1
            },
            {
                /**
                 * Special column type which provides
                 * clickable icons in each row
                 */
                xtype:'actioncolumn',
                width:90,
                items:[
                    /*{if {acl_is_allowed privilege=update}}*/
                        {
                            iconCls:'sprite-minus-circle-frame',
                            action:'deletePosition',
                            tooltip: grid.snippets.deletePosition,
                            /**
                             * Add button handler to fire the deleteOrder event which is handled
                             * in the list controller.
                             */
                            handler:function (view, rowIndex, colIndex, item) {
                                var store = view.getStore(),
                                    position = store.getAt(rowIndex);

                                grid.fireEvent('deletePosition', position, store, {
                                    callback: function(order) {
                                        me.fireEvent('updateForms', order, me.up('window'));
                                    }
                                });
                            }
                        },
                    /*{/if}*/
                    {
                        iconCls:'sprite-inbox',
                        action:'openArticle',
                        tooltip: grid.snippets.openArticle,
                        /**
                         * Add button handler to fire the openCustomer event which is handled
                         * in the list controller.
                         */
                        handler:function (view, rowIndex, colIndex, item) {
                            var store = view.getStore(),
                                record = store.getAt(rowIndex);

                            grid.fireEvent('openArticle', record);
                        },
                        getClass: function(value, metadata, record) {
                             if (!record.get('articleId') || record.get('mode') !== 0)  {
                                 return 'x-hidden';
                             }
                        }
                    }, {
                        iconCls: 'sprite-attributes',
                        name: 'grid-attribute-plugin',
                        handler: function (view, rowIndex, colIndex, item, opts, record) {
                            me.attributeActionColumnClick(record);
                        },
                        getClass: me.attributeColumnRenderer,
                        scope: grid
                    }
                ]
            }
        ];

    },

    /**
     * @param record - Ext.data.Model
     */
    attributeActionColumnClick: function(record) {
        var me = this;

        me.attributeWindow = Ext.create('Shopware.attribute.Window', {
            table: 's_order_details_attributes',
            record: record
        });
        me.attributeWindow.show();
    },

    /**
     *
     * @param value - mixed
     * @param meta - Object
     * @param record - Ext.data.Model
     * @returns { string }
     */
    attributeColumnRenderer: function(value, meta, record) {
        if (!record.get('id') || !this.backendAttributes || this.backendAttributes.length <= 0) {
            return 'x-hidden';
        }
    },

    /**
     *
     * @param returnValue
     * @param hiddenReturnValue
     * @param name
     * @return Shopware.form.field.ArticleSearch
     */
    createArticleSearch: function(returnValue, hiddenReturnValue, name ) {
        return Ext.create('Shopware.form.field.ArticleSearch', {
            name: name,
            returnValue: returnValue,
            hiddenReturnValue: hiddenReturnValue,
            articleStore: Ext.create('Shopware.apps.Base.store.Variant'),
            allowBlank: false,
            getValue: function() {
                return this.getSearchField().getValue();
            },
            setValue: function(value) {
                this.getSearchField().setValue(value);
            }
        });
    },

    /**
     * Creates the toolbar for the position grid.
     * @return Ext.toolbar.Toolbar
     */
    createGridToolbar: function() {
        var me = this;

        me.deletePositionsButton = Ext.create('Ext.button.Button', {
            iconCls:'sprite-minus-circle-frame',
            text:me.snippets.remove,
            disabled:true,
            action:'deletePosition',
            handler: function() {
                me.fireEvent('deleteMultiplePositions', me.record, me.orderPositionGrid, {
                    callback: function(order) {
                        me.fireEvent('updateForms', order, me.up('window'));
                    }
                });
            }
        });

        me.addPositionButton = Ext.create('Ext.button.Button', {
            iconCls:'sprite-plus-circle-frame',
            text:me.snippets.add,
            action:'addPosition',
            handler: function() {
                me.fireEvent('addPosition', me.record, me.orderPositionGrid, me.rowEditor)
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            dock:'top',
            ui: 'shopware-ui',
            items:[
                me.addPositionButton,
                /*{if {acl_is_allowed privilege=save}}*/
                    me.deletePositionsButton
                /*{/if}*/
            ]
        });
    },

    /**
     * Creates the grid selection model for checkboxes
     *
     * @return [Ext.selection.CheckboxModel] grid selection model
     */
    getGridSelModel:function () {
        var me = this;

        var selModel = Ext.create('Ext.selection.CheckboxModel', {
            listeners:{
                // Unlocks the save button if the user has checked at least one checkbox
                selectionchange:function (sm, selections) {
                    me.deletePositionsButton.setDisabled(selections.length === 0);
                }
            }
        });
        return selModel;
    },

    /**
     * Render function for the tax column. The function parameter contains the tax id which is used
     * to get the tax record of the grid tax store.
     *
     * @param value
     * @return string
     */
    taxColumn: function(value, metaData, rowRecord) {
        var me = this;

        if (value === Ext.undefined) {
            return value;
        }
        var record =  me.taxStore.getById(value);
        if (record instanceof Ext.data.Model) {
            return record.get('name');
        } else {
            // SW-3289);
            if(value == 0 || value == null) {
                return rowRecord.get('taxRate')+'%';
            }
            return value;
        }
    },

    /**
     * Render function for the status column. The function parameter contains the status id which is used
     * to get the status record of the grid status store.
     * @param value
     * @return string
     */
    statusColumn: function(value) {
        var me = this, record;

        if (value === Ext.undefined) {
            return value;
        }
        record =  me.statusStore.getById(value);
        if (record instanceof Ext.data.Model) {
            return record.get('description');
        } else {
            return value;
        }
    }
});
//{/block}
