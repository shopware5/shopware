
Ext.define('Shopware.apps.PluginManager.model.Comment', {
    extend: 'Ext.data.Model',

    fields: [
        { name: 'rating', type: 'int' },
        { name: 'creationDate', type: 'datetime' },
        { name: 'headline', type: 'string' },
        { name: 'author', type: 'string' },
        { name: 'text', type: 'string' }
    ]
});