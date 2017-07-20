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
 * @package    Customer
 * @subpackage Detail
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name=backend/customer/view/detail}

/**
 * Shopware UI - Customer detail page additional panel
 *
 * Displayed on the right side of the detail page when a customer is edit.
 *
 */
// {block name="backend/customer/view/detail/additional"}
Ext.define('Shopware.apps.Customer.view.detail.Additional', {

    /**
     * Define that the additional information is an Ext.panel.Panel extension
     * @string
     */
    extend: 'Ext.panel.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.customer-additional-panel',

    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'more-info',

    /**
     * Allow to scroll within the panel
     * @boolean
     */
    autoScroll: true,

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets: {
        registeredSince: '{s name=additional/registered_since}Registered since:{/s}',
        lastLogin: '{s name=additional/last_login}Last login:{/s}',
        language: '{s name=additional/language}Language:{/s}',
        shop: '{s name=additional/shop}Shop:{/s}',
        orders: '{s name=additional/orders_since_registration}Orders since registration:{/s}',
        sales: '{s name=additional/sales}Turnover:{/s}',
        paymentDefaults: '{s name=additional/payment_defaults}Payment defaults:{/s}',
        emptyText: '{s name=additional/empty}No additional information found{/s}',
        performOrderBtn: '{s name=additional/do_order}Perform order{/s}',
        quickOrder: '{s name=additional/quick_order}For this customer, no account has been created (quick order){/s}',
        title: '{s name=additional/title}Further information{/s}',
        createAccountBtn: '{s name=additional/create_account}Create account{/s}'
    },

    /**
     * Component event which is fired when the component is initials.
     * @return void
     */
    initComponent: function () {
        var me = this;
        me.title = me.snippets.title;
        me.registerEvents();
        me.items = [ me.createButtonsContainer(), me.createInfoView() ];
        me.callParent(arguments);
    },

    /**
     * Registers the "performOrder" event which is handled in the detail controller
     * and will be fired when the user clicks on the associated "perform order" button
     * which is displayed on bottom of the additional panel.
     * @return void
     */
    registerEvents: function () {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the "Perform order" button
             * which is placed in the additional panel at the bottom.
             *
             * @event
             * @param [Ext.data.Model] record - The current form record
             */
            'performOrder',

            /**
             * Event will be fired when the user clicks the "Create customer account" button
             * which is placed in the additional panel at the bottom.
             *
             * @event
             * @param [Ext.data.Model]          customer - The current record of the detail window.
             * @param [Ext.container.Container] infoView - The info view container
             * @param [Ext.XTemplate]           tpl - The view template
             * @param [Ext.button.Button]       btn - The "create account" button which has to be hide when the operation was successfully
             */
            'createAccount'
        );
    },

    /**
     * Creates the container for the "Perform order" button which
     * is displayed on bottom of the panel.
     * @return [Ext.container.Container] - Contains the perform order button and the create account button when the accountMode of the customer is set to 1
     */
    createButtonsContainer: function () {
        var me = this,
            buttons = [];

        /* {if {acl_is_allowed privilege=perform_order}} */
        me.performOrderBtn = Ext.create('Ext.button.Button', {
            text: me.snippets.performOrderBtn,
            handler: function () {
                me.fireEvent('performOrder', me.record);
            }
        });
        buttons.push(me.performOrderBtn);
        /* {/if} */

        /* {if {acl_is_allowed privilege=update}} */
        if (me.record.get('accountMode') == 1) {
            me.createAccountButton = Ext.create('Ext.button.Button', {
                text: me.snippets.createAccountBtn,
                handler: function () {
                    var tpl = me.createInfoPanelTemplate();
                    me.fireEvent('createAccount', me.record, me.infoView, tpl, me.createAccountButton);
                }
            });
            buttons.push(me.createAccountButton);
        }
        /* {/if} */

        return Ext.create('Ext.container.Container', {
            height: 40,
            cls: Ext.baseCSSPrefix + 'button-container',
            items: buttons
        });
    },

    /**
     * Creates the XTemplate for the information panel
     *
     * Note that the template has different member methods
     * which are only callable in the actual template.
     *
     * @return [Ext.XTemplate] generated Ext.XTemplate
     */
    createInfoPanelTemplate: function () {
        var me = this;

        return new Ext.XTemplate(
            '{literal}<tpl for=".">',
                '<div class="media-info-pnl">',
                    '<div class="base-info">',
                        // If the type is image, then show the image
                        '<tpl if="accountMode == 1">',
                            '<p>',
                                '<strong>' + me.snippets.quickOrder + '</strong>',
                            '</p>',
                        '</tpl>',
                        '<p>',
                            '<strong>' + me.snippets.registeredSince + '</strong>',
                            '<span>{[this.formatDate(values.firstLogin)]}</span>',
                        '</p>',
                        '<p>',
                            '<strong>' + me.snippets.lastLogin + '</strong>',
                            '<span>{[this.formatDate(values.lastLogin)]}</span>',
                        '</p>',
                        '<p>',
                            '<strong>' + me.snippets.language + '</strong>',
                            '<span>{language}</span>',
                        '</p>',
                        '<p>',
                            '<strong>' + me.snippets.shop + '</strong>',
                            '<span>{shopName}</span>',
                        '</p>',
                        '<p>',
                            '<strong>' + me.snippets.orders + '</strong>',
                            '<span>{orderCount}</span>',
                        '</p>',
                        '<p>',
                            '<strong>' + me.snippets.sales + '</strong>',
                            '<span>{[this.formatCurrency(values.amount)]}</span>',
                        '</p>',
                        '<p>',
                            '<strong>' + me.snippets.paymentDefaults + '</strong>',
                            '<span>{[this.formatCurrency(values.canceledOrderAmount)]}</span>',
                        '</p>',
                    '</div>',
                '</div>',
                '</tpl>{/literal}',
            {
                    /**
                     * Member function of the template which formats a date string
                     *
                     * @param value [string] value - Date string in the following format: Y-m-d H:i:s
                     * @return [string] - The passed value, formatted with Ext.util.Format.date
                     */
                formatDate: function (value) {
                    if (value === Ext.undefined) {
                        return value;
                    }
                    return Ext.util.Format.date(value);
                }
            },
            {
                /**
                 * Member function of the template which format a currency string
                 * @param value [string] - The currency value to be format
                 * @return [string] - The passed value, formatted with Ext.util.Format.currency
                 */
                formatCurrency: function (value) {
                    if (value === Ext.undefined) {
                        return value;
                    }

                    return Ext.util.Format.currency(value);
                }
            }
        );
    },

    /**
     * Creates a new panel which displays additional information
     * about the selected media.
     *
     * @return [object] this.infoPanel - generated Ext.panel.Panel
     */
    createInfoView: function () {
        var me = this;

        me.infoView = Ext.create('Ext.container.Container', {
            cls: Ext.baseCSSPrefix + 'outer-customer-info-pnl',
            emptyText: me.snippets.emptyText,
            autoScroll: true,
            renderTpl: me.createInfoPanelTemplate(),
            renderData: me.record.data
        });

        return me.infoView;
    }

});
// {/block}
