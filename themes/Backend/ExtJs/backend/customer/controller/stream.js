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
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name=backend/customer/view/main}
// {block name="backend/customer/controller/stream"}
Ext.define('Shopware.apps.Customer.controller.Stream', {

    extend: 'Ext.app.Controller',

    refs: [
        { ref: 'mainWindow', selector: 'customer-list-main-window' },
        { ref: 'mainToolbar', selector: 'customer-main-toolbar' }
    ],

    mixins: {
        batch: 'Shopware.helper.BatchRequests'
    },

    init: function () {
        var me = this;

        me.control({
            'customer-main-toolbar': {
                'switch-layout': me.switchLayout,
                'reload-view': me.reloadView,
                'create-or-update-stream': me.createOrUpdateStream,
                'create-stream': me.createStream,
                'index-search': me.indexSearch,
                'create-filter-condition': me.createFilterCondition,
                'reset-conditions': me.resetConditions
            },
            'customer-list-main-window': {
                'switch-layout': me.switchLayout,
                'reset-conditions': me.resetConditions,
                'reset-progressbar': me.resetProgressbar,
                'save-stream-details': me.saveStreamDetails,
                'customerStream-edit-item': me.editStream,
                'stream-selected': me.streamSelected
            },
            'customer-list': {
                'selection-changed': me.onCustomerSelectionChange
            },
            'customer-stream-listing': {
                'customerstream-edit-item': me.editStream
            }
        });

        me.callParent(arguments);
    },

    onCustomerSelectionChange: function(selection) {
        var me = this,
            toolbar = me.getMainToolbar(),
            deleteBtn = toolbar.deleteCustomerButton;

        deleteBtn.setDisabled(selection.length < 1);
    },

    switchLayout: function (layout) {
        var me = this,
            window = me.getMainWindow();

        switch (layout) {
            case 'table':
                window.cardContainer.getLayout().setActiveItem(0);
                window.gridPanel.getStore().load();
                break;

            case 'amount_chart':
                window.cardContainer.getLayout().setActiveItem(1);
                window.metaChartStore.load();
                break;

            case 'stream_chart':
                window.cardContainer.getLayout().setActiveItem(2);
                me.loadStreamChart();
                break;
        }
    },

    reloadView: function() {
        var me = this,
            window = me.getMainWindow();

        if (window.filterPanel.getForm().isValid()) {
            window.listStore.getProxy().extraParams = window.filterPanel.getSubmitData();
            window.listStore.load();
        }

        window.metaChartStore.load();
        me.loadStreamChart();
    },

    createOrUpdateStream: function(callback) {
        var me = this,
            window = me.getMainWindow(),
            record = window.formPanel.getForm().getRecord();

        if (record) {
            me.saveStream(record, callback);
            return;
        }
        me.createStream(callback);
    },

    createStream: function(callback) {
        var me = this;
        var record = Ext.create('Shopware.apps.Customer.model.CustomerStream', {
            id: null,
            name: '{s name=stream/new_stream}New stream{/s}'
        });
        me.saveStream(record, callback);
    },

    saveStream: function (record, callback) {
        var me = this,
            window = me.getMainWindow();

        if (!window.filterPanel.getForm().isValid()) {
            return;
        }

        var isNew = (record.get('id') === null);

        window.formPanel.getForm().updateRecord(record);

        record.save({
            callback: function() {
                if (isNew) {
                    window.streamListing.getStore().insert(0, record);
                }
                me.preventStreamChanged = true;
                window.streamListing.selModel.deselectAll(true);
                me.preventStreamChanged = false;
                window.streamListing.selModel.select([record], false, true);
                me.startPopulate(record);
            }
        });
    },

    editStream: function(grid, record) {
        var me = this,
            window = me.getMainWindow();

        var detail = Ext.create('Shopware.apps.Customer.view.customer_stream.Detail', {
            record: record
        });

        window.streamDetailForm.removeAll();
        window.streamDetailForm.add(detail);
        window.streamDetailForm.loadRecord(record);
        window.cardContainer.getLayout().setActiveItem(3);
    },

    streamSelected: function(selModel, selection) {
        var me = this,
            window = me.getMainWindow();

        if (me.preventStreamChanged) {
            return;
        }

        if (selection.length <= 0) {
            window.resetTitles();
            me.resetConditions();
        } else {
            me.loadStream(selection[0]);
        }

        me.loadChart();
    },

    loadStream: function(record) {
        var me = this,
            window = me.getMainWindow();

        window.streamListing.setLoading(true);

        window.resetFilterPanel();
        window.formPanel.loadRecord(record);

        window.streamListing.setLoading(false);
        window.listStore.getProxy().extraParams = {
            conditions: record.get('conditions')
        };

        me.updateTitles(record);

        window.listStore.load();
    },

    loadChart: function() {
        var me = this,
            window = me.getMainWindow(),
            metaChartStore = window.metaChartStore,
            record = window.formPanel.getForm().getRecord();

        metaChartStore.getProxy().extraParams = { };

        if (record && record.get('id')) {
            metaChartStore.getProxy().extraParams = {
                streamId: record.get('id')
            };
        }

        metaChartStore.load();
    },

    loadStreamChart: function() {
        var me = this,
            window = me.getMainWindow(),
            streamChartContainer = window.streamChartContainer;

        streamChartContainer.removeAll();
        streamChartContainer.setLoading(true);

        var store = window.streamListing.getStore();

        Ext.create('Shopware.apps.Customer.view.chart.AmountChartFactory').createChart(store, function (chart) {
            streamChartContainer.add(chart);
            streamChartContainer.setLoading(false);
        });
    },

    updateTitles: function (stream) {
        var me = this,
            window = me.getMainWindow(),
            toolbar = me.getMainToolbar(),
            title = stream.get('name').substr(0, 20);

        if (stream.get('name').length > 20) {
            title += '...';
        }

        window.formPanel.setTitle(stream.get('name'));
        toolbar.saveStreamButton.setText('{s name=save}Save{/s}: ' + title);
        window.setTitle('{s name=window/customer_list_for}Customer list for{/s} ' + stream.get('name'));
    },

    startPopulate: function(record) {
        var me = this,
            window = me.getMainWindow(),
            toolbar = me.getMainToolbar();

        toolbar.indexingBar.value = 0;
        window.formPanel.setDisabled(true);

        Ext.Ajax.request({
            url: '{url controller=CustomerStream action=loadStream}',
            params: {
                streamId: record.get('id')
            },
            success: function(operation) {
                var response = Ext.decode(operation.responseText);
                me.start([{
                    text: '{s name=stream/indexing_customers}Indexing customers{/s}',
                    url: '{url controller=CustomerStream action=indexStream}',
                    params: {
                        total: response.total,
                        streamId: record.get('id')
                    }
                }]);
            }
        });
    },

    saveStreamDetails: function() {
        var me = this,
            window = me.getMainWindow(),
            streamDetailForm = window.streamDetailForm;

        if (!streamDetailForm.getForm().isValid()) {
            return;
        }
        var record = streamDetailForm.getRecord();
        streamDetailForm.getForm().updateRecord(record);

        record.save({
            callback: function() {
                me.switchLayout('table');
                me.updateTitles(record);
            }
        });
    },

    startPartialIndexing: function() {
        var me = this,
            window = me.getMainWindow(),
            toolbar = me.getMainToolbar();

        toolbar.indexingBar.value = 0;
        window.formPanel.setDisabled(true);

        Ext.Ajax.request({
            url: '{url controller=CustomerStream action=getPartialCount}',
            success: function(operation) {
                var response = Ext.decode(operation.responseText);

                var params = { total: response.total };

                if (response.lastIndexTime) {
                    params.lastIndexTime = response.lastIndexTime;
                }

                me.start([{
                    text: '{s name=window/analysing_new_customer}Analyse new customer{/s}',
                    url: '{url controller=CustomerStream action=buildSearchIndex}',
                    params: params
                }]);
                me.resetProgressbar();
            }
        });
    },

    indexSearch: function() {
        var me = this,
            window = me.getMainWindow(),
            toolbar = me.getMainToolbar();

        toolbar.indexingBar.value = 0;
        window.formPanel.setDisabled(true);

        Ext.Ajax.request({
            url: '{url controller=CustomerStream action=getCustomerCount}',
            params: { },
            success: function(operation) {
                var response = Ext.decode(operation.responseText);

                me.start([{
                    text: '{s name=stream/analysing_customers}Analysing customers{/s}',
                    url: '{url controller=CustomerStream action=buildSearchIndex}',
                    params: {
                        total: response.total
                    }
                }]);
            }
        });
    },

    createFilterCondition: function (handler) {
        var me = this,
            window = me.getMainWindow();

        window.filterPanel.createCondition(handler);
    },

    resetConditions: function() {
        var me = this,
            window = me.getMainWindow();

        window.resetFilterPanel();
        window.loadListing();
    },

    updateProgressBar: function(request, response) {
        var me = this,
            toolbar = me.getMainToolbar();
        toolbar.indexingBar.updateProgress(response.progress, response.text, true);
    },

    resetProgressbar: function () {
        var me = this,
            toolbar = me.getMainToolbar();

        Ext.Ajax.request({
            url: '{url controller=CustomerStream action=getLastFullIndexTime}',
            success: function(operation) {
                var response = Ext.decode(operation.responseText);
                Ext.defer(function () {
                    toolbar.indexingBar.updateProgress(0, '{s name=window/last_analyse}Last analyse at: {/s}' + Ext.util.Format.date(response.last_index_time), true);
                }, 1000);
            }
        });
    },

    finish: function() {
        var me = this,
            window = me.getMainWindow();
        window.formPanel.setDisabled(false);
        window.loadListing();
        me.resetProgressbar();
    }

});
// {/block}
