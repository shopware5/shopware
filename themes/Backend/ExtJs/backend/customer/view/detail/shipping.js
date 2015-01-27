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
 * Shopware UI - Customer detail page
 *
 * The shipping field set contains the shipping address data of the customer
 * which is stored in the shipping model and filled over the s_user_shippingaddress table
 */
//{block name="backend/customer/view/detail/shipping"}
Ext.define('Shopware.apps.Customer.view.detail.Shipping', {
    /**
     * Define that the shipping field set is an extension of the Ext.form.FieldSet
     * @string
     */
    extend:'Ext.form.FieldSet',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.customer-shipping-field-set',
    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'shipping-field-set',
    /**
     * Enable field set collapse
     * @boolean
     */
    collapsible:true,
    /**
     * Marks that the field set is collapsed at the start.
     * @boolean
     */
    collapsed:true,
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
        title:'{s name=shipping/title}Alternative shipping address{/s}',
        firstName:'{s name=address/first_name}First name{/s}',
        lastName:'{s name=address/last_name}Last name{/s}',
        street:'{s name=address/street}street{/s}',
        zipCode:'{s name=address/zip_code}Zip code{/s}',
        city:'{s name=address/city}City{/s}',
        additionalAddressLine1:'{s name=address/additionalAddressLine1}Additional address line 1{/s}',
        additionalAddressLine2:'{s name=address/additionalAddressLine2}Additional address line 2{/s}',
        salutation:{
            label:'{s name=address/salutation}Salutation{/s}',
            mr:'{s name=address/salutation_mr}Mr{/s}',
            ms:'{s name=address/salutation_ms}Mrs{/s}'
        },
        country:'{s name=address/country}Country{/s}',
        state:'{s name=address/state}State{/s}',
        company:'{s name=address/company}Company{/s}',
        department:'{s name=address/department}Department{/s}',
        text1: {
            label:     '{s name=shipping/text_1_label}Text 1{/s}',
            support:   '{s name=shipping/text_1_support}Free text field 1{/s}',
            helpTitle: '{s name=shipping/text_1_help_title}Free text field 1{/s}',
            helpText:  '{s name=shipping/text_1_help_text}You can use the text module administration for the labelling of the text fields. Snippet: backend/customer/view/detail/shipping/text_1_*{/s}'
        },
        text2: {
            label:     '{s name=shipping/text_2_label}Text 2{/s}',
            support:   '{s name=shipping/text_2_support}Free text field 2{/s}',
            helpTitle: '{s name=shipping/text_2_help_title}Free text field 2{/s}',
            helpText:  '{s name=shipping/text_2_help_text}You can use the text module administration for the labelling of the text fields. Snippet: backend/customer/view/detail/shipping/text_2_*{/s}'
        },
        text3: {
            label:     '{s name=shipping/text_3_label}Text 3{/s}',
            support:   '{s name=shipping/text_3_support}Free text field 3{/s}',
            helpTitle: '{s name=shipping/text_3_help_title}Free text field 3{/s}',
            helpText:  '{s name=shipping/text_3_help_text}You can use the text module administration for the labelling of the text fields. Snippet: backend/customer/view/detail/shipping/text_3_*{/s}'
        },
        text4: {
            label:     '{s name=shipping/text_4_label}Text 4{/s}',
            support:   '{s name=shipping/text_4_support}Free text field 4{/s}',
            helpTitle: '{s name=shipping/text_4_help_title}Free text field 4{/s}',
            helpText:  '{s name=shipping/text_4_help_text}You can use the text module administration for the labelling of the text fields. Snippet: backend/customer/view/detail/shipping/text_4_*{/s}'
        },
        text5: {
            label:     '{s name=shipping/text_5_label}Text 5{/s}',
            support:   '{s name=shipping/text_5_support}Free text field 5{/s}',
            helpTitle: '{s name=shipping/text_5_help_title}Free text field 5{/s}',
            helpText:  '{s name=shipping/text_5_help_text}You can use the text module administration for the labelling of the text fields. Snippet: backend/customer/view/detail/shipping/text_5_*{/s}'
        },
        text6: {
            label:     '{s name=shipping/text_6_label}Text 6{/s}',
            support:   '{s name=shipping/text_6_support}Free text field 6{/s}',
            helpTitle: '{s name=shipping/text_6_help_title}Free text field 6{/s}',
            helpText:  '{s name=shipping/text_6_help_text}You can use the text module administration for the labelling of the text fields. Snippet: backend/customer/view/detail/shipping/text_6_*{/s}'
        },
        copyBilling: '{s name=shipping/copy_billing}For usability purposes, click here to use the billing address as shipping address.{/s}',
        copyButton: '{s name=shipping/copy_button}Copy data{/s}'
    },

    /**
     * Component event method which is fired when the component
     * is initials. The component is initials when the user
     * want to create a new customer or edit an existing customer
     * @return void
     */
    initComponent:function () {
        var me = this;
        me.registerEvents();
        me.title = me.snippets.title;
        me.salutationData = [
            ['mr', me.snippets.salutation.mr],
            ['ms', me.snippets.salutation.ms]
        ];

        me.items = me.createShippingForm();

        me.callParent(arguments);
    },

    /**
     * Registers the component event "copyAddress" which is fired
     * by the "copy data" button.
     * @return void
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the "copy data" button
             * which is placed in the shipping field set on top.
             *
             * @event
             * @param [Ext.form.Panel] form - The form panel
             */
            'copyAddress',
            /**
             * Fired when the user changes his country. Used to fill the state box
             * @param field
             * @param newValue
             */
            'countryChanged'
        );
    },

    /**
     * Creates the three containers for the field set
     * to display the form fields in two columns.
     *
     * @return [Array] Contains the left and right container
     */
    createShippingForm:function () {
        var leftContainer, rightContainer, topContainer,  me = this;

        topContainer = Ext.create('Ext.container.Container', {
            columnWidth: 1,
            border: false,
            cls: Ext.baseCSSPrefix + 'field-set-container ' + Ext.baseCSSPrefix + 'copy-billing-container',
            items: me.createShippingFormTop()
        });

        leftContainer = Ext.create('Ext.container.Container', {
            columnWidth:.5,
            border:false,
            layout:'anchor',
            cls: Ext.baseCSSPrefix + 'field-set-container',
            defaults:{
                anchor:'100%',
                labelWidth:150,
                minWidth:250,
                xtype:'textfield'
            },
            items: me.createShippingFormLeft()
        });

        rightContainer = Ext.create('Ext.container.Container', {
            columnWidth:.5,
            border:false,
            layout:'anchor',
            cls: Ext.baseCSSPrefix + 'field-set-container',
            defaults:{
                anchor:'100%',
                labelWidth:100,
                xtype:'textfield'
            },
            items: me.createShippingFormRight()
        });

        return [ topContainer, leftContainer, rightContainer ];
    },

    /**
     * Creates the top container fo the shipping field set which contains the
     * "Transfer data" button to copy the billing address into the shipping address
     * @return [Array] - Contains the button description and the button to copy the billing address into the shipping address
     */
    createShippingFormTop: function() {
        var me = this;

        return [
            {
                xtype: 'container',
                anchor: '100%',
                border: false,
                cls: Ext.baseCSSPrefix + 'copy-billing-label',
                html: me.snippets.copyBilling
            },
            {
                xtype: 'button',
                iconCls: 'sprite-blue-document-copy',
                cls: Ext.baseCSSPrefix + 'copy-billing-button',
                text:me.snippets.copyButton,
                action: 'copy-data',
                handler:function () {
                    var form = me.up('form');
                    me.fireEvent('copyAddress', form);
                }
            }
        ];
    },

    /**
     * Creates the left container of the shipping field set.
     *
     * @return [Array] Contains the different form fields of the left container
     */
    createShippingFormLeft:function () {
        var me = this;
        me.countryCombo = Ext.create('Ext.form.field.ComboBox', {
            triggerAction:'all',
            editable:false,
            name:'shipping[countryId]',
            fieldLabel:me.snippets.country,
            forceSelection: true,
            valueField:'id',
            anchor:'100%',
            labelWidth:150,
            queryMode: 'local',
            minWidth:250,
            displayField:'name',
            listeners: {
                change: function(field, newValue, oldValue) {
                    me.fireEvent('countryChanged', field, newValue, me.countryStateCombo);
                }
            }
        });

        me.countryStateCombo = Ext.create('Ext.form.field.ComboBox', {
            name:'shipping[stateId]',
            action: 'shippingStateId',
            forceSelection: true,
            fieldLabel:me.snippets.state,
            valueField: 'id',
            displayField: 'name',
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
            name:'shipping[salutation]',
            fieldLabel:me.snippets.salutation.label,
            valueField:'text',
            displayField:'snippet',
            mode:'local',
            editable:false,
            store:new Ext.data.SimpleStore({
                fields:['text', 'snippet'], data:me.salutationData
            })
        }, {
            name:'shipping[firstName]',
            fieldLabel:me.snippets.firstName
        }, {
            name:'shipping[lastName]',
            fieldLabel:me.snippets.lastName
        }, {
            name:'shipping[street]',
            fieldLabel:me.snippets.street
        }, {
            name:'shipping[additionalAddressLine1]',
            fieldLabel:me.snippets.additionalAddressLine1
        }, {
            name:'shipping[additionalAddressLine2]',
            fieldLabel:me.snippets.additionalAddressLine2
        }, {
            name:'shipping[zipCode]',
            fieldLabel:me.snippets.zipCode
        }, {
            name:'shipping[city]',
            fieldLabel:me.snippets.city
        },
            me.countryStateCombo,
            me.countryCombo,
        {
            name:'shipping[company]',
            fieldLabel:me.snippets.company
        }, {
            name:'shipping[department]',
            fieldLabel:me.snippets.department
        }];
    },

    /**
     * Creates the right container of the shipping field set.
     *
     * @return [Array] Contains the different form fields for the right container
     */
    createShippingFormRight:function () {
        var me = this;
        return [{
            name:'shippingAttribute[text1]',
            fieldLabel:me.snippets.text1.label,
            supportText: me.snippets.text1.support,
            helpTitle: me.snippets.text1.helpTitle,
            helpText: me.snippets.text1.helpText
        }, {
            name:'shippingAttribute[text2]',
            fieldLabel:me.snippets.text2.label,
            supportText: me.snippets.text2.support,
            helpTitle: me.snippets.text2.helpTitle,
            helpText: me.snippets.text2.helpText
        }, {
            name:'shippingAttribute[text3]',
            fieldLabel:me.snippets.text3.label,
            supportText: me.snippets.text3.support,
            helpTitle: me.snippets.text3.helpTitle,
            helpText: me.snippets.text3.helpText
        }, {
            name:'shippingAttribute[text4]',
            fieldLabel:me.snippets.text4.label,
            supportText: me.snippets.text4.support,
            helpTitle: me.snippets.text4.helpTitle,
            helpText: me.snippets.text4.helpText
        }, {
            name:'shippingAttribute[text5]',
            fieldLabel:me.snippets.text5.label,
            supportText: me.snippets.text5.support,
            helpTitle: me.snippets.text5.helpTitle,
            helpText: me.snippets.text5.helpText
        }, {
            name:'shippingAttribute[text6]',
            fieldLabel:me.snippets.text6.label,
            supportText: me.snippets.text6.support,
            helpTitle: me.snippets.text6.helpTitle,
            helpText: me.snippets.text6.helpText
        }];
    }
});
//{/block}
