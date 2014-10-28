/**
 * Shopware 4
 * Copyright © shopware AG
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
 * Shopware First Run Wizard - Home tab
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

//{namespace name=backend/first_run_wizard/main}
//{block name="backend/first_run_wizard/view/main/home"}

Ext.define('Shopware.apps.FirstRunWizard.view.main.Home', {
    extend: 'Ext.container.Container',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.first-run-wizard-home',

    /**
     * Name attribute used to generate event names
     */
    name:'home',

    snippets: {
        content: {
            title: '{s name=home/content/title}Welcome to Shopware{/s}',
            message: '{s name=home/content/message}Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.{/s}'
        },
        buttons: {
            next: '{s name=home/buttons/next}Next{/s}',
            skip: '{s name=home/buttons/skip}Skip{/s}'
        }
    },

    initComponent: function() {
        var me = this;

        me.firstRunWizardIsConnected = Ext.util.Cookies.get('firstRunWizardIsConnected');

        if (Ext.isEmpty(me.firstRunWizardIsConnected)) {
            me.firstRunWizardIsConnected = null;
        }

        me.items = [
            {
                xtype: 'container',
                border: false,
                bodyPadding: 20,
                style: 'font-weight: 700; line-height: 20px;',
                html: '<h1>' + me.snippets.content.title + '</h1>'
            },
            {
                xtype: 'container',
                border: false,
                bodyPadding: 20,
                style: 'margin-bottom: 10px;',
                html: '<p>' + me.snippets.content.message + '</p>'
            },
            me.createLoadingIndicator()
        ];

        me.callParent(arguments);
    },

    getButtons: function() {
        var me = this,
            buttons = {
                previous: {
                    enabled: false,
                    text: ''
                },
                next: {
                }
            };

        if (me.firstRunWizardIsConnected === null && me.connectionResult !== true) {
            buttons.next.text = me.snippets.buttons.skip;
        }

        return buttons;
    },

    createLoadingIndicator: function() {
        var me = this;

        if (me.firstRunWizardIsConnected === null) {
            me.loadingIndicator = Ext.create('Ext.ProgressBar', {
                animate: true,
                width: 300
            });

            me.loadingIndicator.wait({
                text: 'Checking Shopware server connection...',
                scope: this
            });

            Ext.Ajax.request({
                url: '{url controller="firstRunWizard" action="pingServer"}',
                method: 'GET',
                timeout: 4000000,
                success: function(response) {
                    var result = Ext.JSON.decode(response.responseText);

                    if(!result || result.success == false || result.message == false) {
                        me.fireEvent('setConnectivityMode', false);
                    } else {
                        me.fireEvent('setConnectivityMode', true);
                    }
                },
                failure: function (response, request) {
                    me.fireEvent('setConnectivityMode', false);
                }
            });
        } else {
            me.loadingIndicator = null;
        }

        return me.loadingIndicator;
    }
});

//{/block}