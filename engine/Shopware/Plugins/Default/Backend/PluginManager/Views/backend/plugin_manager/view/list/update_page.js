
Ext.define('Shopware.apps.PluginManager.view.list.UpdatePage', {
    extend: 'Ext.container.Container',
    autoScroll: true,
    alias: 'widget.plugin-manager-update-page',

    initComponent: function() {
        var me = this;

        me.items = [ me.createStoreListing() ];

        me.callParent(arguments);
    },

    createStoreListing: function() {
        var me = this;

        me.updateStore = Ext.create('Shopware.apps.PluginManager.store.UpdatePlugins');

        me.listing = Ext.create('PluginManager.components.Listing', {
            store: me.updateStore,
            padding: 30,
            width: 1007
        });

        me.content = Ext.create('Ext.container.Container', {
            items: [
                me.listing
            ]
        });

        return me.content;
    }
});