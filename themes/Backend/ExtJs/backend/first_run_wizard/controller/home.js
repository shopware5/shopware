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
//{block name="backend/first_run_wizard/controller/home"}

Ext.define('Shopware.apps.FirstRunWizard.controller.Home', {

    extend:'Ext.app.Controller',

    refs: [
        { ref: 'homePanel', selector: 'first-run-wizard-home' },
        { ref: 'wizardWindow', selector: 'first-run-wizard' },
    ],

    init: function () {
        var me = this;

        me.control({
            'first-run-wizard-home': {
                retryConnectivityTest: me.onRetryConnectivityTest
            }
        });

        me.on('setConnectivityMode', me.onSetConnectivityMode);

        me.firstRunWizardIsConnected = Ext.util.Cookies.get('firstRunWizardIsConnected');

        if (me.firstRunWizardIsConnected === null) {
            me.checkConnectivityStatus();
        }

        me.callParent(arguments);
    },

    onRetryConnectivityTest: function() {
        var me = this,
            homePanel = me.getHomePanel();

        homePanel.connectionResult = false;
        homePanel.firstRunWizardIsConnected = null;
        me.getController('Main').validateButtons();

        homePanel.loadingResultContainer.hide();
        homePanel.loadingIndicator.show();
        me.checkConnectivityStatus();
    },

    onSetConnectivityMode: function(isConnected) {
        var me = this,
            homePanel = me.getHomePanel(),
            wizardWindow = me.getWizardWindow();

        Ext.util.Cookies.set('firstRunWizardIsConnected', isConnected);

        if (isConnected) {
            wizardWindow.navigation.store.each(
                function(elem) {
                    elem.set('disabled', false);
                    return true;
                }
            );
        }

        homePanel.connectionResult = true;
        homePanel.firstRunWizardIsConnected = isConnected;

        homePanel.loadingIndicator.hide();
        homePanel.refreshLoadingResultContainer(isConnected);

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

                if (!result || result.success == false || result.message == false) {
                    me.fireEvent('setConnectivityMode', false);
                } else {
                    me.fireEvent('setConnectivityMode', true);
                }
            },
            failure: function (response, request) {
                me.fireEvent('setConnectivityMode', false);
            }
        });
    }
});

//{/block}
