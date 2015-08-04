
Ext.define('Shopware.apps.ProductStream.store.Preview', {
    extend: 'Ext.data.Store',
    model: 'Shopware.apps.ProductStream.model.Product',
    proxy: {
        type: 'ajax',
        api: {
            read: '{url controller="ProductStream" action="loadPreview"}'
        },
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    }
});