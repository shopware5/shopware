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
 * Shopware First Run Wizard - Localization controller
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

// {namespace name=backend/first_run_wizard/main}
// {block name="backend/first_run_wizard/controller/localization"}

Ext.define('Shopware.apps.FirstRunWizard.controller.Localization', {

    extend: 'Ext.app.Controller',

    refs: [
        { ref: 'localizationPanel', selector: 'first-run-wizard-localization' },
        { ref: 'wizardWindow', selector: 'first-run-wizard' }
    ],

    snippets: {
        isConnected: {
            text: '{s name=home/is_connected/text}Connection to Shopware server available{/s}',
            icon: 'tick-circle'
        },
        isNotConnected: {
            text: '{s name=home/is_not_connected/text}Could not connect to Shopware server{/s}',
            icon: 'cross-circle'
        },
        switchLocaleError: {
            errorTitle: '{s name=switch_locale_error/errorTitle}Language switch{/s}',
            errorServerMessage: '{s name=switch_locale_error/errorServerMessage}The following error was detected: [0]{/s}'
        },
        growlMessage: '{s name=growlMessage}First run wizard{/s}'
    },

    /**
     * If any plugin is installed, we set this to true.
     * When switching to next step, if dirty, we prompt language change
     */
    dirty: false,

    init: function () {
        var me = this;

        me.control({
            'first-run-wizard-localization': {
                localizationResetData: me.onLocalizationResetData,
                retryConnectivityTest: me.onRetryConnectivityTest,
                promptInstallLocalization: me.promptInstallLocalization,
                'install-plugin': me.onSwitchLanguage
            },
            'first-run-wizard-localization-switcher': {
                switchLanguage: me.onSwitchLanguage,
                closeWindow: me.onCloseWindow
            },
            'first-run-wizard-localization-installer': {
                installLanguage: me.installLanguage
            },
            'first-run-wizard': {
                'navigate-next-localization': me.promptLanguageChange,
                'navigate-back-localization': me.promptLanguageChange
            }
        });

        me.firstRunWizardIsConnected = me.getController('Main').firstRunWizardIsConnected;

        if (me.firstRunWizardIsConnected === true) {
            me.onSetConnectivityMode(me.firstRunWizardIsConnected);
        } else {
            me.checkConnectivityStatus();
        }

        Shopware.app.Application.on(
            'install-plugin',
            function() { this.dirty = true; },
            me,
            { single: true }
        );

        Shopware.app.Application.on(
            'reinstall-plugin',
            function() { this.dirty = true; },
            me,
            { single: true }
        );

        me.callParent(arguments);
    },

    /**
     * Installs and switches to the language given.
     *
     * @param { string } pluginName
     * @param { string } locale
     */
    installLanguage: function(pluginName, locale) {
        var me = this,
            localizationPanel = me.getLocalizationPanel(),
            communityStore = localizationPanel.communityStore,
            plugin;

        plugin = communityStore.findRecord('technicalName', pluginName);

        if (Ext.isEmpty(plugin)) {
            plugin = Ext.create('Shopware.apps.PluginManager.model.Plugin', {
                technicalName: pluginName
            });
        }

        Shopware.app.Application.fireEvent(
            'update-dummy-plugin',
            plugin,
            function() {
                Shopware.app.Application.fireEvent(
                    'install-plugin',
                    plugin,
                    function() {
                        Shopware.app.Application.fireEvent(
                            'activate-plugin',
                            plugin,
                            function() {
                                me.onSwitchLanguage(locale)
                            }
                        );
                    }
                );
            }
        );
    },

    /**
     * @param { string } localeId
     */
    onSwitchLanguage: function(localeId) {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller="cache" action="clearCache"}',
            method: 'POST',
            params: {
                'cache[template]': 'on',
                'cache[proxy]': 'on'
            },
            success: function(response, opts) {

                Ext.Ajax.request({
                    url: '{url controller="index" action="changeLocale"}',
                    method: 'POST',
                    params: {
                        localeId: localeId
                    },
                    success: function(response, opts) {
                        if (!Ext.isEmpty(me.callback)) {
                            me.callback();
                        }
                        location.reload();
                    },
                    failure: function(response, opts) {
                        var result = Ext.JSON.decode(response.responseText);

                        Shopware.Notification.createGrowlMessage(
                            me.snippets.switchLocaleError.errorTitle,
                            Ext.String.format(me.snippets.switchLocaleError.errorServerMessage, result.message),
                            me.snippets.growlMessage
                        );
                    }
                });

            },
            failure: function(response, opts) {
                var result = Ext.JSON.decode(response.responseText);

                Shopware.Notification.createGrowlMessage(
                    me.snippets.switchLocaleError.errorTitle,
                    Ext.String.format(me.snippets.switchLocaleError.errorServerMessage, result.message),
                    me.snippets.growlMessage
                );
            }
        });
    },

    onCloseWindow: function() {
        var me = this;

        if (!Ext.isEmpty(me.callback)) {
            me.callback();
        }
    },

    onLocalizationResetData: function() {
        var me = this;

        me.dirty = false;
    },

    /**
     * Checks if the plugin with the given name should be installed
     *
     * @param { string } pluginName
     */
    promptInstallLocalization: function(pluginName) {
        var me = this,
            installerLocale = Ext.util.Cookies.get('installed-locale');

        if (installerLocale && installerLocale !== '{s namespace="backend/base/index" name=script/ext/locale}{/s}') {
            try {
                me.localizationInstallerWindow = me.getView('main.LocalizationInstaller').create({
                    installerLocale: installerLocale,
                    pluginName: pluginName,
                    store: me.localeStore
                }).show();
            } catch(e) {
                // Locale not supported
            }
        }
    },

    promptLanguageChange: function(context, callback) {
        var me = this;

        if (me.dirty) {
            me.dirty = false;
            me.callback = callback;
            me.localeStore = Ext.create('Shopware.apps.FirstRunWizard.store.Locale').load(
                function(records, operation, success) {
                    me.localizationSwitcherWindow = me.getView('main.LocalizationSwitcher').create({
                        store: me.localeStore
                    }).show();
                }
            );
        } else {
            callback();
        }
    },

    onRetryConnectivityTest: function() {
        var me = this,
            localizationPanel = me.getLocalizationPanel();

        localizationPanel.connectionResult = false;
        localizationPanel.firstRunWizardIsConnected = null;
        me.getController('Main').validateButtons();

        localizationPanel.loadingResultContainer.hide();
        localizationPanel.loadingIndicator.show();
        me.checkConnectivityStatus();
    },

    onSetConnectivityMode: function(isConnected) {
        var me = this,
            localizationPanel = me.getLocalizationPanel(),
            wizardWindow = me.getWizardWindow();

        Ext.util.Cookies.set('firstRunWizardIsConnected', isConnected);

        localizationPanel.connectionResult = true;
        localizationPanel.firstRunWizardIsConnected = isConnected;

        localizationPanel.loadingIndicator.hide();
        localizationPanel.refreshLoadingResultContainer(isConnected);

        localizationPanel.communityStore.load();

        wizardWindow.isConnected = isConnected;
        wizardWindow.updateNavigation();
        wizardWindow.navigation.refresh();
        me.getController('Main').validateButtons();
    },

    checkConnectivityStatus: function() {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller="firstRunWizard" action="pingServer"}',
            method: 'GET',
            timeout: 10000,
            success: function(response) {
                var result = Ext.JSON.decode(response.responseText);

                me.onSetConnectivityMode((result && result.success === true && result.message === true));
            },
            failure: function (response, request) {
                me.onSetConnectivityMode(false);
            }
        });
    }
});

// {/block}
