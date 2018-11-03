//{namespace name="backend/benchmark/categories"}
//{block name="backend/benchmark/model/shop_config"}
Ext.define('Shopware.apps.Benchmark.model.ShopConfig', {
    extend: 'Ext.data.Model',

    fields: [
        //{block name="backend/benchmark/model/shop_config/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'shopId', type: 'int' },
        { name: 'shopName', type: 'string' },
        { name: 'active', type: 'boolean' },
        { name: 'lastSent', type: 'date', dateFormat: 'd.m.Y H:i:s' },
        { name: 'lastReceived', type: 'date', dateFormat: 'd.m.Y H:i:s' },
        { name: 'lastOrderId', type: 'int' },
        { name: 'lastCustomerId', type: 'int' },
        { name: 'lastProductId', type: 'int' },
        { name: 'batchSize', type: 'int' },
        { name: 'industry', type: 'int' },
        { name: 'type', type: 'string' },
        { name: 'responseToken', type: 'string', useNull: true }
    ],
});
//{/block}
