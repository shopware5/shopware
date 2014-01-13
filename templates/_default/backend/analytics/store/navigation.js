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
        children: [{
            id: 'overview',
            text: 'Overview',
            store: 'analytics-store-navigation-overview',
            iconCls: 'sprite-report-paper',
            comparable: true,
            leaf: true
        },{
            id: 'rating',
            text: 'Rating',
            store: 'analytics-store-navigation-rating',
            iconCls: 'sprite-report-paper',
            comparable: true,
            leaf: true
        }, {
            id: 'referrer_revenue',
            text: 'Umsatz nach Referrer',
            store: 'analytics-store-navigation-referrer_revenue',
            iconCls: 'sprite-report-paper',
            comparable: true,
            leaf: true
        }, {
            id: 'partner_revenue',
            text: 'Umsatz nach Partnern',
            store: 'analytics-store-navigation-partner_revenue',
            iconCls: 'sprite-report-paper',
            comparable: true,
            leaf: true
        }, {
            id: 'referrer_visitors',
            text: 'Besucher Zugriffsquellen',
            store: 'analytics-store-navigation-referrer_visitors',
            iconCls: 'sprite-report-paper',
            comparable: true,
            leaf: true
        }, {
            id: 'month',
            text: '{s name=nav/salesBy/month}Month{/s}',
            store: 'analytics-store-navigation-month',
            iconCls: 'sprite-calendar-month',
            comparable: true,
            leaf: true
        }, {
            id: 'week',
            text: '{s name=nav/salesBy/calendarWeeks}Calendar weeks{/s}',
            store: 'analytics-store-navigation-calendar_weeks',
            iconCls: 'sprite-calendar-select-week',
            comparable: true,
            leaf: true
        }, {
            id: 'weekday',
            text: '{s name=nav/salesBy/weekdays}Weekdays{/s}',
            store: 'analytics-store-navigation-weekdays',
            iconCls: 'sprite-calendar-select-days',
            comparable: true,
            leaf: true
        }, {
            id: 'daytime',
            text: '{s name=nav/salesBy/time}Time{/s}',
            store: 'analytics-store-navigation-time',
            iconCls: 'sprite-clock',
            comparable: true,
            leaf: true
        }, {
            id: 'category',
            text: '{s name=nav/salesBy/categories}Categories{/s}',
            store: 'analytics-store-navigation-categories',
            iconCls: 'sprite-category',
            leaf: true
        }, {
            id: 'country',
            text: '{s name=nav/salesBy/countries}Countries{/s}',
            store: 'analytics-store-navigation-countries',
            iconCls: 'sprite-locale',
            leaf: true
        }, {
            id: 'payment',
            text: '{s name=nav/salesBy/payment}Payment{/s}',
            store: 'analytics-store-navigation-payment',
            iconCls: 'sprite-moneys',
            leaf: true
        }, {
            id: 'dispatch',
            text: '{s name=nav/salesBy/shippingMethods}Shipping methods{/s}',
            store: 'analytics-store-navigation-shipping_methods',
            iconCls: 'sprite-truck-box-label',
            leaf: true
        }, {
            id: 'supplier',
            text: '{s name=nav/salesBy/vendors}Vendors{/s}',
            store: 'analytics-store-navigation-vendors',
            iconCls: 'sprite-toolbox',
            leaf: true
        }, {
            id: 'search',
            text: '{s name=nav/search}Popular search terms{/s}',
            store: 'analytics-store-navigation-search',
            iconCls: 'sprite-magnifier',
            leaf: true
        }, {
            id: 'visitors',
            text: '{s name=nav/visitors}Visitors{/s}',
            store: 'analytics-store-navigation-visitors',
            iconCls: 'sprite-chart-up-color',
            comparable: true,
            leaf: true
        }, {
            id: 'article_impression',
            text: 'Artikel nach Aufrufen(Impressionen)',
            store: 'analytics-store-navigation-article_impressions',
            iconCls: 'sprite-chart-up-color',
            comparable: true,
            leaf: true
        }]
    },
    constructor: function(config) {
        var me = this;

        config.root = Ext.clone(me.root);

        me.callParent(arguments);
    }
});
//{/block}
