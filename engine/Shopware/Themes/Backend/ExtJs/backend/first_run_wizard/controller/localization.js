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

    init: function () {
        var me = this;

        me.control({
            'first-run-wizard-location': {
                switchLanguage: me.switchLanguage
            }
        });

        me.callParent(arguments);
    },

    switchLanguage: function(locale) {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller="index" action="changeLocale"}',
            method: 'POST',
            params: {
                locale: locale
            },
            success: function(response, opts) {
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
    }
});

//{/block}
