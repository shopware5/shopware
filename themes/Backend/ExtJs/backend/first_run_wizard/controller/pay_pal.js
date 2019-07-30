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
 * Shopware First Run Wizard - PayPal controller
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

// {namespace name=backend/first_run_wizard/main}
// {block name="backend/first_run_wizard/controller/pay_pal"}

Ext.define('Shopware.apps.FirstRunWizard.controller.PayPal', {

    extend: 'Ext.app.Controller',

    pluginName: 'SwagPaymentPayPalUnified',

    refs: [
        { ref: 'skipButton', selector: 'first-run-wizard button[name=skip-button]' },
        { ref: 'nextButton', selector: 'first-run-wizard button[name=next-button]' },
        { ref: 'payPalCard', selector: 'first-run-wizard-pay-pal' },
        { ref: 'firstRunWizard', selector: 'first-run-wizard' },
        { ref: 'configurationForm', selector: 'first-run-wizard-pay-pal form[name=pay-pal-configuration-form]' },
        { ref: 'saveButton', selector: 'first-run-wizard-pay-pal form[name=pay-pal-configuration-form] button' }
    ],

    snippets: {
        downloadFailed: '{s name=pay_pal/errors/download}{/s}',
        installFailed: '{s name=pay_pal/errors/install}{/s}',
        unknownFailed: '{s name=pay_pal/errors/unknown}{/s}',
        configurationFailed: '{s name=pay_pal/errors/configuration}{/s}',
        activateFailed: '{s name=pay_pal/errors/activate}{/s}',
        formInvalid: '{s name=pay_pal/errors/configuration_invalid}{/s}',
    },

    init: function () {
        this.control({
            'first-run-wizard-pay-pal': {
                'activate': this.initCard,
                'start': this.startInstall,
                'save-configuration': this.saveConfiguration
            },
            'first-run-wizard': {
                'navigate-back-pay-pal': this.onLeavePayPalCard,
                'navigate-next-pay-pal': this.onLeavePayPalCard,
                'navigate-skip-pay-pal': this.onLeavePayPalCard,
            },
            'first-run-wizard-pay-pal container[itemId=install]': {
                'activate': this.initInstallCard
            },

            'first-run-wizard-pay-pal container[itemId=done]': {
                'activate': this.initDoneCard
            }
        });

        this.callParent(arguments);
    },

    onLeavePayPalCard: function (context, callback) {
        this.getSkipButton().hide();
        this.getNextButton().enable();
        callback();
    },

    startInstall: function () {
        this.getPayPalCard().navigateToCard('install');
    },

    initCard: function () {
        this.getNextButton().disable();
        this.getPayPalCard().navigateToCard('empty');

        this.handlePluginState();
    },

    initInstallCard: function () {
        this.getPayPalCard().setLoading(true);

        this.installPlugin();
    },

    initDoneCard: function () {
        this.getNextButton().enable();
    },

    saveConfiguration: function () {
        var me = this,
            values = this.getConfigurationForm().getValues(),
            valid = this.getConfigurationForm().getForm().isValid(),
            saveButton = this.getSaveButton();

        if (!valid) {
            Shopware.Notification.createGrowlMessage(null, me.snippets.formInvalid);
            return;
        }

        saveButton.disable();

        me.initAjaxRequest(
            '{url controller=FirstRunWizardPluginManager action=saveConfiguration}',
            values,
            me.snippets.configurationFailed,
            function () {
                me.initAjaxRequest(
                    '{url controller=PluginInstaller action=activatePlugin}',
                    { technicalName: me.pluginName},
                    me.snippets.activateFailed,
                    Ext.bind(me.compileTheme, me)
                );
            }
        );
    },

    compileTheme: function () {
        var me = this;

        me.initAjaxRequest(
            '{url controller="Cache" action="clearCache"}',
            { 'cache[theme]': 'on' },
            me.snippets.unknownFailed,
            function () {
                Shopware.app.Application.fireEvent('shopware-theme-cache-warm-up-request');
                me.getFirstRunWizard().fireEvent('navigate-next');
                me.getNextButton().enable();
                me.getSaveButton().enable();
            }
        );
    },

    startDownload: function () {
        var me = this;

        me.getPayPalCard().setLoading(true);
        this.initAjaxRequest(
            '{url controller=PluginManager action=metaDownload}',
            { technicalName: me.pluginName },
            me.snippets.downloadFailed,
            function (response) {
                Ext.bind(
                    me.downloadPlugin,
                    me,
                    [
                        response,
                        Ext.bind(me.extractPlugin, me)
                    ]
                )();
            }
        );
    },

    handlePluginState: function () {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller=PluginManager action=detail}',
            params: {
                technicalName: me.pluginName
            },
            success: function (operation) {
                var response = Ext.decode(operation.responseText),
                    successState = response.success;

                if (!successState) {
                    me.displayError(me.snippets.unknownFailed);

                    return;
                }

                // Plugin is not yet in the system
                if (response.data === null) {
                    me.startDownload();

                    return;
                }

                me.getSkipButton().show();

                // Plugin not yet installed
                if (response.data.installationDate === null) {
                    me.getPayPalCard().navigateToCard('start');

                    return;
                }

                if (response.data.active === false) {
                    me.getPayPalCard().navigateToCard('configuration');

                    return;
                }

                me.getSkipButton().hide();
                me.getPayPalCard().navigateToCard('done');
            },
        });
    },

    installPlugin: function () {
        var me = this;

        me.initAjaxRequest(
            '{url controller=PluginInstaller action=installPlugin}',
            { technicalName: me.pluginName },
            me.snippets.installFailed,
            function () {
                me.getPayPalCard().setLoading(false);
                me.getPayPalCard().navigateToCard('configuration');
                me.getSkipButton().show();
            }
        );
    },

    /**
     * @param { Object } responseData
     * @param { Function } callback
     * @param { int } offset
     */
    downloadPlugin: function (responseData, callback, offset) {
        var me = this;

        offset = offset || 0;

        me.initAjaxRequest(
            '{url controller=PluginManager action=rangeDownload}',
            {
                offset: offset,
                fileName: responseData.fileName,
                uri: responseData.uri,
                size: responseData.size,
                sha1: responseData.sha1
            },
            me.snippets.downloadFailed,
            function (response) {
                if (!!(response.finish) === true) {
                    Ext.Function.defer(function () {
                        callback(response.destination);
                    }, 300);
                } else {
                    offset = response.offset;
                    me.downloadPlugin(responseData, callback, offset);
                }
            }
        );
    },

    /**
     * @param { string } destination
     */
    extractPlugin: function(destination) {
        var me = this;

        this.initAjaxRequest(
            '{url controller=PluginManager action=extract}',
            { technicalName: me.pluginName, fileName: destination },
            me.snippets.downloadFailed,
            function () {
                me.getPayPalCard().setLoading(false);
                me.getPayPalCard().navigateToCard('start');
                me.getSkipButton().show();
            }
        );
    },

    /**
     * @param { string } messageName
     */
    displayError: function (messageName) {
        this.getPayPalCard().displayErrorCard(messageName);
        this.getSkipButton().show();
    },

    initAjaxRequest: function (url, params, errorMessage, callback) {
        var me = this;

        Ext.Ajax.request({
            url: url,
            params: params,
            success: function(operation) {
                var response = Ext.decode(operation.responseText),
                    successState = response.success;

                if (successState) {
                    callback(response);

                    return;
                }

                me.displayError(errorMessage);
            },
            failure: function () {
                me.displayError(errorMessage);
            }
        });
    },
});
// {/block}
