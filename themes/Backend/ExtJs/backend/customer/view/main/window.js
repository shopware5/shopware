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
 * @package    Customer
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/customer/view/main}

/**
 * Shopware UI - Customer list main window.
 *
 * todo@all: Documentation
 */
//{block name="backend/customer/view/main/window"}
Ext.define('Shopware.apps.Customer.view.main.Window', {
    /**
     * Define that the customer main window is an extension of the enlight application window
     * @string
     */
    extend:'Enlight.app.Window',
    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'customer-list-window',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.customer-list-main-window',
    /**
     * Set no border for the window
     * @boolean
     */
    border:false,
    /**
     * True to automatically show the component upon creation.
     * @boolean
     */
    autoShow:true,
    /**
     * Set border layout for the window
     * @string
     */
    layout:'border',
    /**
     * Define window width
     * @integer
     */
    width:'90%',
    /**
     * Define window height
     * @integer
     */
    height:'90%',
    /**
     * True to display the 'maximize' tool button and allow the user to maximize the window, false to hide the button and disallow maximizing the window.
     * @boolean
     */
    maximizable:true,
    /**
     * True to display the 'minimize' tool button and allow the user to minimize the window, false to hide the button and disallow minimizing the window.
     * @boolean
     */
    minimizable:true,
    /**
     * A flag which causes the object to attempt to restore the state of internal properties from a saved state on startup.
     */
    stateful:true,
    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId:'shopware-customer-main-window',
    /**
     * Set window title which is displayed in the window header
     * @string
     */
    title:'{s name=window_title}Customer list{/s}',

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        toolbar:{
            add:'{s name=toolbar/button_add}Add{/s}',
            remove:'{s name=toolbar/button_delete}Delete all selected{/s}',
            customerGroup:'{s name=toolbar/customer_group}Customer group{/s}',
            groupEmpty:'{s name=toolbar/customer_group_empty}Select...{/s}',
            search:'{s name=toolbar/search_empty_text}Search...{/s}'
        }
    },
    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent:function () {
        var me = this;

        Ext.suspendLayouts();

        me.listStore = Ext.create('Shopware.apps.CustomerStream.store.Preview', {
            pageSize: 10
        }).load({
            conditions: null,
        });
        console.log(me.listStore);

        me.gridPanel = Ext.create('Shopware.apps.Customer.view.list.List', {
            store: me.listStore,
            region: 'center'
        });

        me.streamListing = Ext.create('Shopware.apps.CustomerStream.view.list.CustomerStream', {
            store: Ext.create('Shopware.apps.CustomerStream.store.CustomerStream').load(),
            subApp: me.subApp,
            collapsible: true,
            title: 'Definierte Streams',
            height: 200,
            selectionChanged: function(selModel, selection) {
                if (selection.length <= 0) {
                    me.filterPanel.setValue(null);
                    return;
                }
                var record = selection[0];
                me.streamListing.setLoading(true);
                me.filterPanel.loadRecord(record);
                me.filterPanel.setValue(record.get('conditions'));
                me.streamListing.setLoading(false);
                me.listStore.getProxy().extraParams = record.get('conditions');
                me.listStore.load();
            }
        });

        me.filterPanel = Ext.create('Shopware.apps.CustomerStream.view.detail.ConditionPanel', {
            flex: 4
        });

        me.filterPanel.on('load-preview', function(conditions) {
            if (!me.filterPanel.getForm().isValid()) {
                return;
            }
            me.listStore.getProxy().extraParams = conditions;
            me.listStore.load();
        });

        //add the customer list grid panel and set the store
        me.items = [
            {
                xtype: 'panel',
                region: 'west',
                collapsible: true,
                layout: {
                    type: 'vbox',
                    align: 'stretch'
                },
                width: 300,
                title: 'Filter & Customer Streams',
                items: [
                    me.filterPanel,
                    me.streamListing
                ]
            },
            me.gridPanel
        ];
        me.dockedItems = [ me.getToolbar()];

        Ext.resumeLayouts(true);

        me.callParent(arguments);
    },
    /**
     * Creates the grid toolbar with the add and delete button
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar:function () {
        var me = this;

        me.deleteCustomerButton = Ext.create('Ext.button.Button', {
            iconCls:'sprite-minus-circle-frame',
            text:me.snippets.toolbar.remove,
            disabled:true,
            action:'deleteCustomer'
        });

        var items = me.filterPanel.createToolbarItems();

        /*{if {acl_is_allowed privilege=create}}*/
            items.push({
                iconCls:'sprite-plus-circle-frame',
                text:me.snippets.toolbar.add,
                action:'addCustomer'
            });
        /*{/if}*/

        /*{if {acl_is_allowed privilege=delete}}*/
            items.push(me.deleteCustomerButton);
        /*{/if}*/

        items.push('->');
        items.push({
            xtype:'textfield',
            name:'searchfield',
            cls:'searchfield',
            width:170,
            emptyText:me.snippets.toolbar.search,
            enableKeyEvents:true,
            checkChangeBuffer:500
        });

        items.push({ xtype:'tbspacer', width:6 });

        return Ext.create('Ext.toolbar.Toolbar', {
            dock:'top',
            ui: 'shopware-ui',
            items: items
        });
    },
});
//{/block}
