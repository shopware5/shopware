
Ext.define('Shopware.apps.PluginManager.model.Licence', {
    extend: 'Ext.data.Model',

    fields: [
        { name: 'id', type: 'int' },
        { name: 'label', type: 'string' },
        { name: 'technicalName', type: 'string' },
        { name: 'iconPath', type: 'string' },
        { name: 'shop', type: 'string' },
        { name: 'subscription', type: 'string' },
        { name: 'creationDate', type: 'datetime' },
        { name: 'expirationDate', type: 'datetime' },
        { name: 'licenseKey', type: 'string' },
        { name: 'binaryLink', type: 'string' },
        { name: 'binaryVersion', type: 'string' },

        { name: 'priceColumn', type: 'string' }
    ],

    associations: [{
        type: 'hasMany',
        model: 'Shopware.apps.PluginManager.model.Price',
        name: 'getPrice',
        associationKey: 'priceModel'
    }]
});