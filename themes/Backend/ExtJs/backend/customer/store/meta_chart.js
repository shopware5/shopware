
Ext.define('Shopware.apps.Customer.store.MetaChart', {
    extend: 'Ext.data.Store',

    fields:[
        { name:'count_orders', type: 'int' },
        { name:'invoice_amount_avg', type: 'float' },
        { name:'invoice_amount_max', type: 'float' },
        { name:'invoice_amount_min', type: 'float' },
        { name:'invoice_amount_sum', type: 'float' },
        { name:'product_avg', type: 'float' },
        { name:'yearMonth', type: 'string' }
    ],
    proxy:{
        type:'ajax',
        url: '{url controller="CustomerStream" action="loadChart"}',
        reader:{
            type:'json',
            root:'data'
        }
    }
});