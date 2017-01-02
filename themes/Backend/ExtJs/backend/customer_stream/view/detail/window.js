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
 */

//{namespace name="backend/customer_stream/translation"}

Ext.define('Shopware.apps.CustomerStream.view.detail.Window', {
    extend: 'Enlight.app.Window',
    alias: 'widget.customer-stream-detail-window',
    title : '{s name=detail_title}{/s}',
    height: '80%',
    width: '80%',
    layout: { type: 'vbox', align: 'stretch' },

    initComponent: function() {
        var me = this;

        me.items = me.createItems();
        me.dockedItems = me.createDockedItems();
        me.callParent(arguments);
    },

    loadRecord: function(record) {
        var me = this;
        me.record = record;
        me.formPanel.loadRecord(record);
        me.loadPreview({
            conditions: Ext.JSON.decode(record.get('conditions'))
        });
    },

    createItems: function() {
        var me = this;

        me.formPanel = Ext.create('Ext.form.Panel', {
            flex: 1,
            bodyPadding: 10,
            items: [{
                xtype: 'container',
                items: me.createTopItems(),
                layout: { type: 'hbox', align: 'stretch' }
            }, {
                xtype: 'container',
                items: me.createBottomItems(),
                flex: 1,
                layout: { type: 'hbox', align: 'stretch' }
            }],
            layout: { type: 'vbox', align: 'stretch' }
        });
        return [me.formPanel];
    },

    createTopItems: function() {
        return [{
            xtype: 'custom-stream-details',
            flex: 1,
            record: Ext.create('Shopware.apps.CustomerStream.model.CustomerStream')
        }];
    },

    createBottomItems: function() {
        var me = this;

        me.conditionPanel = Ext.create('Shopware.apps.CustomerStream.view.detail.ConditionPanel', {
            flex: 1
        });

        me.conditionPanel.on('load-preview', Ext.bind(me.loadPreview, me));

        me.previewGrid = Ext.create('Shopware.apps.CustomerStream.view.detail.PreviewGrid', {
            store: Ext.create('Shopware.apps.CustomerStream.store.Preview'),
            flex: 1,
            margin: '0 0 0 10'
        });

        return [me.conditionPanel, me.previewGrid];
    },

    createDockedItems: function() {
        var me = this;
        return [{
            xtype: 'toolbar',
            dock: 'bottom',
            style: 'border: 1px solid #9aacb8;',
            ui: 'shopware-ui',
            items: [
                '->',
                {
                    xtype: 'button',
                    text: '{s name="save"}{/s}',
                    cls: 'secondary',
                    handler: Ext.bind(me.save, me)
                },
                {
                    xtype: 'button',
                    text: '{s name="save_and_populate"}{/s}',
                    cls: 'primary',
                    handler: Ext.bind(me.saveAndPopulate, me)
                }
            ]
        }];
    },

    saveAndPopulate: function() {
        var me = this;

        me.saveRecord(function() {
            me.startPopulate(
                me.formPanel.getRecord()
            );
        });
    },

    save: function() {
        var me = this;

        me.saveRecord(function() {
            me.destroy();
        });
    },

    startPopulate: function(record) {
        Ext.create('Shopware.apps.CustomerStream.view.detail.IndexingWindow', {
            width: 450,
            height: 150,
            requests: [{
                text: 'Indexing customers',
                url: '{url controller=CustomerStream action=indexStream}',
                params: {
                    streamId: record.get('id')
                }
            }]
        }).show();
    },

    saveRecord: function(callback) {
        var me = this,
            record = me.formPanel.getRecord();

        if (!me.formPanel.getForm().isValid()) {
            return;
        }

        me.updateRecord(record);

        record.save({
            callback: function() {
                me.fireEvent('stream-saved');
                callback();
            }
        });
    },

    updateRecord: function(record) {
        var me = this;
        me.formPanel.getForm().updateRecord(record);
    },

    loadPreview: function(values) {
        var me = this,
            store = me.previewGrid.getStore();

        if (!me.formPanel.getForm().isValid()) {
            return;
        }
        store.getProxy().extraParams = values;
        store.load();
    }
});
