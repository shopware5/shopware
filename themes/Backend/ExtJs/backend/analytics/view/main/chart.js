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
 * Analytics Chart Base Class
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/main/chart"}
Ext.define('Shopware.apps.Analytics.view.main.Chart', {
    extend: 'Ext.chart.Chart',
    alias: 'widget.analytics-chart',
    multipleShopTips: false,
    animate: true,
    theme: 'Category3',

    initComponent: function () {
        var me = this;

        me.callParent(arguments);
    },


    initMultipleShopTipsStores: function () {
        var me = this;

        me.tipStore = Ext.create('Ext.data.JsonStore', {
            fields: ['name', 'data']
        });

        me.tipStoreTable = Ext.create('Ext.data.JsonStore', {
            fields: ['name', 'data']
        });

        me.tipChart = {
            xtype: 'chart',
            width: 100,
            height: 100,
            animate: false,
            store: me.tipStore,
            shadow: false,
            insetPadding: 5,
            theme: 'Base:gradients',
            series: [
                {
                    type: 'pie',
                    field: 'data',
                    showInLegend: false,
                    label: {
                        field: 'name',
                        display: 'rotate',
                        contrast: true,
                        font: '9px Arial'
                    }
                }
            ]
        };

        me.tipGrid = {
            xtype: 'grid',
            store: me.tipStoreTable,
            height: 130,
            flex: 1,
            columns: [
                {
                    text: '{s name="main/chart/name"}Name{/s}',
                    dataIndex: 'name',
                    flex: 1
                },
                {
                    xtype: 'numbercolumn',
                    text: '{s name=general/turnover}Turnover{/s}',
                    dataIndex: 'data',
                    align: 'right',
                    flex: 1
                }
            ]
        };
    },

    initMultipleShopTipsData: function (item, tipObj, dateFormatString, defaultTitle) {

        var storeItem = item.storeItem, me = this,
            dataChart = [], dataTable = [];

        if (!dateFormatString) {
            dateFormatString = 'F, Y';
        }

        if (!defaultTitle) {
            defaultTitle = '{s name=general/turnover}Turnover{/s}';
        }

        me.shopStore.each(function (shop) {
            var value = storeItem.get('turnover' + shop.data.id);

            if (!value) {
                return;
            }

            if (shop.data.currencyChar == "&euro;") {
                shop.data.currencyChar = "€";
            }

            // Data for chart
            dataChart[dataChart.length] = { name: shop.data.name, data: value};
            // Data for table
            dataTable[dataTable.length] = { name: shop.data.name, data: value};

        });
        // Load data with plain values into pie chart
        me.tipStore.loadData(dataChart);

        // Add total sum to table
        dataTable[dataTable.length] = {
            name: '{s name=general/turnover}Turnover{/s}',
            data: storeItem.get('turnover')
        };

        // Load formatted data with sum row into table
        me.tipStoreTable.loadData(dataTable);

        if (!dataChart.length) {
            tipObj.hide();
        } else {
            tipObj.show();
        }
        tipObj.setTitle(defaultTitle + " " + Ext.Date.format(storeItem.get('date'), dateFormatString));
    },

    createLineSeries: function(config, tips) {
        var defaultConfig = {
            type: 'line',
            axis: [ 'left', 'bottom' ],
            highlight: true,
            fill: true,
            smooth: true
        },
        tipsConfig = {
            trackMouse: true,
            height: 60,
            width: 120,
            layout: 'fit',
            highlight: {
                size: 7,
                radius: 7
            }
        };

        if (Ext.isObject(config)) {
            defaultConfig = Ext.apply({ }, config, defaultConfig);
        }

        if (Ext.isObject(tips)) {
            tipsConfig = Ext.apply({ }, tips, tipsConfig);
        }

        defaultConfig.tips = tipsConfig;

        return defaultConfig;
    },

    getAxesFields: function(fieldPrefix) {
        var me = this,
            fields = [];

        if (me.shopSelection == Ext.undefined || me.shopSelection.length <= 0) {
            return [fieldPrefix];
        }

        Ext.each(me.shopSelection, function (shopId) {
            fields.push(fieldPrefix + shopId);
        });

        return fields;
    },

    getAxesTitles: function(defaultName) {
        var me = this,
            titles = [];

        if (me.shopSelection == Ext.undefined || me.shopSelection.length <= 0) {
            return defaultName;
        }

        Ext.each(me.shopSelection, function (shopId) {
            if (shopId) {
                var shop = me.shopStore.getById(shopId);
                titles.push(shop.get('name'));
            }
        });

        return titles;
    }


});
//{/block}
