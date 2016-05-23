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

//{namespace name=backend/customer/view/address}

/**
 * Shopware UI - Customer address detail backend module
 */
//{block name="backend/customer/view/address/detail/address"}
Ext.define('Shopware.apps.Customer.view.address.detail.Address', {
    extend: 'Shopware.model.Container',
    padding: 20,
    alias: 'widget.customer-address-detail-address',

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets: {
        fieldset: {
            company: '{s name="detail/fieldset/company"}Company data{/s}',
            address: '{s name="detail/fieldset/address"}Address data{/s}',
            actions: '{s name="detail/fieldset/actions"}Actions{/s}'
        },
        fields: {
            salutation: {
                label: '{s name=detail/label/salutation}Salutation{/s}'
            },
            firstname: '{s name=detail/label/firstname}First name{/s}',
            title: '{s name=detail/label/title}Title{/s}',
            lastname: '{s name=detail/label/lastname}Last name{/s}',
            street: '{s name=detail/label/street}Street{/s}',
            zipcode: '{s name=detail/label/zipcode}Zip code{/s}',
            city: '{s name=detail/label/city}City{/s}',
            additionalAddressLine1: '{s name=detail/label/additionalAddressLine1}Additional address line 1{/s}',
            additionalAddressLine2: '{s name=detail/label/additionalAddressLine2}Additional address line 2{/s}',
            country: '{s name=detail/label/country}Country{/s}',
            state: '{s name=detail/label/state}State{/s}',
            phone: '{s name=detail/label/phone}Phone{/s}',
            company: '{s name=detail/label/company}Company{/s}',
            department: '{s name=detail/label/department}Department{/s}',
            vatId: '{s name=detail/label/vat_id}VAT ID{/s}',
            setDefaultBillingAddress: '{s name=detail/label/setDefaultBillingAddress}VAT ID{/s}',
            setDefaultShippingAddress: '{s name=detail/label/setDefaultShippingAddress}VAT ID{/s}'
        }
    },

    configure: function () {
        var me = this;

        me.countryStateStore = Ext.create('Shopware.apps.Base.store.CountryState');

        return {
            controller: 'Address',
            fieldSets: [
                {
                    title: me.snippets.fieldset.company,
                    fields: {
                        company: {
                            fieldLabel: me.snippets.fields.company
                        },
                        department: {
                            fieldLabel: me.snippets.fields.department
                        },
                        vatId: {
                            fieldLabel: me.snippets.fields.vatId
                        }
                    }
                },
                {
                    title: me.snippets.fieldset.address,
                    fields: {
                        salutation: {
                            xtype: 'combobox',
                            triggerAction: 'all',
                            fieldLabel: me.snippets.fields.salutation.label,
                            editable: false,
                            allowBlank: false,
                            valueField: 'key',
                            displayField: 'label',
                            store: Ext.create('Shopware.apps.Base.store.Salutation').load()
                        },
                        title: me.snippets.fields.title,
                        firstname: {
                            allowBlank: false,
                            fieldLabel: me.snippets.fields.firstname
                        },
                        lastname: {
                            allowBlank: false,
                            fieldLabel: me.snippets.fields.lastname
                        },
                        street: {
                            allowBlank: false,
                            fieldLabel: me.snippets.fields.street
                        },
                        additionalAddressLine1: {
                            /*{if {config name=showAdditionAddressLine1} && {config name=requireAdditionAddressLine1}}*/
                            allowBlank: false,
                            /*{/if}*/
                            fieldLabel: me.snippets.fields.additionalAddressLine1
                        },
                        additionalAddressLine2: {
                            /*{if {config name=showAdditionAddressLine2} && {config name=requireAdditionAddressLine2}}*/
                            allowBlank: false,
                            /*{/if}*/
                            fieldLabel: me.snippets.fields.additionalAddressLine2
                        },
                        zipcode: {
                            allowBlank: false,
                            fieldLabel: me.snippets.fields.zipcode
                        },
                        city: {
                            allowBlank: false,
                            fieldLabel: me.snippets.fields.city
                        },
                        country_id: {
                            allowBlank: false,
                            fieldLabel: me.snippets.fields.country,
                            listeners: {
                                change: me.onCountryChanged,
                                scope: me
                            }
                        },
                        state_id: {
                            store: me.countryStateStore,
                            fieldLabel: me.snippets.fields.state
                        },
                        phone: {
                            /*{if {config name=showphonenumberfield} && {config name=requirePhoneField}}*/
                            allowBlank: false,
                            /*{/if}*/
                            fieldLabel: me.snippets.fields.phone
                        }
                    }
                },
                {
                    title: me.snippets.fieldset.actions,
                    fields: {
                        setDefaultBillingAddress: {
                            xtype: 'checkbox',
                            uncheckedValue: false,
                            inputValue: true,
                            boxLabel: me.snippets.fields.setDefaultBillingAddress,
                            fieldLabel: null
                        },
                        setDefaultShippingAddress: {
                            xtype: 'checkbox',
                            uncheckedValue: false,
                            inputValue: true,
                            boxLabel: me.snippets.fields.setDefaultShippingAddress,
                            fieldLabel: null
                        }
                    }
                }
            ]
        };
    },

    /**
     * Called when the user changes the country combobox in the shipping or billing form
     * @param countryCombo
     * @param newValue
     */
    onCountryChanged: function(countryCombo, newValue) {
        var me = this,
            countryStateCombo = me.down('combobox[name=state_id]'),
            oldState = countryStateCombo.getValue(),
            store = countryStateCombo.store,
            country = countryCombo.findRecord('id', newValue);

        if (country) {
            countryStateCombo.allowBlank = !country.get('forceStateInRegistration');
        }

        if (newValue === null) {
            countryStateCombo.setValue(null);
            countryStateCombo.hide();
            return;
        }

        store.getProxy().extraParams.countryId = newValue;

        store.load({
            callback: function() {
                var record = store.getById(oldState);

                if (store.getCount() === 0) {
                    countryStateCombo.setValue(null);
                    countryStateCombo.hide();
                    return true;
                }

                if (record instanceof Ext.data.Model) {
                    countryStateCombo.setValue(record.get('id'));
                } else {
                    countryStateCombo.setValue(null);
                }
                countryStateCombo.show();
            }
        });
    }
});
//{/block}