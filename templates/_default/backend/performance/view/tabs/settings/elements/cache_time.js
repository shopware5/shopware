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
 * @package    Customer
 * @subpackage Order
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/performance/main}

/**
 * Shopware UI - Customer list backend module
 *
 * todo@all: Documentation
 */
//{block name="backend/performance/view/tabs/settings/elements/cache_time"}
Ext.define('Shopware.apps.Performance.view.tabs.settings.elements.CacheTime', {
    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend:'Ext.grid.Panel',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.performance-tabs-settings-elements-cache-time',

    /**
     * The view needs to be scrollable
     * @string
     */
    autoScroll:true,

    /**
     * Initialize the Shopware.apps.Customer.view.main.List and defines the necessary
     * default configuration
     * @return void
     */
    initComponent:function () {
        var me = this;

        me.columns = me.getColumns();
        me.toolbar = me.getToolbar();
        me.pagingbar = me.getPagingBar();
        me.dockedItems = [ me.toolbar, me.pagingbar ];
        me.callParent(arguments);
    },

    /**
     * Registers the "openOrder" event which is fired when
     * the user clicks on the action column of the grid
     * to open the order in a new window.
     * @return void
     */
    registerEvents:function () {
        this.addEvents(
        );
    },

    /**
     * Creates the paging toolbar for the customer grid to allow
     * and store paging. The paging toolbar uses the same store as the Grid
     *
     * @return [Ext.toolbar.Paging] - The paging toolbar for the customer grid
     */
    getPagingBar:function () {
        var me = this;

        return Ext.create('Ext.toolbar.Paging', {
            store:me.store,
            dock:'bottom',
            displayInfo:true
        });
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns:function () {
        var me = this;

        return [
            {
                header: 'controller',
                dataIndex: 'controller',
                flex: 2
            },
            {
                header: 'time',
                dataIndex: 'time',
                flex: 1
            },
            {
                /**
                 * Special column type which provides
                 * clickable icons in each row
                 */
                xtype:'actioncolumn',
                width:50,
                items:[
                    {
                        cls:'delteBtn',
                        iconCls:'sprite-minus-circle-frame',
                        action:'http-cache-delete-controller-time',
                        tooltip:'Delete row',
                        /**
                         * Add button handler to fire the generatePassword event which is handled
                         * in the detail controller. The detail controller generates a password and set it into the password field
                         */
                        handler:function (view, rowIndex, colIndex, item) {
                            var store = view.getStore(),
                                    record = store.getAt(rowIndex);

                            me.fireEvent('deleteRow', record);
                        }
                    }
                ]
            }
        ];

    },

    /**
     * Creates the grid toolbar with the add and delete button
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar:function () {
        var me = this;
        return Ext.create('Ext.toolbar.Toolbar', {
            dock:'top',
            ui: 'shopware-ui',
            cls: Ext.baseCSSPrefix + 'order-list-toolbar',
            items:[
                '->',
                {
                    xtype:'textfield',
                    name:'searchfield',
                    cls:'searchfield',
                    width:170,
                    emptyText:me.snippets.search,
                    enableKeyEvents:true,
                    checkChangeBuffer:500,
                    listeners: {
                        change: function(field, value) {
                            me.fireEvent('searchOrder', value, me.gridStore);
                        }
                    }
                },
                { xtype:'tbspacer', width:6 }
            ]
        });
    }

});
//{/block}
