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
 * Analytics Country Table
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/table/country"}
Ext.define('Shopware.apps.Analytics.view.table.Country', {
    extend: 'Shopware.apps.Analytics.view.main.Table',
    alias: 'widget.analytics-table-country',
    shopColumnText: "{s name=general/turnover}Turnover{/s}: [0]",

    initComponent: function () {
        var me = this;

        me.columns = {
            items: me.getColumns(),
            defaults: {
                flex: 1,
                sortable: false
            }
        };

        me.initStoreIndices('turnover', me.shopColumnText, {
            xtype: 'numbercolumn',
            renderer: me.priceRenderer
        });

        me.callParent(arguments);
    },

    getColumns: function () {
        var me = this;

        return [
            {
                dataIndex: 'name',
                text: '{s name=table/country/country}Country{/s}'
            },
            {
                xtype: 'numbercolumn',
                dataIndex: 'turnover',
                text: '{s name=general/turnover}Turnover{/s}',
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
