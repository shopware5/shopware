
Ext.define('Shopware.apps.PluginManager.store.LocalPlugin', {
    extend:'Shopware.store.Listing',

    pageSize: 20000,

    remoteSort: false,
    remoteFilter: false,

    groupers: [{
        property: 'groupingState',
        direction: 'DESC'
    }],

    sorters: [{
        property: 'plugin.active',
        direction: 'DESC'
    }, {
        property: 'plugin.installation_date',
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
