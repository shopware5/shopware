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
 * @package    Shopware_Config
 * @subpackage Config
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Controller - Config backend module
 *
 * todo@all: Documentation
 */
//{block name="backend/config/controller/main"}
Ext.define('Shopware.apps.Config.controller.Main', {
    extend: 'Enlight.app.Controller',

    views: [
        'base.Form',
        'base.Table',
        'base.Detail',
        'base.Search',
        'base.Property',

        'main.Window', 'main.Navigation',
        'main.Panel',
        'main.Form', 'main.Fieldset',

        'element.Boolean',
        'element.Button',
        'element.Date',
        'element.Html',
        'element.Select',
        'element.Text',
        'element.TextArea',
        'element.Time',
        'element.Number',
        'element.DateTime',
        'element.Interval',
        'element.SelectTree',
        'element.ProductBoxLayoutSelect',
        'element.Color',
        'element.CustomSortingSelection',
        'element.CustomSortingGrid',
        'element.CustomFacetGrid'
    ],

    stores:[
        'main.Navigation',
        'main.Form'
    ],

    models:[
        'main.Form', 'main.Navigation',
        'main.Element' , 'main.Value',
        'main.ElementTranslation', 'main.FormTranslation'
    ],

    refs: [
        { ref: 'window', selector: 'config-main-window' },
        { ref: 'shopField', selector: 'config-navigation [name=shop]' },
        { ref: 'panel', selector: 'config-main-panel' },
        { ref: 'form', selector: 'config-main-form' }
    ],

    /**
     * The main window instance
     * @object
     */
    mainWindow: null,

    init: function () {
        var me = this;

        // Init main window
        me.mainWindow = me.getView('main.Window').create({
            mode: me.subApplication.mode,
            autoShow: true,
            hideNavigation: !!me.action
        });

        // Register base stores
        me.navigationStore = me.getStore('main.Navigation');
        me.shopStore = Ext.data.StoreManager.lookup('base.ShopLanguage').load();
        me.formStore = me.getStore('main.Form');

        // Register events
        me.control({
            'config-navigation treepanel': {
                select: me.onSelectForm
            },
            'config-main-form button[action=save]': {
                click: me.onSaveForm
            },
            'config-main-form button[action=reset]': {
                click: me.onResetForm
            },
            'config-navigation config-base-search': {
                change: me.onSearchForm
            }
        });
        me.formStore.on('load', me.onLoadForm, me);

        if(me.action) {
            me.formStore.load({
                filters : [{
                    property: Ext.isNumeric(me.action) ? 'id' : 'name',
                    value: me.action
                }]
            });
        }
    },

    onResetForm: function(btn){
        var me = this,
            formPanel = btn.up('form'),
            basicForm = formPanel.getForm();

        Ext.each(basicForm.getFields().items, function(field) {
            field.reset();
        });
    },

    onLoadForm: function(store, records, success) {
        var me = this, form, controller;
        if (success !== true || !records.length) {
            return;
        }
        form = records[0];

        if(form.associations.containsKey('getTranslation')) {
            if(form.getTranslation().getAt(0) && form.getTranslation().getAt(0).get('label')) {
                form.data.label = form.getTranslation().getAt(0).get('label');
            }
        }

        if(form.get('name') == 'Document') {
            controller = 'Document';
            me.getController('Form');
        } else {
            controller = 'Form';
        }
        me.getController(controller);

        me.initForm(form);
    },

    onSelectForm: function(tree, record) {
        var me = this;
        if(!record.data.id) {
            return;
        }
        if(!record.get('leaf')) {
            record.expand();
            return;
        }

        var panel = me.mainWindow.contentPanel;
        panel.setLoading('Loading ' + record.get('label') + '...');

        me.formStore.load({
            filters : [{
                property: 'id',
                value: record.data.id
            }]
        });
    },

    onSearchForm: function(field, value) {
        var me = this;
        var store = me.getStore('main.Navigation');
        if (value.length === 0 ) {
            store.load();
        } else {
            store.load({
                filters : [{
                    property: 'search',
                    value: '%' + value + '%'
                }]
            });
        }
    },

    onSaveForm: function(button) {
        var me = this,
            formPanel = button.up('form'),
            basicForm = formPanel.getForm(),
            form = basicForm.getRecord(),
            values = basicForm.getFieldValues(),
            fieldName, fieldValue, valueStore;

        form.getElements().each(function(element) {
            valueStore = element.getValues();
            valueStore.removeAll();
            me.shopStore.each(function(shop) {
                fieldName = 'values[' + shop.get('id') + ']['+ element.get('id') + ']';
                fieldValue = values[fieldName];
                if(fieldValue !== null) {
                    valueStore.add({
                        shopId: shop.get('id'),
                        value: fieldValue
                    });
                }
            });
        });

        form.setDirty();

        var title = '{s name=form/message/save_form_title}Save form{/s}',
            win = me.getWindow();

        form.store.add(form);
        form.store.sync({
            success :function (records, operation) {
                var template = new Ext.Template('{s name=form/message/save_form_success}Form „[name]“ has been saved.{/s}'),
                    message = template.applyTemplate({
                        name: form.data.label || form.data.name
                    });
                Shopware.Notification.createGrowlMessage(title, message, win.title);
            },
            failure:function (batch) {
                var template = new Ext.Template('{s name=form/message/save_form_error}Form „[name]“ could not be saved.{/s}'),
                    message = template.applyTemplate({
                        name: form.data.label || form.data.name
                    });
                if(batch.proxy.reader.rawData.message) {
                    message += '<br />' + batch.proxy.reader.rawData.message;
                }
                Shopware.Notification.createGrowlMessage(title, message, win.title);
            }
        });
    },

    /**
     *
     * @param form
     */
    initForm: function(form) {
        var me = this,
            win = me.mainWindow,
            panel = win.contentPanel;

        if(me.shopStore.isLoading()) {
            Ext.defer(me.initForm, 100, me, [ form ]);
            return false;
        }

        var formPanel,
            formType = 'widget.config-form-' + form.get('name').toLowerCase();

        panel.removeAll(true);

        win.loadTitle(form);

        if (Ext.ClassManager.getNameByAlias(formType)) {
            formPanel = Ext.create(formType, {
                shopStore: me.shopStore,
                formRecord: form
            });
        } else {
            formPanel =  Ext.createByAlias('widget.config-main-form', {
                shopStore: me.shopStore,
                formRecord: form
            });
        }
        panel.add(formPanel);
        panel.setLoading(false);
    }
});
//{/block}
