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
 * @package    Workshop
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/workshop/view/main}

/**
 * Shopware UI - Customer list backend module
 *
 * todo@all: Documentation
 */
Ext.define('Shopware.apps.Workshop.view.user.List', {

    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend:'Ext.grid.Panel',

    /**
     * Use Shopware ui
     */
    ui:'default',

    /**
     * Alias name for the view. Could be used to get an instance
     * of the view through Ext.widget('customer-list')
     * @string
     */
    alias:'widget.workshop-user-list',

    /**
     * The window uses a border layout, so we need to set
     * a region for the grid panel
     * @string
     */
    region:'center',

    /**
     * The view needs to be scrollable
     * @string
     */
    autoScroll:true,

    /**
     * Initialize the Shopware.apps.Customer.view.main.List and defines the necessary
     * default configuration
     */
    initComponent:function () {
        var me = this;

        me.store = me.userStore;
        me.selModel = me.getGridSelModel();
        me.columns = me.getColumns();
        me.toolbar = me.getToolbar();
        me.pagingbar = me.getPagingBar();
        me.dockedItems = [ me.toolbar, me.pagingbar ];
        me.callParent(arguments);
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns:function () {
        var me = this;

        var columns = [
            {
                header: '{s name=column/name}User name{/s}',
                dataIndex:'name',
                flex:1
            },
            {
                header: '{s name=column/resource}Resource{/s}',
                dataIndex:'resourceName',
                flex:1
            },
            {
                header: '{s name=column/privilege}Privilege{/s}',
                dataIndex:'privilegeName',
                flex:1
            }
        ];
        return columns;
    },

    /**
     * Creates the grid selection model for checkboxes
     *
     * @return [Ext.selection.CheckboxModel] grid selection model
     */
    getGridSelModel:function () {
        var selModel = Ext.create('Ext.selection.CheckboxModel', {
            listeners:{
                // Unlocks the save button if the user has checked at least one checkbox
                selectionchange:function (sm, selections) {
                    var owner = this.view.ownerCt,

                    btn = owner.down('button[action=deleteUser]');

                    btn.setDisabled(selections.length == 0);
                }
            }
        });
        return selModel;
    },


    /**
     * Creates the grid toolbar with the add and delete button
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar:function () {
        return Ext.create('Ext.toolbar.Toolbar',
            {
                dock:'top',
                items:[
                    {
                        iconCls:'add',
                        text: '{s name=toolbar/button_add}Add{/s}',
                        action:'addUser'
                    } ,
                    {
                        iconCls:'delete',
                        text: '{s name=toolbar/button_delete}Delete all selected{/s}',
                        disabled:true,
                        action:'deleteUser'
                    },
                    '->',
                    {
                        xtype:'textfield',
                        name:'searchfield',
                        cls:'searchfield',
                        width:170,
                        emptyText: '{s name=toolbar/search_empty_text}Search...{/s}',
                        enableKeyEvents:true,
                        checkChangeBuffer:500
                    }
                ]
            });
    },

    /**
     * Creates the paging toolbar for the customer grid to allow
     * and store paging. The paging toolbar uses the same store as the Grid
     *
     * @return Ext.toolbar.Paging The paging toolbar for the customer grid
     */
    getPagingBar: function () {
        var me = this;

        return Ext.create('Ext.toolbar.Paging', {
            store:me.userStore,
            dock:'bottom',
            displayInfo:true
        });
    }

});

