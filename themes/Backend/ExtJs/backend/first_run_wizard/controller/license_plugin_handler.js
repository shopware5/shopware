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
 * Shopware First Run Wizard - License Plugin Handler controller
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

//{namespace name=backend/first_run_wizard/main}
//{block name="backend/first_run_wizard/controller/license_plugin_handler"}

Ext.define('Shopware.apps.FirstRunWizard.controller.LicensePluginHandler', {

    extend:'Shopware.apps.PluginManager.controller.Plugin',

    snippets: {
        'licencePluginDownloadInstall':  '{s name="licence_plugin_download_and_install"}{/s}',
        'licencePluginDownloadActivate': '{s name="licence_plugin_install_and_activate"}{/s}',
        'licencePluginActivate':         '{s name="licence_plugin_activate"}{/s}'
    },

    init: function () {
        var me = this;

        me.callParent(arguments);

        me.checkLicences();
    },

    getEventListeners: function() {
        return {};
    },

    checkLicences: function(plugin, callback) {
        var me = this;

        if (plugin && !plugin.get('licenceCheck')) {
            callback();
        }

        Ext.Ajax.request({
            url: '{url controller=FirstRunWizardPluginManager action=checkShopLicence}',
            method: 'GET',
            success: function(operation, opts) {
                var response = Ext.decode(operation.responseText);

                if (response.success === true) {
                    if (Ext.isFunction(callback)) {
                        callback(response);
                    }
                    return;
                }

                if (response.data) {
                    me.checkLicencePlugin(plugin, callback);
                }
            }
        });
    }
});

//{/block}
