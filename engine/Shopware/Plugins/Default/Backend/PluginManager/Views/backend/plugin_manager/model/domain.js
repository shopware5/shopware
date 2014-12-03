

Ext.define('Shopware.apps.PluginManager.model.Domain', {
    extend: 'Ext.data.Model',

    fields: [
        { name: 'domain', type: 'string' },
        { name: 'balance', type: 'float' },
        { name: 'dispo', type: 'float' },
        { name: 'isPartner', type: 'boolean' }
    ]
});
