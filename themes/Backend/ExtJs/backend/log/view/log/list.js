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
 * @package    Log
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/log/main}

/**
 * Shopware UI - Log view list
 *
 * This grid contains all logs and its information.
 */
//{block name="backend/log/view/log/list"}
Ext.define('Shopware.apps.Log.view.log.List', {

    /**
    * Extend from the standard ExtJS 4
    * @string
    */
    extend: 'Ext.grid.Panel',
    border: 0,

    ui: 'shopware-ui',

    /**
    * Alias name for the view. Could be used to get an instance
    * of the view through Ext.widget('log-main-list')
    * @string
    */
    alias: 'widget.log-main-list',
    /**
    * The window uses a border layout, so we need to set
    * a region for the grid panel
    * @string
    */
    region: 'center',
    /**
    * The view needs to be scrollable
    * @string
    */
    autoScroll: true,

    /**
    * Sets up the ui component
    * @return void
    */
    initComponent: function() {
        var me = this;
        me.registerEvents();
        me.selModel = me.getGridSelModel();
        me.toolbar = me.getToolbar();
        me.columns = me.getColumns();
        me.dockedItems = [];
        me.dockedItems.push(me.toolbar);

        // Add paging toolbar to the bottom of the grid panel
        me.dockedItems.push({
            dock: 'bottom',
            xtype: 'pagingtoolbar',
            displayInfo: true,
            store: me.store
        });
        me.callParent(arguments);
    },

    /**
     * Creates the toolbar
     *
     * @return [object] Ext.toolbar.Toolbar
     */
    getToolbar: function(){
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'top',
            ui: 'shopware-ui',
            items: [
                /*{if {acl_is_allowed privilege=delete}}*/
                {
                    xtype: 'button',
                    iconCls: 'sprite-minus-circle',
                    text: '{s name=toolbar/deleteMarkedEntries}Delete marked entries{/s}',
                    disabled: true,
                    action: 'deleteMultipleLogs'
                },
                /*{/if}*/
                '->',
                {
                    xtype: 'textfield',
                    cls : 'searchfield',
                    width : 170,
                    emptyText : '{s name=toolbar/search}Search...{/s}',
                    enableKeyEvents : true,
                    checkChangeBuffer: 500,
                    listeners: {
                        change: function (field, value) {
                            me.fireEvent('searchLog', value);
                        }
                    }
                }
            ]
        });
    },

    /**
     * Creates the selectionModel of the grid with a listener to enable the delete-button
     */
    getGridSelModel: function(){
        return Ext.create('Ext.selection.CheckboxModel',{
            listeners: {
                selectionchange: function(sm, selections) {
                    var owner = this.view.ownerCt,
                        btn = owner.down('button[action=deleteMultipleLogs]');

                    //If no log is marked
                    if(btn) {
                        btn.setDisabled(selections.length == 0);
                    }
                }
            }
        });
    },

    /**
     *  Creates the columns
     *
     *  @return array columns Contains all columns
     */
    getColumns: function(){
        var me = this;

        var columns = [{
            header: '{s name=grid/column_date}Date{/s}',
            dataIndex: 'date',
            flex: 1,
            xtype: 'datecolumn',
            renderer: me.renderDate
        },{
            header: '{s name=grid/column_user}User{/s}',
            dataIndex: 'user',
            flex: 1
        }, {
            header: '{s name=grid/column_module}Module{/s}',
            dataIndex: 'key',
            flex: 1
        }, {
            header: '{s name=grid/column_text}Text{/s}',
            dataIndex: 'text',
            flex: 1
        }, {
            header: '{s name=grid/actioncolumn}Options{/s}',
            xtype: 'actioncolumn',
            items: [
                /*{if {acl_is_allowed privilege=delete}}*/
                {
                    iconCls:'sprite-minus-circle',
                    action:'deleteColumn',
                    tooltip: '{s name=grid/actioncolumn/buttonTooltip}Delete log{/s}',
                    handler:function (view, rowIndex) {
                        me.fireEvent('deleteColumn', rowIndex);
                    }
                },
                /*{/if}*/
                {
                    iconCls:'sprite-magnifier',
                    action:'openLog',
                    tooltip: '{s name="grid/open_log"}Open log{/s}',
                    handler:function (view, rowIndex, colIndex, item, event, record) {
                        me.fireEvent('openLog', record);
                    }
                }
            ]
        }];

        return columns;
    },

    /**
     * Renders the date
     *
     * @param value
     * @return { String } value Contains the date
     */
    renderDate: function(value){
        return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, timeFormat);
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
             * @event deleteColumn
             * @param { Number } rowIndex - Row index of the selection
             */
            'deleteColumn',

            /**
             * Event will be fired when the user clicks on the magnifier icon
             * in the action column
             *
             * @event openLog
             * @param { Number } rowIndex - Row index of the selection
             */
            'openLog'
        )
    }
});
//{/block}
