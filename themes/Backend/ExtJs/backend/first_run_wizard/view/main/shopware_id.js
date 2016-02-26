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
 * Shopware First Run Wizard - Shopware Id tab
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

//{namespace name=backend/first_run_wizard/main}
//{block name="backend/first_run_wizard/view/main/shopware_id"}

Ext.define('Shopware.apps.FirstRunWizard.view.main.ShopwareId', {
    extend:'Ext.container.Container',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.first-run-wizard-shopware-id',

    /**
     * Name attribute used to generate event names
     */
    name:'shopware-id',

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        newRegistrationForm: {
            title: '{s name=shopware_id/new_registration_form/title}New to Shopware?{/s}',
            shopwareId: '{s name=shopware_id/new_registration_form/shopwareId}Shopware ID{/s}',
            password: '{s name=shopware_id/new_registration_form/password}Password{/s}',
            passwordMessage: '{s name=shopware_id/new_registration_form/passwordMessage}The passwords do not match.{/s}',
            confirmPassword: '{s name=shopware_id/new_registration_form/confirmPassword}Confirm password{/s}',
            email: '{s name=shopware_id/new_registration_form/email}Email{/s}',
            registerButton: '{s name=shopware_id/new_registration_form/register_button}Register{/s}',
            registerDomain: '{s name=shopware_id/new_registration_form/register_domain}Register domain{/s}'
        },
        existingAccountForm: {
            title: '{s name=shopware_id/existing_account_form/title}Already have an account?{/s}',
            shopwareId: '{s name=shopware_id/existing_account_form/shopwareId}Shopware ID{/s}',
            password: '{s name=shopware_id/existing_account_form/password}Password{/s}',
            passwordMessage: '{s name=shopware_id/existing_account_form/passwordMessage}The passwords do not match.{/s}',
            forgotPassword: '{s name=shopware_id/existing_account_form/forgotPassword}Forgot your password?{/s}',
            forgotPasswordLink: '{s name=shopware_id/existing_account_form/forgotPasswordLink}https://account.shopware.com/#/forgotPassword{/s}',
            registerButton: '{s name=shopware_id/existing_account_form/registerButton}Login{/s}',
            registerDomain: '{s name=shopware_id/existing_account_form/register_domain}Register domain{/s}'
        },
        content: {
            title: '{s name=shopware_id/content/title}Shopware ID{/s}',
            descriptionMessage: '{s name=shopware_id/content/description_message}Here you can create you personal Shopware ID. The Shopware ID will give you access to your Shopware account in our forum, wiki and other community resources. It will also grant you access to our plugin store, where you can find many more plugins that will help you easily customize your shop to your needs.{/s}',
            lockedMessage: '{s name=shopware_id/content/locked_message}You are already logged in using your Shopware ID.{/s}',
            lockedErrorMessage: "{s name=shopware_id/content/locked_error_message}<p>You have successfully logged in using your Shopware ID, but the domain validation process failed with the following error message:</p><br><br><pre>[0]</pre><br><br><p>Please click <a href='http://en.wiki.shopware.com/Shopware-ID-Shopware-Account_detail_1433.html#Add_shop_.2F_domain' target='_blank'>here</a> to use manual domain validation, or click the 'Next' button to continue without validating your domain.</p>{/s}"
        },
        buttons: {
            skip: '{s name=shopware_id/buttons/skip}Skip{/s}'
        }
    },

    initComponent: function() {
        var me = this;

        me.sbpLogin = Shopware.app.Application.getController('Shopware.apps.Index').sbpLogin;

        // If 1, we are already logged in
        if (me.sbpLogin == 1) {
            me.descriptionContainer = Ext.create('Ext.container.Container', {
                border: false,
                bodyPadding: 20,
                width: '100%',
                style: 'margin-bottom: 10px;',
                html: '<p>' + me.snippets.content.lockedMessage + '</p>'
            });
        
            me.items = [
                {
                    xtype: 'container',
                    border: false,
                    bodyPadding: 20,
                    style: 'font-weight: 700; line-height: 20px;',
                    html: '<h1>' + me.snippets.content.title + '</h1>'
                },
                me.descriptionContainer
            ];

            me.snippets.buttons.next = null;
        
        } else {
            me.existingAccountForm = me.createExistingAccountForm();
            me.newRegistrationForm = me.createNewRegistrationForm();
            me.descriptionContainer = Ext.create('Ext.container.Container', {
                border: false,
                bodyPadding: 20,
                width: '100%',
                style: 'margin-bottom: 10px;',
                html: '<p>' + me.snippets.content.descriptionMessage + '</p>'
            });

            me.items = [
                {
                    xtype: 'container',
                    border: false,
                    bodyPadding: 20,
                    style: 'font-weight: 700; line-height: 20px;',
                    html: '<h1>' + me.snippets.content.title + '</h1>'
                },
                me.descriptionContainer,
                me.newRegistrationForm,
                me.existingAccountForm
            ]
        }

        me.registerEvents();

        me.callParent(arguments);
    },

    lock: function(message) {
        var me = this;
        
        me.existingAccountForm.hide();
        me.newRegistrationForm.hide();
        if (Ext.isEmpty(message)) {
            me.descriptionContainer.update('<p>' + me.snippets.content.lockedMessage + '</p>');
        } else {
            me.descriptionContainer.update(
                Ext.String.format(me.snippets.content.lockedErrorMessage, message)
            );
        }
        me.sbpLogin = 1;
    },

    getButtons: function() {
        var me = this,
            buttons = { next: {} };

        if (me.sbpLogin == 0) {
            buttons.next.text = me.snippets.buttons.skip;
        }

        return buttons;
    },

    /**
     * Registers additional component events.
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the register button.
             */
            'submitRegistrationForm',
            /**
             * Event will be fired when the user clicks the login button.
             */
            'submitLoginForm'
        );
    },

    /**
     * Creates the new registration form
     *
     * @return Ext.form.FieldSet Contains the form for new user registration
     */
    createNewRegistrationForm: function () {
        var me = this;

        me.newRegistrationShopwareId = Ext.create('Ext.form.field.Text', {
            fieldLabel: me.snippets.newRegistrationForm.shopwareId,
            name:'shopwareID',
            allowBlank:false,
            required:true,
            enableKeyEvents:true,
            checkChangeBuffer:700,
            labelWidth:150
        });

        me.newRegistrationPasswordField = Ext.create('Ext.form.field.Text', {
            name:'password',
            inputType:'password',
            allowBlank: false,
            required: true,
            fieldLabel:me.snippets.newRegistrationForm.password,
            cls: Ext.baseCSSPrefix + 'password-field',
            minLength: 5,
            labelWidth:150,
            validator:function (value) {
                if ( Ext.String.trim(value) == Ext.String.trim(me.newRegistrationPasswordConfirmationField.getValue()) ) {
                    me.newRegistrationPasswordConfirmationField.clearInvalid();
                    return true;
                } else {
                    return me.snippets.newRegistrationForm.passwordMessage;
                }
            }
        });

        me.newRegistrationPasswordConfirmationField = Ext.create('Ext.form.field.Text', {
            name:'passwordConfirmation',
            inputType:'password',
            allowBlank: false,
            required: true,
            fieldLabel:me.snippets.newRegistrationForm.confirmPassword,
            minLength: 5,
            labelWidth:150,
            validator:function (value) {
                if ( Ext.String.trim(value) == Ext.String.trim(me.newRegistrationPasswordField.getValue()) ) {
                    me.newRegistrationPasswordField.clearInvalid();
                    return true;
                } else {
                    return me.snippets.newRegistrationForm.passwordMessage;
                }
            }
        });

        me.newRegistrationEmail = Ext.create('Ext.form.field.Text', {
            fieldLabel:me.snippets.newRegistrationForm.email,
            name:'email',
            vtype: 'remote',
            validationUrl: '{url controller="base" action="validateEmail"}',
            validationErrorMsg: '{s name=invalid_email namespace=backend/base/vtype}The email address entered is not valid{/s}',
            allowBlank:false,
            required:true,
            enableKeyEvents:true,
            checkChangeBuffer:700,
            labelWidth:150
        });

        me.newRegistrationRegisterDomain = Ext.create('Ext.form.field.Checkbox', {
            fieldLabel: me.snippets.newRegistrationForm.registerDomain,
            name:'registerDomain',
            labelWidth:150,
            checked: true
        });

        me.newRegistrationSendButton = Ext.create('Ext.Button', {
            text: me.snippets.newRegistrationForm.registerButton,
            cls: 'primary',
            action:'register',
            minWidth: 150,
            style: {
                float: 'right'
            },
            handler: function() {
                var fields =[
                    me.newRegistrationShopwareId,
                    me.newRegistrationPasswordField,
                    me.newRegistrationEmail,
                    me.newRegistrationRegisterDomain
                ];

                me.fireEvent('submitRegistrationForm', fields);
            }
        });

        me.newRegistrationFormItems = [
            me.newRegistrationShopwareId,
            me.newRegistrationPasswordField,
            me.newRegistrationPasswordConfirmationField,
            me.newRegistrationEmail,
            me.newRegistrationRegisterDomain,
            { xtype: 'container', items: me.newRegistrationSendButton }
        ];

        return Ext.create('Ext.form.FieldSet', {
            title: me.snippets.newRegistrationForm.title,
            cls: Ext.baseCSSPrefix + 'base-field-set',
            width: 405,
            layout: 'anchor',
            defaults:{
                anchor: '100%',
                xtype:'textfield'
            },
            items: me.newRegistrationFormItems
        });
    },

    /**
     * Creates the existing account form
     *
     * @return Ext.form.FieldSet Contains the form for existing account login
     */
    createExistingAccountForm: function () {
        var me = this;

        me.existingAccountShopwareId = Ext.create('Ext.form.field.Text', {
            fieldLabel: me.snippets.existingAccountForm.shopwareId,
            name:'shopwareID',
            allowBlank:false,
            required:true,
            enableKeyEvents:true,
            checkChangeBuffer:700,
            labelWidth:150
        });

        me.existingAccountPasswordField = Ext.create('Ext.form.field.Text', {
            name:'password',
            inputType:'password',
            allowBlank: false,
            required: true,
            fieldLabel:me.snippets.existingAccountForm.password,
            cls: Ext.baseCSSPrefix + 'password-field',
            labelWidth:150
        });

        me.existingAccountRegisterDomain = Ext.create('Ext.form.field.Checkbox', {
            fieldLabel:me.snippets.existingAccountForm.registerDomain,
            name:'registerDomain',
            labelWidth:150,
            checked: true
        });

        me.existingAccountForgotPassword = Ext.create('Ext.container.Container', {
            html: '<a target="_blank" href="' + me.snippets.existingAccountForm.forgotPasswordLink + '">'+me.snippets.existingAccountForm.forgotPassword+'</a>'
        });

        me.existingAccountSendButton = Ext.create('Ext.Button', {
            text: me.snippets.existingAccountForm.registerButton,
            cls: 'primary',
            width: 150,
            style: {
                float: 'right'
            },
            handler: function() {
                var fields =[
                    me.existingAccountShopwareId,
                    me.existingAccountPasswordField,
                    me.existingAccountRegisterDomain
                ];

                me.fireEvent('submitLoginForm', fields);
            }
        });

        me.existingAccountFormItems = [
            me.existingAccountShopwareId,
            me.existingAccountPasswordField,
            me.existingAccountRegisterDomain,
            { xtype: 'container', items: me.existingAccountSendButton },
            me.existingAccountForgotPassword
        ];

        return Ext.create('Ext.form.FieldSet', {
            title: me.snippets.existingAccountForm.title,
            cls: Ext.baseCSSPrefix + 'base-field-set',
            width: 405,
            layout: 'anchor',
            defaults:{
                anchor: '100%',
                xtype:'textfield'
            },
            items: me.existingAccountFormItems
        });
    }
});

//{/block}
