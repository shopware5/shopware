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
 * Analytics Payment Table
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 * todo@all - documentation
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/table/payment"}
Ext.define('Shopware.apps.Analytics.view.table.Payment', {
    extend: 'Shopware.apps.Analytics.view.main.Table',
    alias: 'widget.analytics-table-payment',

    initComponent: function () {
        var me = this;

        me.columns = {
            items: me.getColumns(),
            defaults: {
                flex: 1,
                sortable: false
            }
        };

        me.initStoreIndices('amount', '{s name=table/payment/sales}Sales{/s}: [0]', { xtype: 'numbercolumn' });

        me.callParent(arguments);
    },

    getColumns: function () {
        return [
            {
                dataIndex: 'name',
                text: '{s name=table/payment/payment}Payment{/s}'
            },
            {
                xtype: 'numbercolumn',
                dataIndex: 'amount',
                text: '{s name=table/payment/sales}Sales{/s}'
            }
        ];
    }
});
//{/block}