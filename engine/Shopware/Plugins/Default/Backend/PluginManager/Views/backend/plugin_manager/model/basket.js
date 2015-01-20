
Ext.define('Shopware.apps.PluginManager.model.Basket', {
    extend: 'Ext.data.Model',

    fields: [
        { name: 'grossPrice', type: 'float' },
        { name: 'netPrice', type: 'float' },
        { name: 'taxPrice', type: 'float' },
        { name: 'taxRate', type: 'string' },
        { name: 'bookingDomain', type: 'string' },
        { name: 'licenceDomain', type: 'string' }
    ],

    associations: [
    {
        type: 'hasMany',
        model: 'Shopware.apps.PluginManager.model.BasketPosition',
        name: 'getPositions',
        associationKey: 'positions'
    } ,
    {
        type: 'hasMany',
        model: 'Shopware.apps.PluginManager.model.Domain',
        name: 'getDomains',
        associationKey: 'domains'
    } ,
    {
        type: 'hasMany',
        model: 'Shopware.apps.PluginManager.model.Address',
        name: 'getAddress',
        associationKey: 'address'
    }
    ]

});
