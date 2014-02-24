/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * Shopware Controller - Config backend module
 *
 * todo@all: Documentation
 */
//{block name="backend/config/controller/template"}
Ext.define('Shopware.apps.Config.controller.Template', {

    extend: 'Enlight.app.Controller',

    views: [
        'form.Template',
        'template.View',
        'template.Detail',
        'template.Preview'
    ],

    stores:[
        'form.Template'
    ],

    models:[
        'form.Template'
    ],

    refs: [
        { ref: 'table', selector: 'config-template-view' },
        { ref: 'dataView', selector: 'config-template-view dataview' },
        { ref: 'shopSelect', selector: 'config-template-view combobox[name=shop]' },
        { ref: 'previewButton', selector: 'config-template-view button[action=preview]' },
        { ref: 'stopButton', selector: 'config-template-view button[action=stop-preview]' },
        { ref: 'enableButton', selector: 'config-template-view button[action=enable]' }
    ],

    init: function () {
        var me = this;

        me.control({
            'config-template-view dataview': {
                selectionchange: me.onChangePreview,
                itemdblclick: me.onShowPreviewImage
            },
            'config-template-preview image': {
                click: function(image) {
                    image.up('window').close();
                }
            },
            'config-template-view textfield[name=searchfield]': {
                change: me.onSearchTemplate
            },
            'config-template-view button[action=preview]': {
                click: me.onSelectPreview
            },
            'config-template-view button[action=stop-preview]': {
                click: me.onSelectPreview
            },
            'config-template-view combobox[name=shop]': {
                select: me.onSelectShop
            },
            'config-template-view button[action=enable]': {
                click: me.onSelectTemplate
            }
        });

        me.callParent(arguments);
    },

    onChangePreview: function(view, records) {
        var me = this,
            panel = view.view.up('config-form-template'),
            formPanel = panel.down('form'),
            basicForm = formPanel.getForm(),
            previewButton = me.getPreviewButton(),
            stopButton = me.getStopButton(),
            enableButton = me.getEnableButton(),
            combo = me.getShopSelect(),
            template = records.length ? records[0] : null;

        enableButton.disable();
        stopButton.hide();
        previewButton.show().disable();

        if(template && combo.getValue() != null && combo.getValue() > 0) {
            me.template = template;
            basicForm.loadRecord(template);
            if(template.get('preview')) {
                stopButton.show();
                previewButton.hide();
            } else if(me.shop) {
                previewButton.enable();
            }
            if(me.shop && !template.get('enabled')) {
                enableButton.enable();
            }
        }
    },

    onShowPreviewImage: function(view, record) {
        var me = this;
        if(!record.get('previewFull')) {
            return;
        }
        me.getView('template.Preview').create({
            template: record,
            autoShow: true
        });
    },

    onSearchTemplate: function(field, value) {
        var me = this,
            store = me.getTable().store;

        if (value.length === 0 ) {
            store.clearFilter();
        } else {
            store.filters.clear();
            store.filter('name', '%' + value + '%');
        }
    },

    onSelectShop: function(combo, records) {
        var me = this,
            enableButton = me.getEnableButton(),
            store = me.getTable().store,
            shop = records.length > 0 ? records[0] : '';

        me.shop = shop;
        store.getProxy().extraParams['shopId'] = shop.getId();
        store.load();

        if(me.template) {
            enableButton.enable();
        }
    },

    onSelectPreview: function(button, event) {
        var me = this,
            dataView = me.getDataView(),
            record = me.template,
            store = dataView.getStore(),
            oldRecord = store.findRecord('preview', true),
            shopId = me.getShopSelect().getValue(),
            url = '{url action=previewTemplate}';

        url += '?shopId=' + shopId;

        if(oldRecord) {
            oldRecord.set('preview', false);
        }
        if(button.action == 'preview') {
            record.set('preview', true);
            url += '&template=' + record.get('template');
            if(me.win) {
                me.win.close();
            }
            me.win = window.open(url);
        } else {
            Ext.Ajax.request({
                url: url
            });
        }
    },

    onSelectTemplate: function(button, event) {
        var me = this,
            dataView = me.getDataView(),
            record = me.template,
            store = dataView.getStore(),
            oldRecord = store.findRecord('enabled', true);

        if(oldRecord && oldRecord !== record) {
            oldRecord.set('enabled', false);
        }

        record.set('enabled', true);
        store.sync();
    }
});
//{/block}
