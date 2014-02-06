
Ext.define('Shopware.apps.Template.model.Template', {
    extend: 'Shopware.data.Model',

    configure: function() {
        return {
            controller: 'Template',
            detail: 'Shopware.apps.Template.view.detail.Template'
        };
    },


    fields: [
        { name : 'id', type: 'int', useNull: true },
        { name : 'name', type: 'string' },
        { name : 'template', type: 'string' },
        { name : 'description', type: 'string' },
        { name : 'author', type: 'string' },
        { name : 'license', type: 'string' },
        { name : 'esi', type: 'boolean' },
        { name : 'emotion', type: 'boolean' },
        { name : 'style', type: 'boolean' },
        { name : 'version', type: 'int' },
        { name : 'pluginId', type: 'int' }
    ]
});

