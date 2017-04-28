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
        { ref: 'mainToolbar', selector: 'customer-main-toolbar' },
        { ref: 'streamView', selector: 'stream-view' }
    ],

    mixins: {
        batch: 'Shopware.helper.BatchRequests'
    },

    init: function () {
        var me = this;

        me.control({
            'stream-view': {
                'switch-layout': me.switchLayout,
                'save-stream-details': me.saveStreamDetails,
                'stream-selected': me.streamSelected,
                'change-auto-index': me.changeAutoIndex,
                'index-search': me.indexSearch,
                'save-edited-stream': me.saveEditedStream,
                'save-as-new-stream': me.saveAsNewStream,
                'refresh-stream-views': me.reloadView,
                'check-index-state': me.checkIndexState,
                'condition-added': me.updateSaveButtons,
                'reset-progressbar': me.resetProgressbar
            },
            'customer-stream-condition-panel': {
                'condition-removed': me.updateSaveButtons
            },
            'customer-stream-listing': {
                'customerstream-edit-item': me.editStream
            }
        });

        me.callParent(arguments);
    },

    checkIndexState: function() {
        var me = this;
        var streamView = me.getStreamView();

        Ext.Ajax.request({
            url: '{url controller=CustomerStream action=getNotIndexedCount}',
            success: function(operation) {
                var response = Ext.decode(operation.responseText);

                if (response.total <= 0) {
                    streamView.indexSearchButton.setIconCls('sprite-blue-document-search-result');
                    return;
                }

                if (me.subApplication.userConfig && me.subApplication.userConfig.autoIndex) {
                    me.indexSearch();
                    return;
                }

                streamView.indexSearchButton.setIconCls('sprite-exclamation');

                var position = streamView.indexSearchButton.getPosition();
                position[1] = position[1] + 30;
                position[0] = position[0] - 90;
                streamView.indexSearchNoticeTooltip.showAt(position);
            }
        });
    },

    changeAutoIndex: function(checkbox, newValue) {
        var me = this, config = me.subApplication.userConfig;

        checkbox.setDisabled(true);
        config.autoIndex = newValue;

        Ext.Ajax.request({
            url: '{url controller=UserConfig action=save}',
            params: {
                config: Ext.JSON.encode(config),
                name: 'customer_module'
            },
            callback: function() {
                checkbox.setDisabled(false);
            }
        });
    },

    switchLayout: function (layout) {
        var me = this,
            streamView = me.getStreamView();

        switch (layout) {
            case 'table':
                streamView.cardContainer.getLayout().setActiveItem(0);
                streamView.gridPanel.getStore().load();
                break;

            case 'amount_chart':
                streamView.cardContainer.getLayout().setActiveItem(1);
                streamView.metaChartStore.load();
                break;

            case 'stream_chart':
                streamView.cardContainer.getLayout().setActiveItem(2);
                me.loadStreamChart();
                break;
        }
    },

    loadStreamChart: function() {
        var me = this,
            streamView = me.getStreamView(),
            streamChartContainer = streamView.streamChartContainer;

        streamChartContainer.removeAll();

        var store = streamView.streamListing.getStore();

        Ext.create('Shopware.apps.Customer.view.chart.AmountChartFactory').createChart(store, function (chart) {
            streamChartContainer.add(chart);
        });
    },

    reloadView: function() {
        var me = this,
            streamView = me.getStreamView();

        if (streamView.formPanel.getForm().isValid()) {
            streamView.listStore.getProxy().extraParams = streamView.filterPanel.getSubmitData();
            streamView.listStore.load();
        }

        streamView.metaChartStore.load();
        me.loadStreamChart();
    },

    saveAsNewStream: function() {
        this.saveStream(
            Ext.create('Shopware.apps.Customer.model.CustomerStream', {
                id: null,
                name: '{s name=stream/new_stream}{/s}'
            })
        );
    },

    saveEditedStream: function() {
        var streamView = this.getStreamView();
        this.saveStream(streamView.formPanel.getForm().getRecord());
    },

    saveStream: function (record) {
        var me = this;
        var streamView = this.getStreamView();

        if (!streamView.formPanel.getForm().isValid()) {
            return;
        }

        streamView.formPanel.getForm().updateRecord(record);

        record.save({
            callback: function() {
                me.preventStreamChanged = true;
                streamView.streamListing.selModel.deselectAll(true);
                me.preventStreamChanged = false;
                streamView.streamListing.selModel.select([record], false, true);
                me.startPopulate(record);
            }
        });
    },

    editStream: function(grid, record) {
        var streamView = this.getStreamView();

        var detail = Ext.create('Shopware.apps.Customer.view.customer_stream.Detail', {
            record: record
        });

        streamView.streamDetailForm.removeAll();
        streamView.streamDetailForm.add(detail);
        streamView.streamDetailForm.loadRecord(record);
        streamView.cardContainer.getLayout().setActiveItem(3);
    },

    streamSelected: function(selection) {
        var me = this;
        var streamView = me.getStreamView();

        if (me.preventStreamChanged) {
            return;
        }

        if (selection.length <= 0) {
            streamView.resetFilterPanel();
            streamView.listStore.getProxy().extraParams = { };
            streamView.listStore.load();
        } else {
            me.loadStream(selection[0]);
        }

        Ext.defer(Ext.bind(me.updateSaveButtons, me), 100);

        me.loadChart();
    },

    loadStream: function(record) {
        var streamView = this.getStreamView();

        streamView.streamListing.setLoading(true);

        streamView.resetFilterPanel();

        streamView.formPanel.loadRecord(record);

        streamView.streamListing.setLoading(false);
        streamView.listStore.getProxy().extraParams = {
            conditions: record.get('conditions')
        };

        streamView.listStore.load();
    },

    loadChart: function() {
        var streamView = this.getStreamView();
        var metaChartStore = streamView.metaChartStore;
        var record = streamView.formPanel.getForm().getRecord();

        metaChartStore.getProxy().extraParams = { };

        if (record && record.get('id')) {
            metaChartStore.getProxy().extraParams = {
                streamId: record.get('id')
            };
        }

        metaChartStore.load();
    },

    saveStreamDetails: function() {
        var me = this;
        var streamView = me.getStreamView();
        var streamDetailForm = streamView.streamDetailForm;

        if (!streamDetailForm.getForm().isValid()) {
            return;
        }
        var record = streamDetailForm.getRecord();
        streamDetailForm.getForm().updateRecord(record);

        record.save({
            callback: function() {
                me.switchLayout('table');
            }
        });
    },

    startPartialIndexing: function() {
        var me = this;

        me.initProgressbar();

        Ext.Ajax.request({
            url: '{url controller=CustomerStream action=getPartialCount}',
            success: function(operation) {
                var response = Ext.decode(operation.responseText);
                var params = { total: response.total };

                if (response.lastIndexTime) {
                    params.lastIndexTime = response.lastIndexTime;
                }

                me.start([{
                    url: '{url controller=CustomerStream action=buildSearchIndex}',
                    params: params
                }]);
            }
        });
    },

    startPopulate: function(record) {
        var me = this;

        me.initProgressbar();

        Ext.Ajax.request({
            url: '{url controller=CustomerStream action=loadStream}',
            params: {
                streamId: record.get('id')
            },
            success: function(operation) {
                var response = Ext.decode(operation.responseText);

                me.start([{
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

        me.initProgressbar();

        me.getSearchIndexingParameters(function(params) {
            me.start([{
                url: '{url controller=CustomerStream action=buildSearchIndex}',
                params: params
            }]);
        });
    },

    getSearchIndexingParameters: function(callback) {
        Ext.Ajax.request({
            url: '{url controller=CustomerStream action=getNotIndexedCount}',
            params: { },
            success: function(operation) {
                var notIndexed = Ext.decode(operation.responseText);

                Ext.Ajax.request({
                    url: '{url controller=CustomerStream action=getCustomerCount}',
                    params: {},
                    success: function (operation) {
                        var full = Ext.decode(operation.responseText);

                        if (notIndexed.total > 0 && full.total !== notIndexed.total) {
                            callback({ total: notIndexed.total });
                        } else {
                            callback({ total: full.total, full: true });
                        }
                    }
                });
            }
        });
    },

    updateProgressBar: function(request, response) {
        this.getStreamView().indexingBar.updateProgress(response.progress, response.text, true);
    },

    initProgressbar: function() {
        this.getStreamView().indexingBar.updateProgress(0);
        this.getStreamView().indexingBar.show();

        this.getStreamView().indexSearchButton.setDisabled(true);
        this.getStreamView().leftContainer.setDisabled(true);
    },

    resetProgressbar: function () {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller=CustomerStream action=getLastFullIndexTime}',
            success: function(operation) {
                var response = Ext.decode(operation.responseText);
                Ext.defer(function () {
                    me.getStreamView().indexingBar.updateProgress(0, '{s name=last_analyse}{/s}' + Ext.util.Format.date(response.last_index_time), true);
                }, 1000);
            }
        });

        me.getStreamView().indexSearchButton.setDisabled(false);
        me.getStreamView().leftContainer.setDisabled(false);
        me.checkIndexState();
    },

    finish: function() {
        var me = this,
            streamView = me.getStreamView();

        streamView.listStore.load();
        streamView.streamListing.getStore().load();
        me.resetProgressbar();
    },

    updateSaveButtons: function() {
        var streamView = this.getStreamView();

        var conditions = streamView.filterPanel.getSubmitData();
        conditions = Object.keys(Ext.JSON.decode(conditions['conditions']));

        var hasCondition = conditions.length > 0;

        var isNew = true;
        if (streamView.formPanel.getForm().getRecord()) {
            isNew = streamView.formPanel.getForm().getRecord().get('id') === null;
        }

        streamView.saveStreamButton.setDisabled(
            !hasCondition || isNew
        );

        streamView.saveNewStreamButton.setDisabled(
            !hasCondition
        );
    }
});
// {/block}
