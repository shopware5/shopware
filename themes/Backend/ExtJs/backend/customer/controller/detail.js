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
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name=backend/customer/view/detail}

/**
 * Shopware Controller - Customer list backend module
 * Detail controller of the customer module. Handles all action around to
 * edit or create a customer. The detail controller knows the different field sets
 * to display the customer data in the form panel.
 * Listeners:
 *  - Add button  => Display the customer detail form to create a new customer (order tab is removed)
 *  - Edit button => Display the customer detail form to edit the selected customer
 *  - Grid double click => Display the customer detail form to edit the selected customer
 *  - Save button => Saves the customer detail data which was inserted by the user
 *  - Order button => Fired from the detail window when the user clicks the perform order button to perform an order as the customer.
 *  - Copy data button => Fired from the detail window when the user clicks the "copy data" button.
 *  - Payment combo box => When the payment changed, the account fields for the bank information will be hide when the payment not equals debit
 *  - Password button => Generates a password for the customer account.
 */
// {block name="backend/customer/controller/detail"}
Ext.define('Shopware.apps.Customer.controller.Detail', {

    /**
     * Defines that this component is an extension of the extJs application controller
     * @string
     */

    extend: 'Ext.app.Controller',

    refs: [
        { ref: 'detailWindow', selector: 'customer-detail-window' },
        { ref: 'quickView', selector: 'customer-list' }
    ],

    /**
     * Contains all snippets for the controller
     * @object
     */

    snippets: {
        form: {
            errorTitle: '{s name=message/password/form/error_title}Error saving the form{/s}',
            errorMessage: '{s name=message/password/form/error_message}The field [0] is not valid{/s}'
        },
        password: {
            support: '{s name=message/password/generated_password}The generated password is:{/s}',
            successTitle: '{s name=message/password/success_title}Successfully{/s}',
            successText: '{s name=message/password/success_text}Customer [0] has been saved{/s}',
            errorTitle: '{s name=message/password/error_title}Failure{/s}',
            errorText: '{s name=message/password/error_text}There is an error occurred while saving.{/s}'
        },
        account: {
            successTitle: '{s name=message/account/success_title}Successfully{/s}',
            successText: '{s name=message/account/success_text}The account for the customer [0] has been created successfully.{/s}',
            errorTitle: '{s name=message/account/error_title}Failure{/s}',
            errorText: '{s name=message/account/error_text}There is an error occurred while saving.{/s}'
        },
        overwriteCustomer: {
            title: '{s name=overwriteCustomer/title}Overwrite most recent changes{/s}',
            message: '{s name=overwriteCustomer/message}Do you really want to overwrite the latest changes?{/s}',
        },

        unlock: {
            successTitle: '{s name=base/unlock_customer/success_title}Successfully{/s}',
            successText: '{s name=base/unlock_customer/success_text}Successfully unlocked the customer.{/s}',
            errorTitle: '{s name=base/unlock_customer/error_title}Failure{/s}',
            errorText: '{s name=base/unlock_customer/error_text}An error occurred while unlocking the customer.{/s}'
        },

        growlMessage: '{s name=message/growlMessage}Customer{/s}'
    },

    /**
     * Component event method which is fired when the component is initials.
     * Register the different events to handle all around the customer editing and creation
     * @return void
     */

    init: function () {
        var me = this;

        // listen to different events to handle the user actions.
        me.control({
            'customer-list': {
                edit: me.onEditCustomer,
                create: me.onCreateCustomer,
                itemdblclick: me.onGridDblClick
            },
            'customer-stream-preview-list': {
                edit: me.onEditCustomer,
            },
            'customer-detail-window button[action=save-customer]': {
                click: me.onSaveCustomer
            },
            'customer-base-field-set': {
                generatePassword: me.onGeneratePassword,
                unlockCustomer: me.onUnlockCustomer
            },
            'customer-debit-field-set': {
                changePayment: me.onChangePayment
            },
            'customer-additional-panel': {
                performOrder: me.onPerformOrder,
                createAccount: me.onCreateAccount
            }
        });

        Shopware.app.Application.on('address-save-successfully', function() {
            me.getDetailWindow().reloadRecord();
        });

        me.callParent(arguments);
    },

    /**
     * Event listener method which is fired when the user clicks on the "create account" button,
     * which is displayed in the additional info panel when the accountMode of the customer is set to 1 (quick order).
     * Sets the accountMode to 0 and saves the customer.
     *
     * @param [Ext.data.Model]          customer - The current record of the detail window.
     * @param [Ext.container.Container] infoView - The info view container
     * @param [Ext.XTemplate]           tpl - The view template
     * @param [Ext.button.Button]       btn - The "create account" button which has to be hide when the operation was successfully
     * @return [Ext.data.Model]
     */
    onCreateAccount: function (customer, infoView, tpl, btn) {
        var me = this;

        customer.set('accountMode', 0);
        customer.save({
            callback: function (data, operation) {
                var records = operation.getRecords(),
                    record = records[0],
                    rawData = record.getProxy().getReader().rawData;

                if (operation.success === true) {
                    var number = record.get('number');
                    Shopware.Notification.createGrowlMessage(me.snippets.account.successTitle, Ext.String.format(me.snippets.account.successText, number), me.snippets.growlMessage);

                    infoView.tpl = tpl;
                    infoView.renderTpl = tpl;
                    infoView.update(customer.data);
                    btn.hide();
                } else {
                    Shopware.Notification.createGrowlMessage(me.snippets.account.errorTitle, me.snippets.account.errorText + ' ' + rawData.message, me.snippets.growlMessage);
                }
                return customer;
            }
        });
    },

    /**
     * Event listener method which is fired when the user do a double click in
     * the grid to edit the customer.
     * @param [Ext.grid.View] view
     * @param [Ext.data.Model] record
     * @return void
     */
    onGridDblClick: function (view, record) {
        var me = this;

        /* {if {acl_is_allowed privilege=update}} */
        me.openCustomerDetailPage(record);
        /* {/if} */
    },

    openCustomerDetailPage: function(record) {
        var me = this,
            detailStore = me.subApplication.getStore('Detail');

        detailStore.getProxy().extraParams = {
            customerID: record.data.id
        };

        var win = me.getView('detail.Window').create().show();
        win.setLoading(true);

        var store = Ext.create('Shopware.apps.Customer.store.Batch');
        store.load({
            callback: function (records) {
                var storeData = records[0];
                detailStore.load({
                    callback: function (records) {
                        win.record = records[0];
                        win.createTabPanel();
                        win.setStores(storeData);
                        win.setLoading(false);
                    }
                });
            }
        });
    },

    /**
     * Event will be fired when the user change the payment combo box which
     * is displayed on bottom of the detail page.
     *
     * @param [object] value     - the new value of the combo box
     * @param [object] container - The field container which contains the debit account fields
     * @return void
     */
    onChangePayment: function (value, container) {
        var me = this;
        var window = me.getDetailWindow();
        var paymentFieldSet = window.down('customer-debit-field-set');

        if (value !== 2) {
            container.getEl().fadeOut({
                opacity: 0,
                easing: 'easeOut',
                duration: 500,
                callback: function () {
                    container.hide();
                }
            });
        } else {
            container.show();
            container.getEl().fadeIn({
                opacity: 1,
                easing: 'easeOut',
                duration: 500
            });
        }

        if (paymentFieldSet.accountNumberField != Ext.undefined) {
            paymentFieldSet.accountNumberField.allowBlank = (value !== 2);
        }
        if (paymentFieldSet.accountHolderField != Ext.undefined) {
            paymentFieldSet.accountHolderField.allowBlank = (value !== 2);
        }
        if (paymentFieldSet.bankCodeField != Ext.undefined) {
            paymentFieldSet.bankCodeField.allowBlank = (value !== 2);
        }
        if (paymentFieldSet.bankNameField != Ext.undefined) {
            paymentFieldSet.bankNameField.allowBlank = (value !== 2);
        }
    },

    /**
     * Event listener method which is fired when the user edit
     * a customer and clicks on the "Perform order" button which
     * is placed on bottom of the additional info panel.
     *
     * @param [Shopware.apps.Customer.model.Base] record - The current form record.
     * @return void
     */
    onPerformOrder: function (record) {
        window.open('{url action="performOrder"}?id=' + record.get('id') + '&__csrf_token=' + Ext.CSRFService.getToken());
    },

    /**
     * Event listener which is fired when the user edit or create a customer
     * and clicks on the "generate password" button which is placed
     * in the base field set on the right hand of the password field.
     *
     * @param passwordField
     * @param confirmField
     * @return void
     */
    onGeneratePassword: function (passwordField, confirmField) {
        var me = this,
            pool = '01234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            password = '', i = 8, length = pool.length;

        while (i--) password += pool[Math.floor(length * Math.random())];

        Ext.suspendLayouts();
        passwordField.setValue(password);
        confirmField.setValue(password);
        passwordField.clearInvalid();
        confirmField.clearInvalid();
        passwordField.supportTextEl.update(me.snippets.password.support + ' ' + password);
        Ext.resumeLayouts(true);
    },

    /**
     * Event listener method which is fired when the user click on
     * the add button in the toolbar to create a new customer.
     * @return void
     */
    onCreateCustomer: function () {
        var me = this,
            record = me.getModel('Customer').create({ active: true });

        var detailWindow = me.subApplication.getView('detail.Window').create().show();
        detailWindow.setLoading(true);

        var store = Ext.create('Shopware.apps.Customer.store.Batch');
        store.load({
            callback: function (records) {
                var storeData = records[0];
                detailWindow.record = record;
                detailWindow.createTabPanel();
                detailWindow.setLoading(false);
                detailWindow.setStores(storeData);
            }
        });
    },

    /**
     * Event listener method which is fired when the user clicks on the grid row pencil
     * to edit the customer data. Loads the detail, order and chart store with the given customer id
     *
     * @param [object] view - The view
     * @param [integer] rowIndex - On which row position has been clicked
     * @return void
     */
    onEditCustomer: function (record) {
        var me = this;

        /* {if {acl_is_allowed privilege=update}} */
        me.openCustomerDetailPage(record);
        /* {/if} */
    },

    /**
     * Event listener method which is fired when the user try to save
     * the inserted customer detail data. Merges the form record with
     * the form values to get a model with all data.
     *
     * @param btn Ext.button.Button contains the save button
     * @return void
     */
    onSaveCustomer: function (btn) {
        var me = this, number,
            win = btn.up('window'),
            form = win.down('form'),
            model = form.getRecord(),
            quickView = me.getQuickView(),
            missingField = '{s name=unknown_field}Unknown field{/s}';

        if (!form.getForm().isValid()) {
            // check which field is not valid in order to tell the user, why the customer cannot be saved
            // SW-4322
            form.getForm().getFields().each(function(f) {
                if (!f.validate()) {
                    if (f.fieldLabel) {
                        missingField = f.fieldLabel;
                    } else if (f.name) {
                        missingField = f.name;
                    }
                    Shopware.Notification.createGrowlMessage(me.snippets.form.errorTitle, Ext.String.format(me.snippets.form.errorMessage, missingField), me.snippets.growlMessage);
                    return false;
                }
            });
            return;
        }

        if (!model.get('id')) {
            var addressData = {};
            Ext.each(me.getDetailWindow().addressForm.query('field'), function(field) {
                field.submitValue = false;
                addressData[field.getName()] = field.getValue();
            });

            var addressModel = Ext.create('Shopware.apps.Customer.model.Address', addressData);
        }

        form.getForm().updateRecord(model);

        // save the model and check in the callback function if the operation was successfully
        model.save({
            callback: function (data, operation) {
                var records = operation.getRecords(),
                    record = records[0],
                    rawData = record.getProxy().getReader().rawData;

                if (operation.success === true) {
                    if (typeof addressModel !== 'undefined') {
                        addressModel.set('user_id', record.get('id'));
                        addressModel.save({
                            success: function (result) {
                                Shopware.app.Application.fireEvent('customer-address-save-successfully', me, result, win, addressModel, form);
                            }
                        });
                    }

                    number = model.get('number');

                    Shopware.Notification.createGrowlMessage(
                        me.snippets.password.successTitle,
                        Ext.String.format(me.snippets.password.successText, number),
                        me.snippets.growlMessage
                    );

                    win.attributeForm.saveAttribute(record.get('id'));

                    Shopware.app.Application.fireEvent('customer-save-successfully', me, record, win, form);

                    win.destroy();
                    quickView.getStore().load();
                } else {
                    Shopware.Notification.createGrowlMessage(me.snippets.password.errorTitle, me.snippets.password.errorText + '<br> ' + rawData.message, me.snippets.growlMessage);

                    if (rawData.overwriteAble) {
                        Ext.MessageBox.confirm(me.snippets.overwriteCustomer.title, me.snippets.overwriteCustomer.message, function (response) {
                            if (response === 'yes') {
                                record.set('changed', rawData.data.changed);
                                me.onSaveCustomer(btn);
                            }
                        });
                    }
                }
            }
        });
    },

    /**
     * @param { Ext.container.Container } unlockContainer
     * @param { Ext.data.Model } record
     */
    onUnlockCustomer: function (unlockContainer, record) {
        var me = this,
            displayField = unlockContainer.down('displayfield'),
            button = unlockContainer.down('button');

        Ext.Ajax.request({
            url: '{url action=unlockCustomer}',
            params: {
                customerId: record.get('id')
            },
            success: function (response) {
                var result = Ext.JSON.decode(response.responseText);

                if (!result.success) {
                    Shopware.Notification.createGrowlMessage(me.snippets.unlock.errorTitle, me.snippets.unlock.errorText, me.snippets.growlMessage);
                    return;
                }

                record.set('lockedUntil', null);
                record.set('failedLogins', 0);

                Shopware.Notification.createGrowlMessage(me.snippets.unlock.successTitle, me.snippets.unlock.successText, me.snippets.growlMessage);
                displayField.setValue('');
                button.setDisabled(true);
            }
        });
    }
});
// {/block}
