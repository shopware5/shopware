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
 * @package    Article
 * @subpackage Esd
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Article ESD serials page
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/esd/serials"}
Ext.define('Shopware.apps.Article.view.esd.Serials', {

    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend: 'Ext.grid.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.article-esd-serials',

    /**
     * Set css class
     * @string
     */
    cls: Ext.baseCSSPrefix + 'article-esd-serials',

    /**
     * The view needs to be scrollable
     * @string
     */
    autoScroll: true,

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        title:'{s name=esd/serials/title}Serialnumber Administration{/s}',
        columns:{
            serial:'{s name=esd/serials/column/serial}Serialnumber{/s}',
            assignedCustomer:'{s name=esd/serials/column/assigned_customer}Assigned Customer{/s}',
            assignedDate:'{s name=esd/serialsals/column/assigned_date}Assigned at{/s}',

            remove: '{s name=esd/serials/column/remove}Delete serial{/s}',
            openCustomer: '{s name=esd/serials/column/open_customer}Open Customer{/s}',
            guest: '{s name=esd/serials/column/guest}Guest{/s}'
        },
        toolbar:{
            add:'{s name=esd/serials/toolbar/button_add}Add Serials{/s}',
            remove:'{s name=esd/serials/toolbar/button_delete}Delete selected{/s}',
            search:'{s name=esd/serials/toolbar/search_empty_text}Search...{/s}',
            removeUnused: '{s name=esd/serials/toolbar/button_delete_unused}Delete not assigned Serials{/s}'
        }
    },

    /**
     * Initialize the Shopware.apps.Article.view.esd.Serials and defines the necessary default configuration
     * @return void
     */
    initComponent: function () {
        var me = this;

        me.registerEvents();

        me.title = me.snippets.title;

        me.selModel = me.getGridSelectionModel();

        me.columns = me.getColumns();
        me.toolbar = me.getToolbar();
        me.pagingbar = me.getPagingBar();

        me.dockedItems = [ me.toolbar, me.pagingbar ];

        me.callParent(arguments);
    },

    /**
     * Defines additional events which will be fired from the component
     *
     * @return void
     */
    registerEvents:function () {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the delete button
             * in the toolbar or the action column of the grid
             * @event deleteSerials
             * @param [array] Records - The selected records
             */
            'deleteSerials',

            /**
             * @event addSerials
             */
            'addSerials',

            /**
             * @event deleteUnusedSerials
             */
            'deleteUnusedSerials',

            /**
             * @event searchSerials
             */
            'searchSerials',

            /**
             * @event openCustomer
             * @param [Ext.data.Model] Record - The selected record
             */
            'openCustomer'
        );
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function() {
        var me = this;

        return [
            {
                header: me.snippets.columns.serial,
                dataIndex: 'serialnumber',
                flex: 2
            },
            {
                header: me.snippets.columns.assignedCustomer,
                flex: 2,
                renderer: me.assignedCustomerColumnRenderer
            },
            {
                header: me.snippets.columns.assignedDate,
                xtype: 'datecolumn',
                dataIndex: 'date',
                flex: 1
            },
            {
                /**
                 * Special column type which provides clickable icons in each row
                 */
                xtype: 'actioncolumn',
                width: 70,
                items:[
                    {
                        iconCls: 'sprite-minus-circle-frame',
                        action: 'deleteSerial',
                        tooltip: me.snippets.columns.remove,
                        handler: function (view, rowIndex, colIndex, item, opts, record) {
                            var serials = [ record ];
                            me.fireEvent('deleteSerials', serials);
                        }
                    },
                    {
                        iconCls: 'sprite-user--arrow',
                        action: 'openCustomer',
                        tooltip:  me.snippets.columns.openCustomer,
                        handler: function (view, rowIndex, colIndex, item, opts, record) {
                            me.fireEvent('openCustomer', record);
                        },
                        getClass: function(value, metadata, record) {
                            if (!record.get('customerId')) {
                                return 'x-hidden';
                            }

                            if (record.get('accountMode') !== 0) {
                                return 'x-hidden';
                            }

                        }
                    }
                ]
            }
        ];
    },

    /**
     * Creates the grid selection model for checkboxes
     *
     * @return [Ext.selection.CheckboxModel] grid selection model
     */
    getGridSelectionModel: function() {
        var me = this;

        return Ext.create('Ext.selection.CheckboxModel', {
            listeners:{
                // Unlocks the delete button if the user has checked at least one checkbox
                selectionchange: function(sm, selections) {
                    me.deleteButton.setDisabled(selections.length === 0);
                }
            }
        });
    },

    /**
     * Creates the grid toolbar with the different buttons.
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar: function() {
        var me = this;

        me.deleteButton = Ext.create('Ext.button.Button', {
            iconCls: 'sprite-minus-circle-frame',
            text: me.snippets.toolbar.remove,
            disabled: true,
            action: 'deleteSerials',
            handler: function() {
                var selectionModel = me.getSelectionModel(),
                    records = selectionModel.getSelection();

                if (records.length > 0) {
                    me.fireEvent('deleteSerials', records);
                }
            }
        });

        me.searchField = Ext.create('Ext.form.field.Text', {
            name: 'searchfield',
            cls: 'searchfield',
            width: 170,
            emptyText: me.snippets.toolbar.search,
            enableKeyEvents:true,
            checkChangeBuffer: 500,
            listeners: {
                change: function(field, value) {
                    me.fireEvent('searchSerials', value);
                }
            }
        });

        return {
            xtype: 'toolbar',
            dock: 'top',
            items: [
                {
                    xtype: 'button',
                    text: me.snippets.toolbar.add,
                    iconCls:'sprite-plus-circle-frame',
                    handler: function() {
                        me.fireEvent('addSerials');
                    }
                },
                me.deleteButton,
                { xtype: 'tbseparator' },
                {
                    xtype: 'button',
                    iconCls: 'sprite-minus-circle-frame',
                    text: me.snippets.toolbar.removeUnused,
                    handler: function() {
                        me.fireEvent('deleteUnusedSerials');
                    }
                },
                { xtype: 'tbfill' },
                me.searchField,
                { xtype: 'tbspacer' }
            ]
        };
    },

    /**
     * Creates the paging toolbar for the grid to allow store paging. The paging toolbar uses the same store as the Grid
     *
     * @return [Ext.toolbar.Paging] The paging toolbar for the serials grid
     */
    getPagingBar: function() {
        var me = this;

        return {
            store: me.store,
            xtype: 'pagingtoolbar',
            displayInfo: true,
            dock: 'bottom'
        };
    },

    /**
     * Formats the customer column
     *
     * @param [string] value - Name of the disptach
     * @param [object] metaData - Meta data for this column
     * @param [object] record - current record
     */
    assignedCustomerColumnRenderer: function(value, metaData, record) {
        var me = this;

        if (!record.get('customerId')) {
            return '';
        }

        if (record.get('accountMode') === 0) {
            return record.get('customerEmail');
        } else {
            return Ext.String.format('{literal}{0} ({1})</b>{/literal}', record.get('customerEmail'), me.snippets.guest);
        }
    }
});
//{/block}
