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
 * @package    Order
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/order/main}

/**
 * Shopware UI - Order detail page billing field set
 *
 * todo@all: Documentation
 */
//{block name="backend/order/view/detail/billing"}
Ext.define('Shopware.apps.Order.view.detail.Billing', {
    /**
     * Define that the billing field set is an extension of the Ext.form.FieldSet
     * @string
     */
    extend:'Ext.form.FieldSet',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.order-billing-field-set',
    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'billing-field-set',
    /**
     * Layout for the component.
     * @string
     */
    layout: 'column',
    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        title:'{s name=billing/title}Billing data{/s}',
        salutation:{
            label:'{s name=address/salutation}Salutation{/s}'
        },
        titleField:'{s name=address/title_field}Title{/s}',
        firstName:'{s name=address/first_name}First name{/s}',
        lastName:'{s name=address/last_name}Last name{/s}',
        street:'{s name=address/street}Street{/s}',
        zipCode:'{s name=address/zip_code}Zip code{/s}',
        city:'{s name=address/city}City{/s}',
        additionalAddressLine1:'{s name=address/additionalAddressLine1}Additional address line 1{/s}',
        additionalAddressLine2:'{s name=address/additionalAddressLine2}Additional address line 2{/s}',
        state:'{s name=address/state}State{/s}',
        birthday:'{s name=address/birthday_label}Day of birth{/s}',
        country:'{s name=address/country}Country{/s}',
        phone:'{s name=address/phone}Phone{/s}',
        company:'{s name=address/company}Company{/s}',
        department:'{s name=address/department}Department{/s}',
        vatId:'{s name=address/vat_id}VAT ID{/s}'
    },

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @return void
     */
    initComponent:function () {
        var me = this;
        me.title = me.snippets.title;

        me.items = me.createElements();
        me.addEvents(
                /**
                 * Fired when the user changes his country. Used to fill the state box
                 * @param field
                 * @param newValue
                 */
                'countryChanged'

        );
        me.callParent(arguments);
    },

    /**
     * Creates the both containers for the field set
     * to display the form fields in two columns.
     *
     * @return [Array] Contains the left and right container
     */
    createElements:function () {
        var leftContainer, rightContainer, me = this;

        leftContainer = Ext.create('Ext.container.Container', {
            columnWidth:.5,
            border:false,
            layout:'anchor',
            defaults:{
                anchor:'95%',
                labelWidth:155,
                minWidth:250,
                labelStyle: 'font-weight: 700;',
                style: {
                    margin: '0 0 10px'
                },
                xtype:'textfield'
            },
            items:me.createLeftElements()
        });

        rightContainer = Ext.create('Ext.container.Container', {
            columnWidth:.5,
            border:false,
            layout:'anchor',
            defaults:{
                anchor:'95%',
                labelWidth:155,
                minWidth:250,
                labelStyle: 'font-weight: 700;',
                style: {
                    margin: '0 0 10px'
                },
                xtype:'textfield'
            },
            items:me.createRightElements()
        });

        var id = null;
        if (me.record && me.record.getBilling() && me.record.getBilling().first()) {
            id = me.record.getBilling().first().get('id');
        }

        me.attributeForm = Ext.create('Shopware.attribute.Form', {
            name: 'billing-attributes',
            table: 's_order_billingaddress_attributes',
            columnWidth: 1
        });
        me.attributeForm.loadAttribute(id);

        return [ leftContainer, rightContainer, me.attributeForm ];
    },

    /**
     * Creates the left container of the billing field set.
     *
     * @return Ext.container.Container Contains the three components
     */
    createLeftElements:function () {
        var me = this;

        return [{
            xtype:'combobox',
            queryMode: 'local',
            triggerAction:'all',
            name:'billing[salutation]',
            fieldLabel:me.snippets.salutation.label,
            mode:'local',
            editable:false,
            allowBlank: false,
            valueField: 'key',
            displayField: 'label',
            store: Ext.create('Shopware.apps.Base.store.Salutation').load()
        }, {
            name:'billing[title]',
            fieldLabel:me.snippets.titleField,
            allowBlank: true
        }, {
            name:'billing[firstName]',
            fieldLabel:me.snippets.firstName,
            allowBlank:false
        }, {
            name:'billing[lastName]',
            fieldLabel:me.snippets.lastName,
            required:true,
            allowBlank:false
        }, {
            name:'billing[company]',
            fieldLabel:me.snippets.company
        }, {
            name:'billing[department]',
            fieldLabel:me.snippets.department
        }, {
            name:'billing[vatId]',
            fieldLabel:me.snippets.vatId
        }, {
            name:'billing[phone]',
            fieldLabel:me.snippets.phone
        }];
    },

    /**
     * Creates the left container of the billing field set.
     *
     * @return Ext.container.Container Contains the three components
     */
    createRightElements:function () {
        var me = this;

        me.countryStateCombo = Ext.create('Ext.form.field.ComboBox', {
            name:'billing[stateId]',
            action: 'billingStateId',
            fieldLabel:me.snippets.state,
            valueField: 'id',
            displayField: 'name',
            forceSelection: true,
            labelWidth:155,
            store: Ext.create('Shopware.store.CountryState'),
            minWidth: 250,
            editable: false,
            hidden: true,
            triggerAction:'all',
            queryMode: 'local'
        });

        me.countryCombo = Ext.create('Ext.form.field.ComboBox', {
            triggerAction:'all',
            name:'billing[countryId]',
            fieldLabel:me.snippets.country,
            valueField:'id',
            queryMode: 'local',
            displayField:'name',
            forceSelection: true,
            store:me.countriesStore,
            labelWidth:155,
            minWidth:250,
            required:true,
            editable:false,
            allowBlank:false,
            listeners: {
                change: function(field, newValue, oldValue, record) {
                    me.fireEvent('countryChanged', field, newValue, me.countryStateCombo, me.record.getBilling().first());
                }
            }
        });

        return [{
            name:'billing[street]',
            fieldLabel:me.snippets.street,
            required:true,
            allowBlank:false
        }, {
            name:'billing[additionalAddressLine1]',
            fieldLabel:me.snippets.additionalAddressLine1
        }, {
            name:'billing[additionalAddressLine2]',
            fieldLabel:me.snippets.additionalAddressLine2
        }, {
            name:'billing[zipCode]',
            fieldLabel:me.snippets.zipCode,
            required:true,
            allowBlank:false
        }, {
            name:'billing[city]',
            fieldLabel:me.snippets.city,
            required:true,
            allowBlank:false
        }, me.countryStateCombo, me.countryCombo];
    }
});
//{/block}
