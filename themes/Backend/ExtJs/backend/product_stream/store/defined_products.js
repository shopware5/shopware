
Ext.define('Shopware.apps.ProductStream.store.DefinedProducts', {
    extend: 'Ext.data.Store',
    model: 'Shopware.apps.Base.model.Article',
    autoLoad: false,
    pageSize: 25,
    proxy:{
        type: 'ajax',
        url: '{url controller=ProductStream action=loadDefinedProducts}',
        extraParams: {
            streamId: null
        },
        reader:{
            type:'json',
            root:'data',
            totalProperty:'total'
        }
    }
});