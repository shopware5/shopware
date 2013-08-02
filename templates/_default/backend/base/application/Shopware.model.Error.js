
Ext.define('Shopware.model.Error', {

    extend:'Ext.data.Model',

    fields:[
        { name: 'success', type: 'boolean' },
        { name: 'request' },
        { name: 'params' },
        { name: 'error', type: 'string' },
        { name: 'operation' },

    ],


    setOperation: function(operation) {
        var me = this;

        me.set('success', operation.wasSuccessful());
        me.set('error', operation.getError());
        me.set('request', operation.request);
        me.set('params', true);
        me.set('operation', operation);
    }

});


