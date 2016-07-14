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

/**
 * Analytics Rating Table
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/table/rating"}
Ext.define('Shopware.apps.Analytics.view.table.Rating', {
    extend: 'Shopware.apps.Analytics.view.main.Table',
    alias: 'widget.analytics-table-rating',
    shopColumnName: '{s name=nav/rating_overview}Rating{/s}',

    initComponent: function () {
        var me = this;

        me.columns = {
            items: me.getColumns(),
            defaults: {
                align: 'right',
                flex: 1,
                sortable: false
            }
        };


        me.initShopColumns([
            {
                dataIndex: 'basketConversion',
                text: '{s name="table/rating/basket_rate"}Order success rate{/s}: <br>[0]',
                renderer: me.percentRenderer
            },
            {
                dataIndex: 'orderConversion',
                text: '{s name="table/rating/order_rate"}Order conversion rate{/s}: <br>[0]',
                renderer: me.percentRenderer
            },
            {
                dataIndex: 'basketVisitConversion',
                text: '{s name="table/rating/basket_visit_rate"}Abandoned baskets / visitors{/s}: <br>[0]',
                renderer: me.percentRenderer
            }
        ]);

        me.callParent(arguments);
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function () {
        var me = this;

        return [
            {
                xtype: 'datecolumn',
                dataIndex: 'date',
                height: 30,
                width: 30,
                align: 'left',
                text: '{s name="table/rating/date"}Date{/s}'
            },
            {
                dataIndex: 'basketConversion',
                height: 30,
                text: '{s name="table/rating/basket_rate"}Order success rate{/s}',
                renderer: me.percentRenderer
            },
            {
                dataIndex: 'orderConversion',
                height: 30,
                text: '{s name="table/rating/order_rate"}Order conversion rate{/s}',
                renderer: me.percentRenderer
            },
            {
                dataIndex: 'basketVisitConversion',
                height: 30,
                text: '{s name="table/rating/basket_visit_rate"}Abandoned baskets / visitors{/s}',
                renderer: me.percentRenderer
            }
        ];
    },

    percentRenderer: function(value) {
        return value + ' %';
    }
});
//{/block}
