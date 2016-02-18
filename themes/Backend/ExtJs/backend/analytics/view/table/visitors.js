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
 * Analytics Visitors Table
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/table/visitors"}
Ext.define('Shopware.apps.Analytics.view.table.Visitors', {
    extend: 'Shopware.apps.Analytics.view.main.Table',
    alias: 'widget.analytics-table-visitors',
    shopColumnVisits: "{s name=table/visits/visits}V: [0]{/s}",
    shopColumnImpressions: "{s name=table/visits/impressions}I: [0]{/s}",
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
            { text: me.shopColumnVisits, dataIndex: 'totalVisits' },
            { text: me.shopColumnImpressions, dataIndex: 'totalImpressions' },
        ]);

        me.callParent(arguments);
    },

    getColumns: function () {
        return [
            {
                xtype: 'datecolumn',
                dataIndex: 'datum',
                align: 'left',
                text: '{s name=table/visitors/date}Date{/s}'
            },
            {
                dataIndex: 'desktopVisits',
                text: '{s name=table/visitors/desktop_visits}Desktop visits{/s}'
            },
            {
                dataIndex: 'tabletVisits',
                text: '{s name=table/visitors/tablet_visits}Tablet visits{/s}'
            },
            {
                dataIndex: 'mobileVisits',
                text: '{s name=table/visitors/mobile_visits}Mobile visits{/s}'
            },
            {
                dataIndex: 'totalVisits',
                text: '{s name=table/visitors/totalVisits}Total visits{/s}'
            },
            {
                dataIndex: 'desktopImpressions',
                text: '{s name=table/visitors/desktop_impressions}Desktop impressions{/s}'
            },
            {
                dataIndex: 'tabletImpressions',
                text: '{s name=table/visitors/tablet_impressions}Tablet impressions{/s}'
            },
            {
                dataIndex: 'mobileImpressions',
                text: '{s name=table/visitors/mobile_impressions}Mobile impressions{/s}'
            },
            {
                dataIndex: 'totalImpressions',
                text: '{s name=table/visitors/totalImpressions}Total impressions{/s}'
            }
        ];
    }
});
//{/block}
