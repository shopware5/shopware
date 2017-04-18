// {namespace name=backend/customer_stream/translation}

Ext.define('Shopware.apps.Customer.view.chart.AmountChartFactory', {

    createChart: function (streamStore, callback) {
        var fields = [];
        var modelFields = [];

        streamStore.each(function (item) {
            fields.push({ name: item.get('name') });
            modelFields.push({ name: item.get('name'), type: 'float' });
        });

        fields.push({ name: 'unassigned' });
        modelFields.push({ name: 'unassigned', type: 'float' });
        modelFields.push({ name: 'yearMonth', type: 'string' });

        var store = Ext.create('Ext.data.Store', {
            fields: modelFields,
            proxy: {
                type: 'ajax',
                url: '{url controller="CustomerStream" action="loadAmountPerStreamChart"}',
                reader: {
                    type: 'json',
                    root: 'data'
                }
            }
        }).load({
            callback: function () {
                var chart = Ext.create('Shopware.apps.Customer.view.chart.Chart', {
                    store: store,
                    getFields: function() {
                        return fields;
                    }
                });
                callback(chart);
            }
        });
    }

});
