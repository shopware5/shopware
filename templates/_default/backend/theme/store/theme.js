
Ext.define('Shopware.apps.Theme.store.Theme', {
    extend:'Shopware.store.Listing',
    model: 'Shopware.apps.Theme.model.Theme',

    groupField: 'version',

    groupDir: 'DESC',

    configure: function() {
        return {
            controller: 'Theme'
        };
    }
});