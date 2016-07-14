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
 * @package    CanceledOrder
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/canceled_order/view/main}

/**
 * Shopware UI - Last viewports view
 * Shows exit pages of canceled baskets
 */
//{block name="backend/canceled_order/view/tabs/baskets/viewports"}
Ext.define('Shopware.apps.CanceledOrder.view.tabs.baskets.Viewports', {
    extend: 'Ext.grid.Panel',
    border: false,
    alias: 'widget.canceled-order-tabs-baskets-viewports',
    title: '{s name=baskets/exitPages}Exit Pages{/s}',

    snippets : {
        columns : {
            percentage: '{s name=viewportPercentage}Percent{/s}',
            total: '{s name=viewportTotal}Total{/s}',
            name: '{s name=viewportName}Name{/s}'
        }
    },

    /**
     * Initializes the component, adds columns, pagingBar and Events
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.columns = me.getColumns();
        me.bbar = me.getPagingbar();

        me.callParent(arguments);
    },


    /**
     * Creates the grid columns
     * Data indices where chosen in order to match the database scheme for sorting in the PHP backend.
     * Therefore each Column requieres its own renderer in order to display the correct value.
     *
     * @return [array] grid columns
     */
    getColumns: function() {
        var me = this;

        return [
            {
                header: me.snippets.columns.percentage,
                dataIndex: 'percent',
                flex: 1
            },
            {
                header: me.snippets.columns.total,
                dataIndex: 'number',
                flex: 1
            },
            {
                header: me.snippets.columns.name,
                dataIndex: 'name',
                flex: 1
            }
        ]
    },

    /**
     * Creates pagingbar
     *
     * @return Ext.toolbar.Paging
     */
    getPagingbar: function() {
        var me = this;

        return [{
            xtype: 'pagingtoolbar',
            displayInfo: true,
            store: me.store
        }];
    }
});
//{/block}
