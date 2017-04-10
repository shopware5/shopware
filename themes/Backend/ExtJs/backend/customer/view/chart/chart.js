//{namespace name=backend/customer_stream/translation}

Ext.define('Shopware.apps.Customer.view.chart.Chart', {

    extend: 'Ext.chart.Chart',

    shadow: true,
    margin: 30,
    legend: true,
    animate: true,


    initComponent: function () {
        var me = this;

        me.series = me.createSeries();

        me.axes = me.createAxes();

        me.callParent(arguments);
    },

    getAxesFields: function () {
        var me = this,
            fields = [];

        Ext.each(me.getFields(), function(item) {
            fields.push(item.name);
        });
        return fields;
    },

    getFields: function () {
        return [];
    },

    createAxes: function () {
        var me = this;
        return [{
            type: 'Numeric',
            position: 'left',
            fields: me.getAxesFields(),
            label: {
                renderer: Ext.util.Format.numberRenderer('0,0')
            },
            title: '{s name="window/amount"}Amout{/s}',
            grid: true,
            minimum: 0
        }, {
            type: 'Category',
            position: 'bottom',
            title: '{s name="chart_month"}Month{/s}',
            fields: ['yearMonth']
        }];

    },

    createSeries: function () {
        var me = this,
            series = [];

        Ext.each(me.getFields(), function(item) {
            if (item.hasOwnProperty('title')) {
                series.push(me.createLineSeries(item.name, item.title));
            } else {
                series.push(me.createLineSeries(item.name, item.name));
            }
        });

        return series;
    },

    createLineSeries: function(field, title) {
        return {
            type: 'line',
            highlight: { size: 7, radius: 7 },
            axis: 'left',
            fill: true,
            title: title,
            smooth: true,
            xField: 'yearMonth',
            yField: field,
            markerConfig: { type: 'circle', size: 4, radius: 4, 'stroke-width': 0 }
        };
    }
});