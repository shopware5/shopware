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
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/order/main}

/**
 * Shopware Controller - Order list backend module
 *
 * The shopware order list controller handles all actions around the order list.
 * Listeners:
 *  - Search field => Fires when the user insert a search string into the search field to filter the grid store
 */
//{block name="backend/order/controller/list"}
Ext.define('Shopware.apps.Order.controller.List', {

    /**
     * Defines that this component is a extJs controller extension
     * @string
     */
    extend: 'Ext.app.Controller',

    /**
     * Contains all snippets for the component
     * @object
     */
    snippets: {
        successTitle: '{s name=message/save/success_title}Successful{/s}',
        failureTitle: '{s name=message/save/error_title}Error{/s}',
        customerDoesNotExistAnymore: '{s name=customer_does_not_exist_anymore}{/s}',
        overwriteOrder: {
            title: '{s name=overwriteOrder/title}Overwrite most recent changes{/s}',
            message: '{s name=overwriteOrder/message}The order has been changed by another user in the meantime. To prevent overwriting these changes, saving the order was aborted. To show these changes, please close the order and re-open it.<br /><br /><b>Do you want to overwrite the latest changes?</b>{/s}',
        },
        warningTitle:'{s name=message/save/warning_title}Warning{/s}',
        changeStatus: {
            successMessage: '{s name=message/status/success}The status has been changed successfully{/s}',
            failureMessage: '{s name=message/status/failure}An error has occurred while changing the status.{/s}'
        },
        deleteOrder: {
            title: '{s name=delete_order/title}Delete selected order{/s}',
            message: '{s name=delete_order/message}Are you sure you want to delete the selected order: {/s}',
            successTitle: '{s name=delete_order/success_message}Successful{/s}',
            successMessage: '{s name=delete_order/success_title}Order has been removed{/s}',
            failureTitle: '{s name=delete_order/error_title}Failure{/s}',
            failureMessage: '{s name=delete_order/error_message}An error has occurred while deleting:{/s}'
        },
        deletePosition: {
            title: '{s name=delete_position/title}Delete selected order position{/s}',
            message: '{s name=delete_position/message}Are you sure you want to delete the selected order: {/s}',
            successTitle: '{s name=delete_position/success_message}Successful{/s}',
            successMessage: '{s name=delete_position/success_title}Order position has been removed{/s}',
            failureTitle: '{s name=delete_position/error_title}Error{/s}',
            failureMessage: '{s name=delete_position/error_message}An error has occurred while deleting:{/s}'
        },

        growlMessage: '{s name=growlMessage}Order{/s}'

    },

    /**
     * all references to get the elements by the applicable selector
     */
    refs: [
        { ref: 'orderListGrid', selector: 'order-list-main-window order-list' },
        { ref: 'orderPositionGrid', selector: 'order-list-main-window order-position-grid' },
        { ref: 'orderReceiptGrid', selector: 'order-list-main-window order-list-navigation order-document-list' },
    ],

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     *
     * @return void
     */
    init: function() {
        var me = this;

        me.control({
            'order-list-main-window order-list': {
                deleteOrder: me.onDeleteOrder,
                openCustomer: me.onOpenCustomer,
                showBatch: me.onShowBatch,
                selectionchange: me.onSelectionChange,
                saveOrder: me.onSaveOrder
            },
            'order-position-grid': {
                openArticle: me.onOpenArticle,
                deletePosition: me.onDeletePosition
            }
        });

        me.callParent(arguments);
    },

    /**
     * Called when the user clicks the 'Update' button in the rowEditor
     * saves the current order with its changes
     * @param editor
     * @param event
     * @param store
     */
    onSaveOrder: function(editor, event, store) {
        var me = this,
            record, rawData,
            grid = me.getOrderListGrid();

        record = store.getAt(event.rowIdx);
        if (record == null) {
            return;
        }

        record.save({
            callback: function(data, operation) {
                var records = operation.getRecords(),
                    record = records[0],
                    proxyReader = record.getProxy().getReader(),
                    rawData = proxyReader.rawData;

                if (operation.success === true) {
                    Shopware.Notification.createGrowlMessage(me.snippets.successTitle, me.snippets.changeStatus.successMessage, me.snippets.growlMessage);
                    if (rawData && rawData.warning) {
                        Shopware.Notification.createGrowlMessage(me.snippets.warningTitle, rawData.warning, me.snippets.growlMessage);
                    }

                    record.set('invoiceAmount', rawData.data.invoiceAmount);

                    //Check if a status mail is created and create a model with the returned data and open the mail window.
                    if (!Ext.isEmpty(rawData.data.mail) && !Ext.isEmpty(proxyReader.jsonData.data.mail.content)) {
                        var mail = Ext.create('Shopware.apps.Order.model.Mail', rawData.data.mail);
                        me.showOrderMail(mail, record)
                    }

                    store.load();
                } else {
                    Shopware.Notification.createGrowlMessage(me.snippets.failureTitle, me.snippets.changeStatus.failureMessage + '<br> ' + rawData.message, me.snippets.growlMessage);

                    if (rawData.overwriteAble) {
                        Ext.MessageBox.confirm(me.snippets.overwriteOrder.title, me.snippets.overwriteOrder.message, function(response) {
                            if (response === 'yes') {
                                record.set('changed', rawData.data.changed);
                                me.onSaveOrder(editor, event, store);
                            }
                        });
                    }
                }
                grid.getSelectionModel().deselectAll(false);
            }
        });
    },

    /**
     * Creates the batch window with a special mode, so only the mail panel will be displayed.
     *
     * @param mail
     * @param { Ext.data.Model } record
     */
    showOrderMail: function(mail, record) {
        var me = this,
            documentTypeStore = Ext.create('Shopware.apps.Order.store.DocType');

        documentTypeStore.load({
            callback: function() {
                me.mainWindow = me.getView('mail.Window').create({
                    listStore: me.subApplication.getStore('Order'),
                    mail: mail,
                    record: record,
                    order: record,
                    documentTypeStore: documentTypeStore
                }).show();
            }
        });
    },

    onShowBatch: function(grid) {
        var me = this;
        var records = grid.getSelectionModel().getSelection();
        //open the order listing window
        me.mainWindow = me.getView('batch.Window').create({
            orderStatusStore: grid.orderStatusStore,
            paymentStatusStore: grid.paymentStatusStore,
            records: records
        }).show();
    },

    onSelectionChange: function(selectionModel, selected, eOpts) {
        var me = this,
            positionGrid = me.getOrderPositionGrid(),
            receiptStore = Ext.create('Ext.data.Store', { model: 'Shopware.apps.Order.model.Receipt' }),
            positionStore = Ext.create('Ext.data.Store', { model: 'Shopware.apps.Order.model.Position' }),
            receiptGrid = me.getOrderReceiptGrid(),
            record = null;

        if (Ext.isArray(selected)) {
            record = selected[selected.length - 1];
        } else {
            record = selected;
        }

        if (record instanceof Ext.data.Model && record.getReceipt() instanceof Ext.data.Store) {
            receiptStore = record.getReceipt();
        }
        if (record instanceof Ext.data.Model && record.getPositions() instanceof Ext.data.Store) {
            positionStore = record.getPositions();
        }

        receiptGrid.reconfigure(receiptStore);
        positionGrid.reconfigure(positionStore);

    },

    /**
     * Event listener method which fired when the user clicks the delete button
     * in the order list to delete a single order.
     * @param record
     * @return void
     */
    onDeleteOrder: function(record) {
        var me = this,
            store = me.subApplication.getStore('Order'),
            message = me.snippets.deleteOrder.message + ' ' + record.get('number'),
            title = me.snippets.deleteOrder.title;

        // we do not just delete - we are polite and ask the user if he is sure.
        Ext.MessageBox.confirm(title, message, function(response) {
            if (response !== 'yes') {
                return;
            }
            record.destroy({
                callback: function(data, operation) {
                    var records = operation.getRecords(),
                        record = records[0],
                        rawData = record.getProxy().getReader().rawData;

                    if (operation.success === true) {
                        Shopware.Notification.createGrowlMessage(me.snippets.deleteOrder.successTitle, me.snippets.deleteOrder.successMessage, me.snippets.growlMessage);
                    } else {
                        Shopware.Notification.createGrowlMessage(me.snippets.deleteOrder.failureTitle, me.snippets.deleteOrder.failureMessage + ' ' + rawData.message, me.snippets.growlMessage);
                    }
                    store.load();
                }
            });
        });
    },

    /**
     * Event listener method which fired when the user clicks the customer button
     * in the order list to show the customer detail page.
     *
     * @param [Ext.data.Model] record - The row record
     */
    onOpenCustomer: function(record) {
        if (!record.getCustomer().first()) {
            Shopware.Notification.createGrowlMessage(this.snippets.failureTitle, this.snippets.customerDoesNotExistAnymore);
            return;
        }

        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Customer',
            action: 'detail',
            params: {
                customerId: record.get('customerId')
            }
        });
    },

    /**
     * Event listener method which is fired when the user clicks the
     * action column icon to open the article detail page of the order position article.
     *
     * @param [Ext.data.Model] record - The row record
     */
    onOpenArticle: function(record) {
        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Article',
            action: 'detail',
            params: {
                articleId: record.get('articleId')
            }
        });
    },

    /**
     * Event listener method which is fired when the user clicks the
     * action column icon to delete a single order position.
     *
     * @param [Ext.data.Model] record - The row record
     * @param [Ext.data.Store] store - The position store
     * @param [object] options - can contain an callback function
     * @callback Return parameters order, position, store
     */
    onDeletePosition: function(position, store, options) {
        var me = this,
            orderPositionGrid = me.getOrderPositionGrid(),
            order = me.subApplication.getStore('Order').getById(position.get('orderId')),
            message = me.snippets.deletePosition.message + ' ' + order.get('number'),
            title = me.snippets.deletePosition.title;

        // we do not just delete - we are polite and ask the user if he is sure.
        Ext.MessageBox.confirm(title, message, function(response) {
            if (response !== 'yes') {
                return;
            }
            if (orderPositionGrid) {
                orderPositionGrid.setLoading(true);
            }
            position.destroy({
                params: {
                    orderID: position.get('orderId'),
                    changed: order.get('changed') ? order.get('changed').toISOString() : null,
                },
                callback: function(data, operation) {
                    if (orderPositionGrid) {
                        orderPositionGrid.setLoading(true);
                    }

                    var records = operation.getRecords(),
                        record = records[0],
                        rawData = record.getProxy().getReader().rawData;

                    if (operation.success === true) {
                        Shopware.Notification.createGrowlMessage(me.snippets.deletePosition.successTitle, me.snippets.deletePosition.successMessage, me.snippets.growlMessage);

                        store.remove(position);
                        order.set('invoiceAmount', rawData.data.invoiceAmount);
                        order.set('changed', rawData.data.changed);

                        orderPositionGrid.setLoading(false);

                        if (options !== Ext.undefined && Ext.isFunction(options.callback)) {
                            options.callback(order);
                        }

                    } else {
                        Shopware.Notification.createGrowlMessage(me.snippets.deletePosition.failureTitle, me.snippets.deletePosition.failureMessage + ' ' + rawData.message, me.snippets.growlMessage);

                        if (rawData.overwriteAble) {
                            Ext.MessageBox.confirm(me.snippets.overwriteOrder.title, me.snippets.overwriteOrder.message, function (response) {
                                if (response === 'yes') {
                                    order.set('changed', rawData.data.changed);
                                    me.onDeletePosition(position, store, options);
                                } else {
                                    store.rejectChanges();
                                    orderPositionGrid.setLoading(false);
                                }
                            });
                        }
                    }
                }
            });
        });
    }

});
//{/block}
