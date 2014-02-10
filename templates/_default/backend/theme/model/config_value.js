
Ext.define('Shopware.apps.Theme.model.ConfigValue', {
    extend: 'Ext.data.Model',

    fields: [
        { name: 'id', type: 'int', useNull: true },
        { name: 'elementId', type: 'int' },
        { name: 'shopId', type: 'int' },
        { name: 'value' }
    ]
});

