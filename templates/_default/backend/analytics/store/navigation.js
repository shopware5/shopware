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
 * @subpackage Navigation
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * todo@all: Documentation
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/store/navigation"}
Ext.define('Shopware.apps.Analytics.store.Navigation', {
    extend: 'Ext.data.TreeStore',
    model: 'Shopware.apps.Analytics.model.Navigation',
    root: {
        expanded: true,
        children: [
            { "text": "{s name=nav/salesBy/month}Month{/s}", "leaf": true, "iconCls": "sprite-calendar-month", "action": "order_analytics", "id": "month", "comparable": true},
            { "text": "{s name=nav/salesBy/calendarWeeks}Calendar weeks{/s}", "leaf": true, "iconCls": "sprite-calendar-select-week", "action": "order_analytics", "id": "week", "comparable": true},
            { "text": "{s name=nav/salesBy/weekdays}Weekdays{/s}", "leaf": true, "iconCls": "sprite-calendar-select-days", "action": "order_analytics", "id": "weekday", "comparable": true},
            { "text": "{s name=nav/salesBy/time}Time{/s}", "leaf": true, "iconCls": "sprite-clock", "action": "order_analytics", "id": "daytime", "comparable": true},
            { "text": "{s name=nav/salesBy/categories}Categories{/s}", "leaf": true, "iconCls": "sprite-category", "action": "order_detail_analytics", "id": "category"},
            { "text": "{s name=nav/salesBy/countries}Countries{/s}", "leaf": true, "iconCls": "sprite-locale", "action": "order_analytics", "id": "country"},
            { "text": "{s name=nav/salesBy/payment}Payment{/s}", "leaf": true, "iconCls": "sprite-moneys", "action": "order_analytics", "id": "payment"},
            { "text": "{s name=nav/salesBy/shippingMethods}Shipping methods{/s}", "leaf": true, "iconCls": "sprite-truck-box-label", "action": "order_analytics", "id": "dispatch"},
            { "text": "{s name=nav/salesBy/vendors}Vendors{/s}", "leaf": true, "iconCls": "sprite-toolbox", "action": "order_detail_analytics", "id": "supplier"},
            { "text": "{s name=nav/rating/orderConversion}Order conversion rate{/s}", "leaf": true, "iconCls": "sprite-newspapers", "store": "analytics-store-conversion", id: "conversion", "comparable": true, "action": "conversion_rate"},
            { "text": "{s name=nav/search}Popular search terms{/s}", "leaf": true, "iconCls": "sprite-magnifier", "action": "search_analytics", "id": "search", "store": 'analytics-store-search'},
            { "text": "{s name=nav/visitors}Visitors{/s}", "leaf": true, "iconCls": "sprite-chart-up-color", "action": "visits", "id": "visitors", "store": 'analytics-store-visitors', "comparable": true},
            { "text": "Artikel nach Aufrufen(Impressionen)", "leaf": true, "iconCls": "sprite-chart-up-color", "action": "ArticleImpression", "id": "article_impression", "store": 'analytics-store-article_impressions', "comparable": true}
        ]
    },
    constructor: function(config) {

        config.root = Ext.clone(this.root);

        this.callParent([config]);
    }
});
//{/block}
