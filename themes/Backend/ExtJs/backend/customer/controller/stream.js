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
        { ref: 'streamListing', selector: 'customer-stream-listing' },
        { ref: 'streamDetailForm', selector: 'stream-view form[name=detail-form]' },
        { ref: 'conditionPanel', selector: 'customer-stream-condition-panel' }
    ],

    mixins: {
        batch: 'Shopware.helper.BatchRequests'
    },

    init: function () {
        var me = this;

        me.lastRecords = [];
        me.lastRecord = null;

        me.control({
            'stream-view': {
                'switch-layout': me.switchLayout,
                'stream-selection-changed': me.streamSelectionChanged,
                'change-auto-index': me.changeAutoIndex,
                'full-index': me.fullIndex,
                'save-stream': me.saveEditedStream,
                'refresh-stream-views': me.reloadView,
                'tab-activated': me.onTabActivated,
                'reset-progressbar': me.resetProgressbar,
                'add-customer-to-stream': me.addCustomerToStream,
                'validitychange': me.streamDetailValidityChanged
            },
            'customer-stream-detail': {
                'static-changed': me.staticCheckboxChanged
            },
            'customer-list': {
                'delete': me.deleteCustomerFromStream
            },
            'customer-stream-listing': {
                'index-stream': me.indexStream,
                'add-stream': me.addStream,
                'reset-progressbar': me.resetProgressbar,
                'save-as-new-stream': me.duplicateStream,
                'save-stream-selection': me.saveStreamSelection,
                'restore-stream-selection': me.restoreStreamSelection,
                'delete-stream': me.deleteStreamItem
            },
            'customer-stream-condition-panel': {
                'condition-panel-change': me.conditionPanelChange
            }
        });

        me.callParent(arguments);
    },

    addCustomerToStream: function(record) {
        var me = this,
            stream = me.getStreamDetailForm().getForm().getRecord();

        if (!stream.get('id')) {
            return false;
        }

        Ext.Ajax.request({
            url: '{url controller=CustomerStream action=addCustomerToStream}',
            params: {
                streamId: stream.get('id'),
                customerId: record.get('id')
            },
            callback: function(operation, success, response) {
                success = Ext.JSON.decode(response.responseText);

                if (success.success) {
                    stream.set('customer_count', stream.get('customer_count') + 1);
                    Shopware.Notification.createGrowlMessage('', '{s name="add_customer_success"}{/s}');
                } else {
                    Shopware.Notification.createGrowlMessage('', '{s name="add_customer_error"}{/s}');
                }
            }
        });

        return false;
    },

    deleteCustomerFromStream: function(record) {
        var me = this,
            stream = me.getStreamDetailForm().getForm().getRecord();

        if (!stream.get('id')) {
            return false;
        }

        me.getStreamView().gridPanel.getStore().remove(record);

        Ext.Ajax.request({
            url: '{url controller=CustomerStream action=removeCustomerFromStream}',
            params: {
                streamId: stream.get('id'),
                customerId: record.get('id')
            },
            success: function () {
                stream.set('customer_count', stream.get('customer_count') - 1);
            }
        });
    },

    staticCheckboxChanged: function(value) {
        var me = this;

        me.refreshDateTimePicker(value);
        me.refreshSaveButton();
        me.refreshEmptyMessage();
    },

    addStream: function() {
        var me = this;

        me.getStreamListing().getSelectionModel().deselectAll();

        me.loadStream(
            Ext.create('Shopware.apps.Customer.model.CustomerStream')
        );

        me.getStreamListing().getStore().add(Ext.create('Shopware.apps.Customer.model.CustomerStream', {
            id: null,
            name: '{s name="stream/new_stream"}{/s}'
        }));

        me.lastRecords = me.getStreamListing().getStore().getNewRecords();
        me.lastRecord = me.lastRecords[me.lastRecords.length - 1];
        me.getStreamListing().getSelectionModel().select([me.lastRecord]);

        me.disableDateTimeInput(true);
        me.refreshAddButton();
    },

    fullIndex: function() {
        var me = this,
            store = me.getStreamListing().getStore();
        me.saveStreamSelection();

        me.indexSearch(true, function() {
            var streamView = me.getStreamView();
            streamView.listStore.load();

            if (store.getCount() > 0) {
                var streams = me.filterUnsavedElements(store.data.items);
                me.refreshWhileFullIndex(streams, streams.length);
            } else {
                me.resetProgressbar();
            }
        });
    },

    onTabActivated: function() {
        var me = this;

        if (me.subApplication.userConfig && me.subApplication.userConfig.autoIndex) {
            me.fullIndex();
            return;
        }

        me.resetProgressbar();
    },

    indexStreams: function(stream, streams, total) {
        var me = this;

        /*{if !{acl_is_allowed resource=customerstream privilege=save}}*/
            return;
        /*{/if}*/

        me.indexStream(stream, function() {
            if (streams.length > 0) {
                me.refreshWhileFullIndex(streams, total);
            } else {
                var streamView = me.getStreamView();
                streamView.listStore.load();
                streamView.streamListing.getStore().load({
                    callback: Ext.bind(me.restoreStreamSelection, me)
                });
                me.resetProgressbar();
            }
        });
    },

    filterUnsavedElements: function(elements) {
        return Ext.Array.filter(elements, function (elem) {
            return elem.get('id') !== null;
        });
    },

    refreshWhileFullIndex: function(streams, total) {
        var me = this,
            next = streams.shift(),
            node = me.getStreamListing().getView().getNode(next),
            nodes = me.getStreamListing().getView().getNodes();

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
            }, 650);
        }, 400);
    },

    checkIndexState: function() {
        var me = this,
            streamView = me.getStreamView();

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

        me.layout = layout;

        switch (layout) {
            case 'table':
                streamView.cardContainer.getLayout().setActiveItem(0);
                streamView.gridPanel.getStore().load();
                streamView.formPanel.setDisabled(false);
                break;

            case 'amount_chart':

                /*{if !{acl_is_allowed resource=customerstream privilege=charts}}*/
                    return;
                /*{/if}*/

                streamView.cardContainer.getLayout().setActiveItem(1);
                streamView.metaChartStore.load();
                streamView.formPanel.setDisabled(true);
                break;

            case 'stream_chart':
                /*{if !{acl_is_allowed resource=customerstream privilege=charts}}*/
                    return;
                /*{/if}*/
                streamView.cardContainer.getLayout().setActiveItem(2);
                me.loadStreamChart();
                streamView.formPanel.setDisabled(true);
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

    duplicateStream: function(record) {
        var me = this,
            streamData = record.getData();

        delete streamData.id;
        var stream = Ext.create('Shopware.apps.Customer.model.CustomerStream', streamData);

        stream.set('name', '{s name="copy_of"}{/s} ' + record.get('name'));

        me.sendSave(stream, function() {
            me.resetProgressbar();
            me.resetFilterPanel();
            me.saveStreamSelection();
            me.getStreamView().streamListing.getStore().load({
                callback: Ext.bind(me.restoreStreamSelection, me)
            });
        });
    },

    saveEditedStream: function() {
        var me = this,
            record = me.getStreamView().formPanel.getForm().getRecord(),
            isNewRecord = record.get('id') === null;

        me.saveStreamSelection();

        me.saveStream(record, function() {
            me.resetProgressbar();
            me.getStreamView().streamListing.getStore().load({
                callback: Ext.bind(me.restoreStreamSelection, me),
                forceReload: isNewRecord
            });

            if (isNewRecord) {
                me.lastRecord = null;
                me.lastRecords = [];
            }

            me.refreshAddButton();

            if (me.isChartViewActive()) {
                me.reloadView();
            }
        });
    },

    saveStream: function (record, callback) {
        var me = this,
            streamView = this.getStreamView(),
            streamDetailForm = me.getStreamDetailForm();

        /*{if !{acl_is_allowed resource=customerstream privilege=save}}*/
            return;
        /*{/if}*/

        if (!streamView.formPanel.getForm().isValid()) {
            Shopware.Notification.createGrowlMessage('', '{s name="not_valid_stream"}{/s}');
            return;
        }

        if (!streamDetailForm.getForm().isValid()) {
            Shopware.Notification.createGrowlMessage('', '{s name="not_valid_stream"}{/s}');
            return;
        }

        var before = {
            'freezeUp': record.get('freezeUp'),
            'static': record.get('static')
        };

        streamDetailForm.getForm().updateRecord(record);
        streamView.formPanel.getForm().updateRecord(record);

        if (!record.get('static') && !record.hasConditions()) {
            Shopware.Notification.createGrowlMessage('', '{s name="filter_missing"}{/s}');
            return;
        }

        if (record.get('static') && !before.static && record.hasConditions()) {
            before.static = record.get('static');
            record.set({ freezeUp: null, static: false });

            me.sendSave(record, function() {
                record.set(before);
                record.save({ callback: callback });
            });
        } else if (!record.get('static') && before.static) {
            Ext.MessageBox.confirm(
                '{s name="indexing"}{/s}',
                '{s name="static_to_dynamic_message"}{/s}',
                function (response) {
                    if (response !== 'yes') {
                        callback();
                        return;
                    }

                    record.set({ freezeUp: null, static: false });
                    me.sendSave(record, callback);
                }
            );
        } else {
            me.sendSave(record, callback);
        }
    },

    sendSave: function(record, callback) {
        var me = this,
            streamView = me.getStreamView(),
            isNewRecord = record.get('id') === null;

        record.save({
            callback: function(newRecord) {
                if (isNewRecord) {
                    me.getStreamListing().getStore().remove(record);
                    streamView.formPanel.getForm().updateRecord(newRecord);
                }

                Shopware.Notification.createGrowlMessage('', '{s name="stream_saved"}{/s}');
                me.indexStream(newRecord, callback);
            }
        });
    },

    streamSelectionChanged: function(selection) {
        var me = this,
            streamView = me.getStreamView();

        if (me.preventStreamChanged) {
            return;
        }
        streamView.addCustomerToStreamSelection.setDisabled(true);

        if (selection.length <= 0) {
            me.resetFilterPanel();
            streamView.listStore.getProxy().extraParams = { };
            streamView.listStore.load();
            streamView.streamDetailForm.loadRecord({ });
            streamView.streamDetailForm.setDisabled(true);
        } else {
            me.loadStream(selection[0]);
        }
        me.loadChart();

        me.refreshEmptyMessage();
    },

    loadStream: function(record) {
        var me = this,
            streamView = this.getStreamView();

        streamView.streamListing.setLoading(true);
        streamView.addCustomerToStreamSelection.setDisabled(true);

        streamView.gridPanel.displayDeleteIcon = false;

        me.resetFilterPanel();
        streamView.formPanel.loadRecord(record);

        if (record.get('static')) {
            streamView.formPanel.setDisabled(true);
            streamView.addCustomerToStreamSelection.setDisabled(false);
            streamView.gridPanel.displayDeleteIcon = true;
        }

        streamView.streamListing.setLoading(false);
        streamView.listStore.getProxy().extraParams = {
            streamId: record.get('id')
        };

        streamView.listStore.load();
        streamView.streamDetailForm.loadRecord(record);
        streamView.streamDetailForm.setDisabled(false);

        if (me.isChartViewActive()) {
            streamView.formPanel.setDisabled(true);
        }
    },

    loadChart: function() {
        var streamView = this.getStreamView(),
            metaChartStore = streamView.metaChartStore,
            record = streamView.formPanel.getForm().getRecord();

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

    indexStream: function(record, callback) {
        var me = this;

        /*{if !{acl_is_allowed resource=customerstream privilege=save}}*/
            return;
        /*{/if}*/

        if (record.get('static') || record.get('id') === null) {
            Ext.callback(callback);
            return;
        }

        me.initProgressbar();

        Ext.Ajax.request({
            url: '{url controller=CustomerStream action=loadStream}',
            params: {
                conditions: record.get('conditions')
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
                }, 500);
            }
        });

        me.getStreamView().indexSearchButton.setDisabled(false);
        me.getStreamView().leftContainer.setDisabled(false);
        me.checkIndexState();
    },

    finish: function(requests, callback) {
        var me = this;

        if (Ext.isFunction(callback)) {
            callback();
        } else {
            me.resetProgressbar();
        }
    },

    saveStreamSelection: function () {
        var me = this,
            selectionModel = me.getStreamListing().getSelectionModel();

        if (selectionModel.hasSelection()) {
            me.currentStreamSelection = selectionModel.getSelection()[0];
        } else {
            me.currentStreamSelection = null;
        }
    },

    restoreStreamSelection: function () {
        var me = this,
            streamListing = me.getStreamListing(),
            store = streamListing.getStore();

        if (!me.currentStreamSelection) {
            return;
        }

        var newest = -1;
        var recordIndex = store.findBy(function (record) {
            var id = record.get('id');
            if (id === me.currentStreamSelection.data.id) {
                return true;
            }

            if (id > newest) {
                newest = id;
            }
        });

        var record = null;
        if (newest !== -1 && recordIndex === -1) {
            record = store.getById(newest);
        } else {
            if (recordIndex === null || recordIndex < 0) {
                return;
            }
            record = store.getAt(recordIndex);
        }

        streamListing.getSelectionModel().select([record]);
    },

    disableDateTimeInput: function (disabled) {
        var me = this,
            streamDetailForm = me.getStreamDetailForm();
        streamDetailForm.getForm().findField('freezeUpTime').setDisabled(disabled);
        streamDetailForm.getForm().findField('freezeUpDate').setDisabled(disabled);
    },

    disableSaveButton: function(disabled) {
        var me = this,
            streamView = me.getStreamView();

        streamView.saveStreamButton.setDisabled(disabled);
    },

    refreshAddButton: function () {
        var me = this,
            streamListing = me.getStreamListing(),
            addButton = streamListing.addButton;

        if (me.lastRecords.length > 0) {
            addButton.setDisabled(true);
            addButton.setTooltip('{s name=unsaved_stream}{/s}');
        } else {
            addButton.setDisabled(false);
            addButton.setTooltip('');
        }
    },

    conditionPanelChange: function() {
        var me = this;
        me.refreshEmptyMessage();
        me.refreshSaveButton();
    },

    streamDetailValidityChanged: function () {
        var me = this;
        me.refreshSaveButton();
    },

    deleteStreamItem: function(record) {
        var me = this;
        Ext.MessageBox.confirm('{s name="delete_confirm_title"}{/s}', '{s name=delete_confirm_text}{/s}', function (response) {
            if (response !== 'yes') {
                return false;
            }

            if (record.phantom) {
                me.lastRecords = [];
            }

            me.getStreamListing().getStore().remove(record);

            record.destroy();

            me.reloadStreamList();
            me.refreshAddButton();
        });
    },

    refreshEmptyMessage: function() {
        var me = this,
            conditionPanel = me.getConditionPanel(),
            selection = me.getStreamListing().getSelectionModel().getSelection(),
            isStatic = me.getStreamDetailForm().getForm().findField('static').getValue(),
            conditions = me.hasConditions();

        if (selection.length === 1 && !isStatic && !conditions) {
            if (conditionPanel.items.length <= 0) {
                conditionPanel.add(conditionPanel.createEmptyMessage());
            }
        } else {
            if (!conditions) {
                conditionPanel.removeAll();
            }
        }
    },

    refreshDateTimePicker: function (isStatic) {
        var me = this,
            streamView = me.getStreamView();

        if (isStatic) {
            me.disableDateTimeInput(false);
            streamView.formPanel.setDisabled(true);
            streamView.formPanel.getForm().getFields().findBy(function(field) {
                var isValid = field.isValid(),
                    comp = field.getEl().up('.customer-stream-condition-field');

                if (comp && !isValid) {
                    streamView.filterPanel.remove(Ext.getCmp(comp.id).ownerCt);
                }
            });
        } else {
            me.disableDateTimeInput(true);
            streamView.formPanel.setDisabled(false);
        }
    },

    refreshSaveButton: function () {
        this.disableSaveButton(!this.isStreamValid());
    },

    isStreamValid: function () {
        var me = this,
            isValid = me.getStreamDetailForm().getForm().isValid(),
            selection = me.getStreamListing().getSelectionModel().getSelection(),
            isStatic = me.getStreamDetailForm().getForm().findField('static').getValue(),
            conditions = me.hasConditions();

        if (!isValid || (selection.length === 1 && !isStatic && !conditions)) {
            return false;
        }

        return true;
    },

    hasConditions: function () {
        var me = this;
        if (!me.getConditionPanel()) {
            return false;
        }
        return me.getConditionPanel().hasConditions;
    },

    reloadStreamList: function () {
        var me = this,
            streamView = me.getStreamView();

        me.saveStreamSelection();
        streamView.streamListing.getStore().load({
            callback: Ext.bind(me.restoreStreamSelection, me)
        });
    },

    isChartViewActive: function () {
        return this.layout === 'amount_chart' || this.layout === 'stream_chart';
    },

    resetFilterPanel: function() {
        var me = this,
            streamView = me.getStreamView();

        streamView.filterPanel.removeAll();
        streamView.filterPanel.loadRecord(null);

        streamView.formPanel.loadRecord(null);
        streamView.formPanel.setDisabled(me.isChartViewActive());
    }

});
// {/block}
