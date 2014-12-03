
Ext.define('Shopware.apps.PluginManager.store.Basket', {
    extend: 'Ext.data.Store',

    proxy: {
        type: 'ajax',
        api: {
            read: '{url controller="PluginManager" action="checkout"}'
        },
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    },

    model: 'Shopware.apps.PluginManager.model.Basket'
});
