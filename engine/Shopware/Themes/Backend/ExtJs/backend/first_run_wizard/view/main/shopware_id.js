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
     * Layout type for the component.
     * @string
     */
    layout: 'vbox',

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
            skipDomainRegistration: '{s name=shopware_id/new_registration_form/skipDomainRegistration}Don\'t register domain{/s}'
        },
        existingAccountForm: {
            title: '{s name=shopware_id/existing_account_form/title}Already have an account?{/s}',
            shopwareId: '{s name=shopware_id/existing_account_form/shopwareId}Shopware ID{/s}',
            password: '{s name=shopware_id/existing_account_form/password}Password{/s}',
            passwordMessage: '{s name=shopware_id/existing_account_form/passwordMessage}The passwords do not match.{/s}',
            forgotPassword: '{s name=shopware_id/existing_account_form/forgotPassword}Forgot your password?{/s}',
            forgotShopwareId: '{s name=shopware_id/existing_account_form/forgotShopwareId}Forgot your Shopware ID?{/s}',
            registerButton: '{s name=shopware_id/existing_account_form/registerButton}Login{/s}',
            skipDomainRegistration: '{s name=shopware_id/existing_account_form/skipDomainRegistration}Don\'t register domain{/s}'
        },
        content: {
            title: '{s name=shopware_id/content/title}Shopware ID{/s}',
            descriptionMessage: '{s name=shopware_id/content/description_message}Description lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut in dui aliquam, luctus leo ut, euismod mauris. Nunc ac ultrices sapien. Curabitur augue nunc, euismod a ullamcorper vel, pulvinar in lorem. Nam ornare leo a mi semper porta. Pellentesque faucibus nisl massa, nec ultrices ante condimentum et.{/s}',
            lockedMessage: '{s name=shopware_id/content/locked_message}Locked lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut in dui aliquam, luctus leo ut, euismod mauris. Nunc ac ultrices sapien. Curabitur augue nunc, euismod a ullamcorper vel, pulvinar in lorem. Nam ornare leo a mi semper porta. Pellentesque faucibus nisl massa, nec ultrices ante condimentum et.{/s}'
        },
        buttons: {
            skip: '{s name=shopware_id/buttons/skip}Skip{/s}'
        }
    },

    initComponent: function() {
        var me = this;

        me.sbpLogin = Shopware.app.Application.getController('Shopware.apps.Index').sbpLogin;

        me.title = me.snippets.title;

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
                me.existingAccountForm,
                me.newRegistrationForm
            ]
        }

        me.registerEvents();

        me.callParent(arguments);
    },

    lock: function() {
        var me = this;
        me.existingAccountForm.hide();
        me.newRegistrationForm.hide();
        me.descriptionContainer.update('<p>' + me.snippets.content.lockedMessage + '</p>');
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
            checkChangeBuffer:700
        });

        me.newRegistrationPasswordField = Ext.create('Ext.form.field.Text', {
            name:'password',
            anchor: '92%',
            inputType:'password',
            allowBlank: false,
            required: true,
            fieldLabel:me.snippets.newRegistrationForm.password,
            cls: Ext.baseCSSPrefix + 'password-field',
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
            anchor: '95%',
            allowBlank: false,
            required: true,
            fieldLabel:me.snippets.newRegistrationForm.confirmPassword,
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
            checkChangeBuffer:700
        });

        me.newRegistrationSkipDomainRegistration = Ext.create('Ext.form.field.Checkbox', {
            fieldLabel:me.snippets.newRegistrationForm.skipDomainRegistration,
            name:'skipDomainRegistration'
        });

        me.newRegistrationSendButton = Ext.create('Ext.Button', {
            text: me.snippets.newRegistrationForm.registerButton,
            cls: 'primary',
            action:'register',
            handler: function() {
                var fields =[
                    me.newRegistrationShopwareId,
                    me.newRegistrationPasswordField,
                    me.newRegistrationEmail,
                    me.newRegistrationSkipDomainRegistration
                ];

                me.fireEvent('submitRegistrationForm', fields);
            }
        });

        me.newRegistrationFormItems = [
            me.newRegistrationShopwareId,
            me.newRegistrationPasswordField,
            me.newRegistrationPasswordConfirmationField,
            me.newRegistrationEmail,
            me.newRegistrationSkipDomainRegistration,
            me.newRegistrationSendButton
        ];

        return Ext.create('Ext.form.FieldSet', {
            title: me.snippets.newRegistrationForm.title,
            cls: Ext.baseCSSPrefix + 'base-field-set',
            defaults:{
                labelWidth: 150,
                minWidth: 250,
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
            checkChangeBuffer:700
        });

        me.existingAccountPasswordField = Ext.create('Ext.form.field.Text', {
            name:'password',
            anchor: '92%',
            inputType:'password',
            allowBlank: false,
            required: true,
            fieldLabel:me.snippets.existingAccountForm.password,
            cls: Ext.baseCSSPrefix + 'password-field'
        });

        me.existingAccountSkipDomainRegistration = Ext.create('Ext.form.field.Checkbox', {
            fieldLabel:me.snippets.existingAccountForm.skipDomainRegistration,
            name:'skipDomainRegistration'
        });

        me.existingAccountForgotPassword = Ext.create('Ext.container.Container', {
            html: '<a target="_blank" href="http://account.shopware.de/shopware.php/sViewport,LostPassword">'+me.snippets.existingAccountForm.forgotPassword+'</a>'
        });
        me.existingAccountForgotShopwareId = Ext.create('Ext.container.Container', {
            html: '<a target="_blank" href="http://account.shopware.de/shopware.php/sViewport,LostShopwareId">'+me.snippets.existingAccountForm.forgotShopwareId+'</a>'
        });

        me.existingAccountSendButton = Ext.create('Ext.Button', {
            text: me.snippets.existingAccountForm.registerButton,
            cls: 'primary',
            handler: function() {
                var fields =[
                    me.existingAccountShopwareId,
                    me.existingAccountPasswordField,
                    me.existingAccountSkipDomainRegistration
                ];

                me.fireEvent('submitLoginForm', fields);
            }
        });

        me.existingAccountFormItems = [
            me.existingAccountShopwareId,
            me.existingAccountPasswordField,
            me.existingAccountSkipDomainRegistration,
            me.existingAccountForgotPassword,
            me.existingAccountForgotShopwareId,
            me.existingAccountSendButton
        ];

        return Ext.create('Ext.form.FieldSet', {
            title: me.snippets.existingAccountForm.title,
            cls: Ext.baseCSSPrefix + 'base-field-set',
            defaults:{
                anchor:'95%',
                labelWidth:150,
                minWidth:250,
                xtype:'textfield'
            },
            items: me.existingAccountFormItems
        });
    }
});

//{/block}
