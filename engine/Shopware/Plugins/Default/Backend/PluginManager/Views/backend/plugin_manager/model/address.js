
Ext.define('Shopware.apps.PluginManager.model.Address', {
    extend: 'Ext.data.Model',

    fields: [
        { name: 'countryName', type: 'string' },
        { name: 'zipCode', type: 'string' },
        { name: 'city', type: 'string' },
        { name: 'street', type: 'string' },
        { name: 'email', type: 'string' },
        { name: 'firstName', type: 'string' },
        { name: 'lastName', type: 'string' }
    ]
});
