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
 * Shopware First Run Wizard - Config controller
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

//{namespace name=backend/first_run_wizard/main}
//{block name="backend/first_run_wizard/controller/config"}

Ext.define('Shopware.apps.FirstRunWizard.controller.Config', {

    extend:'Ext.app.Controller',

    refs: [
        { ref: 'configPanel', selector: 'first-run-wizard-config' }
    ],

    snippets: {
        configFormValidation: {
            successTitle: '{s name=config/configFormValidation/successTitle}Shop configuration{/s}',
            successMessage: '{s name=config/configFormValidation/successMessage}Configuration saved{/s}',
            errorTitle: '{s name=config/configFormValidation/errorTitle}Error saving your shop configuration{/s}',
            errorFormValidationMessage: '{s name=config/configFormValidation/errorFormValidationMessage}The field [0] is not valid{/s}',
            errorServerMessage: '{s name=config/configFormValidation/errorServerMessage}The following error was detected: [0]{/s}'
        },
        waitTitle: '{s name=config/waitTitle}Saving configuration settings{/s}',
        waitMessage: '{s name=config/waitMessage}This process might take a few seconds{/s}',
        growlMessage:'{s name=config/growlMessage}First run wizard{/s}'
    },

    init: function () {
        var me = this;

        me.control({
            'first-run-wizard': {
                'navigate-next-config': me.saveConfigData
            },
            'first-run-wizard-config': {
                'navigate-next': function() {
                    me.getController('Main').navigateNext();
                }
            }
        });

        me.fetchShopConfigData();

        me.callParent(arguments);
    },

    saveConfigData: function(context, callback) {
        var me = this,
            configPanel = me.getConfigPanel();

        var fields = [
            configPanel.themeBrandPrimaryColor,
            configPanel.themeBrandSecondaryColor,
            configPanel.themeDesktopLogo,
            configPanel.shopNameField,
            configPanel.mailField,
            configPanel.addressField,
            configPanel.bankAccountField,
            configPanel.companyField
        ];

        var formValidation = me.validateForm(fields);
        if (formValidation === false) {
            return false;
        }

        me.submitShopConfigData(
            formValidation,
            callback
        );

    },

    fetchShopConfigData: function() {
        var me = this,
            configPanel = me.getConfigPanel();

        Ext.Ajax.request({
            url: '{url controller="firstRunWizard" action="loadConfiguration"}',
            method: 'GET',
            success: function(response) {
                var result = Ext.JSON.decode(response.responseText);

                if(result.success) {
                    var formData = result.data;

                    configPanel.themeDesktopLogo.setValue(formData.desktopLogo);
                    configPanel.themeBrandPrimaryColor.setValue(formData['brand-primary']);
                    configPanel.themeBrandSecondaryColor.setValue(formData['brand-secondary']);
                    configPanel.shopNameField.setValue(formData.shopName);
                    configPanel.mailField.setValue(formData.mail);
                    configPanel.addressField.setValue(formData.address);
                    configPanel.bankAccountField.setValue(formData.bankAccount);
                    configPanel.companyField.setValue(formData.company);
                }
            }
        });
    },

    submitShopConfigData: function(params, callback) {
        var me = this;

        me.splashScreen = Ext.Msg.wait(
            me.snippets.waitMessage,
            me.snippets.waitTitle
        );

        Ext.Ajax.request({
            url: '{url controller="firstRunWizard" action="saveConfiguration"}',
            method: 'POST',
            params: params,
            success: function(response) {
                var result = Ext.JSON.decode(response.responseText);

                me.splashScreen.close();
                if (!result || result.success == false) {
                    Shopware.Notification.createGrowlMessage(
                        me.snippets.configFormValidation.errorTitle,
                        Ext.String.format(me.snippets.configFormValidation.errorServerMessage, result.message),
                        me.snippets.growlMessage
                    );
                } else if(result.success) {
                    Shopware.Notification.createGrowlMessage(
                        me.snippets.configFormValidation.successTitle,
                        me.snippets.configFormValidation.successMessage,
                        me.snippets.growlMessage
                    );
                    callback();
                }
            },
            failure: function(response, opts) {
                var result = Ext.JSON.decode(response.responseText);

                Shopware.Notification.createGrowlMessage(
                    me.snippets.configFormValidation.errorTitle,
                    Ext.String.format(me.snippets.configFormValidation.errorServerMessage, result.message),
                    me.snippets.growlMessage
                );
            }
        });
    },

    validateForm: function(fields) {
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
                    me.snippets.configFormValidation.errorTitle,
                    Ext.String.format(me.snippets.configFormValidation.errorFormValidationMessage, fieldName),
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
