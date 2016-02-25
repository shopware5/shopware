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

//{namespace name=backend/customer/view/detail}

/**
 * Shopware UI - Customer detail page billing field set
 *
 * The billing field set contains the billing data of the customer
 * which is stored in the billing model and filled over the s_user_billingaddress table
 */
//{block name="backend/customer/view/detail/billing"}
Ext.define('Shopware.apps.Customer.view.detail.Billing', {
    /**
     * Define that the billing field set is an extension of the Ext.form.FieldSet
     * @string
     */
    extend:'Ext.form.FieldSet',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.customer-billing-field-set',
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
            label:'{s name=address/salutation}Salutation{/s}',
            mr:'{s name=address/salutation_mr}Mr{/s}',
            ms:'{s name=address/salutation_ms}Mrs{/s}'
        },
        firstName:'{s name=address/first_name}First name{/s}',
        lastName:'{s name=address/last_name}Last name{/s}',
        street:'{s name=address/street}Street{/s}',
        zipCode:'{s name=address/zip_code}Zip code{/s}',
        city:'{s name=address/city}City{/s}',
        additionalAddressLine1:'{s name=address/additionalAddressLine1}Additional address line 1{/s}',
        additionalAddressLine2:'{s name=address/additionalAddressLine2}Additional address line 2{/s}',
        birthday:'{s name=address/birthday_label}Day of birth{/s}',
        country:'{s name=address/country}Country{/s}',
        state:'{s name=address/state}State{/s}',
        phone:'{s name=address/phone}Phone{/s}',
        company:'{s name=address/company}Company{/s}',
        department:'{s name=address/department}Department{/s}',
        vatId:'{s name=address/vat_id}VAT ID{/s}',
        fax:'{s name=address/fax}Fax{/s}',
        text1: {
            label:     '{s name=billing/text_1_label}Text 1{/s}',
            support:   '{s name=billing/text_1_support}Free text field 1{/s}',
            helpTitle: '{s name=billing/text_1_help_title}Free text field 1{/s}',
            helpText:  '{s name=billing/text_1_help_text}You can use the text module administration for the labelling of the text fields. Snippet: backend/customer/view/detail/billing/text_1_*{/s}'
        },
        text2: {
            label:     '{s name=billing/text_2_label}Text 2{/s}',
            support:   '{s name=billing/text_2_support}Free text field 2{/s}',
            helpTitle: '{s name=billing/text_2_help_title}Free text field 2{/s}',
            helpText:  '{s name=billing/text_2_help_text}You can use the text module administration for the labelling of the text fields. Snippet: backend/customer/view/detail/billing/text_2_*{/s}'
        },
        text3: {
            label:     '{s name=billing/text_3_label}Text 3{/s}',
            support:   '{s name=billing/text_3_support}Free text field 3{/s}',
            helpTitle: '{s name=billing/text_3_help_title}Free text field 3{/s}',
            helpText:  '{s name=billing/text_3_help_text}You can use the text module administration for the labelling of the text fields. Snippet: backend/customer/view/detail/billing/text_3_*{/s}'
        },
        text4: {
            label:     '{s name=billing/text_4_label}Text 4{/s}',
            support:   '{s name=billing/text_4_support}Free text field 4{/s}',
            helpTitle: '{s name=billing/text_4_help_title}Free text field 4{/s}',
            helpText:  '{s name=billing/text_4_help_text}You can use the text module administration for the labelling of the text fields. Snippet: backend/customer/view/detail/billing/text_4_*{/s}'
        },
        text5: {
            label:     '{s name=billing/text_5_label}Text 5{/s}',
            support:   '{s name=billing/text_5_support}Free text field 5{/s}',
            helpTitle: '{s name=billing/text_5_help_title}Free text field 5{/s}',
            helpText:  '{s name=billing/text_5_help_text}You can use the text module administration for the labelling of the text fields. Snippet: backend/customer/view/detail/billing/text_5_*{/s}'
        },
        text6: {
            label:     '{s name=billing/text_6_label}Text 6{/s}',
            support:   '{s name=billing/text_6_support}Free text field 6{/s}',
            helpTitle: '{s name=billing/text_6_help_title}Free text field 6{/s}',
            helpText:  '{s name=billing/text_6_help_text}You can use the text module administration for the labelling of the text fields. Snippet: backend/customer/view/detail/billing/text_6_*{/s}'
        }
    },

    /**
     * Component event method which is fired when the component
     * is initials. The component is initials when the user
     * want to create a new customer or edit an existing customer
     * @return void
     */
    initComponent:function () {
        var me = this;
        me.title = me.snippets.title;
        me.salutationData = [
            ['mr', me.snippets.salutation.mr],
            ['ms', me.snippets.salutation.ms]
        ];

        me.items = me.createBillingForm();

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
    createBillingForm:function () {
        var leftContainer, rightContainer, me = this;

        leftContainer = Ext.create('Ext.container.Container', {
            columnWidth:.5,
            border:false,
            cls: Ext.baseCSSPrefix + 'field-set-container',
            layout:'anchor',
            defaults:{
                anchor:'100%',
                labelWidth:150,
                minWidth:250,
                xtype:'textfield'
            },
            items:me.createBillingFormLeft()
        });

        rightContainer = Ext.create('Ext.container.Container', {
            columnWidth:.5,
            border:false,
            cls: Ext.baseCSSPrefix + 'field-set-container',
            layout:'anchor',
            defaults:{
                anchor:'100%',
                labelWidth:100,
                xtype:'textfield'
            },
            items:me.createBillingFormRight()
        });

        return [ leftContainer, rightContainer ];
    },

    /**
     * Creates the left container of the billing field set.
     * Contains the following components and data:
     * - [combobox]  Salutation
     * - [textfield] First name
     * - [textfield] Last name
     * - [textfield] Street name
     * - [textfield] Zip code
     * - [textfield] City name
     * - [combobox]  Country
     * - [datefield] Birthday
     * - [textfield] Company name
     * - [textfield] Department name
     * - [textfield] Vat id
     *
     * @return Ext.container.Container Contains the three components
     */
    createBillingFormLeft:function () {
        var me = this;

        me.countryCombo = Ext.create('Ext.form.field.ComboBox', {
            triggerAction:'all',
            name:'billing[countryId]',
            fieldLabel:me.snippets.country,
            valueField:'id',
            queryMode: 'local',
            displayField:'name',
            forceSelection: true,
            anchor:'100%',
            labelWidth:150,
            minWidth:250,
            editable:false,
            allowBlank:false,
            listeners: {
                change: function(field, newValue, oldValue) {
                    me.fireEvent('countryChanged', field, newValue, me.countryStateCombo);
                }
            }
        });

        me.countryStateCombo = Ext.create('Ext.form.field.ComboBox', {
            name:'billing[stateId]',
            action: 'billingStateId',
            fieldLabel:me.snippets.state,
            valueField: 'id',
            displayField: 'name',
            forceSelection: true,
            editable: false,
            hidden: true,
            triggerAction:'all',
            queryMode: 'local',
            anchor:'100%',
            labelWidth:150,
            minWidth:250
        });


        return [{
            xtype:'combobox',
            triggerAction:'all',
            name:'billing[salutation]',
            fieldLabel:me.snippets.salutation.label,
            valueField:'text',
            displayField:'snippet',
            mode:'local',
            editable:false,
            allowBlank: false,
            store:new Ext.data.SimpleStore({
                fields:['text', 'snippet'], data:me.salutationData
            })
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
        },
            me.countryStateCombo,
            me.countryCombo,
        {
            //define birthday date field with a defined format
            xtype:'datefield',
            name:'billing[birthday]',
            fieldLabel:me.snippets.birthday,
            submitFormat: 'd.m.Y'
        }, {
            name:'billing[company]',
            fieldLabel:me.snippets.company
        }, {
            name:'billing[department]',
            fieldLabel:me.snippets.department
        }, {
            name:'billing[vatId]',
            fieldLabel:me.snippets.vatId
        }];
    },

    /**
     * Creates the left container of the billing field set.
     * Contains the following components and data:
     * - [textfield] Phone number
     * - [textfield] Fax number
     * - [textfield] Free text field 1
     * - [textfield] Free text field 2
     * - [textfield] Free text field 3
     * - [textfield] Free text field 4
     * - [textfield] Free text field 5
     * - [textfield] Free text field 6
     *
     * @return Ext.container.Container Contains the three components
     */
    createBillingFormRight:function () {
        var me = this;

        return [{
            name:'billing[phone]',
            fieldLabel:me.snippets.phone
        }, {
            name:'billing[fax]',
            fieldLabel:me.snippets.fax
        }, {
            name:'billingAttribute[text1]',
            fieldLabel:me.snippets.text1.label,
            supportText: me.snippets.text1.support,
            helpTitle: me.snippets.text1.helpTitle,
            helpText: me.snippets.text1.helpText
        }, {
            name:'billingAttribute[text2]',
            fieldLabel:me.snippets.text2.label,
            supportText: me.snippets.text2.support,
            helpTitle: me.snippets.text2.helpTitle,
            helpText: me.snippets.text2.helpText
        }, {
            name:'billingAttribute[text3]',
            fieldLabel:me.snippets.text3.label,
            supportText: me.snippets.text3.support,
            helpTitle: me.snippets.text3.helpTitle,
            helpText: me.snippets.text3.helpText
        }, {
            name:'billingAttribute[text4]',
            fieldLabel:me.snippets.text4.label,
            supportText: me.snippets.text4.support,
            helpTitle: me.snippets.text4.helpTitle,
            helpText: me.snippets.text4.helpText
        }, {
            name:'billingAttribute[text5]',
            fieldLabel:me.snippets.text5.label,
            supportText: me.snippets.text5.support,
            helpTitle: me.snippets.text5.helpTitle,
            helpText: me.snippets.text5.helpText
        }, {
            name:'billingAttribute[text6]',
            fieldLabel:me.snippets.text6.label,
            supportText: me.snippets.text6.support,
            helpTitle: me.snippets.text6.helpTitle,
            helpText: me.snippets.text6.helpText
        }];
    }
});
//{/block}
