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
// {namespace name=backend/plugin_manager/translation}

// {block name="backend/plugin_manager/view/account/register"}
Ext.define('Shopware.apps.PluginManager.view.account.Register', {
    extend: 'Ext.container.Container',

    cls: 'plugin-manager-login-window',

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets: {
        title: '{s name=account/register/title}New to Shopware?{/s}',
        shopwareId: '{s name=account/register/shopwareId}Shopware ID{/s}',
        password: '{s name=account/register/password}Password{/s}',
        passwordMessage: '{s name=account/register/passwordMessage}The passwords do not match.{/s}',
        confirmPassword: '{s name=account/register/confirmPassword}Confirm password{/s}',
        email: '{s name=account/register/email}Email{/s}',
        registerButton: '{s name=account/register/register_button}Register{/s}',
        cancelButton: '{s name=account/register/cancel_button}Cancel{/s}',
        registerDomain: '{s name=account/register/register_domain}Register domain{/s}'
    },

    width: 400,
    border: false,
    layout: 'fit',

    initComponent: function () {
        var me = this;

        me.items = [
            me.createFormPanel()
        ];

        me.callParent(arguments);
    },

    createFormPanel: function () {
        var me = this;

        me.formPanel = Ext.create('Ext.form.Panel', {
            border: false,
            layout: 'anchor',
            defaults: {
                anchor: '100%'
            },
            cls: 'form-panel',
            items: [
                me.createRegisterText(),
                me.createShopwareIdField(),
                me.createPasswordField(),
                me.createPasswordConfirmationField(),
                me.createEmailField(),
                me.createRegisterDomainCheckbox(),
                me.createActionButtons()
            ]
        });

        return me.formPanel;
    },

    createRegisterText: function () {
        var me = this;

        me.newRegistrationRegisterText = {
            border: false,
            margin: '0 0 20 0',
            html: '<span class="section-title">' + me.snippets.title + '</span>'
        };

        return me.newRegistrationRegisterText;
    },

    createShopwareIdField: function () {
        var me = this;

        me.newRegistrationShopwareId = Ext.create('Ext.form.field.Text', {
            name: 'shopwareID',
            allowBlank: false,
            cls: 'input--field',
            emptyText: me.snippets.shopwareId,
            required: true,
            enableKeyEvents: true,
            checkChangeBuffer: 700,
            margin: '10 0',
            listeners: {
                specialkey: function (field, e) {
                    if (e.getKey() == e.ENTER) {
                        me.sendRegisterForm();
                    }
                }
            }
        });

        return me.newRegistrationShopwareId;
    },

    createPasswordField: function () {
        var me = this;

        me.newRegistrationPasswordField = Ext.create('Ext.form.field.Text', {
            name: 'password',
            inputType: 'password',
            emptyText: me.snippets.password,
            allowBlank: false,
            required: true,
            cls: Ext.baseCSSPrefix + 'password-field input--field',
            minLength: 5,
            validator: function (value) {
                if (Ext.String.trim(value) === Ext.String.trim(me.newRegistrationPasswordConfirmationField.getValue())) {
                    me.newRegistrationPasswordConfirmationField.clearInvalid();
                    return true;
                } else {
                    return me.snippets.passwordMessage;
                }
            },
            listeners: {
                specialkey: function (field, e) {
                    if (e.getKey() == e.ENTER) {
                        me.sendRegisterForm();
                    }
                }
            }
        });

        return me.newRegistrationPasswordField;
    },

    createPasswordConfirmationField: function () {
        var me = this;

        me.newRegistrationPasswordConfirmationField = Ext.create('Ext.form.field.Text', {
            name: 'passwordConfirmation',
            inputType: 'password',
            emptyText: me.snippets.confirmPassword,
            allowBlank: false,
            required: true,
            minLength: 5,
            cls: 'input--field',
            validator: function (value) {
                if (Ext.String.trim(value) === Ext.String.trim(me.newRegistrationPasswordField.getValue())) {
                    me.newRegistrationPasswordField.clearInvalid();
                    return true;
                } else {
                    return me.snippets.passwordMessage;
                }
            },
            listeners: {
                specialkey: function (field, e) {
                    if (e.getKey() == e.ENTER) {
                        me.sendRegisterForm();
                    }
                }
            }
        });

        return me.newRegistrationPasswordConfirmationField;
    },

    createEmailField: function () {
        var me = this;

        me.newRegistrationEmail = Ext.create('Ext.form.field.Text', {
            name: 'email',
            emptyText: me.snippets.email,
            vtype: 'remote',
            cls: 'input--field',
            validationUrl: '{url controller="base" action="validateEmail"}',
            validationErrorMsg: '{s name=invalid_email namespace=backend/base/vtype}The email address entered is not valid{/s}',
            allowBlank: false,
            required: true,
            enableKeyEvents: true,
            listeners: {
                specialkey: function (field, e) {
                    if (e.getKey() == e.ENTER) {
                        me.sendRegisterForm();
                    }
                }
            }
        });

        return me.newRegistrationEmail;
    },

    createRegisterDomainCheckbox: function () {
        var me = this;

        me.newRegistrationRegisterDomain = Ext.create('Ext.form.field.Checkbox', {
            name: 'registerDomain',
            boxLabel: me.snippets.registerDomain,
            cls: 'input--field',
            checked: true,
            listeners: {
                specialkey: function (field, e) {
                    if (e.getKey() == e.ENTER) {
                        me.sendRegisterForm();
                    }
                }
            }
        });

        return me.newRegistrationRegisterDomain;
    },

    createActionButtons: function () {
        var me = this;

        me.registerButton = Ext.create('PluginManager.container.Container', {
            html: me.snippets.registerButton,
            cls: 'plugin-manager-action-button primary',
            margin: '0 0 0 0',
            handler: function () {
                me.sendRegisterForm();
            }
        });

        me.actionButtons = Ext.create('Ext.container.Container', {
            margin: '10 0 0 0',
            width: 400,
            cls: 'action-buttons',
            items: [me.registerButton]
        });

        return me.actionButtons;
    },

    sendRegisterForm: function () {
        var me = this;

        if (!me.formPanel.getForm().isValid()) {
            return;
        }

        var formValues = me.formPanel.getForm().getValues();

        formValues.registerDomain = formValues.registerDomain === 'on';

        Shopware.app.Application.fireEvent(
            'store-register',
            formValues,
            function () {
                me.callback();
            }
        );
    }
});
// {/block}
