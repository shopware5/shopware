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
//{block name="backend/first_run_wizard/controller/shopware_id"}

Ext.define('Shopware.apps.FirstRunWizard.controller.ShopwareId', {

    extend:'Ext.app.Controller',

    refs: [
        { ref: 'shopwareIdPanel', selector: 'first-run-wizard-shopware-id' }
    ],

    /**
     * Contains all snippets for the controller
     * @object
     */
    snippets:{
        newRegistrationForm: {
            successTitle: '{s name=newRegistrationForm/successTitle}Shopware ID registration{/s}',
            successMessage: '{s name=newRegistrationForm/successMessage}Your Shopware ID has been successfully registered{/s}',
            errorTitle: '{s name=newRegistrationForm/errorTitle}Error registering your Shopware ID{/s}',
            errorFormValidationMessage: '{s name=newRegistrationForm/errorFormValidationMessage}The field [0] is not valid{/s}',
            errorServerMessage: '{s name=newRegistrationForm/errorServerMessage}The following error was detected: [0]{/s}',
            waitTitle: '{s name=newRegistrationForm/waitTitle}Registering your Shopware ID{/s}',
            waitMessage: '{s name=newRegistrationForm/waitMessage}This process might take a few seconds{/s}'
        },
        existingAccountForm: {
            successTitle: '{s name=existingAccountForm/successTitle}Shopware ID login{/s}',
            successMessage: '{s name=existingAccountForm/successMessage}Login successful{/s}',
            errorTitle: '{s name=existingAccountForm/errorTitle}Error logging in to your account{/s}',
            errorFormValidationMessage: '{s name=existingAccountForm/errorFormValidationMessage}The field [0] is not valid{/s}',
            errorServerMessage: '{s name=existingAccountForm/errorServerMessage}The following error was detected: [0]{/s}',
            waitTitle: '{s name=existingAccountForm/waitTitle}Logging in{/s}',
            waitMessage: '{s name=existingAccountForm/waitMessage}This process might take a few seconds{/s}'
        },
        domainRegistration: {
            successTitle: '{s name=domainRegistration/successTitle}Domain registration{/s}',
            successMessage: '{s name=domainRegistration/successMessage}Domain registration successful{/s}',
            errorServerMessage: '{s name=domainRegistration/errorServerMessage}The following error was detected: [0]{/s}',
            waitTitle: '{s name=domainRegistration/waitTitle}Registering domain{/s}',
            waitMessage: '{s name=domainRegistration/waitMessage}This process might take a few seconds{/s}'
        },
        growlMessage:'{s name=growlMessage}First run wizard{/s}'
    },

    init: function () {
        var me = this;

        me.control({
            'first-run-wizard-shopware-id': {
                submitRegistrationForm: me.onSubmitRegistrationForm,
                submitLoginForm: me.onSubmitLoginForm
            }
        });

        me.callParent(arguments);
    },

    onSubmitRegistrationForm: function(fields) {
        var me = this,
            formValidation;

        formValidation = me.validateForm(fields, me.snippets.newRegistrationForm);
        if (formValidation === false) {
            return false;
        }

        me.submitShopwareIdRequest(
            formValidation,
            '{url controller="firstRunWizard" action="registerNewId"}',
            me.snippets.newRegistrationForm
        );
    },

    onSubmitLoginForm: function(fields) {
        var me = this,
            formValidation;

        formValidation = me.validateForm(fields, me.snippets.existingAccountForm);
        if (formValidation === false) {
            return false;
        }

        me.submitShopwareIdRequest(
            formValidation,
            '{url controller="firstRunWizard" action="login"}',
            me.snippets.existingAccountForm

        );
    },

    submitShopwareIdRequest: function(params, url, snippetNamespace) {
        var me = this;

        me.splashScreen = Ext.Msg.wait(
            snippetNamespace.waitMessage,
            snippetNamespace.waitTitle
        );

        Ext.Ajax.request({
            url: url,
            method: 'POST',
            params: params,
            callback: function(options, success, response) {
                var result = Ext.JSON.decode(response.responseText, true),
                    message;

                if (!Ext.isEmpty(result) && !Ext.isEmpty(result.message)) {
                    message = result.message;
                } else {
                    message = response.responseText;
                }

                if (!success || !result || result.success == false) {
                    Shopware.Notification.createGrowlMessage(
                        snippetNamespace.errorTitle,
                        Ext.String.format(snippetNamespace.errorServerMessage, message),
                        me.snippets.growlMessage
                    );
                    me.splashScreen.close();
                } else if (success && result.success) {
                    Shopware.Notification.createGrowlMessage(
                        snippetNamespace.successTitle,
                        snippetNamespace.successMessage,
                        me.snippets.growlMessage
                    );

                    Ext.create('Shopware.notification.SubscriptionWarning').checkSecret();

                    if (params.registerDomain !== false) {
                        me.submitShopwareDomainRequest(params);
                    } else {
                        me.lockView();
                    }
                }
            }
        });
    },

    submitShopwareDomainRequest: function(params) {
        var me = this;

        me.splashScreen = Ext.Msg.wait(
            me.snippets.domainRegistration.waitMessage,
            me.snippets.domainRegistration.waitTitle
        );

        Ext.Ajax.request({
            url: '{url controller="firstRunWizard" action="registerDomain"}',
            method: 'POST',
            params: params,
            success: function(response) {
                var result = Ext.JSON.decode(response.responseText);
                
                if (!result || result.success == false) {
                    me.lockView(result.message);
                } else if (result.success) {
                    Shopware.Notification.createGrowlMessage(
                        me.snippets.domainRegistration.successTitle,
                        me.snippets.domainRegistration.successMessage,
                        me.snippets.growlMessage
                    );
                    me.lockView();
                }

                
            }
        });
    },

    lockView: function(message) {
        var me = this;

        me.getShopwareIdPanel().lock(message);
        me.splashScreen.close();
        if (Ext.isEmpty(message)) {
            me.getController('Main').navigateNext();
        }
    },

    validateForm: function(fields, snippetNamespace) {
        var me = this,
            isValid = true,
            values = {},
            fieldName;

        Ext.each(fields, function(field) {
            if (!field.validate()) {

                isValid = false;

                if (field.getFieldLabel()) {
                    fieldName = field.getFieldLabel();
                } else if (f.getName()) {
                    fieldName = field.getName();
                }

                Shopware.Notification.createGrowlMessage(
                    snippetNamespace.errorTitle,
                    Ext.String.format(snippetNamespace.errorFormValidationMessage, fieldName),
                    me.snippets.growlMessage
                );
                return false;
            } else {
                values[field.getName()] = field.getValue();
            }
        });

        if (isValid) {
            return values;
        } else {
            return false;
        }
    }
});

//{/block}
