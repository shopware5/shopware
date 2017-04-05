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

//{block name="backend/customer/view/main/window"}
Ext.define('Shopware.apps.Customer.view.main.Window', {
    extend:'Enlight.app.Window',
    cls:Ext.baseCSSPrefix + 'customer-list-window',
    alias:'widget.customer-list-main-window',
    border:false,
    autoShow:true,
    layout:'border',
    width:'95%',
    height:'95%',
    title:'{s name=window_title}Customer list{/s}',

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
        me.listStore = Ext.create('Shopware.apps.Customer.store.Preview', { pageSize: 15 }).load({ conditions: null });

        me.gridPanel = Ext.create('Shopware.apps.Customer.view.list.List', {
            store: me.listStore
        });
        me.gridPanel.on('selection-changed', Ext.bind(me.customerSelected, me));

        me.gridPanel.on('afterrender', function() {
            me.gridPanel.getEl().on('click', function(event, element) {
                element = Ext.get(element);
                event.preventDefault();

                me.streamListing.getSelectionModel().select([
                    me.streamListing.getStore().getById(
                        window.parseInt(element.getAttribute('data-id'))
                    )
                ]);
            }, me, {
                delegate: ".stream-inline"
            });
        });

        me.streamListing = Ext.create('Shopware.apps.Customer.view.customer_stream.Listing', {
            store: Ext.create('Shopware.apps.Customer.store.CustomerStream').load(),
            subApp: me.subApp,
            collapsible: true,
            hideHeaders: true,
            title: 'Definierte Streams',
            height: 200,
            iconCls: 'sprite-product-streams',
            selectionChanged: Ext.bind(me.streamSelected, me)
        });

        me.streamListing.cellEditor.on('edit', Ext.bind(me.streamEdited, me));
        me.streamListing.on('customerStream-edit-item', Ext.bind(me.editStream, me));

        me.filterPanel = Ext.create('Shopware.apps.Customer.view.customer_stream.ConditionPanel', { flex: 4 });

        me.metaChart = me.createChart(
            [
                { name: 'count_orders', title: 'Anzahl Bestellungen' },
                { name: 'invoice_amount_avg', title: 'Ø Warenkorb' },
                { name: 'invoice_amount_max', title: 'Größte Bestellung' },
                { name: 'invoice_amount_min', title: 'Kleinste Bestellung' },
                { name: 'invoice_amount_sum', title: 'Gesamt Umsatz' },
                { name: 'product_avg', title: 'Ø Warenwert' }
            ],
            me.metaChartStore = Ext.create('Shopware.apps.Customer.store.MetaChart')
        );

        me.streamChartContainer = Ext.create('Ext.container.Container', {
            items: [],
            flex: 1,
            layout: 'border'
        });

        me.streamDetailForm = Ext.create('Ext.form.Panel', {
            bodyPadding: 20,
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'bottom',
                items: ['->', me.createSaveStreamDetailButton()]
            }]
        });

        me.cardContainer = Ext.create('Ext.container.Container', {
            items: [me.gridPanel, me.metaChart, me.streamChartContainer, me.streamDetailForm] ,
            region: 'center',
            layout: 'card'
        });

        me.formPanel = Ext.create('Ext.form.Panel', {
            region: 'west',
            collapsible: true,
            cls: 'shopware-form customer-filter-panel',
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            width: 400,
            title: 'Filter & Customer Streams',
            items: [
                me.filterPanel,
                me.streamListing
            ]
        });
        me.items = [
            me.formPanel,
            me.cardContainer
        ];
        me.dockedItems = [ me.getToolbar()];

        Ext.resumeLayouts(true);

        me.callParent(arguments);

        me.resetProgressbar();

        me.startPartialIndexing();
    },

    resetProgressbar: function () {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller=CustomerStream action=getLastFullIndexTime}',
            success: function(operation) {
                var response = Ext.decode(operation.responseText);
                Ext.defer(function () {
                    me.indexingBar.updateProgress(0, 'Letzte Analyze am: ' + Ext.util.Format.date(response.last_index_time), true);
                }, 1000);
            }
        });
    },

    createChart: function(fields, store) {
        var me = this;
        var axesFields = [];
        var series = [];

        Ext.each(fields, function(item) {
            axesFields.push(item.name);

            if (item.hasOwnProperty('title')) {
                series.push(me.createLineSeries(item.name, item.title));
            } else {
                series.push(me.createLineSeries(item.name, item.name));
            }
        });

        return Ext.create('Ext.chart.Chart', {
            shadow:true,
            margin:30,
            legend: true,
            animate:true,
            store: store,
            axes: [{
                type: 'Numeric',
                position: 'left',
                fields: axesFields,
                label: {
                    renderer: Ext.util.Format.numberRenderer('0,0')
                },
                title: '{s name="amount"}Amout{/s}',
                grid: true,
                minimum: 0
            }, {
                type: 'Category',
                position: 'bottom',
                title: '{s name="chart_month"}Month{/s}',
                fields: ['yearMonth']
            }],
            series: series
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


    /**
     * Creates the grid toolbar with the add and delete button
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar:function () {
        var me = this;

        me.toolbar = Ext.create('Shopware.apps.Customer.view.main.Toolbar', {
            handlers: me.filterPanel.handlers
        });

        me.indexingBar = me.toolbar.indexingBar;

        return me.toolbar;
    },


    customerSelected: function (selection) {
        var me = this;
        me.deleteCustomerButton.setDisabled(selection.length == 0);
    },






    resetFilterPanel: function() {
        var me = this;
        var newStream = Ext.create('Shopware.apps.Customer.model.CustomerStream', {
            id: null,
            name: 'New stream'
        });

        me.filterPanel.removeAll();
        me.filterPanel.loadRecord(null);
        me.formPanel.loadRecord(null);
    },

    resetConditions: function() {
        var me = this;

        me.resetFilterPanel();
        me.loadListing();
    },









    reloadView: function() {
        var me = this;

        if (me.filterPanel.getForm().isValid()) {
            me.listStore.getProxy().extraParams = me.filterPanel.getSubmitData();
            me.listStore.load();
        }

        me.metaChartStore.load();
        me.loadStreamChart();
    },

    loadListing: function() {
        var me = this;

        if (!me.filterPanel.getForm().isValid()) {
            return;
        }

        me.listStore.getProxy().extraParams = me.filterPanel.getSubmitData();
        me.listStore.load();
    },

    streamSelected: function(selModel, selection) {
        var me = this;

        if (me.preventStreamChanged) {
            return;
        }

        if (selection.length <= 0) {
            me.resetTitles();
            me.resetConditions();
            me.loadChart();
            return;
        }

        me.loadStream(selection[0]);
        me.loadChart();
    },

    loadStream: function(record) {
        var me = this;

        me.streamListing.setLoading(true);

        me.resetFilterPanel();
        me.formPanel.loadRecord(record);

        me.streamListing.setLoading(false);
        me.listStore.getProxy().extraParams = {
            conditions: record.get('conditions')
        };

        me.updateTitles(record);

        me.listStore.load();
    },

    updateTitles: function (stream) {
        var me = this,
            title = stream.get('name').substr(0, 20);

        if (stream.get('name').length > 20) {
            title += '...';
        }

        me.formPanel.setTitle(stream.get('name'));
        me.saveStreamButton.setText('Save: ' + title);
        me.setTitle('Kundenliste für ' + stream.get('name'));
    },

    resetTitles: function() {
        var me = this;

        me.formPanel.setTitle('Stream filter');
        me.setTitle('Kundenliste');
    },

    loadChart: function() {
        var me = this;
        var record = me.formPanel.getForm().getRecord();

        me.metaChartStore.getProxy().extraParams = { };

        if (record && record.get('id')) {
            me.metaChartStore.getProxy().extraParams = {
                streamId: record.get('id')
            };
        }

        me.metaChartStore.load();
    },



    loadStreamChart: function() {
        var me = this;
        var fields = [];
        var modelFields = [];

        me.streamListing.getStore().each(function (item) {
            fields.push({ name: item.get('name') });
            modelFields.push({ name: item.get('name'), type: 'float' });
        });

        fields.push({ name: 'unassigned' });
        modelFields.push({ name: 'unassigned', type: 'float' });
        modelFields.push({ name: 'yearMonth', type: 'string'});

        me.streamChartContainer.removeAll();
        me.streamChartContainer.setLoading(true);

        var store = Ext.create('Ext.data.Store', {
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
            callback: function () {
                var chart = me.createChart(fields, store);
                me.streamChartContainer.add(chart);
                me.streamChartContainer.setLoading(false);
            }
        });
    },


    createSaveStreamDetailButton: function() {
        var me = this;

        return Ext.create('Ext.button.Button', {
            text: 'Save',
            cls: 'primary',
            handler: function () {
                me.saveStreamDetails();
            }
        });
    },

    saveStreamDetails: function() {
        var me = this;

        if (!me.streamDetailForm.getForm().isValid()) {
            return;
        }
        var record = me.streamDetailForm.getRecord();
        me.streamDetailForm.getForm().updateRecord(record);

        record.save({
            callback: function() {
                me.fireEvent('switch-layout', 'table');
                me.updateTitles(record);
            }
        });
    },

    editStream: function(grid, record) {
        var me = this;

        var detail = Ext.create('Shopware.apps.Customer.view.customer_stream.Detail', {
            record: record
        });

        me.streamDetailForm.removeAll();
        me.streamDetailForm.add(detail);
        me.streamDetailForm.loadRecord(record);
        me.cardContainer.getLayout().setActiveItem(3);
    },

    streamEdited: function (editor, event) {
        this.updateTitles(event.record);
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

    createStream: function(callback) {
        var me = this;
        var record = Ext.create('Shopware.apps.Customer.model.CustomerStream', {
            id: null,
            name: 'New stream'
        });
        me.saveStream(record, callback);
    },



    startPartialIndexing: function() {
        var me = this;

        me.indexingBar.value = 0;
        me.formPanel.setDisabled(true);

        Ext.Ajax.request({
            url: '{url controller=CustomerStream action=getPartialCount}',
            success: function(operation) {
                var response = Ext.decode(operation.responseText);

                var params = { total: response.total };

                if (response.lastIndexTime) {
                    params.lastIndexTime = response.lastIndexTime;
                }

                me.start([{
                    text: 'Analyzing new customers',
                    url: '{url controller=CustomerStream action=buildSearchIndex}',
                    params: params
                }]);
            }
        });
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
        this.loadListing();
        this.resetProgressbar();
    },




});
//{/block}
