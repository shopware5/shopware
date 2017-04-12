/* global Ext */
// {namespace name=backend/customer_stream/translation}

Ext.define('Shopware.apps.Customer.view.chart.MetaChart', {

    extend: 'Shopware.apps.Customer.view.chart.Chart',

    initComponent: function () {
        var me = this;
        me.store = Ext.create('Shopware.apps.Customer.store.MetaChart');
        me.callParent(arguments);
    },

    getFields: function () {
        return [
            { name: 'count_orders', title: '{s name=window/number_of_orders}Numer of orders{/s}' },
            { name: 'invoice_amount_avg', title: '{s name=window/order_avg}Ø Cart{/s}' },
            { name: 'invoice_amount_max', title: '{s name=window/max_order}Most expensive order{/s}' },
            { name: 'invoice_amount_min', title: '{s name=window/min_order}Least expensive order{/s}' },
            { name: 'invoice_amount_sum', title: '{s name=window/total_revenue}Total revenue{/s}' },
            { name: 'product_avg', title: '{s name=window/merchandise_value}Ø Merchandise value{/s}' }
        ];
    }
});
