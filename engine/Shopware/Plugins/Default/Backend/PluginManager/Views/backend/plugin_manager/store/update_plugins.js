
Ext.define('Shopware.apps.PluginManager.store.UpdatePlugins', {
    extend: 'Ext.data.Store',

    model: 'Shopware.apps.PluginManager.model.Plugin',

    pageSize: 500,

    proxy: {
        type: 'ajax',
        api: {
            read: '{url controller="PluginManager" action="updateListing"}'
        },
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    }
});