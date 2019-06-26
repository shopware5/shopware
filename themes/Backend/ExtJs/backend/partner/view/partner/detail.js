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
 * @package    Partner
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/partner/view/partner}
/**
 * Shopware UI - Partner detail main window.
 *
 * Displays all Detail Partner Information
 */
//{block name="backend/partner/view/partner/detail"}
Ext.define('Shopware.apps.Partner.view.partner.Detail', {
    extend: 'Ext.container.Container',
    alias: 'widget.partner-partner-detail',
    border: 0,
    title: '{s name=partner/configuration/title}Partner configuration{/s}',
    partnerId:0,
    autoScroll: true,

    //Text for the ModusCombobox
    cookieLifeTimeGrading:[
        [0, '{s name=detail_general/life_time_grading/none}None (0 Sec.){/s}'],
        [900, '{s name=detail_general/mode_combo_box/fifteen_minutes}15 Minutes (900 Sec.){/s}'],
        [1800, '{s name=detail_general/mode_combo_box/thirty_minutes}30 Minutes (1800 Sec.){/s}'],
        [3600, '{s name=detail_general/mode_combo_box/one_hour}1 Hour (3600 Sec.){/s}'],
        [7200, '{s name=detail_general/mode_combo_box/two_hours}2 Hours (7200 Sec.){/s}'],
        [14400, '{s name=detail_general/mode_combo_box/four_hours}4 Hours (14400 Sec.){/s}'],
        [28800, '{s name=detail_general/mode_combo_box/eight_hours}8 Hours (28800 Sec.){/s}'],
        [86400, '{s name=detail_general/mode_combo_box/one_day}1 Day (86400 Sec.){/s}'],
        [172800, '{s name=detail_general/mode_combo_box/two_days}2 Days (172800 Sec.){/s}'],
        [259200, '{s name=detail_general/mode_combo_box/three_days}3 Days (259200 Sec.){/s}'],
        [604800, '{s name=detail_general/mode_combo_box/one_week}1 Week (604800 Sec.){/s}'],
        [1209600, '{s name=detail_general/mode_combo_box/two_weeks}2 Weeks (1209600 Sec.){/s}'],
        [2592000, '{s name=detail_general/mode_combo_box/one_month}1 Month (2592000 Sec.){/s}'],
        [5184000, '{s name=detail_general/mode_combo_box/two_months}2 Months (5184000 Sec.){/s}'],
        [7776000, '{s name=detail_general/mode_combo_box/three_months}3 Months (7776000 Sec.){/s}'],
        [14515200, '{s name=detail_general/mode_combo_box/six_months}6 Months (15768000 Sec.){/s}'],
        [31536000, '{s name=detail_general/mode_combo_box/one_year}1 Year (31536000 Sec.){/s}'],
        [63072000, '{s name=detail_general/mode_combo_box/two_years}2 Years (63072000 Sec.){/s}'],
        [126144000, '{s name=detail_general/mode_combo_box/four_years}4 Years (126144000 Sec.){/s}'],
        [252288000, '{s name=detail_general/mode_combo_box/eight_years}8 Years (252288000 Sec.){/s}']
    ],

    /**
     * Initialize the Shopware.apps.Partner.view.partner.detail and defines the necessary
     * default configuration
     */
    initComponent:function () {
        var me = this;

        me.registerEvents();

        if(me.record){
            me.partnerId = me.record.data.id;
        }

        me.generalFieldset = Ext.create('Ext.form.FieldSet', {
            title: '{s name=detail_general/field_set/configuration}General configuration{/s}',
            bodyPadding: 10,
            layout: 'column',
            defaults: {
                columnWidth: 0.5
            },
            items:me.createGeneralForm()
        });

        me.partnerFieldset = Ext.create('Ext.form.FieldSet', {
            title: '{s name=detail_general/field_set/partner_information}Partner information{/s}',
            bodyPadding: 10,
            layout: 'column',
            defaults: {
                columnWidth: 0.5
            },
            items:me.createPartnerForm()
        });

        me.attributeForm = Ext.create('Shopware.attribute.Form', {
            table: 's_emarketing_partner_attributes'
        });

        me.attributeForm.loadAttribute(me.record.get('id'));

        me.items = [ me.generalFieldset, me.partnerFieldset, me.attributeForm ];

        me.callParent(arguments);
        if (me.record.get('customerId') > 0) {
            me.fireEvent('mapCustomerAccount', me.customerMapping, me.record.get('customerId'), 0,null);
        }
    },

    /**
     * Defines additional events which will be
     * fired from the component
     *
     * @return void
     */
    registerEvents:function () {
        this.addEvents(
                /**
                 * Event will be fired when the user changes the customer-account field
                 *
                 * @event mapCustomerAccount
                 * @param [Ext.form.field.Field] this
                 * @param [object] newValue
                 * @param [object] oldValue
                 * @param [object] eOpts
                 */
                'mapCustomerAccount'
        );

        return true;
    },


    /**
     * creates the general form and layout
     *
     * @return [Array] computed form
     */
    createGeneralForm:function () {
        var leftContainer, rightContainer, me = this;

        leftContainer = Ext.create('Ext.container.Container', {
            defaults:{
                labelWidth:180,
                minWidth:250,
                width: 400,
                labelStyle: 'font-weight: 700;',
                xtype: 'textfield'
            },
            items:me.createGeneralFormLeft()
        });

        rightContainer = Ext.create('Ext.container.Container', {
            defaults:{
                labelWidth:180,
                minWidth:250,
                width: 400,
                labelStyle: 'font-weight: 700;',
                xtype: 'textfield'
            },
            items:me.createGeneralFormRight()
        });

        return [ leftContainer, rightContainer ];
    },

    /**
     * Creates the general form and layout
     *
     * @return [Array] computed form
     */
    createPartnerForm: function () {
        var leftContainer, rightContainer, me = this;

        leftContainer = Ext.create('Ext.container.Container', {
            defaults:{
                labelWidth: 180,
                minWidth: 250,
                width: 400,
                labelStyle: 'font-weight: 700;',
                xtype: 'textfield'
            },
            items: me.createPartnerFormLeft()
        });

        rightContainer = Ext.create('Ext.container.Container', {
            defaults:{
                labelWidth: 180,
                minWidth: 250,
                width: 400,
                labelStyle: 'font-weight: 700;',
                xtype: 'textfield'
            },
            items: me.createPartnerFormRight()
        });

        return [ leftContainer, rightContainer ];
    },


    /**
     * Creates all fields for the general form on the left side
     */
    createGeneralFormLeft:function () {
        var me = this;
        me.customerMapping = Ext.create('Ext.form.field.Text',{
            fieldLabel: '{s name=detail_general/field/customer_account}Customer account{/s}',
            name: 'customerId',
            checkChangeBuffer: 800,
            labelWidth: 180,
            minWidth: 250,
            width: 400,
            labelStyle: ' font-weight: 700;',
            supportText: "{s name=detail_general/supportText/noCustomerMapped}No customer account has been linked{/s}",
            helpText: '{s name=detail_general/field/customerMapping/help}Link a customer account to enable a partner to have a look at the frontend statistics in the account section. You can link an account by entering a customer email or a customer number.{/s}',
            listeners: {
                change: function(field, newValue, oldValue, eOpts) {
                    me.fireEvent('mapCustomerAccount', field, newValue, oldValue, eOpts)
                }
            }
        });
        return [
            {
                fieldLabel: '{s name=detail_general/field/tracking_code}Tracking code{/s}',
                name: 'idCode',
                allowBlank: false,
                required: true,
                enableKeyEvents: true,
                checkChangeBuffer: 500,
                helpText: '{s name=detail_general/field/tracking_code/help}This is the individual tracking code for each partner that will be appended to the URL.{/s}',
                vtype: 'remote',
                validationUrl: '{url controller="partner" action="validateTrackingCode"}',
                validationRequestParam: me.partnerId,
                validationErrorMsg: '{s name=detail_general/error_message/used_tracking_code}The tracking code is already in use{/s}',
                validateOnChange: true,
                validateOnBlur: false
            },
            me.customerMapping,
            {
                xtype: 'checkbox',
                fieldLabel: '{s name=detail_general/field/active}Active{/s}',
                inputValue: 1,
                uncheckedValue: 0,
                name: 'active'
            }
        ];
    },

    /**
     * Creates all fields for the general form on the left side
     */
    createGeneralFormRight:function () {
        var me = this;

        return [
            {
                fieldLabel: '{s name=detail_general/field/commission}Commission in %{/s}',
                xtype: 'numberfield',
                name: 'percent',
                decimalPrecision: 2,
                maxValue: 100,
                minValue: 0,
                allowBlank: false,
                hideTrigger: true,
                keyNavEnabled: false,
                mouseWheelEnabled: false,
                required: true,
                allowDecimals: true
            },
            {
                xtype: 'combobox',
                name: 'cookieLifeTime',
                fieldLabel: '{s name=detail_general/field/cookieLifeTime}Cookie lifetime(Sec.){/s}',
                store: new Ext.data.SimpleStore({
                    fields: ['id', 'text'], data:me.cookieLifeTimeGrading
                }),
                valueField: 'id',
                displayField: 'text',
                mode: 'local'
            }
        ]
    },

    /**
     * Creates all fields for the general form on the right side
     */
    createPartnerFormLeft:function () {
        return [
            {
                fieldLabel: '{s name=detail_general/field/company}Company{/s}',
                allowBlank: false,
                required: true,
                name: 'company'
            },
            {
                fieldLabel: '{s name=detail_general/field/street}Street{/s}',
                name: 'street'
            },
            {
                fieldLabel: '{s name=detail_general/field/zip_code}Zip code{/s}',
                name: 'zipCode'
            },
            {
                fieldLabel: '{s name=detail_general/field/city}City{/s}',
                name: 'city'
            },
            {
                fieldLabel: '{s name=detail_general/field/country}Country{/s}',
                name: 'countryName'
            }

        ]
    },

    /**
     * Creates all fields for the general form on the right side
     */
    createPartnerFormRight:function () {
        return [
            {
                fieldLabel: '{s name=detail_general/field/phone}Phone{/s}',
                name: 'phone'
            },
            {
                fieldLabel: '{s name=detail_general/field/fax}Fax{/s}',
                name: 'fax'
            },
            {
                fieldLabel: '{s name=detail_general/field/email}Email{/s}',
                name: 'email'
            },
            {
                fieldLabel: '{s name=detail_general/field/web}Web{/s}',
                name: 'web'
            },
            {
                fieldLabel: '{s name=detail_general/field/profile}Profile{/s}',
                name: 'profile'
            }
        ]
    }
});
//{/block}
