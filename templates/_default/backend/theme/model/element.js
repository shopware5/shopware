
Ext.define('Shopware.apps.Theme.model.Element', {
    extend: 'Shopware.data.Model',

    fields: [
        { name: 'id', type: 'int', useNull: true },
        { name: 'type', type: 'string' },
        { name: 'name', type: 'string' },
        { name: 'position', type: 'int' },
        { name: 'defaultValue', useNull: true },
        { name: 'selection', useNull: true },
        { name: 'fieldLabel', type: 'string', useNull: true },
        { name: 'supportText', type: 'string', useNull: true },
        { name: 'allowBlank', type: 'boolean', defaultValue: true },
        { name: 'tab', type: 'string' },

        //mapping fields which used only for the form field generation
        { name: 'value', mapping: 'defaultValue' },
        { name: 'xtype', type: 'string', mapping: 'type' },
        { name: 'elementId', type: 'string', mapping: 'id' }
    ],

    associations: [
        {
            type: 'hasMany',
            model: 'Shopware.apps.Theme.model.ConfigValue',
            name: 'getConfigValues',
            associationKey: 'values'
        }
    ]
});

