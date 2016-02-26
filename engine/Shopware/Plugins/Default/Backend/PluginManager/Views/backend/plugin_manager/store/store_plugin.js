
Ext.define('Shopware.apps.PluginManager.store.StorePlugin', {
    extend: 'Ext.data.Store',

    model: 'Shopware.apps.PluginManager.model.Plugin',

    pageSize: 30,

    proxy: {
        type: 'ajax',
        api: {
            read: '{url controller="PluginManager" action="storeListing"}'
        },
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    }
});
