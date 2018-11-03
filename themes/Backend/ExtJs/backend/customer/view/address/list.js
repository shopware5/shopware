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
 * @subpackage Address
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name=backend/customer/view/address}

/**
 * Shopware UI - Customer address list backend module
 */
// {block name="backend/customer/view/address/list"}
Ext.define('Shopware.apps.Customer.view.address.List', {

    extend: 'Shopware.grid.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.customer-address-grid',

    /**
     * The view needs to be scrollable
     * @string
     */
    autoScroll: true,

    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'address-list',

    /**
     * Set region to expand width/height
     */
    region: 'center',

    /**
     * Snippets for grid headers
     */
    snippets: {
        header: {
            isDefaultAddress: '{s name="list/header/isDefaultAddress"}Default{/s}',
            company: '{s name="list/header/company"}Company{/s}',
            salutation: '{s name="list/header/salutation"}Salutation{/s}',
            name: '{s name="list/header/name"}Name{/s}',
            street: '{s name="list/header/street"}Street{/s}',
            zipcode: '{s name="list/header/zipcode"}Zipcode{/s}',
            city: '{s name="list/header/city"}City{/s}',
            state: '{s name="list/header/state"}State{/s}',
            country: '{s name="list/header/country"}Country{/s}',
            phone: '{s name="list/header/phone"}Phone{/s}',
            vatId: '{s name="list/header/vatId"}VAT ID{/s}'
        }
    },

    /**
     * Listeners
     * - Enable double-click to open detail window
     * - Disable deletion by disabling the row selection for default addresses
     */
    listeners: {
        itemdblclick: function(view, record) {
            this.controller.onEditItem(this, record);
        },
        beforeselect: function(row, record) {
            var me = this;

            if (me.isDefaultAddress(record)) {
                return false;
            }
        }
    },

    /**
     * configure the grid
     * @returns { Object }
     */
    configure: function() {
        var me = this;

        var columns = {
            defaultAddress: {
                header: me.snippets.header.isDefaultAddress,
                renderer: me.defaultColumnRenderer,
                flex: 1,
                sortable: false
            },
            company: {
                header: me.snippets.header.company,
                renderer: me.companyRenderer,
                flex: 2
            },
            vatId: {
                header: me.snippets.header.vatId,
                flex: 1
            },
            firstname: {
                header: me.snippets.header.name,
                renderer: me.nameRenderer,
                flex: 2
            },
            street: {
                header: me.snippets.header.street,
                renderer: me.addressRenderer,
                flex: 2
            },
            zipcode: {
                header: me.snippets.header.zipcode,
                flex: 1
            },
            city: {
                header: me.snippets.header.city,
                flex: 1
            },
            stateId: {
                header: me.snippets.header.state,
                flex: 1,
                sortable: false
            },
            countryId: {
                header: me.snippets.header.country,
                flex: 1,
                sortable: false
            }
        };

        /* {if {config name=showphonenumberfield}} */
        columns['phone'] = {
            header: me.snippets.header.phone,
            flex: 1
        };
        /* {/if} */

        return {
            columns: columns,
            searchField: false,
            detailWindow: 'Shopware.apps.Customer.view.address.detail.Window'
        };
    },

    /**
     * Combine First- and Lastname
     *
     * @param value
     * @param col
     * @param record
     * @returns { String }
     */
    nameRenderer: function (value, col, record) {
        return record.get('firstname') + ' ' + record.get('lastname');
    },

    /**
     * Combine Company, Department and VatID
     *
     * @param value
     * @param col
     * @param record
     * @returns { String }
     */
    companyRenderer: function (value, col, record) {
        if (!record.get('company')) {
            return null;
        }

        var companyName = '<b>' + record.get('company') + '</b>';

        if (record.get('department')) {
            companyName += '<br/>' + record.get('department');
        }

        return companyName;
    },

    /**
     * Combine additional address lines and street
     *
     * @param value
     * @param col
     * @param record
     * @returns { String }
     */
    addressRenderer: function (value, col, record) {
        var addressLine = record.get('street');

        if (record.get('additionalAddressLine1')) {
            addressLine += '<br/>' + record.get('additionalAddressLine1');
        }

        if (record.get('additionalAddressLine2')) {
            addressLine += '<br/>' + record.get('additionalAddressLine2');
        }

        return addressLine;
    },

    /**
     * Indicate default billing and shipping addresses
     *
     * @param value
     * @param col
     * @param record
     * @returns { String }
     */
    defaultColumnRenderer: function (value, col, record) {
        var defaults = [],
            customer = record.getCustomerStore && record.getCustomerStore.first();

        if (customer && customer.get('default_billing_address_id') == record.get('id')) {
            defaults.push('{s name="list/data/billingaddress"}{/s}');
        }

        if (customer && customer.get('default_shipping_address_id') == record.get('id')) {
            defaults.push('{s name="list/data/shippingaddress"}{/s}');
        }

        return defaults.join('<br/>');
    },

    /**
     * Inject customer id for model relation
     */
    createNewRecord: function() {
        var me = this;

        return Ext.create(this.store.model, {
            user_id: me.customerRecord.get('id')
        });
    },

    /**
     * Hide delete icon for default billing or shipping address
     */
    createDeleteColumn: function() {
        var me = this,
            button = me.callParent(arguments);

        button.getClass = function(value, meta, record) {
            if (me.isDefaultAddress(record)) {
                return 'x-hidden';
            }
        };

        return button;
    },

    /**
     * Checks if the given address is set as default billing or shipping address
     *
     * @param record
     * @returns { boolean }
     */
    isDefaultAddress: function(record) {
        var customer = record.getCustomerStore && record.getCustomerStore.first();

        return customer &&
            (customer.get('default_billing_address_id') == record.get('id') || customer.get('default_shipping_address_id') == record.get('id'));
    }
});
// {/block}
