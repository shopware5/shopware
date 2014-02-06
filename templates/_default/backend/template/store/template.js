
Ext.define('Shopware.apps.Template.store.Template', {
    extend:'Shopware.store.Listing',

    configure: function() {
        return {
            controller: 'Template'
        };
    },
    model: 'Shopware.apps.Template.model.Template'
});