/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @package    Analytics
 * @subpackage Month
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * todo@all: Documentation
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/chart/month"}
Ext.define('Shopware.apps.Analytics.view.chart.Month', {
    extend: 'Shopware.apps.Analytics.view.main.Chart',
    alias: 'widget.analytics-chart-month',
    legend: {
        position: 'right'
    },
    axes: [{
        type: 'Numeric',
        minimum: 0,
        grid: true,
        position: 'left',
        fields: ['amount'],
        title: '{s name=chart/month/titleLeft}Sales{/s}'
    }, {
        type: 'Time',
        position: 'bottom',
        fields: ['date'],
        title: '{s name=chart/month/titleBottom}Month{/s}',
        step: [Ext.Date.MONTH, 1],
        dateFormat: 'M, Y',
        label: {
            rotate: {
                degrees: 315
            }
        }
    }],
    initComponent: function() {
        var me = this;
        // Initiate stores for handling multiple shop values
        me.initMultipleShopTipsStores();

        me.series = [{
            type: 'line',
            axis : ['left', 'bottom'],
            xField: 'date',
            highlight: true,
            yField: 'amount',
            fill: true,
            smooth: true,
            title: '{s name=chart/month/legendSum}Sum{/s}',
            tips: {
                trackMouse: true,
                width: 580,
                height: 130,
                layout: 'fit',
                items: {
                    xtype: 'container',
                    layout: 'hbox',
                    items: [me.tipChart, me.tipGrid]
                },
                renderer: function(cls, item) {
                    me.initMultipleShopTipsData(item,this);
                }
            }
        }];

        me.shopStore.each(function(shop) {
            me.series.push({
                type: 'line',
                title: shop.data.name,
                axis : ['left', 'bottom'],
                xField: 'date',
                yField: 'amount' + shop.data.id,
                smooth: true,
                tips: {
                   trackMouse: true,
                   width: 120,
                   highlight: {
                       size: 7,
                       radius: 7
                   },
                   height: 60,
                   renderer: function(storeItem, item) {
                       this.setTitle(Ext.Date.format(storeItem.get('date'), 'F, Y'));
                       var sales = Ext.util.Format.currency(storeItem.get('amount'+shop.data.id), shop.data.currencyChar);
                       this.update(sales);
                   }
                }
            });
        }, me);

        me.callParent(arguments);
    }
});
//{/block}