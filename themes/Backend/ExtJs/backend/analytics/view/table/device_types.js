/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

/**
 * Analytics Device Type Table
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 */
//{namespace name="backend/analytics/view/main"}
//{block name="backend/analytics/view/table/device_types"}
Ext.define('Shopware.apps.Analytics.view.table.DeviceTypes', {
    extend: 'Shopware.apps.Analytics.view.main.Table',
    alias: 'widget.analytics-table-device_types',

    initComponent: function () {
        var me = this;

        me.columns = {
            items: me.getColumns(),
            defaults: {
                flex: 1,
                sortable: false
            }
        };

        me.initStoreIndices('turnover', '{s name="general/turnover"}Turnover{/s}: [0]', {
            xtype: 'numbercolumn',
            renderer: me.currencyRenderer
        });

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
                dataIndex: 'deviceTypeHuman',
                text: '{s name="table/device_types/device_type"}Device type{/s}'
            },
            {
                xtype: 'numbercolumn',
                dataIndex: 'turnover',
                text: '{s name="general/turnover"}Turnover{/s}',
                renderer: me.currencyRenderer
            }
        ];
    },

    currencyRenderer: function(value) {
        var me = this;

        return Ext.util.Format.currency(
            value,
            me.subApp.currencySign,
            2,
            (me.subApp.currencyAtEnd == 1)
        );
    }
});
//{/block}
