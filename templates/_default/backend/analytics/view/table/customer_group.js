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
 * Analytics CustomerGroup Table
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 * todo@all - documentation
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/table/customer_group"}
Ext.define('Shopware.apps.Analytics.view.table.CustomerGroup', {
    extend: 'Shopware.apps.Analytics.view.main.Table',
    alias: 'widget.analytics-table-customer-group',

    initComponent: function () {
        var me = this;

        me.columns = {
            items: me.getColumns(),
            defaults: {
                flex: 1,
                sortable: false
            }
        };

        me.initStoreIndices('amount', '{s name=table/customer_group/sales}Sales{/s}: [0]');
        me.initStoreIndices('count', '{s name=table/customer_group/count}Number of orders{/s}: [0]');

        me.callParent(arguments);
    },

    getColumns: function () {
        return [
            {
                dataIndex: 'customerGroup',
                text: '{s name=table/customer_group/group}Customer group{/s}'
            },
            {
                xtype: 'numbercolumn',
                dataIndex: 'amount',
                text: '{s name=table/customer_group/sales}Sales{/s}'
            },
            {
                xtype: 'numbercolumn',
                format: '0',
                dataIndex: 'count',
                text: '{s name=table/customer_group/count}Number of orders{/s}'
            }
        ];
    }
});
//{/block}
