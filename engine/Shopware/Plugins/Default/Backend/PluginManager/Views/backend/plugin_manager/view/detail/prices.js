
//{namespace name=backend/plugin_manager/translation}
Ext.define('Shopware.apps.PluginManager.view.detail.Prices', {
    extend: 'PluginManager.tab.Panel',
    cls: 'store-plugin-detail-prices-tab shopware-plugin-manager-tab',
    margin: '10 0',

    mixins: {
        events: 'Shopware.apps.PluginManager.view.PluginHelper'
    },

    initComponent: function() {
        var me = this, items = [];

        var buyPrice = me.getPriceByType(me.prices, 'buy');
        var rentPrice = me.getPriceByType(me.prices, 'rent');
        var testPrice = me.getPriceByType(me.prices, 'test');
        var freePrice = me.getPriceByType(me.prices, 'free');

        if (buyPrice) {
            items.push(me.createBuyTab(buyPrice));
        }
        if (rentPrice) {
            items.push(me.createRentTab(rentPrice));
        }
        if (testPrice) {
            items.push(me.createTestTab(testPrice));
        }
        if (freePrice) {
            items.push(me.createFreeTab(freePrice));
        }
        if (items.length <= 0 && me.plugin.get('useContactForm')) {
            items.push(me.createContactTab());
        }

        me.items = items;

        me.callParent(arguments);
    },

    createContactTab: function() {
        var me = this, items = [];


        items.push({
            xtype: 'plugin-manager-container-container',
            cls: 'button contact',
            html: '<div class="text">{s name="contact_text"}{/s}</div>',
            handler: function() {
                var link = '{s name="contact_link"}{/s}?technicalName=' + me.plugin.get('technicalName');
                window.open(link);
            }
        });

        return Ext.create('Ext.container.Container', {
            title: '{s name="contact_version"}{/s}',
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
            html: '<div class="text">{s name="download_now"}{/s}</div>',
            handler: function() {
                me.downloadFreePluginEvent(me.plugin, price);
            }
        });

        items.push({
            xtype: 'component',
            cls: 'price-free',
            html: '{s name="for_free"}{/s}'
        });

        return Ext.create('Ext.container.Container', {
            title: '{s name="free_version"}{/s}',
            cls: 'tab',
            height: 110,
            items: items
        });
    },


    createBuyTab: function(price) {
        var me = this, items = [];

        items.push({
            xtype: 'plugin-manager-container-container',
            cls: 'button buy',
            html: '<div class="text">{s name="buy_now"}{/s}</div>',
            handler: function() {
                me.buyPluginEvent(me.plugin, price);
            }
        });

        items.push({
            xtype: 'component',
            cls: 'price',
            html: me.formatPrice(price.get('price')) + ' *'
        });

        if (price.get('subscription')) {
            items.push({
                xtype: 'component',
                cls: 'subscription',
                html: '<div class="icon">U</div>' +
                '<div class="text">{s name="subscription_info"}{/s}</div>'
            });
        }

        return Ext.create('Ext.container.Container', {
            title: '{s name="buy_version"}{/s}',
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
            html: '<div class="text">{s name="rent_now"}{/s}</div>',
            handler: function() {
                me.rentPluginEvent(me.plugin, price);
            }
        });

        items.push({
            xtype: 'component',
            cls: 'price',
            html: me.formatPrice(price.get('price')) + ' * <div class="month">/ {s name="per_month"}{/s}</div>'
        });

        items.push({
            xtype: 'component',
            cls: 'subscription',
            html: '<div class="icon">U</div>' +
            '<div class="text">{s name="rent_subscription_info"}{/s}</div>'
        });

        items.push({
            xtype: 'component',
            cls: 'dismissal',
            html: '{s name="rent_cancel"}{/s}'
        });

        return Ext.create('Ext.container.Container', {
            title: '{s name="rent_version"}{/s}',
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
            html: '<div class="text">{s name="request_test_version"}{/s}</div>',
            handler: function() {
                me.requestPluginTestVersionEvent(me.plugin, price);
            }
        });

        items.push({
            xtype: 'component',
            cls: 'price-free',
            html: '{s name="for_free"}{/s}'
        });

        return Ext.create('Ext.container.Container', {
            title: '{s name="test_version"}{/s}',
            cls: 'tab',
            height: 110,
            items: items
        });
    },

    getPriceByType: function(prices, type) {
        var me = this, price = null;

        prices.each(function(item) {
            if (item.get('type') == type) {
                price = item;
            }
        });
        return price;
    },

    formatPrice: function(value) {
        return Ext.util.Format.currency(value, ' €', 2, true);
    }

});