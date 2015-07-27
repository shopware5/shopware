
Ext.define('Shopware.apps.PluginManager.view.detail.Window', {
    extend: 'Enlight.app.Window',

    cls: 'plugin-manager-window detail-window',
    alias: 'widget.plugin-manager-detail-window',

    height: '90%',
    minWidth: 995,
    autoScroll: true,
    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    initComponent: function() {
        var me = this;

        me.detailContainer = Ext.create('Shopware.apps.PluginManager.view.detail.Container');

        me.items = [ me.detailContainer ];

        me.callParent(arguments);

        me.on('afterrender', function() {
            //fix to prevent scrolling after tab change
            me.setHeight(me.getEl().dom.clientHeight + 1);
        });

    },

    setActivePriceTab: function(priceName) {
        var me = this;

        if (!me.detailContainer.pricesContainer) {
            return;
        }
        var tabIndex = me.detailContainer.pricesContainer.tabIndex[priceName];
        me.detailContainer.pricesContainer.navigationClick(tabIndex);
    },

    loadRecord: function(plugin) {
        var me = this;

        me.setTitle(plugin.get('label'));

        me.plugin = plugin;
        me.detailContainer.loadRecord(plugin);
    }
});
