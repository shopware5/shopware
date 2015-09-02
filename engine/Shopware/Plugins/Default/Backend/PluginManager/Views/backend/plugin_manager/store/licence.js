
Ext.define('Shopware.apps.PluginManager.store.Licence', {
    extend: 'Ext.data.Store',

    model: 'Shopware.apps.PluginManager.model.Licence',

    pageSize: 500,

    proxy: {
        type: 'ajax',
        api: {
            read: '{url controller="PluginManager" action="licenceList"}'
        },
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    }
});