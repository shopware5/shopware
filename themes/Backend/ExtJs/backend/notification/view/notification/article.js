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
 * Shopware UI - notification article list .
 *
 * Displays the notification article list
 */
//{block name="backend/notification/view/notification/article"}
Ext.define('Shopware.apps.Notification.view.notification.Article', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.notification-notification-article',
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
        me.features = me.getFeatures();
        me.store = me.articleStore;
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
                 * Event will be fired when the user clicks the user icon in the
                 * action column
                 *
                 * @event statistic
                 * @param [object] View - Associated Ext.view.Table
                 * @param [integer] rowIndex - Row index
                 * @param [integer] colIndex - Column index
                 * @param [object] item - Associated HTML DOM node
                 */
                'showCustomers'
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
                header: '{s name=list/article/column/name}Article{/s}',
                dataIndex: 'name',
                flex: 1
            },
            {
                header: '{s name=list/article/column/number}Article order number{/s}',
                dataIndex: 'number',
                flex: 1
            },
            {
                header: '{s name=list/article/column/registered}Registered{/s}',
                dataIndex: 'registered',
                summaryType: 'sum',
                summaryRenderer: function(value, summaryData, dataIndex) {
                    return me.summaryRenderer(me, value, summaryData, dataIndex);
                },
                flex: 1
            },
            {
                header: '{s name=list/article/column/not_notified}Not notified customers{/s}',
                dataIndex: 'notNotified',
                summaryType: 'sum',
                summaryRenderer: function(value, summaryData, dataIndex) {
                    return me.summaryRenderer(me, value, summaryData, dataIndex);
                },
                flex: 1
            },
            {
                xtype:'actioncolumn',
                width:30,
                align:'center',
                items:me.getActionColumnItems()
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
                            action:'searchArticle',
                            width:170,
                            cls:'searchfield',
                            enableKeyEvents:true,
                            checkChangeBuffer:500,
                            emptyText:'{s name=list/field/search_article}Search...{/s}'
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
            store:me.articleStore,
            dock:'bottom',
            displayInfo:true
        });

    },

    /**
     * Creates the items of the action column
     *
     * @return [array] action column items
     */
    getActionColumnItems: function () {
        var me = this,
                actionColumnData = [];

        actionColumnData.push({
            iconCls:'sprite-users',
            cls:'users',
            tooltip:'{s name=list/action_column/users}Show notification customers{/s}',
            handler:function (view, rowIndex, colIndex, item) {
                me.fireEvent('showCustomers', view, rowIndex, colIndex, item);
            }
        });
        return actionColumnData;
    },

    /**
     * Creates the summary feature for the grid
     *
     * @return Ext.toolbar.Paging The paging toolbar for the customer grid
     */
    getFeatures: function () {
        return [{
            ftype: 'summary'
        }];
    },

    /**
     * Summary Renderer to show the total registered and not notified values
     *
     * @param scope
     * @param value - The calculated value.
     * @param summaryData - the calculated summary
     * @param dataIndex
     * @return [string]
     */
    summaryRenderer: function(scope, value, summaryData, dataIndex) {
        var store = scope.getStore(),
                proxy = store.getProxy(),
                reader = proxy.getReader(),
                rawData = reader.rawData;

            //get the total summary out of the store to get the totals data not only the page summary
            var summaryValue = (dataIndex === 'registered') ? rawData.totalRegistered : rawData.totalNotNotified;

        return '<b> {s name=list/summary/label/total}Total:{/s} ' + summaryValue + '</b>';
    }
});
//{/block}
