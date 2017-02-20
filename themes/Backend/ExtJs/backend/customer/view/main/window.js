/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 *
 * @category   Shopware
 * @package    Customer
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/customer/view/main}

/**
 * Shopware UI - Customer list main window.
 *
 * todo@all: Documentation
 */
//{block name="backend/customer/view/main/window"}
Ext.define('Shopware.apps.Customer.view.main.Window', {
    /**
     * Define that the customer main window is an extension of the enlight application window
     * @string
     */
    extend:'Enlight.app.Window',
    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'customer-list-window',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.customer-list-main-window',
    /**
     * Set no border for the window
     * @boolean
     */
    border:false,
    /**
     * True to automatically show the component upon creation.
     * @boolean
     */
    autoShow:true,
    /**
     * Set border layout for the window
     * @string
     */
    layout:'border',
    /**
     * Define window width
     * @integer
     */
    width:'90%',
    /**
     * Define window height
     * @integer
     */
    height:'90%',
    /**
     * True to display the 'maximize' tool button and allow the user to maximize the window, false to hide the button and disallow maximizing the window.
     * @boolean
     */
    maximizable:true,
    /**
     * True to display the 'minimize' tool button and allow the user to minimize the window, false to hide the button and disallow minimizing the window.
     * @boolean
     */
    minimizable:true,
    /**
     * A flag which causes the object to attempt to restore the state of internal properties from a saved state on startup.
     */
    stateful:true,
    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId:'shopware-customer-main-window',
    /**
     * Set window title which is displayed in the window header
     * @string
     */
    title:'{s name=window_title}Customer list{/s}',

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        toolbar:{
            add:'{s name=toolbar/button_add}Add{/s}',
            remove:'{s name=toolbar/button_delete}Delete all selected{/s}',
            customerGroup:'{s name=toolbar/customer_group}Customer group{/s}',
            groupEmpty:'{s name=toolbar/customer_group_empty}Select...{/s}',
            search:'{s name=toolbar/search_empty_text}Search...{/s}'
        }
    },

    mixins: {
        batch: 'Shopware.helper.BatchRequests'
    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent:function () {
        var me = this;

        Ext.suspendLayouts();
        me.listStore = Ext.create('Shopware.apps.CustomerStream.store.Preview', { pageSize: 10}).load({ conditions: null });

        me.gridPanel = Ext.create('Shopware.apps.Customer.view.list.List', { store: me.listStore });
        me.gridPanel.on('selection-changed', Ext.bind(me.customerSelected, me));

        me.streamListing = Ext.create('Shopware.apps.CustomerStream.view.list.CustomerStream', {
            store: Ext.create('Shopware.apps.CustomerStream.store.CustomerStream').load(),
            subApp: me.subApp,
            collapsible: true,
            hideHeaders: true,
            title: 'Definierte Streams',
            height: 200,
            iconCls: 'sprite-product-streams',
            selectionChanged: Ext.bind(me.streamSelected, me)
        });

        me.filterPanel = Ext.create('Shopware.apps.CustomerStream.view.detail.ConditionPanel', { flex: 4 });

        me.chart = me.createChart();

        me.cardContainer = Ext.create('Ext.container.Container', {
            items: [me.gridPanel, me.chart, me.createStreamChart()] ,
            region: 'center',
            layout: 'card'
        });

        me.formPanel = Ext.create('Ext.form.Panel', {
            region: 'west',
            collapsible: true,
            cls: 'shopware-form',
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            width: 300,
            title: 'Filter & Customer Streams',
            items: [
                me.filterPanel,
                me.streamListing
            ]
        });
        //add the customer list grid panel and set the store
        me.items = [
            me.formPanel,
            me.cardContainer
        ];
        me.dockedItems = [ me.getToolbar()];

        Ext.resumeLayouts(true);

        me.callParent(arguments);

        me.resetProgressbar();
    },

    resetProgressbar: function () {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller=CustomerStream action=getLastFullIndexTime}',
            success: function(operation) {
                var response = Ext.decode(operation.responseText);

                Ext.defer(function () {
                    me.indexingBar.updateProgress(0, 'Letzte Analyze am: ' + Ext.util.Format.date(response.last_index_time), true);
                }, 500);
            }
        });
    },

    loadStreamChart: function() {
        var me = this;
        var fields = [];
        var modelFields = [];

        var series = me.streamChart.series;
        series.removeAll(series.items);
        console.log(series);
        series.add(me.createLineSeries('unassigned', 'Nicht zugewiesen'));

        me.streamListing.getStore().each(function (item) {
            fields.push(item.get('name'));
            modelFields.push({ name: item.get('name'), type: 'float' });
            series.add(me.createLineSeries(item.get('name'), item.get('name')));
        });

        var axes = me.streamChart.axes.first();
        // var series = me.streamChart.series.first();
        // series.yField = fields;
        axes.fields = fields;

        fields.push('unassigned');
        modelFields.push({ name: 'unassigned', type: 'float' });
        modelFields.push({ name: 'yearMonth', type: 'string'});

        me.streamChart.setLoading(true);

        me.streamChart.store = me.streamChartStore = Ext.create('Ext.data.Store', {
            fields: modelFields,
            proxy:{
                type:'ajax',
                url: '{url controller="CustomerStream" action="loadAmountPerStreamChart"}',
                reader:{
                    type:'json',
                    root:'data'
                }
            }
        }).load({
            callback: function() {
                me.streamChart.setLoading(false);
                me.streamChart.updateLayout();
            }
        });


        // me.streamChart.redraw();
    },

    createStreamChart: function () {
        var me = this;

        me.streamChartStore = Ext.create('Ext.data.Store', {
            fields: [
                { name: 'unassigned', type: 'float' },
                { name: 'yearMonth', type: 'string'}
            ],
            proxy:{
                type:'ajax',
                url: '{url controller="CustomerStream" action="loadAmountPerStreamChart"}',
                reader:{
                    type:'json',
                    root:'data'
                }
            }
        });

        me.streamChart = Ext.create('Ext.chart.Chart', {
            shadow: true,
            margin: 30,
            flex: 1,
            legend: true,
            store: me.streamChartStore,
            animate: true,
            axes: [{
                type: 'Numeric',
                position: 'left',
                fields: ['unassigned'],
                title: false,
                grid: true
            }, {
                type: 'Category',
                position: 'bottom',
                fields: ['yearMonth'],
                title: false
            }],
            series: [{
                type: 'line',
                highlight: { size: 7, radius: 7 },
                axis: 'left',
                fill: true,
                title: 'Nicht zugewiesen',
                smooth: true,
                xField: 'yearMonth',
                yField: ['unassigned'],
                markerConfig: { type: 'circle', size: 4, radius: 4, 'stroke-width': 0 }
            }],
            // series: [{
            //     type: 'column',
            //     axis: 'left',
            //     gutter: 80,
            //     xField: 'yearMonth',
            //     yField: ['unassigned'],
            //     stacked: true,
            //     tips: {
            //         trackMouse: true,
            //         width: 65,
            //         height: 28,
            //         renderer: function(storeItem, item) {
            //             this.setTitle(item.value[1]);
            //         }
            //     }
            // }]
        });
        return me.streamChart;
    },

    createChart: function () {
        var me = this;

        me.chartStore = Ext.create('Ext.data.Store', {
            fields:[
                { name:'count_orders', type: 'int'},
                { name:'invoice_amount_avg', type: 'float'},
                { name:'invoice_amount_max', type: 'float'},
                { name:'invoice_amount_min', type: 'float'},
                { name:'invoice_amount_sum', type: 'float'},
                { name:'product_avg', type: 'float'},
                { name:'yearMonth', type: 'string'}
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

        return Ext.create('Ext.chart.Chart', {
            shadow:true,
            margin:30,
            legend: true,
            animate:true,
            snippets:{
                yAxis:'{s name=chart/y_axis}Turnover{/s}',
                xAxis:'{s name=chart/x_axis}Month{/s}'
            },
            store: me.chartStore,
            axes: [{
                type: 'Numeric',
                position: 'left',
                fields: [
                    'count_orders',
                    'invoice_amount_avg',
                    'invoice_amount_max',
                    'invoice_amount_min',
                    'invoice_amount_sum',
                    'product_avg'
                ],
                label: {
                    renderer: Ext.util.Format.numberRenderer('0,0')
                },
                title: 'Umsatz',
                grid: true,
                minimum: 0
            }, {
                type: 'Category',
                position: 'bottom',
                fields: ['yearMonth']
            }],
            series: [
                me.createLineSeries('count_orders', 'Anzahl Bestellungen'),
                me.createLineSeries('invoice_amount_avg', 'Ø Warenkorb'),
                me.createLineSeries('invoice_amount_max', 'Größte Bestellung'),
                me.createLineSeries('invoice_amount_min', 'Kleinste Bestellung'),
                me.createLineSeries('invoice_amount_sum', 'Gesamt Umsatz'),
                me.createLineSeries('product_avg', 'Ø Warenwert')
            ]
        });
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
    },

    customerSelected: function (selection) {
        var me = this;
        me.deleteCustomerButton.setDisabled(selection.length == 0);
    },

    loadPreview: function() {
        var me = this;

        if (!me.filterPanel.getForm().isValid()) {
            return;
        }
        me.listStore.getProxy().extraParams = me.filterPanel.getSubmitData();
        me.listStore.load();
        me.loadChart();
        me.loadStreamChart();
    },

    streamSelected: function(selModel, selection) {
        var me = this;

        if (me.preventStreamChanged) {
            return;
        }
        if (selection.length <= 0) {
            me.resetConditions();
            return;
        }

        me.loadStream(selection[0]);
    },

    loadStream: function(record) {
        var me = this;

        me.streamListing.setLoading(true);

        me.setTitle('Kundenliste: ' + record.get('name'));
        me.resetFilterPanel();
        me.formPanel.loadRecord(record);

        me.streamListing.setLoading(false);
        me.listStore.getProxy().extraParams = {
            conditions: record.get('conditions')
        };
        me.listStore.load();

        me.loadChart();
    },

    saveStream: function (record, callback) {
        var me = this;

        if (!me.filterPanel.getForm().isValid()) {
            return;
        }

        var isNew = (record.get('id') === null);

        me.formPanel.getForm().updateRecord(record);

        record.save({
            callback: function() {
                if (isNew) {
                    me.streamListing.getStore().insert(0, record);
                }
                me.preventStreamChanged = true;
                me.streamListing.selModel.deselectAll(true);
                me.preventStreamChanged = false;
                me.streamListing.selModel.select([record], false, true);
                me.startPopulate(record);
            }
        });
    },

    createOrUpdateStream: function(callback) {
        var me = this;
        var record = me.formPanel.getForm().getRecord();
        if (record) {
            me.saveStream(me.formPanel.getForm().getRecord(), callback);
            return;
        }
        me.createStream(callback);
    },

    loadChart: function() {
        var me = this;
        me.setChartParameter();
        me.chartStore.load();
    },

    setChartParameter: function() {
        var me = this;
        me.chartStore.getProxy().extraParams = { };

        var record = me.formPanel.getForm().getRecord();
        if (record && record.get('id')) {
            me.chartStore.getProxy().extraParams = {
                streamId: record.get('id')
            };
        }
    },

    createStream: function(callback) {
        var me = this;
        var record = Ext.create('Shopware.apps.CustomerStream.model.CustomerStream', {
            id: null,
            name: 'New stream'
        });
        me.saveStream(record, callback);
    },

    startPopulate: function(record) {
        var me = this;

        me.indexingBar.value = 0;
        me.formPanel.setDisabled(true);
        Ext.Ajax.request({
            url: '{url controller=CustomerStream action=loadStream}',
            params: {
                streamId: record.get('id')
            },
            success: function(operation) {
                var response = Ext.decode(operation.responseText);
                me.start([{
                    text: 'Indexing customers',
                    url: '{url controller=CustomerStream action=indexStream}',
                    params: {
                        total: response.total,
                        streamId: record.get('id')
                    }
                }]);

            }
        });
    },

    indexSearch: function() {
        var me = this;

        me.indexingBar.value = 0;
        me.formPanel.setDisabled(true);
        Ext.Ajax.request({
            url: '{url controller=CustomerStream action=getCustomerCount}',
            params: { },
            success: function(operation) {
                var response = Ext.decode(operation.responseText);

                me.start([{
                    text: 'Analyzing customers',
                    url: '{url controller=CustomerStream action=buildSearchIndex}',
                    params: {
                        total: response.total
                    }
                }]);
            }
        });
    },

    updateProgressBar: function(request, response) {
        var me = this;
        me.indexingBar.updateProgress(response.progress, response.text, true);
    },

    finish: function() {
        this.formPanel.setDisabled(false);
        this.loadPreview();
        this.resetProgressbar();
    },

    switchLayout: function (layout) {
        var me = this;

        switch (layout) {
            case 'table':
                me.cardContainer.getLayout().setActiveItem(0);
                me.gridPanel.getStore().load();
                break;

            case 'amount_chart':
                me.cardContainer.getLayout().setActiveItem(1);
                me.chartStore.load();
                break;

            case 'stream_chart':
                me.cardContainer.getLayout().setActiveItem(2);
                me.loadStreamChart();
                break;
        }
    },

    /**
     * Creates the grid toolbar with the add and delete button
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar:function () {
        var me = this, items = [];

        me.saveStreamButton = Ext.create('Ext.button.Split', {
            showText: true,
            textAlign: 'left',
            iconCls: 'sprite-product-streams',
            text: 'Stream speichern',
            name: 'save-stream',
            handler: Ext.bind(me.createOrUpdateStream, me),
            menu: {
                xtype: 'menu',
                items: [{
                    xtype: 'menuitem',
                    iconCls: 'sprite-plus-circle-frame',
                    text: 'Als neuen stream speichern',
                    action: 'create',
                    handler: Ext.bind(me.createStream, me)
                }, {
                    xtype: 'menuitem',
                    iconCls: 'sprite',
                    text: 'Kunden analyzieren',
                    action: 'index',
                    handler: Ext.bind(me.indexSearch, me)
                }]
            }
        });

        items.push(me.saveStreamButton);
        items.push({ xtype: 'tbspacer', width: 10 });
        items.push({ xtype: 'tbseparator' });
        items.push({ xtype: 'tbspacer', width: 10 });

        items.push({
            xtype: 'button',
            iconCls: 'sprite-funnel',
            menu: me.createMenu()
        });

        items.push({
            iconCls: 'sprite-arrow-circle-225-left',
            handler: Ext.bind(me.loadPreview, me)
        });

        me.indexingBar = Ext.create('Ext.ProgressBar', {
            text: 'Indexierung',
            value: 0,
            height: 20,
            width: 300
        });

        items.push({ xtype: 'tbspacer', width: 5 });
        items.push({ xtype: 'tbseparator' });
        items.push({ xtype: 'tbspacer', width: 5 });

        items.push(me.indexingBar);

        items.push({ xtype: 'tbspacer', width: 5 });
        items.push({ xtype: 'tbseparator' });
        items.push({ xtype: 'tbspacer', width: 5 });

        me.deleteCustomerButton = Ext.create('Ext.button.Button', {
            iconCls:'sprite-minus-circle-frame',
            text: 'Markierte löschen',
            disabled:true,
            action:'deleteCustomer'
        });

        /*{if {acl_is_allowed privilege=create}}*/
            items.push({
                iconCls:'sprite-plus-circle-frame',
                text:me.snippets.toolbar.add,
                action:'addCustomer'
            });
        /*{/if}*/

        /*{if {acl_is_allowed privilege=delete}}*/
            items.push(me.deleteCustomerButton);
        /*{/if}*/

        items.push({
            // showText: true,
            xtype: 'cycle',
            prependText: '{s name=toolbar/view}Display as{/s} ',
            action: 'layout',
            listeners: {
                change: function (button, item) {
                    me.switchLayout(item.layout);
                }
            },
            menu: {
                items: [
                    {
                        text: '{s name=view_chart}Umsatz{/s}',
                        layout: 'amount_chart',
                        iconCls: 'sprite-chart'
                    },
                    {
                        text: '{s name=view_chart_stream}Stream Umsatz{/s}',
                        layout: 'stream_chart',
                        iconCls: 'sprite-chart'
                    },
                    {
                        text: '{s name=view_table}Kundenliste{/s}',
                        layout: 'table',
                        iconCls: 'sprite-table',
                        checked: true
                    }
                ]
            }
        });

        items.push('->');

        items.push({
            xtype:'textfield',
            name:'searchfield',
            cls:'searchfield',
            width:170,
            emptyText:me.snippets.toolbar.search,
            enableKeyEvents:true,
            checkChangeBuffer:500
        });

        items.push({ xtype:'tbspacer', width:6 });

        return Ext.create('Ext.toolbar.Toolbar', {
            dock:'top',
            ui: 'shopware-ui',
            items: items
        });
    },

    createMenu: function() {
        var me = this, items = [];

        Ext.each(me.filterPanel.handlers, function(handler) {
            items.push({
                text: handler.getLabel(),
                conditionHandler: handler,
                handler: Ext.bind(me.filterPanel.createCondition, me.filterPanel)
            });
        });

        items.push({ xtype: 'menuseparator' });
        items.push({
            text: 'Reset',
            handler: Ext.bind(me.resetConditions, me)
        });
        return new Ext.menu.Menu({ items: items });
    },

    resetFilterPanel: function() {
        var me = this;

        me.filterPanel.removeAll();
        me.filterPanel.loadRecord(null);
    },

    resetConditions: function() {
        var me = this;
        me.resetFilterPanel();
        me.loadPreview();
    }

});
//{/block}
