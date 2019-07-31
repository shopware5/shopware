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
 * Shopware First Run Wizard - PayPal tab
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

//{namespace name=backend/first_run_wizard/main}
//{block name="backend/first_run_wizard/view/main/pay_pal"}

Ext.define('Shopware.apps.FirstRunWizard.view.main.PayPal', {
    extend: 'Ext.container.Container',

    alias: 'widget.first-run-wizard-pay-pal',

    name: 'pay-pal',

    overflowY: 'auto',

    initComponent: function() {
        this.cardLayoutContainer = Ext.create('Ext.container.Container', {
            layout: 'card',
            region: 'center',
            autoScroll: true,
            name: 'card-container',
            cls: 'card-container',
            items: this.createPayPalCards()
        });

        this.items = [
            this.cardLayoutContainer
        ];

        this.callParent(arguments);
    },

    /**
     * @returns { Array }
     */
    createPayPalCards: function () {
        var items = [];

        items.push(
            this.createEmptyCard(),
            this.createStartCard(),
            this.createInstallCard(),
            this.createConfigurationCard(),
            this.createDoneCard(),
            this.createErrorCard()
        );

        return items;
    },

    /**
     * @param { string } cardId
     */
    navigateToCard: function (cardId) {
        this.cardLayoutContainer.getLayout().setActiveItem(cardId);
    },

    /**
     * @returns { Ext.container.Container }
     */
    createEmptyCard: function() {
        return Ext.create('Ext.container.Container', {
            html: '',
            itemId: 'empty'
        });
    },

    /**
     * @returns { Ext.container.Container }
     */
    createStartCard: function() {
        var me = this;
        return Ext.create('Ext.container.Container', {
            itemId: 'start',
            items: [
                {
                    xtype: 'container',
                    html: me.renderTemplate('{url controller=FirstRunWizard action=payPalStartView}'),
                    height: '325px',
                    style: {
                        margin: 0,
                    },
                },
                {
                    xtype: 'button',
                    cls: 'primary',
                    text: '{s name=pay_pal/start/button}{/s}',
                    style: {
                        marginTop: '10px',
                        marginLeft: 0,
                    },
                    handler: function() {
                        me.fireEvent('start');
                    },
                }
            ]
        });
    },

    /**
     * @returns { Ext.container.Container }
     */
    createInstallCard: function () {
        return Ext.create('Ext.container.Container', {
            itemId: 'install',
        });
    },

    /**
     * @returns { Ext.container.Container }
     */
    createConfigurationCard: function () {
        var me = this;

        me.form = {
            xtype: 'form',
            name: 'pay-pal-configuration-form',
            layout: 'anchor',
            border: false,
            bodyStyle: 'background-color: transparent !important',
            defaults: {
                anchor: '100%'
            },
            items: [
                {
                    xtype: 'container',
                    html: '<h1>{s name="pay_pal/configuration/headline"}{/s}</h1><p>{s name="pay_pal/configuration/instructions"}{/s}</p>',
                    style: {
                        marginBottom: '10px'
                    }
                },
                {
                    xtype: 'textfield',
                    name: 'clientId',
                    fieldLabel: '{s name=pay_pal/configuration/client_id/label}{/s}',
                    allowBlank: false,
                }, {
                    xtype: 'textfield',
                    name: 'clientSecret',
                    fieldLabel: '{s name=pay_pal/configuration/client_secret/label}{/s}',
                    allowBlank: false,
                }, {
                    xtype: 'checkbox',
                    name: 'sandbox',
                    fieldLabel: '{s name=pay_pal/configuration/sand_box/label}{/s}',
                    inputValue: true,
                    uncheckedValue: false
                }, {
                    xtype: 'checkbox',
                    name: 'payPalPlus',
                    fieldLabel: '{s name=pay_pal/configuration/plus/label}{/s}',
                    inputValue: true,
                    uncheckedValue: false,
                    helpTitle: '{s name=pay_pal/configuration/plus/label}{/s}',
                    helpText: '{s name=pay_pal/configuration/plus/help_text}{/s}',
                }, {
                    xtype: 'button',
                    text: '{s name=pay_pal/configuration/save/text}{/s}',
                    cls: 'primary',
                    style: {
                        marginTop: '10px',
                    },
                    handler: function () {
                        me.fireEvent('save-configuration');
                    }
                }
            ]
        };

        return Ext.create('Ext.container.Container', {
            itemId: 'configuration',
            items: [
                me.form,
            ]
        });
    },

    /**
     * @returns { Ext.container.Container }
     */
    createDoneCard: function () {
        return Ext.create('Ext.container.Container', {
            html: '<h1>{s name="pay_pal/done/headline"}{/s}</h1>',
            itemId: 'done'
        });
    },

    /**
     * @returns { Ext.container.Container }
     */
    createErrorCard: function () {
        return Ext.create('Ext.container.Container', {
            html: '',
            itemId: 'error'
        });
    },

    /**
     * @param { string } message
     */
    displayErrorCard: function(message) {
        var template = '<p>' + message + '</p>';
        this.cardLayoutContainer.getLayout().setActiveItem('error');
        this.cardLayoutContainer.getLayout().getActiveItem().el.setHTML(template);
        this.setLoading(false);
    },

    renderTemplate: function (url) {
        // {literal}
        return Ext.String.format('<iframe sandbox="" src="{0}"></iframe>', url);
        // {/literal}
    }
});
//{/block}
