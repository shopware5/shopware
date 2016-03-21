
//{namespace name="backend/attributes/main"}

Ext.define('Shopware.apps.Attributes.store.Types', {
    extend: 'Ext.data.Store',
    proxy: {
        type: 'ajax',
        url: '{url controller="Attributes" action="getTypes"}',
        reader: { type: 'json', root: 'data' }
    },
    model: 'Shopware.apps.Attributes.model.Types'
});
