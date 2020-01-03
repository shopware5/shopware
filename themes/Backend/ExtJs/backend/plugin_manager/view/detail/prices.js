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
 *
 * @category   Shopware
 * @package    PluginManager
 * @subpackage Detail
 * @version    $Id$
 * @author shopware AG
 */
// {namespace name=backend/plugin_manager/translation}

// {block name="backend/plugin_manager/view/detail/prices"}
Ext.define('Shopware.apps.PluginManager.view.detail.Prices', {
    extend: 'PluginManager.tab.Panel',
    cls: 'store-plugin-detail-prices-tab shopware-plugin-manager-tab',
    margin: '10 0',

    mixins: {
        events: 'Shopware.apps.PluginManager.view.PluginHelper'
    },

    tabIndex: { },

    initComponent: function() {
        var me = this,
            items = [],
            index = 0,
            buyPrice = me.getPriceByType(me.prices, 'buy'),
            rentPrice = me.getPriceByType(me.prices, 'rent'),
            testPrice = me.getPriceByType(me.prices, 'test'),
            freePrice = me.getPriceByType(me.prices, 'free'),
            redirectToStore = me.plugin.get('redirectToStore'),
            lowestPrice = me.plugin.get('lowestPrice'),
            price;

        if (redirectToStore) {
            price = me.getLowestPrice({
                lowestPrice: lowestPrice,
                buyPrice: buyPrice,
                freePrice: freePrice
            });

            items.push(me.createCommunityTab(price));
            me.tabIndex['bye'] = index;
            index++;

            // In this case we only want to add the "test tab" if it exists.
            // For this reason set the other prices to "null"
            buyPrice = null;
            rentPrice = null;
            freePrice = null;
        }

        if (buyPrice) {
            items.push(me.createBuyTab(buyPrice));
            me.tabIndex['buy'] = index;
            index++;
        }
        if (rentPrice) {
            items.push(me.createRentTab(rentPrice));
            me.tabIndex['rent'] = index;
            index++;
        }
        if (testPrice) {
            items.push(me.createTestTab(testPrice));
            me.tabIndex['test'] = index;
            index++;
        }
        if (freePrice) {
            items.push(me.createFreeTab(freePrice));
            me.tabIndex['free'] = index;
            index++;
        }
        if (items.length <= 0 && me.plugin.get('useContactForm')) {
            items.push(me.createContactTab());
            me.tabIndex['contact'] = index;
            index++;
        }

        me.items = items;

        me.callParent(arguments);
    },

    /**
     * @param { Shopware.apps.PluginManager.model.Price } price
     * @returns { Ext.container.Container }
     */
    createCommunityTab: function(price) {
        var me = this,
            items = [];

        items.push({
            xtype: 'plugin-manager-container-container',
            cls: 'button buy',
            html: '<div class="text">{s name="show/in/community/store"}Show in store{/s}</div>',
            handler: Ext.bind(me.onShowInCommunityStore, me)
        });

        items.push({
            xtype: 'component',
            cls: 'price',
            html: '{s name="from/price/prefix"}from{/s} ' + me.formatPrice(price.get('price')) + ' *'
        });

        if (price.get('subscription')) {
            items.push({
                xtype: 'component',
                cls: 'subscription',
                html: '<div class="icon">U</div>' +
                '<div class="text">{s name="subscription_info"}Incl. updates for 12 Months (subscription){/s}</div>'
            });
        }

        return Ext.create('Ext.container.Container', {
            title: '{s name="community/store/tab/header"}Community store{/s}',
            cls: 'tab buy-tab',
            height: 110,
            items: items
        });
    },

    /**
     * This is the event handler for the buy button. If the buy button is
     * clicked open a new window with the given URL to the Store.
     */
    onShowInCommunityStore: function() {
        window.open(this.plugin.get('link'));
    },

    /**
     * @param { object } prices
     */
    getLowestPrice: function(prices) {
        if (prices.lowestPrice) {
            return Ext.create('Shopware.apps.PluginManager.model.Price', {
                price: prices.lowestPrice,
                subscription: prices.buyPrice && prices.buyPrice.get('subscription') ? prices.buyPrice.get('subscription') : false,
                type: 'buy'
            });
        }

        if (prices.freePrice) {
            return prices.freePrice;
        }

        if (prices.buyPrice) {
            return prices;
        }

        return Ext.create('Shopware.apps.PluginManager.model.Price', {
            price: 0,
            subscription: false,
            type: 'free'
        });
    },

    createContactTab: function() {
        var me = this, items = [];

        items.push({
            xtype: 'plugin-manager-container-container',
            cls: 'button contact',
            html: '<div class="text">{s name="contact_text"}Contact producer{/s}</div>',
            handler: function() {
                var link = '{s name="contact_link"}http://store.shopware.com/en/contact-producer{/s}?technicalName=' + me.plugin.get('technicalName');
                window.open(link);
            }
        });

        return Ext.create('Ext.container.Container', {
            title: '{s name="contact_version"}Contact{/s}',
            cls: 'tab',
            height: 110,
            items: items
        });
    },

    createFreeTab: function(price) {
        var me = this, items = [];

        items.push({
            xtype: 'plugin-manager-container-container',
            cls: 'button free',
            html: '<div class="text">{s name="download_now"}Download now{/s}</div>',
            handler: function() {
                me.downloadFreePluginEvent(me.plugin, price);
            }
        });

        items.push({
            xtype: 'component',
            cls: 'price-free',
            html: '{s name="for_free"}Free{/s}'
        });

        return Ext.create('Ext.container.Container', {
            title: '{s name="free_version"}Free version{/s}',
            cls: 'tab',
            height: 110,
            items: items
        });
    },

    createBuyTab: function(price) {
        var me = this,
            priceString,
            items = [];

        items.push({
            xtype: 'plugin-manager-container-container',
            cls: 'button buy',
            html: '<div class="text">{s name="buy_now"}Buy now{/s}</div>',
            handler: function() {
                me.buyPluginEvent(me.plugin, price);
            }
        });

        priceString = me.formatPrice(price.get('price')) + ' *';

        if (price.get('discount') > 0) {
            priceString = Ext.String.format('<span class="reduced">[0]</span> <span class="original">[1]</small>',
                priceString,
                me.formatPrice((price.get('price') / (100 - price.get('discount')) * 100))
            );
        }

        items.push({
            xtype: 'component',
            cls: 'price',
            html: priceString
        });

        if (price.get('subscription')) {
            items.push({
                xtype: 'component',
                cls: 'subscription',
                html: '<div class="icon">U</div>' +
                '<div class="text">{s name="subscription_info"}Incl. updates for 12 Months (subscription){/s}</div>'
            });
        }

        return Ext.create('Ext.container.Container', {
            title: '{s name="buy_version"}Purchase version{/s}',
            cls: 'tab buy-tab',
            height: 110,
            items: items
        });
    },

    createRentTab: function(price) {
        var me = this, items = [];

        items.push({
            xtype: 'plugin-manager-container-container',
            cls: 'button rent',
            html: '<div class="text">{s name="rent_now"}Rent now{/s}</div>',
            handler: function() {
                me.rentPluginEvent(me.plugin, price);
            }
        });

        items.push({
            xtype: 'component',
            cls: 'price',
            html: me.formatPrice(price.get('price')) + ' * <div class="month">/ {s name="per_month"}per month{/s}</div>'
        });

        items.push({
            xtype: 'component',
            cls: 'subscription',
            html: '<div class="icon">U</div>' +
            '<div class="text">{s name="rent_subscription_info"}All updates included during renting period{/s}</div>'
        });

        items.push({
            xtype: 'component',
            cls: 'dismissal',
            html: '{s name="rent_cancel"}Is cancelable on a monthly basis.{/s}'
        });

        return Ext.create('Ext.container.Container', {
            title: '{s name="rent_version"}Rent version{/s}',
            cls: 'tab rent-tab',
            height: 110,
            items: items
        });
    },

    createTestTab: function(price) {
        var me = this, items = [];

        items.push({
            xtype: 'plugin-manager-container-container',
            cls: 'button test',
            html: '<div class="text">{s name="request_test_version"}Request test version{/s}</div>',
            handler: function() {
                me.requestPluginTestVersionEvent(me.plugin, price);
            }
        });

        items.push({
            xtype: 'component',
            cls: 'price-free',
            html: '{s name="for_free"}Free{/s}'
        });

        return Ext.create('Ext.container.Container', {
            title: '{s name="test_version"}Test version{/s}',
            cls: 'tab',
            height: 110,
            items: items
        });
    },

    getPriceByType: function(prices, type) {
        var price = null;

        prices.each(function(item) {
            if (item.get('type') === type) {
                price = item;
            }
        });
        return price;
    },

    formatPrice: function(value) {
        return Ext.util.Format.currency(value, ' €', 2, true);
    }

});
// {/block}
