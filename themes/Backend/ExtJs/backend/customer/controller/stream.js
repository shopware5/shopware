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

//{namespace name=backend/customer/view/main}

//{block name="backend/customer/controller/stream"}
Ext.define('Shopware.apps.Customer.controller.Stream', {

    extend:'Ext.app.Controller',

    refs:[
        { ref:'mainWindow', selector:'customer-list-main-window' },
        { ref: 'mainToolbar', selector: 'customer-main-toolbar' }
    ],

    init:function () {
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
            'customer-list-main-window ': {
                'switch-layout': me.switchLayout,
                'reset-conditions': me.resetConditions
            },
            'customer-list': {
                'selection-changed': me.onCustomerSelectionChange
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
                window.loadStreamChart();
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
        // todo move to this file
        window.loadStreamChart();
    },

    createOrUpdateStream: function(callback) {
        console.log('entered createOrUpdateStream');
        var me = this,
        window = me.getMainWindow();
        var record = window.formPanel.getForm().getRecord();

        if (record) {
            me.saveStream(window.formPanel.getForm().getRecord(), callback);
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
                window.preventStreamChanged = true;
                window.streamListing.selModel.deselectAll(true);
                window.preventStreamChanged = false;
                window.streamListing.selModel.select([record], false, true);
                me.startPopulate(record);
            }
        });
    },

    startPopulate: function(record) {
        var me = this,
            window = me.getMainWindow();

        window.indexingBar.value = 0;
        window.formPanel.setDisabled(true);

        Ext.Ajax.request({
            url: '{url controller=CustomerStream action=loadStream}',
            params: {
                streamId: record.get('id')
            },
            success: function(operation) {
                var response = Ext.decode(operation.responseText);
                // todo oli fragen was die start function genau tut :-)
                window.start([{
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
        var me = this,
            window = me.getMainWindow();

        window.indexingBar.value = 0;
        window.formPanel.setDisabled(true);

        Ext.Ajax.request({
            url: '{url controller=CustomerStream action=getCustomerCount}',
            params: { },
            success: function(operation) {
                var response = Ext.decode(operation.responseText);

                window.start([{
                    text: 'Analyzing customers',
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
    }

});
//{/block}
