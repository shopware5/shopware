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

    getMainField: function() {
        var me = this;
        return {
            xtype: 'hidden',
            name: 'mainId',
            fieldLabel: '{s name=shop/detail/main_shop_label}Main shop{/s}',
            helpText: '{s name=shop/detail/main_shop_help}{/s}'
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
                    mainField = form.down('[name=mainId]'),
                    fields = form.query('[isMainField]'),
                    requiredFields = form.query('[isMainRequired]'),
                    //securePathField = form.down('[name=secureBasePath]'),
                    secureField = form.down('[name=secure]'),
                    scopeField = form.down('[name=customerScope]'),
                    action = value ? 'show' : 'hide';
                Ext.each(fields, function(field) {
                    field[action]();
                });
                Ext.each(requiredFields, function(field) {
                    field.allowBlank = !value;
                });
                mainField.setValue(value ? null : 1);
                if(!value) {
                    secureField.setValue(false);
                }
                //securePathField[value ? 'hide' : 'show']();
                scopeField.hide();
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

        return [{
            name: 'name',
            fieldLabel: '{s name=shop/detail/name_label}Name{/s}',
            allowBlank: false
        },{
            name: 'title',
            fieldLabel: '{s name=shop/detail/title_label}Title{/s}',
            helpText: '{s name=shop/detail/title_help}For the output in the shop frontend.{/s}'
        }, me.getMainField(),{
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
            hidden: true,
            handler: function(button, value) {
                var form = button.up('form'),
                    fields = form.query('[isSecure]'),
                    show = value ? 'show' : 'hide';
                Ext.each(fields, function(field) {
                    field[show]();
                    if(!value) {
                        field.setValue(null);
                    }
                });
            }
        }, {
            xtype: 'config-element-boolean',
            name: 'alwaysSecure',
            fieldLabel: '{s name=shop/detail/always_secure}Use always SSL{/s}',
            hidden: true,
            isSecure: true
        }, {
            name: 'secureHost',
            emptyText: '{s name=shop/detail/secure_host_empty_text}secure.example.com{/s}',
            fieldLabel: '{s name=shop/detail/secure_host_label}SSL host{/s}',
            hidden: true,
            isSecure: true
        },{
            name: 'secureBasePath',
            emptyText: '{s name=shop/detail/secure_path_empty_text}/secure{/s}',
            fieldLabel: '{s name=shop/detail/secure_path_label}SSL base path{/s}',
            hidden: true,
            isSecure: true
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
            store: 'base.Locale'
        },
        me.categorySelect,
        {
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
        }, me.getDefaultField(), {
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
    }
});
//{/block}
