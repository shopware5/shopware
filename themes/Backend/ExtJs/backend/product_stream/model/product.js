
Ext.define('Shopware.apps.ProductStream.model.Product', {
    extend: 'Ext.data.Model',
    fields: [
        { name : 'id', type: 'int', useNull: true },
        { name : 'number', type: 'string' },
        { name : 'name', type: 'string' },
        { name : 'stock', type: 'int' },
        { name : 'cheapestPrice' }
    ]
});
