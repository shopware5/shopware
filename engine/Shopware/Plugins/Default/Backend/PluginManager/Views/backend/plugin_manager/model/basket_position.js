
Ext.define('Shopware.apps.PluginManager.model.BasketPosition', {
    extend: 'Ext.data.Model',

    fields: [
        { name: 'technicalName', type: 'string' },
        { name: 'priceType', type: 'string' },
        { name: 'price', type: 'float' }
    ],

    associations: [{
        type: 'hasMany',
        model: 'Shopware.apps.PluginManager.model.Plugin',
        name: 'getPlugin',
        associationKey: 'plugin'
    }]

});
