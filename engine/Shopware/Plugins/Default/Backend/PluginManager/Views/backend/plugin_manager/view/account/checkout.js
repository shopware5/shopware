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
 * @package    PluginManager
 * @subpackage Account
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/plugin_manager/translation}

//{block name="backend/plugin_manager/view/account/checkout"}
Ext.define('Shopware.apps.PluginManager.view.account.Checkout', {
    extend: 'Ext.window.Window',

    modal: true,

    cls: 'plugin-manager-checkout-window',

    header: false,

    layout: {
        type: 'vbox',
        align: 'stretch'
    },
    width: 900,

    bodyPadding: 30,

    mixins: {
        events: 'Shopware.apps.PluginManager.view.PluginHelper'
    },

    initComponent: function() {
        var me = this;

        me.items = [
            me.createHeadline(),
            me.createBookingInformation(),
            me.createPositions(),
            me.createTotal(),
            me.createGtcBox()
        ];

        me.dockedItems = [ me.createButtons() ];

        me.callParent(arguments);
    },

    createGtcBox: function() {
        var me = this;

        me.gtcBox = Ext.create('Ext.form.field.Checkbox', {
            name: 'gtc',
            inputValue: true,
            cls: 'gtc',
            uncheckedValue: false,
            boxLabel: '{s name="gtc_accept"}I have read and agree with the <a href="https://en.shopware.com/gtc" target="_blank">GTC</a> of your shop.{/s}',
            value: false,
            listeners: {
                change: function() {
                    if (me.gtcBox.getValue()) {
                        me.applyButton.enable();
                    } else {
                        me.applyButton.disable();
                    }
                }
            }
        });

        return Ext.create('Ext.container.Container', {
            layout: 'hbox',
            items: [ me.gtcBox ]
        });
    },

    createHeadline: function() {
        return Ext.create('Ext.Component', {
            cls: 'headline',
            html: '{s name="finish_order"}Complete order{/s}',
            height: 65,
            margin: '0 0 5'
        });
    },

    createBookingInformation: function() {
        var me = this;

        var address = me.basket['getAddressStore'].first();

        me.billingAddress = Ext.create('Ext.container.Container', {
            cls: 'booking-container billing-address',
            flex: 1,
            html: '<div class="headline">{s name="billing_address"}Billing address{/s}</div>' +
                  '<div>' + address.get('firstName') + ' ' + address.get('lastName') + '</div>' +
                  '<div>' + address.get('street')  + '</div>' +
                  '<div>' + address.get('zipCode') + ' ' + address.get('city') + '</div>' +
                  '<div>' + address.get('countryName') +'</div>'
        });

        me.licenceDomain = Ext.create('Ext.container.Container', {
            cls: 'booking-container licence-domain',
            flex: 1,
            margin: '0 20',
            html: '<div class="headline">{s name="licence_domain"}License domain{/s}</div>' +
                  '<div class="content">{s name="licence_domain_notice"}Your purchase will be registered for the following domain{/s}</div>' +
                  '<div class="domain">' + me.basket.get('licenceDomain') + '</div>'
        });

        var bookingDomainHeadline = Ext.create('Ext.Component', {
            html: '<div class="headline">{s name="booking_domain"}Domain booking{/s}</div>'
        });

        me.bookingDomainAmount = Ext.create('Ext.Component', {
            html: '',
            margin: '10 0 0',
            cls: 'booking-domain-amount'
        });

        var store = me.basket['getDomainsStore'];

        me.bookingDomainSelection = Ext.create('Ext.form.field.ComboBox', {
            displayField: 'domain',
            valueField: 'domain',
            store: store,
            queryMode: 'local',
            margin: '8 0 0',
            anchor: '100%',
            forceSelection: true,
            allowBlank: false,
            listeners: {
                change: function(combo, records) {
                    var record = combo.lastSelection[0];

                    me.basket.set('bookingDomain', record.get('domain'));

                    me.bookingDomainAmount.update(
                        me.formatPrice(record.get('balance'))
                    );

                    if (record.get('balance') >= 0) {
                        me.bookingDomainAmount.addCls('positive');
                        me.bookingDomainAmount.removeCls('negative');
                    } else {
                        me.bookingDomainAmount.addCls('negative');
                        me.bookingDomainAmount.removeCls('positive');
                    }
                }
            }
        });

        me.chargeDomainButton = Ext.create('PluginManager.container.Container', {
            cls: 'plugin-manager-action-button primary charge-amount',
            html: '<a href="https://account.shopware.com/shops/shops/' +  me.basket.get('licenceShopId') + '/account" target="_blank">{s name="charge_amount"}Charge amount{/s}</a>'
        });

        me.bookingDomain = Ext.create('Ext.container.Container', {
            cls: 'booking-container booking-domain',
            flex: 1,
            layout: 'anchor',
            items: [
                bookingDomainHeadline,
                me.bookingDomainAmount,
                me.bookingDomainSelection,
                me.chargeDomainButton
            ]
        });

        me.basket.set('bookingDomain', me.basket.get('licenceDomain'));
        me.bookingDomainSelection.select(me.basket.get('bookingDomain'));

        return Ext.create('Ext.container.Container', {
            cls: 'booking-information',
            margin: '25 0',
            padding: '10 0',
            height: 230,
            layout: {
                type: 'hbox',
                align: 'stretch'
            },
            items: [ me.bookingDomain, me.licenceDomain, me.billingAddress ]
        });
    },

    createPositions: function() {
        var me = this;

        var positions = [];

        var header = Ext.create('Ext.Component', {
            cls: 'position-header',
            height: 30,
            html: '<div class="name-header">{s name="product"}Article{/s}</div>' +
            '<div class="price-header">{s name="price"}Price{/s}</div>'
        });

        positions.push(header);

        me.basket['getPositionsStore'].each(function(position) {
            var plugin = position['getPluginStore'].first();

            var icon = Ext.create('Ext.Component', {
                cls: 'image',
                width: 58,
                height: 58,
                html: '<img src="' + plugin.get('iconPath') + '" />'
            });

            var type = me.getTextForPriceType(position.get('priceType'));

            var name = Ext.create('Ext.Component', {
                cls: 'plugin-data',
                width: 500,
                html: '<div class="name">' + plugin.get('label') + '</div>' +
                '<div class="number">{s name="product_number"}Article nr.:{/s}'+ plugin.get('code') + '</div>' +
                '<div class="type">'+ type +'</div>'
            });

            var price = Ext.create('Ext.Component', {
                cls: 'plugin-price',
                width: 90,
                html: me.formatPrice(position.get('price')) + ' *'
            });

            var row = Ext.create('Ext.container.Container', {
                cls: 'row',
                minHeight: 120,
                layout: {
                    type: 'hbox',
                    align: 'stretch'
                },
                items: [ icon, name, price ]
            });

            positions.push(row);
        });

        return Ext.create('Ext.container.Container', {
            items: positions,
            minHeight: 165,
            cls: 'position-wrapper'
        })
    },

    createTotal: function() {
        var me = this, items = [];

        var total = Ext.create('Ext.Component', {
            cls: 'amount',
            html: '<div class="label">{s name="total_amount"}Total amount{/s}</div>' +
                  '<div class="value">'+ me.formatPrice(me.basket.get('grossPrice')) +'</div>'
        });
        items.push(total);

        var net = Ext.create('Ext.Component', {
            cls: 'amount-net',
            html: '<div class="label">{s name="total_amount_without_tax"}Total amount excl. VAT:{/s}</div>' +
                  '<div class="value">'+ me.formatPrice(me.basket.get('netPrice')) +'</div>'
        });

        var tax =  Ext.create('Ext.Component', {
            cls: 'tax',
            html: '<div class="label">{s name="tax_rate_label"}plus{/s} '+ me.basket.get('taxRate') +'% {s name="tax_value_label"}VAT:{/s}</div>' +
                  '<div class="value">'+ me.formatPrice(me.basket.get('taxPrice')) +'</div>'
        });

        if (me.basket.get('taxPrice') > 0) {
            items.push(net);
            items.push(tax);
        }

        var container = Ext.create('Ext.container.Container', {
            cls: 'basket-amount-container',
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            width: 390,
            items: items
        });

        return Ext.create('Ext.container.Container', {
            cls: 'basket-amount-wrapper',
            height: 185,
            items: [ container ]
        });
    },

    createButtons: function() {
        var me = this;

        var cancelButton = Ext.create('PluginManager.container.Container', {
            html: '{s name="cancel"}Cancel{/s}',
            cls: 'plugin-manager-action-button cancel',
            handler: function() {
                me.destroy();
            }
        });

        me.applyButton = Ext.create('PluginManager.container.Container', {
            html: '{s name="purchase_button"}Complete payment{/s}',
            cls: 'plugin-manager-action-button primary buy',
            disabled: true,
            handler: function() {
                if (me.gtcBox.getValue() && me.bookingDomainSelection.validate()) {
                    me.callback(me.basket);
                }
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            cls: 'toolbar',
            items: [ cancelButton , '->', me.applyButton]
        });
    },

    formatPrice: function(value) {
        return Ext.util.Format.currency(value, ' €', 2, true);
    }

});
//{/block}