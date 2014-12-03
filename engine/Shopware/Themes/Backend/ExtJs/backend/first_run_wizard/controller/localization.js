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

//{namespace name=backend/first_run_wizard/main}
//{block name="backend/first_run_wizard/controller/localization"}

Ext.define('Shopware.apps.FirstRunWizard.controller.Localization', {

    extend:'Ext.app.Controller',

    refs: [
        { ref: 'localizationPanel', selector: 'first-run-wizard-localization' }
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
        growlMessage:'{s name=growlMessage}First run wizard{/s}'
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
                changeLanguageFilter: me.onChangeLanguageFilter,
                localizationResetData: me.onLocalizationResetData
            },
            'first-run-wizard-localization-switcher': {
                switchLanguage: me.onSwitchLanguage,
                closeWindow: me.onCloseWindow
            },
            'first-run-wizard': {
                'navigate-next-localization': me.promptLanguageChange,
                'navigate-back-localization': me.promptLanguageChange
            }
        });

        me.callParent(arguments);
    },

    onSwitchLanguage: function(localeId) {
        var me = this;

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

    onChangeLanguageFilter: function(value) {
        var me = this;

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

        me.getLocalizationPanel().storeListing.resetListing();
        me.getLocalizationPanel().communityStore.getProxy().extraParams.localeId = value;
        me.getLocalizationPanel().communityStore.load();
        me.getLocalizationPanel().storeListing.setLoading(true);
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
    }
});

//{/block}
