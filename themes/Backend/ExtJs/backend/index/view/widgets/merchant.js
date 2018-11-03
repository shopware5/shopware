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

//{namespace name=backend/index/view/widgets}

/**
 * Shopware UI - Sales Widget
 *
 * This file holds off the sales widget.
 */
//{block name="backend/index/view/widgets/merchant"}
Ext.define('Shopware.apps.Index.view.widgets.Merchant', {
    extend: 'Shopware.apps.Index.view.widgets.Base',
    alias: 'widget.swag-merchant-widget',
    title: '{s name=orders/title}Last orders{/s}',
    layout: 'fit',

    /**
     * Snippets for this widget.
     * @object
     */
    snippets: {
        headers: {
            date: '{s name=merchant/headers/date}Date{/s}',
            company_name: '{s name=merchant/headers/name}Company name{/s}',
            customer: '{s name=merchant/headers/customer}Customer{/s}',
            customer_group: '{s name=merchant/headers/customer_group}Customer group{/s}'
        },
        success_msg: {
            title: '{s name=merchant/success_msg/title}Merchant widget{/s}',
            text: '{s name=merchant/success_msg/text}Selected merchant was successfully unlocked{/s}'
        },
        failure_msg: {
            title: '{s name=merchant/success_msg/title}Merchant widget{/s}',
            text: "{s name=merchant/failure_msg/text}Selected merchant couldn't be unlocked successfully.{/s}"
        },
        tooltips: {
            customer: '{s name=merchant/tooltips/customer}Open customer{/s}',
            unlock: '{s name=merchant/tooltips/allow}Unlock merchant{/s}',
            decline: '{s name=merchant/tooltips/decline}Decline merchant{/s}'
        }
    },

    merchantStore: null,

    constructor: function() {
        var me = this;

        me.merchantStore = Ext.create('Ext.data.Store', {
            model: 'Shopware.apps.Index.model.Merchant',
            remoteFilter: true,
            clearOnLoad: false,
            autoLoad: true,

            proxy: {
                type: 'ajax',
                url: '{url controller="widgets" action="getLastMerchant"}',
                reader: {
                    type: 'json',
                    root: 'data'
                }
            }
        });

        me.callParent(arguments);
    },

    /**
     * Initializes the widget.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.registerEvents();

        me.tools = [{
            type: 'refresh',
            scope: me,
            handler: me.refreshView
        }];

        me.items = [{
            xtype: 'grid',
            viewConfig: {
                hideLoadingMsg: true
            },
            border: 0,
            store: me.merchantStore,
            columns: me.createColumns()
        }];

        me.createTaskRunner();
        me.callParent(arguments);
    },

    /**
     * Register additional events for the widget.
     *
     * @public
     * @return void
     */
    registerEvents: function() {
        this.addEvents(
            'allowMerchant',
            'declineMerchant'
        );
    },

    /**
     * Registers a new task runner to refresh
     * the store after a given time interval.
     *
     * @public
     * @param [object] store - Ext.data.Store
     * @return void
     */
    createTaskRunner: function() {
        var me = this;

        me.storeRefreshTask = Ext.TaskManager.start({
            scope: me,
            run: me.refreshView,
            interval: 300000
        });
    },

    /**
     * Helper method which will be called by the
     * task runner and when the user clicks the
     * refresh icon in the panel header.
     *
     * @public
     * @return void
     */
    refreshView: function() {
        var me = this;

        if(!me.merchantStore) {
            return false;
        }
        me.merchantStore.load();
    },

    /**
     * Helper method which creates the columns for the
     * grid panel in this widget.
     *
     * @return [array] generated columns
     */
    createColumns: function() {
        var me = this;

        return [{
            dataIndex: 'date',
            header: me.snippets.headers.date,
            renderer: me.dateColumn,
            flex: 1
        }, {
            dataIndex: 'company_name',
            header: me.snippets.headers.company_name,
            flex: 1
        }, {
            dataIndex: 'customer',
            header: me.snippets.headers.customer,
            renderer: me.emailColumn,
            flex: 1
        }, {
            dataIndex: 'customergroup_name',
            header: me.snippets.headers.customer_group,
            flex: 1
        }, {
            xtype: 'actioncolumn',
            width: 80,
            items: [{
                iconCls:'sprite-user--arrow',
                tooltip: me.snippets.tooltips.customer,
                handler: function(view, rowIndex, colIndex, item, event, record) {

                    /** Open the customer */
                    Shopware.app.Application.addSubApplication({
                        name: 'Shopware.apps.Customer',
                        action: 'detail',
                        params: {
                            customerId: ~~(1 * record.get('id'))
                        }
                    });
                }
            },{
                iconCls: 'sprite-tick-circle',
                tooltip: me.snippets.tooltips.unlock,
                handler: function(view, rowIndex, colIndex, item, event, record) {
                    me.fireEvent('allowMerchant', record);
                }
            }, {
                iconCls: 'sprite-cross-circle',
                tooltip: me.snippets.tooltips.decline,
                handler: function(view, rowIndex, colIndex, item, event, record) {
                    me.fireEvent('declineMerchant', record);
                }
            }]
        }]
    },

    /**
     * Formats the email column
     *
     * @param [string] value
     * @return [string]
     */
    emailColumn: function(value, cellEl, record) {
        return Ext.String.format('{literal}<a href="mailto:{0}">{1}</a>{/literal}', record.get('email'), value);
    },

    /**
     * Formats the date column
     *
     * @param [string] - The order time value
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
