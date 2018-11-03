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
 * @package    Voucher
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name=backend/voucher/view/voucher}

/**
 * Shopware UI - Voucher detail main window.
 *
 * Displays all Detail Voucher Information
 */
// {block name="backend/voucher/view/voucher/base_configuration"}
Ext.define('Shopware.apps.Voucher.view.voucher.BaseConfiguration', {
    extend: 'Ext.form.Panel',
    cls: 'shopware-form',
    alias: 'widget.voucher-voucher-base_configuration',
    title: '{s name=detail_general/win_title/configuration}Configuration{/s}',
    autoShow: true,
    autoScroll: true,
    bodyPadding: 10,

    // Text for the ModusCombobox
    modusData: [
        [ 0, '{s name=detail_general/mode_combo_box/general}General{/s}' ],
        [ 1, '{s name=detail_general/mode_combo_box/individual}Individual{/s}' ]
    ],
    discountModeData: [
        [ 0, '{s name=detail_general/discount_combo_box/absolute}Absolute{/s}' ],
        [ 1, '{s name=detail_general/discount_combo_box/percental}Percental{/s}' ]
    ],
    voucherID: 0,

    /**
     * Initialize the Shopware.apps.Voucher.view.voucher.base_configuration and defines the necessary
     * default configuration
     */
    initComponent: function () {
        var me = this;

        if (me.record) {
            me.voucherID = me.record.data.id;
            me.description = me.record.data.description;
        }
        me.generalFieldset = Ext.create('Ext.form.FieldSet', {
            title: '{s name=detail_general/field_set/configuration}Voucher configuration{/s}',
            layout: 'column',
            defaults: {
                columnWidth: 0.5,
                bodyStyle: 'padding-right: 20px'
            },
            items: me.createGeneralForm()
        });

        me.restrictionFieldset = Ext.create('Ext.form.FieldSet', {
            title: '{s name=detail_general/field_set/limit}Limit voucher{/s}',
            layout: 'column',
            defaults: {
                columnWidth: 0.5,
                bodyStyle: 'padding-right: 20px'
            },
            items: me.createRestrictionForm()
        });

        me.attributeForm = Ext.create('Shopware.attribute.Form', {
            table: 's_emarketing_vouchers_attributes'
        });

        me.items = [ me.generalFieldset, me.restrictionFieldset, me.attributeForm ];
        me.dockedItems = [{
            dock: 'bottom',
            xtype: 'toolbar',
            ui: 'shopware-ui',
            cls: 'shopware-toolbar',
            items: me.createFormButtons()
        }];
        me.callParent(arguments);

        if (me.record) {
            me.loadRecord(me.record);
            me.attributeForm.loadAttribute(me.record.get('id'));
            me.getForm().isValid();
        }
    },

    /**
     * creates the general form and layout
     *
     * @return [Array] computed form
     */
    createGeneralForm: function () {
        var leftContainer, rightContainer, me = this;

        leftContainer = Ext.create('Ext.container.Container', {
            defaults: {
                anchor: '95%',
                labelWidth: 155,
                minWidth: 250,
                labelStyle: 'font-weight: 700;',
                xtype: 'textfield'
            },
            layout: 'anchor',
            items: me.createGeneralFormLeft()
        });

        rightContainer = Ext.create('Ext.container.Container', {
            defaults: {
                anchor: '95%',
                labelWidth: 155,
                labelStyle: 'font-weight: 700;',
                xtype: 'textfield'
            },
            layout: 'anchor',
            items: me.createGeneralFormRight()
        });

        return [ leftContainer, rightContainer ];
    },

    /**
     * creates the restriction form and all included voucher restriction fields
     */
    createRestrictionForm: function () {
        var leftContainer, rightContainer, bottomContainer, me = this;

        leftContainer = Ext.create('Ext.container.Container', {
            defaults: {
                anchor: '95%',
                labelWidth: 155,
                minWidth: 250,
                labelStyle: 'font-weight: 700;',
                xtype: 'textfield'
            },
            layout: 'anchor',
            items: me.createRestrictionFormLeft()
        });

        rightContainer = Ext.create('Ext.container.Container', {
            defaults: {
                anchor: '95%',
                labelWidth: 155,
                minWidth: 250,
                labelStyle: 'font-weight: 700;',
                xtype: 'textfield'
            },
            layout: 'anchor',
            items: me.createRestrictionFormRight()
        });

        return [ leftContainer, rightContainer ];
    },

    /**
     * creates all fields for the general form on the left side
     */
    createGeneralFormLeft: function () {
        var me = this;
        return [
            {
                fieldLabel: '{s name=detail_general/field/description}Description{/s}',
                name: 'description',
                allowBlank: false,
                required: true,
                enableKeyEvents: true
            },
            {
                xtype: 'combobox',
                name: 'modus',
                fieldLabel: '{s name=detail_general/field/mode}Voucher code mode{/s}',
                store: new Ext.data.SimpleStore({
                    fields: [ 'id', 'text', 'tip' ], data: this.modusData
                }),
                valueField: 'id',
                displayField: 'text',
                mode: 'local',
                editable: false,
                helpText: '{s name=detail_general/field/mode/help}<b>Mode - General</b><br />A general voucher with one voucher code will be created<br /><br /><b>Mode - Individual</b><br />Creates as many individual voucher codes as entered in field: [Number of units]. Each customer gets an individual voucher code.{/s}'
            },
            {
                fieldLabel: '{s name=detail_general/field/number_of_units}Number of units{/s}',
                name: 'numberOfUnits',
                xtype: 'numberfield',
                allowDecimals: false,
                allowBlank: false,
                hideTrigger: true,
                keyNavEnabled: false,
                mouseWheelEnabled: false,
                required: true
            },
            {
                xtype: 'numberfield',
                fieldLabel: '{s name=detail_general/field/minimum_charge}Minimum charge{/s}',
                name: 'minimumCharge',
                allowBlank: false,
                hideTrigger: true,
                keyNavEnabled: false,
                mouseWheelEnabled: false,
                helpText: '{s name=detail_general/field/minimum_charge/help}The minimum basket value for this voucher{/s}',
                validator: function (value) {
                    var form = me.getForm();
                    if (form.getFieldValues().percental != 1) {
                        if (value < form.getFieldValues().value) {
                            return '{s name=detail_general/field/minimum_charge/error/minimum_charge_bigger_than_value}The minimum charge has to be bigger then the voucher value{/s}';
                        }
                    }
                    return true;
                }
            },
            {
                xtype: 'combobox',
                name: 'percental',
                fieldLabel: '{s name=detail_general/field/discharge}Discharge{/s}',
                store: new Ext.data.SimpleStore({
                    fields: [ 'id', 'text' ], data: this.discountModeData
                }),
                valueField: 'id',
                displayField: 'text',
                mode: 'local',
                editable: false,
                helpText: '{s name=detail_general/field/percental/help}The value of the voucher will be reduced perceptually or absolutely{/s}'
            },
            {
                xtype: 'checkbox',
                fieldLabel: '{s name=detail_general/field/shipping_free}Free of shipping costs{/s}',
                inputValue: 1,
                uncheckedValue: 0,
                name: 'shippingFree',
                helpText: '{s name=detail_general/field/shipping_free/help}The order will be free of shipping costs{/s}'
            }
        ];
    },

    /**
     * creates all fields for the general form on the right side
     */
    createGeneralFormRight: function () {
        var me = this;
        return [
            {
                xtype: 'hidden',
                name: 'id'
            },
            {
                fieldLabel: '{s name=detail_general/field/order_number}Order number{/s}',
                supportText: '{s name=detail_general/field/order_code/help}This is the order number of the voucher{/s}',
                name: 'orderCode',
                allowBlank: false,
                required: true,
                enableKeyEvents: true,
                checkChangeBuffer: 500,
                vtype: 'remote',
                validationUrl: '{url controller="voucher" action="validateOrderCode"}',
                validationRequestParam: me.voucherID,
                validationErrorMsg: '{s name=detail_general/error_message/used_order_number}This order number is already in use{/s}',
                validateOnChange: true,
                validateOnBlur: false
            },
            {
                fieldLabel: '{s name=detail_general/field/code}Code{/s}',
                name: 'voucherCode',
                allowBlank: false,
                required: true,
                enableKeyEvents: true,
                checkChangeBuffer: 500,
                helpText: '{s name=detail_general/field/voucher_code/help}The voucher code of generally valid vouchers{/s}',
                vtype: 'remote',
                validationUrl: '{url controller="voucher" action="validateVoucherCode"}',
                validationRequestParam: me.voucherID,
                validationErrorMsg: '{s name=detail_general/error_message/used_voucher_code}The voucher code is already in use{/s}',
                validateOnChange: true,
                validateOnBlur: false
            },
            {
                fieldLabel: '{s name=detail_general/field/value}Value{/s}',
                name: 'value',
                xtype: 'numberfield',
                allowBlank: false,
                hideTrigger: true,
                keyNavEnabled: false,
                mouseWheelEnabled: false,
                required: true,
                helpText: '{s name=detail_general/field/value/help}This is the percentual or absolute value that will be deducted based on the [Discharge] field.{/s}',
                validator: function (value) {
                    var form = me.getForm();
                    var validationValue = value.replace(Ext.util.Format.decimalSeparator, '.');

                    if (form.getFieldValues().percental == 1) {
                        return (validationValue <= 100 && validationValue > 0) ? true : '{s name=detail_general/field/value/error/percental}The value has to be in the range of 1 to 100%{/s}';
                    } else {
                        return (validationValue >= 0) ? true : '{s name=detail_general/field/value/error/bigger_zero}The Value has to be >= 0{/s}';
                    }
                }
            },
            {
                fieldLabel: '{s name=detail_general/field/redeemable_per_customer}Number of redeemable vouchers per customer{/s}',
                name: 'numOrder',
                xtype: 'numberfield',
                allowDecimals: false,
                allowBlank: false,
                hideTrigger: true,
                keyNavEnabled: false,
                mouseWheelEnabled: false
            },
            {
                xtype: 'combobox',
                name: 'taxConfig',
                fieldLabel: '{s name=detail_general/field/tax_configuration}Tax configuration{/s}',
                store: me.taxStore,
                valueField: 'id',
                displayField: 'name',
                helpText: '{s name=detail_general/field/tax_config/help}<b>Standard</b><br />Standard tax configuration of the basket.<br /><br /><b>auto-detection</b><br />Automatically detects the highest tax rate of the basket<br /><br /><b>tax-free</b><br />No tax will be calculated{/s}'
            }
        ];
    },

    /**
     * creates all fields for the restriction form on the left side
     */
    createRestrictionFormLeft: function () {
        var factory = Ext.create('Shopware.attribute.SelectionFactory');
        var articleStore = factory.createEntitySearchStore('Shopware\\Models\\Article\\Detail');

        return [
            {
                xtype: 'datefield',
                fieldLabel: '{s name=detail_general/field/valid_from}From{/s}',
                name: 'validFrom',
                submitFormat: 'd.m.Y',
                id: 'valid_from_date',
                vtype: 'daterange',
                endDateField: 'valid_to_date'
            },
            {
                xtype: 'datefield',
                fieldLabel: '{s name=detail_general/field/valid_to}Till{/s}',
                name: 'validTo',
                submitFormat: 'd.m.Y',
                id: 'valid_to_date',
                vtype: 'daterange',
                startDateField: 'valid_from_date'
            },
            {
                xtype: 'shopware-form-field-product-grid',
                separator: ';',
                store: articleStore,
                searchStore: articleStore,
                fieldLabel: '{s name=detail_general/field/restrict_on_articles}Restrict to articles{/s}',
                name: 'restrictArticles'
            },
            {
                xtype: 'checkbox',
                inputValue: 1,
                uncheckedValue: 0,
                fieldLabel: '{s name=detail_general/field/discount_on_defined_articles_or_supplier}Define discount{/s}',
                boxLabel: '{s name=detail_general/box_label/discount_on_defined_articles_or_supplier}Discount on defined articles/supplier{/s}',
                name: 'strict',
                helpText: '{s name=detail_general/field/discount_on_defined_articles_or_supplier/help}This voucher is only valid for the items defined above.{/s}'
            }
        ];
    },
    /**
     * creates all fields for the restriction form on the right side
     */
    createRestrictionFormRight: function () {
        var factory = Ext.create('Shopware.attribute.SelectionFactory');
        var customerStreamStore = factory.createEntitySearchStore('Shopware\\Models\\CustomerStream\\CustomerStream');

        return [
            {
                xtype: 'shopware-form-field-single-selection',
                name: 'customerGroup',
                fieldLabel: '{s name=detail_general/field/restrict_on_customer_group}Restrict to customer group{/s}',
                store: factory.createEntitySearchStore('Shopware\\Models\\Customer\\Group'),
                valueField: 'id',
                displayField: 'name'
            },
            {
                xtype: 'shopware-form-field-single-selection',
                name: 'shopId',
                fieldLabel: '{s name=detail_general/field/restrict_on_shop}Restrict to subshop{/s}',
                store: factory.createEntitySearchStore('Shopware\\Models\\Shop\\Shop'),
                valueField: 'id',
                displayField: 'name'
            },
            {
                xtype: 'shopware-form-field-single-selection',
                name: 'bindToSupplier',
                fieldLabel: '{s name=detail_general/field/restrict_on_supplier}Restrict to supplier{/s}',
                store: factory.createEntitySearchStore('Shopware\\Models\\Article\\Supplier'),
                minChars: 0,
                valueField: 'id',
                displayField: 'name'
            },
            {
                xtype: 'shopware-form-field-customer-stream-grid',
                fieldLabel: '{s name="restrict_customer_streams"}{/s}',
                store: customerStreamStore,
                searchStore: customerStreamStore,
                height: 180,
                maxHeight: 180,
                name: 'customerStreamIds'
            }
        ];
    },

    /**
     * creates the form buttons cancel and save
     */
    createFormButtons: function() {
        var me = this;
        return ['->',
            {
                text: '{s name=detail_general/button/cancel}Cancel{/s}',
                cls: 'secondary',
                scope: me,
                handler: function () {
                    me.up('window').destroy();
                }
            },
            {
                text: '{s name=detail_general/button/save}Save{/s}',
                action: 'save',
                cls: 'primary'
            }
        ];
    }
});
// {/block}
