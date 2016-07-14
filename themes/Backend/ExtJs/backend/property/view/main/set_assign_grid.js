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
 * @package    Property
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/property/view/main}
//{block name="backend/Property/view/main/set_assign_grid"}
Ext.define('Shopware.apps.Property.view.main.SetAssignGrid', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.property-main-setAssignGrid',
    addBtn: null,

    internalName: 'setAssignGrid',
    title: '{s name=set/assign/grid_title}Group Assignment{/s}',
    sortableColumns: false,

    /**
     * Contains all snippets for the controller
     * @object
     */
    snippets: {
        columnName:          '{s name=set/assign/column_name}Name{/s}',
        tooltipDeleteAssignment: '{s name=set/assign/tooltip_delete_value}Delete assignment{/s}',
        dragText:            '{s name=set/assign/drag_text}Drag and drop to reorganize{/s}'
    },

    /**
     * Sets up the ui component
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.registerEvents();

        me.viewConfig = {
            plugins: {
                ptype: 'gridviewdragdrop',
                ddGroup: 'set-assignment-grid-dd',
                dragText: me.snippets.dragText
            }
        };

        me.store = me.setAssignStore;

        me.tbar     = me.getToolbar();
        me.columns  = me.getColumns();

        me.callParent(arguments);
    },

    /**
     * Defines additional events which will be
     * fired from the component
     *
     * @return void
     */
    registerEvents:function () {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the delete icon in the
             * action column
             *
             * @event deleteOption
             * @param [object] record
             * @param [object] grid - Associated Ext.view.Table
             */
            'deleteAssignment'
        );
    },


    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function() {
        var me = this;

        var columns = [
            {
                header: me.snippets.columnName,
                dataIndex: 'name',
                renderer: 'htmlEncode',
                flex: 2,
                editor: {
                    allowBlank: false
                }
            },
            {
                xtype: 'actioncolumn',
                width: 24,
                hideable: false,
                items: [
                    {
                        iconCls: 'sprite-minus-circle-frame',
                        action: 'delete',
                        cls: 'delete',
                        tooltip: me.snippets.tooltipDeleteAssignment,
                        handler: function (grid, rowIndex) {
                            var record = grid.getStore().getAt(rowIndex);

                            me.fireEvent('deleteAssignment', record, grid);
                        }
                    }
                ]
            }
        ];

        return columns;
    },

    /**
     * Creates the grid toolbar
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar: function() {
        var me      = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: [
                { xtype:'tbspacer', height:30 }
            ]
        });
    }

});
//{/block}
