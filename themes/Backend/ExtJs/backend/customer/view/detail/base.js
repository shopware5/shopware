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
 * Shopware UI - Customer detail page
 *
 * The base field set contains the base data of the customer
 * which is stored in the base model and filled over the s_user table
 *
 */
// {block name="backend/customer/view/detail/base"}
Ext.define('Shopware.apps.Customer.view.detail.Base', {
    /**
     * Define that the base field set is an extension of the Ext.form.FieldSet
     * @string
     */
    extend: 'Ext.form.FieldSet',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.customer-base-field-set',

    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'base-field-set',

    /**
     * Layout type for the component.
     * @string
     */
    layout: 'column',

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets: {
        title: '{s name=base/title}Base data{/s}',
        email: {
            message: '{s name=base/validate_email_message}Email address is already in use{/s}',
            label: '{s name=base/email}Email{/s}'
        },
        confirm: {
            label: '{s name=base/password_confirm}Confirm password{/s}',
            support: '{s name=base/confirm_support}Please confirm the password you have entered{/s}',
            helpTitle: '{s name=base/confirm_help_title}Password confirmation{/s}',
            helpText: '{s name=base/confirm_help_text}For security reasons, please enter the password again.{/s}'
        },
        active: {
            box: '{s name=base/active_box_label}Mark the account as active{/s}',
            field: '{s name=base/active_field_label}Active{/s}'
        },
        password: {
            label: '{s name=base/password}Password{/s}',
            support: '{s name=base/password_support}To automatically create the password, use the button on the right side.{/s}',
            button: '{s name=base/password_generate}Generate password{/s}',
            message: '{s name=base/password_error}The passwords do not match.{/s}'
        },
        group: '{s name=base/customer_group}Customer group{/s}',
        shop: '{s name=base/shop}Shop{/s}',
        number: {
            label: '{s name=base/customer_number}Customer number{/s}',
            helpTitle: '{s name=base/customer_number_help_title}Customer number generation{/s}',
            helpText: '{s name=base/customer_number_help_text}If the parameter sShopwareManagedCustomerNumbers is set to 1 in the shopware configuration, the customer number will be set automatically when the customer is saved and the field is not editable.{/s}'
        }
    },

    /**
     * Component event method which is fired when the component
     * is initials. The component is initials when the user
     * want to create a new customer or edit an existing customer
     * @return void
     */
    initComponent: function () {
        var me = this;
        me.title = me.snippets.title;
        me.registerEvents();

        me.items = me.createBaseForm();

        me.callParent(arguments);
    },

    /**
     * Registers the generatePassword event which is fired when the
     * user clicks on the generatePassword button.
     *
     * @return void
     */
    registerEvents: function () {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the button to
             * generate a new password
             *
             * @event generatePassword
             * @param [object] passwordField - Associated password field
             * @param [object] confirmField - Associated confirm password field
             */
            'generatePassword',

            /**
             * Event will be fired when the user clicks on the "unlock" button
             *
             * @event unlockCustomer
             * @param { Ext.container.Container }
             * @param { Ext.data.Model }
             */
            'unlockCustomer'
        );
    },

    /**
     * Creates the both containers for the field set
     * to display the form fields in two columns.
     *
     * @return [Array] Contains the left and right container
     */
    createBaseForm: function () {
        var leftContainer, rightContainer, me = this;

        leftContainer = Ext.create('Ext.container.Container', {
            columnWidth: 0.5,
            border: false,
            cls: Ext.baseCSSPrefix + 'field-set-container',
            layout: 'anchor',
            defaults: {
                anchor: '95%',
                labelWidth: 155,
                minWidth: 250,
                xtype: 'textfield'
            },
            items: me.createBaseFormLeft()
        });

        rightContainer = Ext.create('Ext.container.Container', {
            columnWidth: 0.5,
            border: false,
            layout: 'anchor',
            cls: Ext.baseCSSPrefix + 'field-set-container',
            defaults: {
                labelWidth: 155,
                xtype: 'textfield',
                anchor: '95%'
            },
            items: me.createBaseFormRight()
        });

        return [ leftContainer, rightContainer ];
    },

    /**
     * Creates the left container of the base field set.
     * Contains the email field and the combo boxes for
     * the shop and customer group
     *
     * @return [Array] Contains the different form field of the left container
     */
    createBaseFormLeft: function () {
        var me = this,
            pwRequired = false;

        me.customerGroupCombo = Ext.create('Ext.form.field.ComboBox', {
            triggerAction: 'all',
            queryMode: 'local',
            name: 'groupKey',
            fieldLabel: me.snippets.group,
            valueField: 'key',
            displayField: 'name',
            editable: false,
            allowBlank: false,
            anchor: '95%',
            labelWidth: 155,
            minWidth: 250
        });

        me.shopStoreCombo = Ext.create('Ext.form.field.ComboBox', {
            triggerAction: 'all',
            name: 'languageId',
            queryMode: 'local',
            fieldLabel: me.snippets.shop,
            valueField: 'id',
            displayField: 'name',
            allowBlank: false,
            editable: false,
            anchor: '95%',
            forceSelection: true,
            labelWidth: 155,
            minWidth: 250,
            listeners: {
                // When the selected job changes, validate the mail address again
                change: function(combo, newValue, oldValue, eOpts) {
                    var me = this,
                        fieldSet = me.up('fieldset');

                    fieldSet.customerMail.validationRequestParams = {
                        'param': fieldSet.record.get('id'),
                        'subshopId': newValue
                    };

                    // set oldValue to null in order to force a re-check
                    // else VType 'remote' will just return "oldValid"
                    fieldSet.customerMail.oldValue = null;
                    fieldSet.customerMail.validate();
                }
            }
        });

        me.customerMail = Ext.create('Ext.form.field.Text', {
            fieldLabel: me.snippets.email.label,
            labelWidth: 155,
            anchor: '95%',
            name: 'email',
            allowBlank: false,
            required: true,
            enableKeyEvents: true,
            vtype: 'remote',
            validationUrl: null,
            validationRequestParams: {
                'param': me.record.get('id'),
                'subshopId': me.record.get('shopId')
            },
            validationErrorMsg: me.snippets.email.message,
            listeners: {
                scope: me,
                afterrender: function(field) {
                    window.setTimeout(function() {
                        field.validationUrl = '{url action="validateEmail"}';
                    }, 750);
                }
            }
        });


        if (me.record.data.id === 0) {
            pwRequired = true;
        }

        // create the confirm password field to get access in the create password
        // button handler to pass the field to the generate password event
        me.confirmField = Ext.create('Ext.form.field.Text', {
            name: 'confirm',
            inputType: 'password',
            anchor: '95%',
            labelWidth: 155,
            allowBlank: !pwRequired,
            required: pwRequired,
            fieldLabel: me.snippets.confirm.label,
            supportText: me.snippets.confirm.support,
            helpTitle: me.snippets.confirm.helpTitle,
            helpText: me.snippets.confirm.helpText,
            validator: function (value) {
                if (Ext.String.trim(value) == Ext.String.trim(me.passwordField.getValue())) {
                    me.passwordField.clearInvalid();
                    return true;
                } else {
                    return me.snippets.password.message;
                }
            }
        });

        me.passwordContainer = me.createPasswordContainer();

        return [
            me.customerMail,
            me.customerGroupCombo,
            me.shopStoreCombo,
            {
                /* {if {config name=shopwareManagedCustomerNumbers}==1} */
                xtype: 'displayfield',
                /* {/if} */
                name: 'number',
                anchor: '95%',
                labelWidth: 155,
                fieldLabel: me.snippets.number.label,
                helpText: me.snippets.number.helpText,
                helpWidth: 360,
                helpTitle: me.snippets.number.helpTitle
            },
            me.passwordContainer, me.confirmField
        ];
    },

    /**
     * Creates the right container of the base field set.
     * Contains the active checkbox and the two text fields
     * for the password
     *
     * @return [Array] Contains the three form fields
     */
    createBaseFormRight: function () {
        var me = this,
            items = [],
            factory = Ext.create('Shopware.attribute.SelectionFactory'),
            activeCheckBox = {
                name: 'active',
                anchor: '95%',
                boxLabel: me.snippets.active.box,
                fieldLabel: me.snippets.active.field,
                xtype: 'checkbox',
                value: true,
                labelWidth: 155,
                uncheckedValue: false,
                inputValue: true
            };

        me.customerStreamSelection = Ext.create('Shopware.form.field.CustomerStreamGrid', {
            name: 'customerStreamIds',
            labelWidth: 155,
            height: 150,
            allowSorting: false,
            allowDelete: false,
            allowAdd: false,
            fieldLabel: '{s name="customer_streams"}{/s}',
            store: factory.createEntitySearchStore("Shopware\\Models\\CustomerStream\\CustomerStream"),
            searchStore: factory.createEntitySearchStore("Shopware\\Models\\CustomerStream\\CustomerStream")
        });

        items.push(activeCheckBox);
        items.push(me.customerStreamSelection);
        items.push(me.createUnlockField());

        return items;
    },

    /**
     * Creates the container for the password field and the generatePassword button.
     * @return [Ext.container.Container] - Contains the text field and the button
     */
    createPasswordContainer: function () {
        var me = this,
            pwRequired = false;

        if (me.record.data.id === 0) {
            pwRequired = true;
        }

        // create the password generation button
        me.passwordButton = Ext.create('Ext.button.Button', {
            cls: Ext.baseCSSPrefix + 'password-button',
            iconCls: 'sprite-license-key',
            action: 'create-password',
            labelWidth: 155,
            tooltip: me.snippets.password.button,
            width: 25,
            /**
             * Add button handler to fire the generatePassword event which is handled
             * in the detail controller. The detail controller generates a password and set it into the password field
             */
            handler: function () {
                me.fireEvent('generatePassword', me.passwordField, me.confirmField);
            }
        });

        // create the password field to get access in the create password
        // button handler to pass the field to the generate password event
        me.passwordField = Ext.create('Ext.form.field.Text', {
            name: 'newPassword',
            flex: 1,
            inputType: 'password',
            labelWidth: 155,
            allowBlank: !pwRequired,
            required: pwRequired,
            fieldLabel: me.snippets.password.label,
            supportText: me.snippets.password.support,
            cls: Ext.baseCSSPrefix + 'password-field',
            validateOnBlur: true,
            validateOnChange: false,
            validator: function (value) {
                if (Ext.String.trim(value) == Ext.String.trim(me.confirmField.getValue())) {
                    me.confirmField.clearInvalid();
                    return true;
                } else {
                    return me.snippets.password.message;
                }
            }
        });

        // create a container for the password field to append the generate password button
        return Ext.create('Ext.container.Container', {
            layout: 'hbox',
            anchor: '95%',
            cls: Ext.baseCSSPrefix + 'password-container',
            height: 70,
            items: [ me.passwordField, me.passwordButton ]
        });
    },

    /**
     * @returns { Ext.container.Container }
     */
    createUnlockField: function () {
        var me = this,
            disabled = true;

        if (me.record.get('lockedUntil')) {
            disabled = false;
        }

        me.unlockContainer = Ext.create('Ext.container.Container', {
            items: [
                {
                    xtype: 'displayfield',
                    fieldLabel: '{s name="base/unlock_customer/label_text"}Locked until{/s}',
                    labelStyle: 'margin-top: 0',
                    name: 'lockedUntil',
                    labelWidth: 155,
                    renderer: function (val) {
                        if (!val) {
                            return '';
                        }

                        return Ext.util.Format.date(val) + ' ' + Ext.util.Format.date(val, timeFormat)
                    }
                }, {
                    xtype: 'button',
                    text: '{s name="base/unlock_button_text"}Unlock{/s}',
                    iconCls: 'sprite-key--pencil',
                    anchor: '100%',
                    cls: 'small secondary',
                    margin: '0 0 0 160',
                    disabled: disabled,
                    handler: Ext.bind(me.onClickUnlock, me)
                }
            ]
        });

        return me.unlockContainer;
    },

    onClickUnlock: function () {
        this.fireEvent('unlockCustomer', this.unlockContainer, this.record);
    }

});
// {/block}
