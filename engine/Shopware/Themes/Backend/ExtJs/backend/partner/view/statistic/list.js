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
 * @package    Partner
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/partner/view/partner}

/**
 * Shopware UI - partner statistic list window.
 *
 * Displays the partner static list
 */
//{block name="backend/partner/view/statistic/list"}
Ext.define('Shopware.apps.Partner.view.statistic.List', {
    extend:'Ext.grid.Panel',
    border: false,
    alias:'widget.partner-statistic-list',
    region:'center',
    autoScroll:true,
    store:'List',
    ui:'shopware-ui',
    /**
     * Initialize the Shopware.apps.Customer.view.main.List and defines the necessary
     * default configuration
     */
    initComponent:function () {
        var me = this;
        me.store = me.listStore;
        me.columns = me.getColumns();
        me.pagingbar = me.getPagingBar();
        me.features = me.getFeatures();

        me.dockedItems = [me.pagingbar ];
        me.callParent(arguments);
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
                header:'{s name=statistic/list/column/ordertime}Order time{/s}',
                dataIndex:'orderTime',
                xtype: 'datecolumn',
                flex:1
            },
            {
                header:'{s name=statistic/list/column/ordernumber}Order number{/s}',
                dataIndex:'number',
                flex:1
            },
            {
                header:'{s name=statistic/list/column/net_turnover}Net turnover{/s}',
                dataIndex:'netTurnOver',
                xtype: 'numbercolumn',
                summaryType: 'sum',
                summaryRenderer: function(value, summaryData, dataIndex) {
                    return me.summaryRenderer(me, value, summaryData, dataIndex);
                } ,
                flex:1
            },
            {
                header:'{s name=statistic/list/column/provision}Provision{/s}',
                dataIndex:'provision',
                xtype: 'numbercolumn',
                summaryType: 'sum',
                summaryRenderer: function(value, summaryData, dataIndex) {
                    return me.summaryRenderer(me, value, summaryData, dataIndex);
                } ,
                flex:1
            }
        ];
        return columnsData;
    },

    /**
     * Normalizes numbers
     *
     * @param [Object] value - The calculated value.
     * @return [string]
     */
    summaryRenderer: function(scope, value, summaryData, dataIndex) {
        var store = scope.getStore(),
            proxy = store.getProxy(),
            reader = proxy.getReader(),
            rawData = reader.rawData,
            summaryValue = (dataIndex === 'netTurnOver') ? rawData.totalNetTurnOver : rawData.totalProvision;

        if (summaryValue !== parseInt(summaryValue, 10)) {
            summaryValue = Ext.util.Format.number(summaryValue, '0.00');
        }
        return '<b> {s name=statistic/list/label/total}Total:{/s} ' + summaryValue + '</b>';
    },

    /**
     * Creates the items of the action column
     *
     * @return [array] action column itesm
     */
    getActionColumnItems: function () {
        var me = this,
                actionColumnData = [];

        actionColumnData.push({
            iconCls:'sprite-pencil',
            cls:'editBtn',
            tooltip:'{s name=list/action_column/edit}Edit partner{/s}',
            handler:function (view, rowIndex, colIndex, item) {
                me.fireEvent('editColumn', view, rowIndex, colIndex, item);
            }
        });

        actionColumnData.push({
            iconCls:'sprite-minus-circle-frame',
            action:'delete',
            cls:'delete',
            tooltip:'{s name=list/action_column/delete}Delete partner{/s}',
            handler:function (view, rowIndex, colIndex, item) {
                me.fireEvent('deleteColumn', view, rowIndex, colIndex, item);
            }
        });

        actionColumnData.push({
            iconCls:'sprite-chart-up-color',
            cls:'chart-up-color',
            tooltip:'{s name=list/action_column/statistic}Statistics{/s}',
            handler:function (view, rowIndex, colIndex, item) {
                me.fireEvent('statistic', view, rowIndex, colIndex, item);
            }
        });
        return actionColumnData;
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
            store:me.listStore,
            dock:'bottom',
            displayInfo:true
        });

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
    }
});
//{/block}
