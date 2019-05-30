//{namespace name="backend/benchmark/categories"}
//{block name="backend/benchmark/store/shop_configs"}
Ext.define('Shopware.apps.Benchmark.store.ShopConfigs', {
    extend: 'Ext.data.Store',
    autoLoad: false,
    model: 'Shopware.apps.Benchmark.model.ShopConfig',

    proxy: {
        type: 'ajax',
        url: '{url action=getShopConfigs}',
        reader: {
            type: 'json',
            root: 'data'
        }
    },
});
//{/block}
