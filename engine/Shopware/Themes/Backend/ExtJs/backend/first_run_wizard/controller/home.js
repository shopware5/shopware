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

    snippets: {
        isConnected: {
            text: '{s name=home/is_connected/text}Connection to Shopware server available{/s}',
            icon: 'tick-circle'
        },
        isNotConnected: {
            text: '{s name=home/is_not_connected/text}Could not connect to Shopware server{/s}',
            icon: 'cross-circle'
        }
    },

    init: function () {
        var me = this;

        me.control({
            'first-run-wizard-home': {
                setConnectivityMode: me.onSetConnectivityMode
            }
        });

        me.callParent(arguments);
    },

    onSetConnectivityMode: function(isConnected) {
        var me = this, snippetNamespace,
            homePanel, wizardWindow;

        Ext.util.Cookies.set('firstRunWizardIsConnected', isConnected);

        homePanel = me.getHomePanel();
        wizardWindow = me.getWizardWindow();

        if (isConnected) {
            snippetNamespace = me.snippets.isConnected;
            wizardWindow.navigation.store.each(
                function(elem) {
                    elem.set('disabled', false);
                    return true;
                }
            );
        } else {
            snippetNamespace = me.snippets.isNotConnected;
        }

        homePanel.connectionResult = true;

        homePanel.loadingIndicator.update(
            Ext.String.format(
                '<div style="width: 16px; height: 16px; float: left;" class="sprite-[0]"></div><div>[1]</div>',
                snippetNamespace.icon, snippetNamespace.text
            )
        );

        wizardWindow.navigation.refresh();
        me.getController('Main').validateButtons();
    }
});

//{/block}
