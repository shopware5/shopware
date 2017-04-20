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
 *
 * @category   Shopware
 * @package    Payment
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/payment/payment}

/**
 * Shopware UI - Main payment window
 *
 * todo@all: Documentation
 */
//{block name="backend/payment/view/main/window"}
Ext.define('Shopware.apps.Payment.view.main.Window', {
    extend: 'Enlight.app.Window',
    title: '{s name=payment_title}Payments{/s}',
    cls: Ext.baseCSSPrefix + 'payment-window',
    alias: 'widget.payment-main-window',
    border: false,
    autoShow: true,
    layout: 'border',
    height: '90%',
    width: 925,

    stateful:true,
    stateId:'shopware-payment-window',

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.registerEvents();
        me.tabPanel = me.createTabPanel();
        me.tree = Ext.create('Shopware.apps.Payment.view.payment.Tree');
        me.items = [{
            xtype: 'container',
            region: 'center',
            layout: 'border',
            items: [ me.createToolbar(), me.tabPanel ]
        }, me.tree ];

        me.callParent(arguments);
    },

    /**
     * This function registers the special events
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * This event is fired, when the user presses the "save"-button
             * @param generalForm Contains the general form-panel
             * @param countrySelection Contains the countries-grid with its selections
             * @param subShopSelection Contains the subShop-grid with its selections
             * @param subShopSelection Contains the surcharge-grid
             * @event savePayment
             */
            'savePayment',

            /**
             * This event is fired, when the user changes the active tab
             * @param tabPanel Contains the tabPanel
             * @param newTab Contains the new active tab
             * @param oldTab Contains the old tab, which was active before
             * @param generalForm Contains the general form-panel
             */
            'changeTab'
        );
    },

    /**
     * This function creates the toolbar
     * @return [Ext.toolbar.Toolbar]
     */
    createToolbar: function() {
        var me = this;

        var buttons = ['->'];
        /*{if {acl_is_allowed privilege=update}}*/
        buttons.push(Ext.create('Ext.button.Button',{
            text: '{s name=button_save}Save{/s}',
            cls: 'primary',
            action: 'save',
            name: 'save',
            disabled: true,
            handler: function() {
                me.fireEvent('savePayment', me.generalForm, me.countrySelection, me.subshopSelection, me.surcharge)
            }
        }));
        /*{/if}*/
        return Ext.create('Ext.toolbar.Toolbar',{
            name: 'gridToolBar',
            region: 'south',
            ui: 'shopware-ui',
            cls: 'shopware-toolbar',
            items: buttons
        });
    },

    /**
     * This function creates the tabPanel with its items
     * @return [Ext.tab.Panel]
     */
    createTabPanel: function() {
        var me = this;

        me.generalForm = Ext.create('Shopware.apps.Payment.view.payment.FormPanel');

        me.countrySelection = Ext.create('Shopware.apps.Payment.view.payment.CountryList', {
            record: me.record,
            paymentStore: me.paymentStore
        });

        me.subshopSelection = Ext.create('Shopware.apps.Payment.view.payment.SubshopList',{
            record: me.record,
            paymentStore: me.paymentStore
        });

        me.surcharge = Ext.create('Shopware.apps.Payment.view.payment.Surcharge',{
            record: me.record,
            paymentStore: me.paymentStore
        });

        me.attributeForm = Ext.create('Shopware.attribute.Form', {
            table: 's_core_paymentmeans_attributes',
            title: '{s namespace="backend/attributes/main" name="attribute_form_title"}{/s}',
            bodyPadding: 10,
            autoScroll: true
        });

        return Ext.create('Ext.tab.Panel', {
            autoShow: false,
            //disabled to enable it, when the tree is clicked at least once
            disabled: true,
            layout: 'fit',
            region: 'center',
            autoScroll: true,
            border: 0,
            bodyBorder: false,
            defaults: {
                layout: 'fit'
            },
            items: [{
                xtype: 'container',
                title: '{s name=title_generalForm}General{/s}',
                items: [ me.generalForm ]
            }, {
                xtype: 'container',
                autoRender: true,
                title: '{s name=title_countrySelection}Country selection{/s}',
                items: [ me.countrySelection ]
            }, {
                xtype: 'container',
                title: '{s name=title_countrySurcharge}Country surcharge{/s}',
                autoRender: true,
                items: [ me.surcharge ]
            }, {
                xtype: 'container',
                autoRender: true,
                title: '{s name=title_subshopSelection}Subshop selection{/s}',
                items: [ me.subshopSelection ]
            }, me.attributeForm],

            listeners: {
                tabchange: function(tabPanel, newTab, oldTab) {
                    me.fireEvent('changeTab', tabPanel, newTab, oldTab, me.generalForm);
                }
            }
        });

    }
});
//{/block}
