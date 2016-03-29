
//{namespace name="backend/attributes/main"}

Ext.define('Shopware.apps.Attributes.store.Entities', {
    extend: 'Ext.data.Store',
    fields: [
        { name: 'label', type: 'string' },
        { name: 'entity', type: 'string' }
    ],
    proxy: {
        type: 'ajax',
        url: '{url controller="Attributes" action="getEntities"}',
        reader: { type: 'json', root: 'data' }
    }
});
