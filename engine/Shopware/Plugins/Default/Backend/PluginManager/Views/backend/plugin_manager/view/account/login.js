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
 * @package    PluginManager
 * @subpackage Account
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/plugin_manager/translation}

//{block name="backend/plugin_manager/view/account/login"}
Ext.define('Shopware.apps.PluginManager.view.account.Login', {
    extend: 'Ext.container.Container',

    cls: 'plugin-manager-login-window',

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets: {
        title: '{s name=account/login/title}Already have an account?{/s}',
        shopwareId: '{s name=account/login/shopwareId}Shopware ID{/s}',
        password: '{s name=account/login/password}Password{/s}',
        passwordMessage: '{s name=account/login/passwordMessage}The passwords do not match.{/s}',
        forgotPassword: '{s name=account/login/forgotPassword}Forgot your password?{/s}',
        forgotPasswordLink: '{s name=account/login/forgotPasswordLink}https://account.shopware.com/#/forgotPassword{/s}',
        registerDomain: '{s name=account/login/register_domain}Register domain{/s}',
        cancelButton: '{s name="account/login/cancel"}Cancel{/s}',
        loginButton: '{s name="account/login/login"}Login{/s}'
    },

    width: 280,
    anchor: '100%',
    border: false,

    initComponent: function () {
        var me = this;

        me.items = [
            me.createForm()
        ];

        me.callParent(arguments);
    },

    createForgotLink: function () {
        var me = this;

        return Ext.create('Ext.Component', {
            html: '<a href="' + me.snippets.forgotPasswordLink + '" target="_blank">' + me.snippets.forgotPassword + '</a>',
            cls: 'forgot'
        });
    },

    createForm: function () {
        var me = this;

        me.formPanel = Ext.create('Ext.form.Panel', {
            border: false,
            layout: 'anchor',
            defaults: {
                anchor: '100%'
            },
            cls: 'form-panel',
            items: [
                me.createLoginText(),
                me.createShopwareIdField(),
                me.createPasswordField(),
                me.createForgotLink(),
                me.createRegisterDomainField(),
                me.createActionButtons()
            ]
        });

        return me.formPanel;
    },

    createLoginText: function() {
        var me = this;

        return {
            border: false,
            margin: '22 0 20 0',
            html: '<span class="section-title">' + me.snippets.title + '</span>'
        };
    },

    createActionButtons: function () {
        var me = this;

        me.registerButton = Ext.create('PluginManager.container.Container', {
            html: me.snippets.loginButton,
            cls: 'plugin-manager-action-button primary',
            margin: '0 0 0 0',
            handler: function () {
                me.applyLogin();
            }
        });

        me.actionButtons = Ext.create('Ext.container.Container', {
            margin: '10 0 0 0',
            padding: '10 0',
            width: 280,
            cls: 'action-buttons',
            items: [me.registerButton]
        });

        return me.actionButtons;

    },

    createRegisterDomainField: function() {
        var me = this;

        me.LoginRegisterDomain = Ext.create('Ext.form.field.Checkbox', {
            name: 'registerDomain',
            boxLabel: me.snippets.registerDomain,
            cls: 'input--field',
            labelWidth: 130,
            listeners: {
                specialkey: function (field, e) {
                    if (e.getKey() == e.ENTER) {
                        me.applyLogin();
                    }
                }
            }
        });

        return me.LoginRegisterDomain;
    },

    createShopwareIdField: function () {
        var me = this;

        me.shopwareIdField = Ext.create('Ext.form.field.Text', {
            name: 'shopwareID',
            allowBlank: false,
            cls: 'input--field',
            emptyText: me.snippets.shopwareId,
            margin: '10 0',
            labelWidth: 130,
            listeners: {
                specialkey: function (field, e) {
                    if (e.getKey() == e.ENTER) {
                        me.applyLogin();
                    }
                }
            }
        });

        return me.shopwareIdField;
    },

    createPasswordField: function () {
        var me = this;

        me.passwordField = Ext.create('Ext.form.field.Text', {
            name: 'password',
            allowBlank: false,
            labelWidth: 130,
            cls: 'input--field',
            emptyText: me.snippets.password,
            inputType: 'password',
            listeners: {
                specialkey: function (field, e) {
                    if (e.getKey() == e.ENTER) {
                        me.applyLogin();
                    }
                }
            }
        });

        return me.passwordField;
    },

    applyLogin: function () {
        var me = this;

        if (!me.formPanel.getForm().isValid()) {
            return;
        }

        var loginData = me.formPanel.getForm().getValues();

        loginData.registerDomain = loginData.registerDomain === "on";
        loginData.shopwareId = loginData.shopwareID;

        Shopware.app.Application.fireEvent(
            'store-login',
            loginData,
            function () {
                me.callback();
            }
        );
    }
});
//{/block}