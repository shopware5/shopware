
Ext.define('Shopware.apps.ProductStream.store.Attribute', {
    extend:'Ext.data.Store',
    fields: [ 'column', 'description' ],
    autoLoad: false,
    pageSize: 15,
    proxy:{
        type:'ajax',
        url: '{url controller=ProductStream action=getAttributes}',
        reader:{
            type:'json',
            root:'data',
            totalProperty:'total'
        }
    }
});
