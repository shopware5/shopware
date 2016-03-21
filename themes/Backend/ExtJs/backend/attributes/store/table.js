
//{namespace name="backend/attributes/main"}

Ext.define('Shopware.apps.Attributes.store.Table', {
    extend: 'Ext.data.Store',
    proxy: {
        type: 'ajax',
        url: '{url controller="Attributes" action="getTables"}',
        reader: { type: 'json', root: 'data' }
    },
    model: 'Shopware.apps.Attributes.model.Table'
});
