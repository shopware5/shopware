
Ext.define('Shopware.model.Error', {

    extend:'Ext.data.Model',

    phantom: true,

    fields:[
        { name: 'success', type: 'boolean' },
        { name: 'request' },
        { name: 'error', type: 'string' },
        { name: 'operation' },
    ],


    setOperation: function(operation) {
        var me = this;

        me.set('success', operation.wasSuccessful());
        me.set('error', operation.getError());
        me.set('request', operation.request);
        me.set('operation', operation);

        console.log(me);
    }

});


