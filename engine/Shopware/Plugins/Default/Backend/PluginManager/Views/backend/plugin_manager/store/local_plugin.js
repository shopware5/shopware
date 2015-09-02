
Ext.define('Shopware.apps.PluginManager.store.LocalPlugin', {
    extend:'Shopware.store.Listing',

    pageSize: 20000,

    remoteSort: true,
    remoteFilter: false,

    groupers: [{
        property: 'groupingState',
        direction: 'DESC'
    }],

    configure: function() {
        return {
            controller: 'PluginManager',
            proxy: {
                type: 'ajax',
                api: {
                    read: '{url controller="PluginManager" action="localListing"}'
                },
                reader: {
                    type: 'json',
                    root: 'data',
                    totalProperty: 'total'
                }
            }
        };
    },

    model: 'Shopware.apps.PluginManager.model.Plugin'
});
