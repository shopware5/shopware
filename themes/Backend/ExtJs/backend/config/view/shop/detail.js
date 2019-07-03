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

/**
 * todo@all: Documentation
 */

//{namespace name=backend/config/view/form}

//{block name="backend/config/view/shop/detail"}
Ext.define('Shopware.apps.Config.view.shop.Detail', {
    extend: 'Shopware.apps.Config.view.base.Detail',
    alias: 'widget.config-shop-detail',

    store: 'detail.Shop',

    createTypeStore: function() {
        var me = this;
        return me.shopTypeStore = Ext.create('Ext.data.Store', {
            fields: [ 'type', 'label' ],
            data: [
                { 'type': 'lang', 'label': '{s name=shop/detail/language_shop_label}Language shop{/s}' },
                { 'type': 'sub', 'label': '{s name=shop/detail/sub_shop_label}Sub shop{/s}' }
            ]
        });
    },

    getIdField: function(){
        var me = this;
        return {
            xtype: 'hidden',
            name: 'id',
            listeners: {
                scope: me,
                change: function (hidden, value) {
                    var form = hidden.up('form'),
                        typeSwitchField = form.down('[name=typeSwitch]'),
                        mainIdField,
                        type;

                    if(Ext.isEmpty(value)) {
                        form.getForm().reset();
                        typeSwitchField.setDisabled(false);
                        return;
                    }

                    mainIdField = form.down('[name=mainId]');
                    type = mainIdField.getValue() ? 'lang' : 'sub';
                    typeSwitchField.setValue(type);
                    typeSwitchField.setDisabled(value == 1);
                }
            }
        }
    },

    getTypeSwitchField: function(){
        var me = this;
        return {
            xtype: 'config-element-select',
            name: 'typeSwitch',
            fieldLabel: '{s name=shop/detail/shop_type_label}Shop type{/s}',
            helpText: '{s name=shop/detail/shop_type_help}A sub shop is available via an extra url, a language shop holds the translation for a sub shop or the default shop{/s}',
            store: me.createTypeStore(),
            valueField : 'type',
            displayField : 'label',
            listeners:{
                scope: me,
                change: function(select, value) {
                    var form = select.up('form'),
                        mainFields = form.query('[isMainField]'),
                        requiredMainFields = form.query('[isMainRequired]'),
                        langFields = form.query('[isLangField]'),
                        requiredLangFields = form.query('[isLangRequired]'),
                        mainIdField = form.down('[name=mainId]'),
                        mainAction = value === 'sub' ? 'show' : 'hide',
                        langAction = value === 'lang' ? 'show' : 'hide';

                    if(value === 'sub') {
                        mainIdField.clearValue();
                    } else {
                        mainIdField.setValue(1);
                    }

                    Ext.each(mainFields, function(field) {
                        field[mainAction]();
                    });
                    Ext.each(requiredMainFields, function(field) {
                        field['allowBlank'] = value !== 'sub';
                    });

                    Ext.each(langFields, function(field) {
                        field[langAction]();
                    });
                    Ext.each(requiredLangFields, function(field) {
                        field['allowBlank'] = value !== 'lang';
                    });

                    if (value === 'lang') {
                        Ext.each(mainFields, function(field) {
                            if (field.xtype !== 'config-shop-currency') {
                                field.setValue('');
                            }
                        });
                    }
                }
            }
        }
    },

    getMainField: function() {
        var me = this;
        return {
            xtype: 'config-element-select',
            name: 'mainId',
            isLangField: true,
            isLangRequired: true,
            fieldLabel: '{s name=shop/detail/main_shop_label}Main shop{/s}',
            helpText: '{s name=shop/detail/main_shop_help}{/s}',
            store: 'base.Shop'
        };
    },

    getDefaultField: function() {
        var me = this;
        return {
            xtype: 'config-element-boolean',
            name: 'default',
            fieldLabel: '{s name=shop/detail/default_label}Default{/s}',
            isMainField: true,
            readOnly: true,
            handler: function(button, value) {
                var form = button.up('form'),
                    fallbackField = form.down('[name=fallbackId]');

                fallbackField[value ? 'hide' : 'show']();
                fallbackField.setValue(null);
            }
        };
    },

    getItems: function() {
        var me = this;

        me.categorySelect = Ext.create('Shopware.apps.Config.view.element.SelectTree', {
            name: 'categoryId',
            allowBlank: false,
            fieldLabel: '{s name=shop/detail/category_label}Category{/s}',
            store: 'base.CategoryTree',
            anchor: '100%',
            labelWidth: 120
        });

        return [
        me.getIdField(),
        me.getTypeSwitchField(),
        me.getMainField(),
        {
            name: 'name',
            fieldLabel: '{s name=shop/detail/name_label}Name{/s}',
            allowBlank: false
        },{
            name: 'title',
            fieldLabel: '{s name=shop/detail/title_label}Title{/s}',
            helpText: '{s name=shop/detail/title_help}For the output in the shop frontend.{/s}'
        },{
            xtype: 'config-element-number',
            name: 'position',
            fieldLabel: '{s name=shop/detail/position_label}Position{/s}',
            helpText: '{s name=shop/detail/position_help}Position in the shop selection.{/s}'
        },{
            name: 'host',
            emptyText: '{s name=shop/detail/host_empty_text}example.com{/s}',
            fieldLabel: '{s name=shop/detail/host_label}Host{/s}',
            helpText: '{s name=shop/detail/host_help}{/s}',
            isMainRequired: true,
            isMainField: true,
            hidden: true
        },{
            name: 'baseUrl',
            emptyText: '{s name=shop/detail/url_empty_text}/shop/en{/s}',
            fieldLabel: '{s name=shop/detail/url_label}Base url{/s}'
        },{
            name: 'basePath',
            emptyText: '{s name=shop/detail/path_empty_text}/shop{/s}',
            fieldLabel: '{s name=shop/detail/path_label}Base path{/s}',
            isMainField: true,
            hidden: true
        },{
            xtype: 'config-element-boolean',
            name: 'secure',
            fieldLabel: '{s name=shop/detail/secure_label}SSL support{/s}',
            isMainField: true,
            hidden: true
        },{
            xtype: 'config-element-textarea',
            name: 'hosts',
            fieldLabel: '{s name=shop/detail/hosts_label}Host aliases{/s}',
            isMainField: true,
            hidden: true
        },{
            xtype: 'config-element-select',
            name: 'currencyId',
            allowBlank: false,
            fieldLabel: '{s name=shop/detail/currency_label}Currency{/s}',
            store: 'base.Currency'
        },{
            xtype: 'config-element-select',
            name: 'localeId',
            allowBlank: false,
            fieldLabel: '{s name=shop/detail/locale_label}Locale{/s}',
            store: Ext.create('Shopware.apps.Base.store.Locale', {
                remoteFilter: false
            }),
            queryMode: 'local',
            forceSelection: true
        },
        me.categorySelect,
        {
            xtype: 'config-element-select',
            name: 'templateId',
            fieldLabel: '{s name=shop/detail/template_label}Template{/s}',
            store: 'base.Template',
            isMainRequired: true,
            isMainField: true,
            hidden: true
        },{
            xtype: 'config-element-select',
            name: 'documentTemplateId',
            fieldLabel: '{s name=shop/detail/document_template_label}Document template{/s}',
            store: 'base.Template',
            isMainRequired: true,
            isMainField: true,
            hidden: true
        },{
            xtype: 'config-element-select',
            name: 'customerGroupId',
            allowBlank: false,
            fieldLabel: '{s name=shop/detail/customer_group_label}Customer group{/s}',
            store: 'base.CustomerGroup'
        },{
            xtype: 'config-element-select',
            name: 'fallbackId',
            fieldLabel: '{s name=shop/detail/fallback_label}Translation fallback{/s}',
            helpText: '{s name=shop/detail/fallback_help}Fallback for translations.{/s}',
            store: 'base.Translation'
        },{
            xtype: 'config-element-boolean',
            name: 'customerScope',
            fieldLabel: '{s name=shop/detail/customer_scope_label}Customer scope{/s}',
            helpText: '{s name=shop/detail/customer_scope_help}Limit the customer registry for the current shop.{/s}',
            isMainField: true,
            hidden: true
        },{
            xtype: 'config-element-boolean',
            name: 'active',
            fieldLabel: '{s name=shop/detail/active_label}Active{/s}'
        },
        me.getDefaultField(),
        {
            xtype: 'config-shop-currency',
            isMainField: true,
            hidden: true
        },{
            xtype: 'config-shop-page'
        }]
    },

    loadRecord: function() {
        var me = this;
        me.categorySelect.setValue(null);
        me.categorySelect.setRawValue(null);

        me.callParent(arguments);
    },

    updateRecord: function(record) {
        var me = this;
        record = record || me.getRecord();
        record.raw.main = { };
        record.raw.template = { };

        me.callParent(arguments);
    }
});
//{/block}
