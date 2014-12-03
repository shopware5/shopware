
Ext.define('Shopware.apps.PluginManager.model.Producer', {
    extend: 'Ext.data.Model',

    fields: [
        { name: 'id', type: 'int' },
        { name: 'name', type: 'string' },
        { name: 'description', type: 'string' },
        { name: 'prefix', type: 'string' },
        { name: 'website', type: 'string' },
        { name: 'iconPath', type: 'string' }
    ]
});