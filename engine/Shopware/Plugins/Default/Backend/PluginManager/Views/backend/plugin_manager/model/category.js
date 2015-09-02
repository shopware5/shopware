
Ext.define('Shopware.apps.PluginManager.model.Category', {
    extend: 'Ext.data.Model',

    fields: [
        { name: 'id', type: 'int' },
        { name: 'name', type: 'string' },
        { name: 'parentId', type: 'int', useNull: true }
    ],

    associations: [{
        type: 'hasMany',
        model: 'Shopware.apps.PluginManager.model.Category',
        name: 'getChildren',
        associationKey: 'children'
    }]
});
