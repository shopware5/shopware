
Ext.define('Shopware.apps.Theme.model.Theme', {
    extend: 'Shopware.data.Model',

    configure: function() {
        return {
            controller: 'Theme',
            detail: 'Shopware.apps.Theme.view.create.Theme'
        };
    },

    fields: [
        { name : 'id', type: 'int', useNull: true },
        { name : 'name', type: 'string' },
        { name : 'description', type: 'string' },
        { name : 'author', type: 'string' },
        { name : 'license', type: 'string' },
        { name : 'screen', type: 'string' },
        { name : 'esi', type: 'boolean' },
        { name : 'emotion', type: 'boolean' },
        { name : 'style', type: 'boolean' },
        { name : 'version', type: 'int' },
        { name : 'pluginId', type: 'int' },

        { name : 'parentId', type: 'int', useNull: true, defaultValue: null },

        { name : 'screen', type: 'string' },
        { name : 'enabled', type: 'boolean', defaultValue: false },
        { name : 'preview', type: 'boolean', defaultValue: false }
    ],

    associations: [
        {
            relation: 'OneToMany',
            type: 'hasMany',
            model: 'Shopware.apps.Theme.model.Element',
            name: 'getElements',
            associationKey: 'elements'
        }
    ]

});

