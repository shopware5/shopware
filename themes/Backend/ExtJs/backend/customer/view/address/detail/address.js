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
 * Shopware UI - Customer address detail backend module
 */
// {block name="backend/customer/view/address/detail/address"}
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

        var factory = Ext.create('Shopware.attribute.SelectionFactory');
        var countryStore = factory.createEntitySearchStore("Shopware\\Models\\Country\\Country");
        countryStore.remoteSort = true;

        countryStore.sort([{
            property: 'active',
            direction: 'DESC'
        }, {
            property: 'name',
            direction: 'ASC'
        }]);
        countryStore.load();

        return {
            controller: 'Address',
            fieldSets: [
                {
                    title: me.snippets.fieldset.company,
                    fields: {
                        company: {
                            fieldLabel: me.snippets.fields.company,
                            labelWidth: 155,
                            anchor: '95%'
                        },
                        department: {
                            fieldLabel: me.snippets.fields.department,
                            labelWidth: 155,
                            anchor: '95%'
                        },
                        vatId: {
                            fieldLabel: me.snippets.fields.vatId,
                            labelWidth: 155,
                            anchor: '95%'
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
                            labelWidth: 155,
                            anchor: '95%',
                            store: Ext.create('Shopware.apps.Base.store.Salutation').load()
                        },
                        title: {
                            fieldLabel: me.snippets.fields.title,
                            labelWidth: 155,
                            anchor: '95%'
                        },
                        firstname: {
                            allowBlank: false,
                            fieldLabel: me.snippets.fields.firstname,
                            labelWidth: 155,
                            anchor: '95%'
                        },
                        lastname: {
                            allowBlank: false,
                            fieldLabel: me.snippets.fields.lastname,
                            labelWidth: 155,
                            anchor: '95%'
                        },
                        street: {
                            allowBlank: false,
                            fieldLabel: me.snippets.fields.street,
                            labelWidth: 155,
                            anchor: '95%'
                        },
                        additionalAddressLine1: {
                            /* {if {config name=showAdditionAddressLine1} && {config name=requireAdditionAddressLine1}} */
                            allowBlank: false,
                            /* {/if} */
                            fieldLabel: me.snippets.fields.additionalAddressLine1,
                            labelWidth: 155,
                            anchor: '95%'
                        },
                        additionalAddressLine2: {
                            /* {if {config name=showAdditionAddressLine2} && {config name=requireAdditionAddressLine2}} */
                            allowBlank: false,
                            /* {/if} */
                            fieldLabel: me.snippets.fields.additionalAddressLine2,
                            labelWidth: 155,
                            anchor: '95%'
                        },
                        zipcode: {
                            allowBlank: false,
                            fieldLabel: me.snippets.fields.zipcode,
                            labelWidth: 155,
                            anchor: '95%'
                        },
                        city: {
                            allowBlank: false,
                            fieldLabel: me.snippets.fields.city,
                            labelWidth: 155,
                            anchor: '95%'
                        },
                        countryId: {
                            allowBlank: false,
                            fieldLabel: me.snippets.fields.country,
                            labelWidth: 155,
                            anchor: '95%',
                            pageSize: 25,
                            listeners: {
                                select: me.onCountrySelect,
                                scope: me
                            },
                            store: countryStore,
                            xtype: 'pagingcombobox',
                            valueField:'id',
                            displayField: 'name'
                        },
                        stateId: {
                            fieldLabel: me.snippets.fields.state,
                            labelWidth: 155,
                            anchor: '95%',
                            pageSize: 25
                        },
                        phone: {
                            /* {if {config name=showphonenumberfield} && {config name=requirePhoneField}} */
                            allowBlank: false,
                            /* {/if} */
                            fieldLabel: me.snippets.fields.phone,
                            labelWidth: 155,
                            anchor: '95%'
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
                            fieldLabel: null,
                            labelWidth: 155,
                            anchor: '95%'
                        },
                        setDefaultShippingAddress: {
                            xtype: 'checkbox',
                            uncheckedValue: false,
                            inputValue: true,
                            boxLabel: me.snippets.fields.setDefaultShippingAddress,
                            fieldLabel: null,
                            labelWidth: 155,
                            anchor: '95%'
                        }
                    }
                }
            ]
        };
    },

    createAssociationSearchStore: function(model, associationKey) {
        var me = this;
        var store = me.callParent(arguments);
        if (associationKey == 'state') {
            me.addCountryIdFilter(store, me.record.get('countryId'));
        }
        return store;
    },

    addCountryIdFilter: function(store, countryId) {
        store.remoteFilter = true;
        store.filters.clear();
        store.pageSize = 25;
        store.filters.add({
            property: 'countryId',
            expression: '=',
            value: countryId
        });
    },

    /**
     * Called when the user changes the country combobox in the shipping or billing form
     * @param countryCombo
     * @param records Ext.data.Model[]
     */
    onCountrySelect: function(countryCombo, records) {
        var me = this,
            countryStateCombo = me.down('combobox[name=stateId]'),
            oldState = countryStateCombo.getValue(),
            store = countryStateCombo.store;

        var country = records.shift();
        if (country) {
            countryStateCombo.allowBlank = !country.get('forceStateInRegistration');
        }

        if (country === null) {
            countryStateCombo.setValue(null);
            countryStateCombo.hide();
            return;
        }

        me.addCountryIdFilter(store, country.get('id'));

        store.loadPage(1, {
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
// {/block}
