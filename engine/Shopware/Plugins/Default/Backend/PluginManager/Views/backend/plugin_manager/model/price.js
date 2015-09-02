
Ext.define('Shopware.apps.PluginManager.model.Price', {
    extend: 'Ext.data.Model',

    fields: [
        { name: 'id', type: 'int' },
        { name: 'type', type: 'string' },
        { name: 'duration', type: 'string' },
        { name: 'price', type: 'float' },
        { name: 'subscription', type: 'boolean' }
    ]
});