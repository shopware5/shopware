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
 * @package    Notification
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/notification/view/main}

/**
 * Shopware UI - notification customer list.
 *
 * Displays the notification customer list
 */
//{block name="backend/notification/view/notification/customer"}
Ext.define('Shopware.apps.Notification.view.notification.Customer', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.notification-notification-customer',
    autoScroll:true,
    ui:'shopware-ui',
    /**
     * Initialize the Shopware.apps.Customer.view.main.List and defines the necessary
     * default configuration
     */
    initComponent:function () {
        var me = this;

        me.registerEvents();
        me.columns = me.getColumns();
        me.toolbar = me.getToolbar();
        me.pagingbar = me.getPagingBar();
        me.store = me.customerStore;
        me.dockedItems = [ me.pagingbar, me.toolbar ];
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
                 * Event will be fired when the user clicks the customer icon in the
                 * action column
                 *
                 * @event deleteColumn
                 * @param [object] View - Associated Ext.view.Table
                 * @param [integer] rowIndex - Row index
                 * @param [integer] colIndex - Column index
                 * @param [object] item - Associated HTML DOM node
                 */
                'openCustomerAccount'
        );

        return true;
    },
    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns:function () {
        var me = this;

        var columnsData = [
            {
                header: '{s name=list/customer/column/registered_date}Registered on{/s}',
                xtype: 'datecolumn',
                dataIndex: 'date',
                renderer: me.dateColumn,
                flex: 1
            },
            {
                header: '{s name=list/customer/column/mail}Email{/s}',
                dataIndex: 'mail',
                flex: 1
            },
            {
                header: '{s name=list/customer/column/name}Name{/s}',
                dataIndex: 'name',
                flex: 1
            },
            {
                header: '{s name=list/customer/column/notified}Notified{/s}',
                dataIndex: 'notified',
                renderer: me.notifiedRenderer,
                flex: 1
            },
            {
                xtype:'actioncolumn',
                width:30,
                align:'center',
                items:[{
                    iconCls:'x-action-col-icon sprite-user--pencil',
                    cls:'sprite-user--pencil',
                    tooltip:'{s name=list/action_column/link_customer}To customer account{/s}',
                    getClass: function(value, metadata, record) {
                        if (!record.get("customerId")) {
                            return 'x-hidden';
                        }
                    },
                    handler:function (view, rowIndex, colIndex, item) {
                        me.fireEvent('openCustomerAccount', view, rowIndex, colIndex, item);
                    }
                }]
            }
        ];
        return columnsData;
    },


    /**
     * Creates the grid toolbar with search field
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar:function () {
        return Ext.create('Ext.toolbar.Toolbar',
                {
                    dock:'top',
                    ui:'shopware-ui',
                    items:[
                        '->',
                        {
                            xtype:'textfield',
                            name:'searchField',
                            action:'searchCustomer',
                            width:170,
                            cls:'searchfield',
                            enableKeyEvents:true,
                            checkChangeBuffer:500,
                            emptyText:'{s name=list/field/search_customer}Search...{/s}'
                        },
                        { xtype:'tbspacer', width:6 }
                    ]
                });
    },
    /**
     * Creates the paging toolbar for the grid to allow
     * and store paging. The paging toolbar uses the same store as the Grid
     *
     * @return Ext.toolbar.Paging The paging toolbar for the customer grid
     */
    getPagingBar: function () {
        var me = this;
        return Ext.create('Ext.toolbar.Paging', {
            store:me.customerStore,
            dock:'bottom',
            displayInfo:true
        });

    },
    /**
     * Renders the notified column
     *
     * @param value
     */
    notifiedRenderer:function (value, p, r) {
        if(value!=1){
            return '<span style="color:red;">{s name=list/render_value/notified/no}No{/s}</span>';
        }
        return '<span style="color:green;">{s name=list/render_value/notified/yes}Yes{/s}</span>';
    },

    /**
     * Formats the date column
     *
     * @param [string] - The register time value
     * @return [string] - The passed value, formatted with Ext.util.Format.date()
     */
    dateColumn:function (value, metaData, record) {
        if ( value === Ext.undefined ) {
            return value;
        }

        return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, timeFormat);
    }
});
//{/block}
