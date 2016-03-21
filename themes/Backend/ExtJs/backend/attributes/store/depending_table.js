
//{namespace name="backend/attributes/main"}

Ext.define('Shopware.apps.Attributes.store.DependingTable', {
    extend: 'Ext.data.Store',
    proxy: {
        type: 'ajax',
        url: '{url controller="Attributes" action="getColumn"}',
        reader: { type: 'json', root: 'data' }
    },
    model: 'Shopware.model.AttributeConfig'
});
