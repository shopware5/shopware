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
    ],

    mixins: {
        batch: 'Shopware.helper.BatchRequests'
    },

    init: function () {
        var me = this;

        me.control({
            'stream-view': {
                'switch-layout': me.switchLayout,
                'stream-selected': me.streamSelected,
                'change-auto-index': me.changeAutoIndex,
                'full-index': me.fullIndex,
                'save-stream': me.saveEditedStream,
                'save-as-new-stream': me.saveAsNewStream,
                'refresh-stream-views': me.reloadView,
                'tab-activated': me.onTabActivated,
                'condition-added': me.updateSaveButtons,
                'reset-progressbar': me.resetProgressbar,
                'add-customer-to-stream': me.addCustomerToStream
            },
            'customer-stream-condition-panel': {
                'condition-removed': me.updateSaveButtons
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
                'reset-progressbar': me.resetProgressbar
            }
        });

        me.callParent(arguments);
    },

    addCustomerToStream: function(record) {
        var me = this;
        var stream = me.getStreamDetailForm().getForm().getRecord();

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
                    Shopware.Notification.createGrowlMessage('', 'Erfolgreich hinzufügt');
                } else {
                    Shopware.Notification.createGrowlMessage('', 'Befindet sich bereits im stream');
                }
            }
        });

        return false;
    },

    deleteCustomerFromStream: function(record) {
        var me = this;
        var stream = me.getStreamDetailForm().getForm().getRecord();

        if (!stream.get('id')) {
            return false;
        }

        me.getStreamView().gridPanel.getStore().remove(record);

        Ext.Ajax.request({
            url: '{url controller=CustomerStream action=removeCustomerFromStream}',
            params: {
                streamId: stream.get('id'),
                customerId: record.get('id')
            }
        });
    },

    staticCheckboxChanged: function(value) {
        var me = this;
        var streamView = me.getStreamView();

        if (value) {
            streamView.formPanel.setDisabled(true);
        } else {
            streamView.formPanel.setDisabled(false);
        }
    },

    addStream: function() {
        this.loadStream(
            Ext.create('Shopware.apps.Customer.model.CustomerStream')
        )
    },

    fullIndex: function() {
        var me = this;
        var store = me.getStreamListing().getStore();

        me.indexSearch(true, function() {
            var streamView = me.getStreamView();
            streamView.listStore.load();

            if (store.getCount() > 0) {
                var streams = store.data.items;
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
            }, 650);
        }, 400);
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
                withAssignment: false,
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
        var streamView = me.getStreamView();

        if (!streamView.formPanel.getForm().isValid()) {
            Shopware.Notification.createGrowlMessage('', '{s name="not_valid_stream"}{/s}');
            return;
        }

        var button = Ext.create('Ext.button.Button', {
            cls: 'primary',
            text: '{s name="save"}{/s}',
            handler: function() {
                me.createStream(form, window);
            }
        });

        var window = Ext.create('Ext.window.Window', {
            modal: true,
            width: 900,
            height: 255,
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

        window.on('afterrender', function() {
            var nameField = window.down('textfield[name=name]');
            nameField.focus(false, 125);
            nameField.on('specialkey', function(field, event) {
                if(event.getKey() !== event.ENTER) {
                    return false;
                }
                me.createStream(form, window);
            });
        });

        window.show();
    },

    createStream: function(form, window) {
        var me = this;

        if (form.getForm().isValid()) {
            var stream = Ext.create('Shopware.apps.Customer.model.CustomerStream', form.getForm().getValues());
            var freezeUp = stream.get('freezeUp');

            stream.set('freezeUp', null);

            window.destroy();

            me.saveStream(stream, function() {
                stream.set('freezeUp', freezeUp);
                stream.save();
                me.resetProgressbar();
                me.getStreamView().resetFilterPanel();
                me.getStreamView().streamListing.getStore().load();
                me.loadStream(stream);
            });
        }
    },

    saveEditedStream: function() {
        var streamView = this.getStreamView();
        var me = this;
        var record = streamView.formPanel.getForm().getRecord();
        var streamListing = streamView.streamListing;

        this.saveStream(record, function() {
            me.resetProgressbar();

            me.preventStreamChanged = true;
            streamListing.getStore().load({
                callback: function() {
                    me.preventStreamChanged = false;

                    streamListing.getSelectionModel().select([
                        streamListing.getStore().getById(record.get('id'))
                    ]);
                }
            });
        });
    },

    saveStream: function (record, callback) {
        var me = this;
        var streamView = this.getStreamView();
        var streamDetailForm = me.getStreamDetailForm();

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
            Shopware.Notification.createGrowlMessage('', 'Sie müssen Filter definieren');
            return;
        }

        var displayMessage = (record.get('static') && !before.static && record.hasConditions());
        before.static = record.get('static');

        if (displayMessage) {
            Ext.MessageBox.confirm(
                'Indexierung',
                'Möchten Sie die aktuelle Filterung in den Statischen Stream übernehmen?',
                function (response) {
                    if (response !== 'yes') {
                        me.sendSave(record, callback);
                        return;
                    }

                    record.set({ freezeUp: null, static: false });

                    me.sendSave(record, function() {
                        record.set(before);
                        record.save({ callback: callback });
                    });
                }
            );
        } else {
            me.sendSave(record, callback);
        }
    },

    sendSave: function(record, callback) {
        var me = this;

        record.save({
            callback: function() {
                Shopware.Notification.createGrowlMessage('', 'Stream gespeichert');
                me.indexStream(record, callback);
            }
        });
    },

    streamSelected: function(selection) {
        var me = this;
        var streamView = me.getStreamView();

        if (me.preventStreamChanged) {
            return;
        }

        streamView.addCustomerToStreamSelection.setDisabled(true);
        if (selection.length <= 0) {
            streamView.resetFilterPanel();
            streamView.listStore.getProxy().extraParams = { };
            streamView.listStore.load();
            streamView.streamDetailForm.loadRecord({ });
            streamView.streamDetailForm.setDisabled(true);
        } else {
            me.loadStream(selection[0]);
        }

        Ext.defer(Ext.bind(me.updateSaveButtons, me), 100);

        me.loadChart();
    },

    loadStream: function(record) {
        var streamView = this.getStreamView();

        streamView.streamListing.setLoading(true);
        streamView.addCustomerToStreamSelection.setDisabled(true);

        streamView.gridPanel.displayDeleteIcon = false;

        streamView.resetFilterPanel();
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

    indexStream: function(record, callback) {
        var me = this;

        /*{if !{acl_is_allowed resource=customerstream privilege=save}}*/
            return;
        /*{/if}*/

        if (record.get('static')) {
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
