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
            message: '{s name=home/content/message}Welcome to Shopware shop. The First Run Wizard will accompany you in your first steps with Shopware and give you valuable tips about the configuration options.{/s}'
        },
        buttons: {
            next: '{s name=home/buttons/next}Next{/s}',
            skip: '{s name=home/buttons/skip}Skip{/s}',
            retry: '{s name=home/buttons/retry}Retry{/s}'
        },
        isConnected: {
            text: '{s name=home/is_connected/text}Connection to Shopware server available{/s}',
            icon: 'tick-circle'
        },
        isNotConnected: {
            text: '{s name=home/is_not_connected/text}Could not connect to Shopware server{/s}',
            icon: 'cross-circle'
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
                style: 'margin-bottom: 40px;',
                html: '<p>' + me.snippets.content.message + '</p>',
                width: '100%'
            },
            me.createLoadingIndicator(),
            me.refreshLoadingResultContainer()
        ];

        me.callParent(arguments);
    },

    getButtons: function() {
        var me = this,
            buttons = {
                previous: {
                    visible: false
                },
                next: {
                }
            };

        if (me.firstRunWizardIsConnected === null && me.connectionResult !== true) {
            buttons.next.text = me.snippets.buttons.skip;
        }

        if (me.firstRunWizardIsConnected === false || me.firstRunWizardIsConnected === 'false') {
            buttons.extraButtonSettings = {
                text: me.snippets.buttons.retry,
                    cls: 'primary',
                    name: 'retry-button',
                    width: 180,
                    handler: function() {
                        me.fireEvent('retryConnectivityTest');
                }
            }
        }

        return buttons;
    },

    createLoadingIndicator: function() {
        var me = this;

        me.loadingIndicator = Ext.create('Ext.ProgressBar', {
            animate: true,
            hidden: me.firstRunWizardIsConnected !== null,
            style: {
                marginLeft: '135px',
                width: '365px'
            }
        });

        me.loadingIndicator.wait({
            text: '{s name=home/content/checking_connection}Checking Shopware server connection{/s}',
            scope: this
        });

        return me.loadingIndicator;
    },

    refreshLoadingResultContainer: function() {
        var me = this;

        if (typeof me.loadingResultContainer == 'undefined') {
            me.loadingResultContainer = Ext.create('Ext.container.Container', {
                html: '',
                style: {
                    'text-align': 'center'
                }
            });
        }

        if (me.firstRunWizardIsConnected == true || me.firstRunWizardIsConnected == 'true') {
            me.loadingResultContainer.update(
                Ext.String.format(
                    '<div style="width: 16px; height: 16px; float: none; display: inline-block;" class="sprite-[0]"></div><div style="display: inline-block;">[1]</div>',
                    me.snippets.isConnected.icon, me.snippets.isConnected.text
                )
            );
            me.loadingResultContainer.show();
        } else if (me.firstRunWizardIsConnected == false || me.firstRunWizardIsConnected == 'false') {
            me.loadingResultContainer.update(
                Ext.String.format(
                    '<div style="width: 16px; height: 16px; float: none; display: inline-block;" class="sprite-[0]"></div><div style="display: inline-block;">[1]</div>',
                    me.snippets.isNotConnected.icon, me.snippets.isNotConnected.text
                )
            );
            me.loadingResultContainer.show();
        } else {
            me.loadingResultContainer.hide();
        }

        return me.loadingResultContainer;
    }
});

//{/block}
