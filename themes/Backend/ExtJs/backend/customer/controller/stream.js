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
        { ref: 'streamView', selector: 'stream-view' },
        { ref: 'streamListing', selector: 'customer-stream-listing' }
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
                'tab-activated': me.onTabActivated,
                'condition-added': me.updateSaveButtons,
                'reset-progressbar': me.resetProgressbar
            },
            'customer-stream-condition-panel': {
                'condition-removed': me.updateSaveButtons
            },
            'customer-stream-listing': {
                'customerstream-edit-item': me.editStream,
                'index-stream': me.indexStream,
                'reset-progressbar': me.resetProgressbar
            }
        });

        me.callParent(arguments);
    },

    onTabActivated: function() {
        var me = this;
        var store = me.getStreamListing().getStore();

        if (me.subApplication.userConfig && me.subApplication.userConfig.autoIndex) {
            me.indexSearch(true, function() {
                if (store.getCount() > 0) {
                    var streams = store.data.items;
                    me.refreshWhileFullIndex(streams, streams.length);
                } else {
                    me.resetProgressbar();
                }
            });
            return;
        }

        me.resetProgressbar();
    },

    indexStreams: function(stream, streams, total) {
        var me = this;

        /*{if !{acl_is_allowed resource=customerstream privilege=stream_index}}*/
            return;
        /*{/if}*/

        me.indexStream(stream, function() {
            if (streams.length > 0) {
                me.refreshWhileFullIndex(streams, total);
            } else {
                var streamView = me.getStreamView();
                streamView.listStore.load();
                streamView.streamListing.getStore().load();
                me.resetProgressbar();
            }
        });
    },

    refreshWhileFullIndex: function(streams, total) {
        var me = this;
        var next = streams.shift();
        var node = me.getStreamListing().getView().getNode(next);
        var nodes = me.getStreamListing().getView().getNodes();

        Ext.each(nodes, function(node) {
            var el = Ext.get(node);
            el.removeCls('rotate');
        });

        var el = Ext.get(node);
        el.addCls('rotate');

        Ext.defer(function() {
            me.getStreamView().indexingBar.updateProgress(
                0,
                Ext.String.format('{s name="batch_progress"}{/s}', next.get('name'), total - streams.length, total),
                true
            );

            Ext.defer(function() {
                me.indexStreams(next, streams, total);
            }, 750);
        }, 500);
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

                /*{if !{acl_is_allowed resource=customerstream privilege=search_index}}*/
                    return;
                /*{/if}*/

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

                /*{if !{acl_is_allowed resource=customerstream privilege=charts}}*/
                    return;
                /*{/if}*/

                streamView.cardContainer.getLayout().setActiveItem(1);
                streamView.metaChartStore.load();
                break;

            case 'stream_chart':
                /*{if !{acl_is_allowed resource=customerstream privilege=charts}}*/
                    return;
                /*{/if}*/
                streamView.cardContainer.getLayout().setActiveItem(2);
                me.loadStreamChart();
                break;
        }
    },

    loadStreamChart: function() {
        var me = this,
            streamView = me.getStreamView(),
            streamChartContainer = streamView.streamChartContainer;

        /*{if !{acl_is_allowed resource=customerstream privilege=charts}}*/
            return;
        /*{/if}*/

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

        /*{if !{acl_is_allowed resource=customerstream privilege=charts}}*/
            return;
        /*{/if}*/
        streamView.metaChartStore.load();
        me.loadStreamChart();
    },

    createNewStreamForm: function() {
        return Ext.create('Ext.form.Panel', {
            items: [{
                xtype: 'customer-stream-detail',
                record: Ext.create('Shopware.apps.Customer.model.CustomerStream')
            }],
            bodyPadding: 20,
            border: false,
            layout: 'anchor',
            flex: 1
        });
    },

    saveAsNewStream: function() {
        var me = this;
        var form = me.createNewStreamForm();

        /*{if !{acl_is_allowed resource=customerstream privilege=save}}*/
            return;
        /*{/if}*/

        var button = Ext.create('Ext.button.Button', {
            cls: 'primary',
            text: '{s name="save"}{/s}',
            handler: function() {
                if (form.getForm().isValid()) {
                    me.saveStream(
                        Ext.create('Shopware.apps.Customer.model.CustomerStream', form.getForm().getValues())
                    );

                    window.destroy();
                }
            }
        });

        var window = Ext.create('Ext.window.Window', {
            modal: true,
            width: 450,
            items: [form],
            title: '{s name="save_new"}{/s}',
            layout: 'fit',
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'bottom',
                ui: 'shopware-ui',
                items: ['->', button]
            }]
        });

        window.show();
    },

    saveEditedStream: function() {
        var streamView = this.getStreamView();
        this.saveStream(streamView.formPanel.getForm().getRecord());
    },

    saveStream: function (record) {
        var me = this;
        var streamView = this.getStreamView();

        /*{if !{acl_is_allowed resource=customerstream privilege=save}}*/
            return;
        /*{/if}*/

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
                me.indexStream(record);
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

        /*{if !{acl_is_allowed resource=customerstream privilege=charts}}*/
            return;
        /*{/if}*/

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

        /*{if !{acl_is_allowed resource=customerstream privilege=save}}*/
            return;
        /*{/if}*/

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

    indexStream: function(record, callback) {
        var me = this;

        /*{if !{acl_is_allowed resource=customerstream privilege=stream_index}}*/
            return;
        /*{/if}*/

        me.initProgressbar();

        Ext.Ajax.request({
            url: '{url controller=CustomerStream action=loadStream}',
            params: {
                streamId: record.get('id')
            },
            success: function(operation) {
                var response = Ext.decode(operation.responseText);
                me.start(
                    [{
                        url: '{url controller=CustomerStream action=indexStream}',
                        params: {
                            total: response.total,
                            streamId: record.get('id')
                        }
                    }],
                    callback
                );
            }
        });
    },

    indexSearch: function(force, callback) {
        var me = this;

        /*{if !{acl_is_allowed resource=customerstream privilege=search_index}}*/
            return;
        /*{/if}*/

        me.initProgressbar();

        me.getSearchIndexingParameters(force, function(params) {
            me.start(
                [{
                    url: '{url controller=CustomerStream action=buildSearchIndex}',
                    params: params
                }],
                callback
            );
        });
    },

    getSearchIndexingParameters: function(force, callback) {
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

                        if (force) {
                            callback({ total: full.total, full: true });
                        } else if (notIndexed.total > 0 && full.total !== notIndexed.total) {
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
        this.getStreamView().indexingBar.removeCls('empty');

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
                    me.getStreamView().indexingBar.addCls('empty');
                }, 1000);
            }
        });

        me.getStreamView().indexSearchButton.setDisabled(false);
        me.getStreamView().leftContainer.setDisabled(false);
        me.checkIndexState();
    },

    finish: function(requests, callback) {
        var me = this,
            streamView = me.getStreamView();

        if (Ext.isFunction(callback)) {
            callback();
        } else {
            streamView.listStore.load();
            streamView.streamListing.getStore().load();
            me.resetProgressbar();
        }
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
