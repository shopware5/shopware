/**
 * Shopware 4
 * Copyright © shopware AG
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
 * todo@all - documentation
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
                flex: 1
            }
        };

        me.initStoreIndices('basketConversion', '{s name="table/rating/basket_rate"}Order success rate{/s}: [0]');
        me.initStoreIndices('orderConversion', '{s name="table/rating/order_rate"}Order conversion rate{/s}: [0]');
        me.initStoreIndices('basketVisitConversion', '{s name="table/rating/basket_visit_rate"}Abandoned baskets / visitors{/s}: [0]');

        me.callParent(arguments);
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function () {
        return [
            {
                xtype: 'datecolumn',
                dataIndex: 'date',
                align: 'left',
                text: '{s name="table/rating/date"}Date{/s}'
            },
            {
                dataIndex: 'basketConversion',
                text: '{s name="table/rating/basket_rate"}Order success rate{/s}'
            },
            {
                dataIndex: 'orderConversion',
                text: '{s name="table/rating/order_rate"}Order conversion rate{/s}'
            },
            {
                dataIndex: 'basketVisitConversion',
                text: '{s name="table/rating/basket_visit_rate"}Abandoned baskets / visitors{/s}'
            }
        ];
    }
});
//{/block}