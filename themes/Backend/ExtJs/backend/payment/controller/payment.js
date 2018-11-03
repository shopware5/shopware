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
 * @package    Payment
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/payment/payment}

/**
 * Shopware Controller - Payment list backend module
 *
 * Payment controller of the payment module.
 * It handles all actions made in the module.
 * Listeners:
 *  - Create button  => Creates a new payment.
 *  - Tab click => Changes the active tab and automatically selects the countries/subshops.
 *  - Save button => Saves the payment with the edited information.
 *  - Delete button => Deletes the selected payment.
 */

//{block name="backend/payment/controller/payment"}
Ext.define('Shopware.apps.Payment.controller.Payment', {

    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend:'Ext.app.Controller',

    refs: [
        { ref: 'mainWindow', selector: 'payment-main-window' },
        { ref: 'surchargeGrid', selector: 'payment-main-surcharge' },
        { ref: 'attributeForm', selector: 'payment-main-window shopware-attribute-form' }
    ],

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * @return void
     */
    init:function(){
        var me = this;

        me.control({
            'payment-main-window payment-main-tree':{
                itemclick:me.onItemClick
            },
            'payment-main-window':{
                savePayment:me.onSavePayment,
                changeTab:me.onChangeTab
            },

            'payment-main-tree':{
                deletePayment: me.onDeletePayment,
                createPayment:me.onCreatePayment
            }

        });
        me.callParent(arguments);
    },

    /**
     * This function deletes the selected payment, if the payment is not a default-payment
     * @param tree Contains the tree
     */
    onDeletePayment: function(tree, btn){
        var selection = tree.getSelectionModel().getSelection(),
            win = tree.up('window'),
            saveButton = win.down('button[name=save]'),
            countryGrid = win.down('payment-main-countrylist'),
            paymentStore = this.subApplication.paymentStore,
            tabPanel = win.down('tabpanel');


        if(selection[0].data.source == 1){
            selection[0].destroy({
                callback:function(data, operation){
                    var records = operation.getRecords(),
                        record = records[0],
                        rawData = record.getProxy().getReader().rawData;

                    if(operation.success){
                        Shopware.Notification.createGrowlMessage('{s name=delete_growlMessage_subject}Delete payment{/s}', "{s name=delete_growlMessage_content}The payment has been successfully deleted.{/s}", '{s name=payment_title}{/s}');
                    }else{
                        Shopware.Notification.createGrowlMessage('{s name=delete_growlMessage_subject}Delete payment{/s}', rawData.errorMsg.errorInfo[2], '{s name=payment_title}{/s}');
                    }

                    paymentStore.load();
                    tabPanel.disable(true);
                    btn.disable(true);
                    saveButton.disable(true);
                }
            });
        }
    },


    /**
     * Is fired, when the tab is changed
     * Automatically selects the countries/shops and sets the surcharge
     * @param tabPanel Contains the tabpanel
     * @param newTab Contains the new tab, which was clicked now
     * @param oldTab Contains the old tab, which was opened before the new tab
     * @param formPanel Contains the general formpanel
     */
    onChangeTab:function(tabPanel, newTab, oldTab, formPanel){
        var grid = newTab.items.items[0],
            record = formPanel.getRecord(),
            recordStore;

        switch (grid.xtype) {
            case 'payment-main-countrylist':
                recordStore = record.getCountriesStore;
                break;
            case 'payment-main-subshoplist':
                recordStore = record.getShopsStore;
                break;
            case 'payment-main-formpanel':
            case 'payment-main-surcharge':
            default:
                return;
        }

        var store = grid.getStore().load({
            callback: function(){
                var matches = [];
                //Selects each country and sets the surcharge
                if(recordStore){
                    Ext.each(recordStore.data.items, function(item){
                        var tmpRecord = store.getById(item.get('id'));
                        matches.push(tmpRecord);
                        tmpRecord.data.surcharge = item.get('surcharge');
                    });
                    grid.getSelectionModel().select(matches);
                }
            }
        });
    },

    /**
     * Is fired, when the "create"-button is pressed
     * @param btn Contains the create-button
     */
    onCreatePayment:function(btn){
        var win = btn.up('window'),
            tabPanel = win.down('tabpanel'),
            formPanel = win.down('form'),
            paymentModel = Ext.create('Shopware.apps.Payment.model.Payment'),
            gridToolBar = win.down('toolbar[name=gridToolBar]'),
            btnSave = gridToolBar.down('button[name=save]');

        paymentModel.set('source', 1);

        tabPanel.setDisabled(false);
        tabPanel.setActiveTab(0);
        formPanel.loadRecord(paymentModel);
        this.getAttributeForm().loadAttribute(null);
        btnSave.enable(true);
    },

    /**
     * Is fired, when the user wants to update a payment
     * Sets the surcharges and the selections and saves them
     * @param generalForm Contains the general form-panel
     * @param countryGrid Contains the grid with all countries
     * @param subShopGrid Contains the grid with all subShops
     * @param surchargeGrid Contains the grid with all surcharges
     */
    onSavePayment:function(generalForm, countryGrid, subShopGrid, surchargeGrid){
        var record = generalForm.getRecord(),
            win = generalForm.up('window'),
            tree = win.down('treepanel'),
            paymentStore = tree.getStore(),
            tabPanel = win.down('tabpanel'),
            me = this;

        generalForm.getForm().updateRecord(record);

        var surchargeStore = Ext.clone(record['getCountriesStore']),
            surchargeString = "";

        //Creates a string with the surcharges and the iso of the countries
        Ext.each(surchargeStore.data.items, function(item){
            if(item.data.surcharge) {
                surchargeString = surchargeString + item.data.iso + ":" + item.data.surcharge + ";";
            }
        });
        surchargeString = surchargeString.slice(0, surchargeString.length - 1);
        record.data.surchargeString = surchargeString;

        var countryStore = record['getCountriesStore'];

        var subshops = subShopGrid.getSelectionModel().getSelection(),
            subshopStore = record['getShopsStore'];

        //If the tab is activated at least once, so changes could be made
        if(subShopGrid.rendered) {
            subshopStore.removeAll();
            subshopStore.add(subshops);
        }

        //If the tab is activated at least once, so changes could be made
        if(countryGrid.rendered) {
            var updated = surchargeGrid.getStore().getUpdatedRecords(),
                selection = countryGrid.getSelectionModel().getSelection();

            // Merge updated records to the main countyStore
            if(updated.length) {
                Ext.each(updated, function(record) {
                    var id = record.get('id');

                    Ext.each(selection, function(tmpRecord) {
                        if(tmpRecord.get('id') === id) {
                            tmpRecord.set('surcharge', record.get('surcharge'));

                            // Clear dirty state
                            tmpRecord.dirty = false;
                            tmpRecord.modified = false;
                        }
                    });

                });
            }
            countryStore.removeAll();
            countryStore.add(selection);
        }

        record.save({
            callback: function(data, operation){
                var records = operation.getRecords(),
                    record = records[0],
                    rawData = record.getProxy().getReader().rawData;

                if(operation.success){
                    me.getAttributeForm().saveAttribute(record.get('id'));
                    paymentStore.load();

                    //tabPanel, newTab, oldTab, formPanel
                    me.onChangeTab(tabPanel, tabPanel.getActiveTab(), '', generalForm);
                    Shopware.Notification.createGrowlMessage('{s name=update_growl_message_subject}Update payment{/s}', "{s name=update_growl_message_content}The payment was successfully updated.{/s}", '{s name=payment_title}{/s}');
                } else {
                    Shopware.Notification.createGrowlMessage('{s name=update_growl_message_subject}Update payment{/s}', rawData.errorMsg, '{s name=payment_title}{/s}');
                }
            }
        });
    },

    /**
     * Function to load all data into the grids and formpanels
     *
     * @param [Ext.view.View] view
     * @param [Ext.data.Model] record The clicked record
     */
    onItemClick:function(view, record) {
        var win = view.up('window'),
            tabPanel = win.tabPanel,
            form = win.generalForm,
            treeToolBar = win.down('toolbar[name=treeToolBar]'),
            gridToolBar = win.down('toolbar[name=gridToolBar]'),
            btnSave = gridToolBar.down('button[name=save]'),
            btnDelete = treeToolBar.down('button[name=delete]'),
            surchargeGrid = win.down('payment-main-surcharge');

        surchargeGrid.reconfigure(Ext.clone(record.getCountriesStore));

        if(record.get('source') == 1){
            btnDelete.enable();
        }else{
            btnDelete.disable();
        }
        tabPanel.setDisabled(false);
        btnSave.enable(true);
        tabPanel.setActiveTab(0);

        form.loadRecord(record);
        this.getAttributeForm().loadAttribute(record.get('id'));
    }
});
//{/block}
