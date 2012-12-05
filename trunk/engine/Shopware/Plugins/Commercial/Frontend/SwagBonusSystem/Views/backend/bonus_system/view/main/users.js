/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @package    BonusSystem
 * @subpackage Main
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     shopware AG
 */

//{namespace name=backend/bonus_system/view/main}
//{block name="backend/bonus_system/view/users"}
Ext.define('Shopware.apps.BonusSystem.view.main.Users', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.bonusSystem-main-users',

    /**
     * Contains all snippets for the component
     * @object
     */
    snippets: {
        search:  '{s name=users/search}search...{/s}',
        tooltip: {
            openCustomer: '{s name=users/tooltip/open_customer}Open customer{/s}'
        },
        column: {
            customernumber: '{s name=users/column/customernumber}Customernumber{/s}',
            email: '{s name=users/column/email}Email{/s}',
            name: '{s name=users/column/name}Name{/s}',
            address: '{s name=users/column/address}Address{/s}',
            points: '{s name=users/column/points}Points{/s}'
        }
    },

    /**
     * Sets up the ui component
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.registerEvents();

        me.editor      = me.getRowEditorPlugin();
        me.plugins     = [ me.editor ];
        me.columns     = me.getColumns();
        me.dockedItems = [ me.getToolbar(), me.getPagingbar() ];

        me.callParent(arguments);
    },

    /**
     * Defines additional events which will be
     * fired from the component
     *
     * @return void
     */
    registerEvents: function () {
        var me = this;

        me.addEvents(
            /**
             * @event saveUser
             * @param [Ext.data.Model] record - The selected record
             */
            'saveUser',

            /**
             * Event that will be fired when the user insert a value into the search field of the toolbar
             * @event searchUser
             * @param [string] searchUser
             */
            'searchUser',

            /**
             * Event will be fired when the user clicks the "open customer" action column icon
             *
             * @event openCustomer
             * @param [Ext.data.Model] - The record of the order position model
             */
            'openCustomer'
        );
    },


    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function () {
        var me = this,
            actionColumItems = [];

        actionColumItems.push({
            iconCls: 'sprite-user--arrow',
            tooltip: me.snippets.tooltip.openCustomer,
            handler: function (view, rowIndex, colIndex, item, opts, record) {
                me.fireEvent('openCustomer', record);
            }
        });

        var columns = [{
            header: me.snippets.column.customernumber,
            dataIndex: 'customernumber',
            flex: 1
        }, {
            header: me.snippets.column.name,
            dataIndex: 'name',
            flex: 1
        }, {
            header: me.snippets.column.email,
            dataIndex: 'email',
            flex: 1
        }, {
            header: me.snippets.column.address,
            dataIndex: 'address',
            flex: 1
        }, {
            header: me.snippets.column.points,
            dataIndex: 'points',
            flex: 1,
            editor: {
                xtype: 'numberfield',
                allowDecimals: false,
                allowBlank: false
            }
        }, {
            /**
             * Special column type which provides
             * clickable icons in each row
             */
            xtype: 'actioncolumn',
            width: actionColumItems.length * 26,
            items: actionColumItems
        }];

        return columns;
    },

    /**
     * Creates the grid toolbar with the add and delete button
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar: function() {
        var me = this;

        var toolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'top',
            ui : 'shopware-ui',
            items: [
                '->',
                {
                    xtype : 'textfield',
                    name : 'searchfield',
                    action : 'searchForms',
                    width: 170,
                    cls: 'searchfield',
                    enableKeyEvents: true,
                    checkChangeBuffer: 500,
                    emptyText : me.snippets.search,
                    listeners: {
                        change: function(field, value) {
                            me.fireEvent('searchUser', value);
                        }
                    }
                }, {
                    xtype: 'tbspacer',
                    width: 6
                }]
        });

        return toolbar;
    },

    /**
     * Creates pagingbar shown at the bottom of the grid
     *
     * @return Ext.toolbar.Paging
     */
    getPagingbar: function () {
        var pagingbar =  Ext.create('Ext.toolbar.Paging', {
            store: this.store,
            dock: 'bottom',
            displayInfo: true
        });

        return pagingbar;
    },

    /**
     * Creates rowEditor Plugin
     *
     * @return [Ext.grid.plugin.RowEditing]
     */
    getRowEditorPlugin: function() {
        var me = this;

        return Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 2,
            errorSummary: false,
            listeners: {
                edit: function(editor, e) {
                    me.fireEvent('saveUser', me, e.record);
                }
            }
        });
    }
});
//{/block}
