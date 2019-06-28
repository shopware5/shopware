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
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Controller - Voucher backend module
 *
 * Detail controller of the voucher module. Handles all action around to
 * edit or create a voucher. The detail controller knows the different field sets
 * to display the voucher data in the form panel.
 */
//{namespace name=backend/voucher/view/voucher}
//{block name="backend/voucher/controller/voucher"}
Ext.define('Shopware.apps.Voucher.controller.Voucher', {
    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend:'Ext.app.Controller',
    /**
     * all references to get the elements by the applicable selector
     */
    refs:[
        { ref:'grid', selector:'voucher-voucher-list' },
        { ref:'codesGrid', selector:'voucher-code-list' },
        { ref:'formModusComboBox', selector:'voucher-voucher-base_configuration combobox[name=modus]' },
        { ref:'textFieldVoucherCode', selector:'voucher-voucher-base_configuration textfield[name=voucherCode]' },
        { ref:'hiddenFieldId', selector:'voucher-voucher-base_configuration hidden[name=id]' },
        { ref:'voucherBaseConfiguration', selector:'window voucher-voucher-base_configuration' },
        { ref:'numberFieldRedeemablePerCustomer', selector:'voucher-voucher-base_configuration numberfield[name=numOrder]' },
        { ref:'numberFieldVoucherCount', selector:'voucher-voucher-base_configuration numberfield[name=numberOfUnits]' },
        { ref:'attributeForm', selector:'voucher-voucher-window shopware-attribute-form' }
    ],

    /**
     * Contains all snippets for the controller
     */
    snippets: {
        copyFromSelectedVoucherTitle: '{s name=message/copyFromSelectedVoucherTitle}Copy of{/s}',
        confirmDeleteSingleVoucherTitle: '{s name=message/confirmDeleteSingleVoucherTitle}Delete this voucher{/s}',
        confirmDeleteSingleVoucher: '{s name=message/confirmDeleteSingleVoucher}Are you sure you want to delete the chosen voucher ([0])?{/s}',
        confirmDeleteMultipleVoucher: '{s name=message/confirmDeleteMultipleVoucher}[0] vouchers selected. Are you sure you want to delete the selected vouchers?{/s}',
        deleteSingleVoucherSuccess: '{s name=message/deleteSingleVoucherSuccess}The voucher has been successfully deleted{/s}',
        deleteSingleVoucherError: '{s name=message/deleteSingleVoucherError}An error has occurred while deleting the selected Voucher: {/s}',
        deleteMultipleVoucherSuccess: '{s name=message/deleteMultipleVoucherSuccess}The vouchers have been successfully deleted.{/s}',
        deleteMultipleVoucherError: '{s name=message/deleteMultipleVoucherError}An error has occured while deleting the selected vouchers: {/s}',
        onSaveVoucherSuccess: '{s name=message/onSaveVoucherSuccess}Changes saved successfully{/s}',
        onSaveVoucherError: '{s name=message/onSaveVoucherError}An error has occured while saving your changes.{/s}',
        growlMessage: '{s name=growlMessage}Voucher{/s}'
    },

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the sub-application
     *
     * @return void
     */
    init:function () {
        var me = this;

        me.control({
            'voucher-voucher-list button[action=add]':{
                click:me.onCreateVoucher
            },
            'voucher-voucher-list textfield[action=searchVoucher]':{
                change:me.onSearchVoucher
            },
            'voucher-voucher-list button[action=deleteVoucher]':{
                click:me.onDeleteMultipleVouchers
            },
            'voucher-voucher-list': {
                deleteColumn: me.onDeleteSingleVoucher,
                editColumn: me.onEditVoucher,
                duplicateColumn: me.onDuplicateVoucher
            },
            'voucher-voucher-base_configuration combobox[name=modus]': {
                select:me.onSelectModus
            },
            'voucher-voucher-window':{
                beforeclose:me.onBeforeCloseBaseConfigurationWindow
            },
            'voucher-voucher-base_configuration button[action=save]': {
                click: me.onSaveVoucher
            }
        });
    },
    /**
     * Opens the Ext.window.window which displays
     * the Ext.form.Panel to create a new voucher
     *
     * @param [object] store - the voucher detail store
     * @return void
     */
    onCreateVoucher:function () {
        var me = this,
            store = me.getStore('Detail'),
            model = Ext.create('Shopware.apps.Voucher.model.Detail');

        //reset the store to create a new voucher
        store.getProxy().extraParams = {
            voucherID:''
        };
        me.getView('voucher.Window').create({
            record: model,
            codeStore: me.getStore('Code'),
            taxStore: me.getStore('Tax')
        });

        me.getFormModusComboBox().setValue(0);
    },
    /**
     * Opens the Ext.window.window which displays
     * the Ext.form.Panel to modify an existing voucher
     *
     * @param [object]  view - The view. Is needed to get the right f
     * @param [object]  item - The row which is affected
     * @param [integer] rowIndex - The row number
     * @return void
     */
    onEditVoucher:function (view, rowIndex) {
        var me = this,
            record = me.getStore('List').getAt(rowIndex);

        me.openVoucher(record.data.id);
    },

    /**
     * Opens voucher detail with voucherId
     * @param [integer] voucherId
     * @return void
     */
    openVoucher: function (voucherId) {
        var me = this,
            store = me.getStore('Detail');

        store.getProxy().extraParams = {
            voucherID: voucherId
        };

        store.load({
            scope:this,
            callback:function (records, operation, success) {
                var record = records[0],
                    mode = record.data.modus;

                me.getView('voucher.Window').create({
                    record: record,
                    codeStore: me.getStore('Code'),
                    taxStore: me.getStore('Tax')
                });

                me.prepareFields(mode);

                if (mode == 1) {
                    me.getCodesGrid().enable();
                }
            }
        });
    },
    /**
     * Opens the Ext.window.window which displays
     * the Ext.form.Panel to duplicate an existing voucher
     *
     * @param [object]  view - The view. Is needed to get the right f
     * @param [object]  item - The row which is affected
     * @param [integer] rowIndex - The row number
     * @return void
     */
    onDuplicateVoucher:function (view, rowIndex) {
        var me = this,
            store = me.getStore('Detail'),
            record = me.getStore('List').getAt(rowIndex);

        store.getProxy().extraParams = {
            voucherID:record.data.id
        };

        store.load({
            scope:this,
            callback:function (records, operation, success) {
                var record = records[0],
                    mode = record.data.modus;

                //prepend with 'copy of ...' since we're duplicating
                record.data.description = me.snippets.copyFromSelectedVoucherTitle + record.data.description;
                //delete this because this fields have to be unique
                record.data.voucherCode = '';
                record.data.orderCode = '';
                //delete id to save a new voucher with the data of the duplicated one
                record.data.id = '';

                store.getProxy().extraParams = {
                    voucherID:''
                };

                me.getView('voucher.Window').create({
                    record: record,
                    codeStore: me.getStore('Code'),
                    taxStore: me.getStore('Tax')
                });

                me.prepareFields(mode);
            }
        });
    },
    /**
     * Filters the grid with the passed search value to find the right voucher
     *
     * @param field
     * @param value
     * @return void
     */
    onSearchVoucher:function (field, value) {
        var me = this,
            searchString = Ext.String.trim(value),
            store = me.getStore('List');
        store.filters.clear();
        store.currentPage = 1;
        store.filter('filter',searchString);
    },
    /**
     * Event listener which deletes a single voucher based on the passed
     * grid (e.g. the grid store) and the row index
     *
     * @param [object] grid - The grid on which the event has been fired
     * @param [integer] rowIndex - Position of the event
     * @return void
     */
    onDeleteSingleVoucher:function (grid, rowIndex) {
        var me = this,
                store = grid.getStore(),
                voucherGrid = me.getGrid(),
                record = store.getAt(rowIndex);
        // we do not just delete - we are polite and ask the user if he is sure.
        Ext.MessageBox.confirm(
            me.snippets.confirmDeleteSingleVoucherTitle,
            Ext.String.format(me.snippets.confirmDeleteSingleVoucher, record.get('description')), function (response) {
            if (response !== 'yes') {
                return false;
            }

            voucherGrid.setLoading(true);
            store.remove(record);
            try {
                store.save({
                    callback: function (batch) {
                        var rawData = batch.proxy.getReader().rawData;
                        if (rawData.success === true) {
                            Shopware.Notification.createGrowlMessage('',me.snippets.deleteSingleVoucherSuccess, me.snippets.growlMessage);
                        }
                        voucherGrid.setLoading(false);
                        store.load();

                    }
                });
            } catch (e) {
                Shopware.Notification.createGrowlMessage('',me.snippets.deleteSingleVoucherError + e.message, me.snippets.growlMessage);
            }

        });

    },

    /**
     * Event listener method which deletes multiple vouchers
     *
     * @return void
     */
    onDeleteMultipleVouchers:function () {
        var me = this,
                grid = me.getGrid(),
                sm = grid.getSelectionModel(),
                selection = sm.getSelection(),
                store = grid.getStore(),
                noOfElements = selection.length;

        // Get the user to confirm the delete process
        Ext.MessageBox.confirm(
                me.snippets.confirmDeleteSingleVoucherTitle,
                Ext.String.format(me.snippets.confirmDeleteMultipleVoucher, noOfElements), function (response) {
            if (response !== 'yes') {
                return false;
            }
            if (selection.length > 0)
                grid.setLoading(true);{
                store.remove(selection);
                store.save({
                    callback: function(batch) {
                        var rawData = batch.proxy.getReader().rawData;
                        if (rawData.success === true) {
                            Shopware.Notification.createGrowlMessage('',me.snippets.deleteMultipleVoucherSuccess, me.snippets.growlMessage);
                        } else {
                            Shopware.Notification.createGrowlMessage('',me.snippets.deleteMultipleVoucherError + rawData.errorMsg, me.snippets.growlMessage);
                        }
                        grid.setLoading(false);
                        store.load();
                    }
                });
            }
        })
    },

    /**
     * Event listener method which will be fired when the user
     * clicks the "save"-button in the edit-window.
     *
     * @event click
     * @param [object] btn - pressed Ext.button.Button
     * @return void
     */
    onSaveVoucher: function () {
        var me = this,
            formPanel = me.getVoucherBaseConfiguration(),
            form = formPanel.getForm(),
            attributeForm = me.getAttributeForm(),
            record = form.getRecord();
        //check if all required fields are valid
        if (!form.isValid()) {
            return;
        }

        var values = form.getFieldValues();

        form.updateRecord(record);

        //to save empty values
        record.set('shopId', values.shopId);
        record.set('customerGroup', values.customerGroup);
        record.set('bindToSupplier', values.bindToSupplier);

        record.save({
            callback: function (self,operation) {
                if (operation.success) {
                    var response = Ext.JSON.decode(operation.response.responseText);
                    var data = response.data;

                    me.getHiddenFieldId().setValue(data.id);
                    attributeForm.saveAttribute(data.id, function() {
                        attributeForm.loadAttribute(data.id);
                    });

                    Shopware.Notification.createGrowlMessage('',me.snippets.onSaveVoucherSuccess, me.snippets.growlMessage);
                    if (self.data.modus) {
                        me.getCodesGrid().enable();
                    }
                } else {
                    Shopware.Notification.createGrowlMessage('',me.snippets.onSaveVoucherError, me.snippets.growlMessage);
                    me.getStore("List").load();
                }
            }
        });
    },
    /**
     * just reloads the voucher grid to keep it up to date
     *
     * @event click
     * @param [object] btn - pressed Ext.button.Button
     * @return void
     */
    onBeforeCloseBaseConfigurationWindow:function () {
        this.getStore("List").load();
    },
    /**
     * the voucher modus combobox was changed
     * react on the event by calling the according helper function
     *
     * @param combo
     * @param selectedRecords
     */
    onSelectModus:function (combo, selectedRecords) {
        var me = this,
            selectedRecord = selectedRecords[0],
            mode = selectedRecord.data.id;

        me.prepareFields(mode);
    },

    /**
     * helper function to prepare all fields according to the actual mode
     *
     * @param mode
     */
    prepareFields: function(mode) {
        var me = this;

        if (mode == 0) {
            //universal voucher

            //show code field and set it to required
            me.getTextFieldVoucherCode().show();
            me.getTextFieldVoucherCode().required = true;
            me.getTextFieldVoucherCode().allowBlank = false;

            //show redeemable field and set it to required
            me.getNumberFieldRedeemablePerCustomer().show();
            me.getNumberFieldRedeemablePerCustomer().required = true;
            me.getNumberFieldRedeemablePerCustomer().allowBlank = false;

            //disable the individual-codes tab
            me.getCodesGrid().disable();
        }
        else {
            //voucher with individual codes

            //hide code field and set it to not-required
            me.getTextFieldVoucherCode().hide();
            me.getTextFieldVoucherCode().setValue('');
            me.getTextFieldVoucherCode().required = false;
            me.getTextFieldVoucherCode().allowBlank = true;

            //hide redeemable field and set it to required
            me.getNumberFieldRedeemablePerCustomer().hide();
            me.getNumberFieldRedeemablePerCustomer().setValue('');
            me.getNumberFieldRedeemablePerCustomer().required = false;
            me.getNumberFieldRedeemablePerCustomer().allowBlank = true;
        }
    }
});
//{/block}
