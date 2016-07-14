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
 * Analytics Table Base Class
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/main/table"}
Ext.define('Shopware.apps.Analytics.view.main.Table', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.analytics-table',

    initComponent: function () {
        var me = this;

        me.createPagingbar();

        me.callParent(arguments);
    },

    createPagingbar: function() {
        var me = this;

        Ext.applyIf(me, {
            dockedItems: [
                {
                    xtype: 'pagingtoolbar',
                    displayInfo: true,
                    store: me.store,
                    dock: 'bottom'
                }
            ]
        });
    },

    initStoreIndices: function (indexName, text, params) {
        var me = this,
            columns = me.columns,
            columnItems = !!columns.items ? columns.items : columns,
            column;

        if (!me.shopSelection) {
            return;
        }

        indexName = indexName || 'amount';
        text = text || '[0]';
        params = params || { };

        Ext.each(me.shopSelection, function (shopId) {
            var shop = me.shopStore.getById(shopId);

            column = Ext.merge({
                dataIndex: indexName + shop.get('id'),
                text: Ext.String.format(text, shop.get('name'))
            }, params);

            columnItems.push(column);
        });
    },

    initShopColumns: function(columns) {
        var me = this;

        if (!me.columns) {
            me.columns = [];
        }
        Ext.each(me.shopSelection, function (shopId) {
            var shop = me.shopStore.getById(shopId);

            Ext.each(columns, function(config) {
                var column = Ext.clone(config);

                column = Ext.merge(column, {
                    dataIndex: config.dataIndex + shopId,
                    text: Ext.String.format(config.text, shop.get('name'))
                });

                me.columns.items.push(column);
            });
        });

    },

    getColumns: function () {
        return this.columns;
    }
});
//{/block}
