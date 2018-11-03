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
 * @package    CanceledOrder
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/canceled_order/controller/main}

/**
 * Shopware Controller - Order Controller
 * controls the order grid and its event
 */
//{block name="backend/canceled_order/controller/order"}
Ext.define('Shopware.apps.CanceledOrder.controller.Order', {

    extend: 'Ext.app.Controller',
    requires: [ 'Shopware.apps.CanceledOrder.controller.Main' ],

    refs: [
        { ref: 'orderGrid', selector: 'canceled-order-tabs-order-orders' },
        { ref: 'orderPositionGrid', selector:'canceled-order-view-order-position' },
        { ref: 'detailView', selector : 'canceled-order-view-order-detail' }
    ],

    snippets : {
        deleteOrder : {
            title: '{s name=deleteOrder/title}Are you sure?{/s}',
            message: '{s name=deleteOrder/message}Do you want to delete these order(s)?{/s}'
        },
        askForReason: {
            title: '{s name=askForReason/title}Ask the customer for reason?{/s}',
            message: '{s name=askForReason/message}Do you want to send the customer a mail asking for the reason for canceling the order?{/s}'
        },
        sendVoucher: {
            title: '{s name=sendVoucher/title}Send the customer a voucher?{/s}',
            message: '{s name=sendVoucher/message}Do you want to send the customer a voucher?{/s}'
        },
        convertOrder: {
            title: '{s name=convertOrder/title}Convert order?{/s}',
            message: '{s name=convertOrder/message}Do you want to convert this order to a regular order?{/s}',
            refreshInStockTitle: '{s name=convertOrder/refreshInStockTitle}Refresh stocks{/s}',
            refreshInStockMessage: '{s name=convertOrder/refreshInStockMessage}Do you want to refresh the stocks?{/s}',
            successTitle: '{s name=convertOrderSuccess/title}Order converted{/s}',
            successMessage: '{s name=convertOrderSuccess/message}Converted the order successfully.{/s}',
            withInStockSuccessMessage: '{s name=convertOrderSuccess/withInStockMessage}Converted the order and refreshed the in stock successfully.{/s}',
            showOrderMessage: '{s name=convertOrderSuccess/showOrderMessage}The order was converted. Do you want to open this order now?{/s}'
        }
    },

    /**
     * A template method that is called when your application boots. It is called before the Application's
     * launch function is executed so gives a hook point to run any code before your Viewport is created.
     */
    init: function() {
        var me = this;

        me.control({
            'canceled-order-tabs-order-orders': {
                deleteOrder: me.onDeleteOrder,
                openOrder: me.onOpenOrder,
                convertOrder: me.onConvertOrder,
//                itemClicked : me.onShowDetails,
                selectionchange : me.onSelectionChange
            },
            'canceled-order-view-order-detail': {
                sendVoucher: me.onSendVoucher,
                askForReason: me.onAskForReason
            },
            'canceled-order-toolbar': {
                search: me.onSearch,
                filter: me.onFilter,
                dateEnter: me.onFilter
            },
            'canceled-order-view-order-position': {
                openArticle: me.onOpenArticle
            }
        });

        me.callParent(arguments);
    },

    /**
     * Callback function for the Button "Ask for Reason" on the detail view
     * Will askt the user for conformation
     */
    onAskForReason: function() {
        var me = this;

        Ext.MessageBox.confirm(me.snippets.askForReason.title, me.snippets.askForReason.message, function (response) {
            if ( response !== 'yes' ) {
                return;
            }
            me.contactUser('sCANCELEDQUESTION');
        });

    },

    /**
     * Callback function for the ComboBox in the Detail view
     * * Will askt the user for conformation
     * @param combo
     * @param record
     */
    onSendVoucher: function(combo, record) {
        var me = this,
            detailView = me.getDetailView(),
            record = record[0],
            voucherId = record.get('id');

        Ext.MessageBox.confirm(me.snippets.sendVoucher.title, me.snippets.sendVoucher.message, function (response) {
            if ( response !== 'yes' ) {
                detailView.voucherCombo.setValue('{s name=selectVoucher}Select Voucher{/s}');
                return;
            }
            me.contactUser('sCANCELEDVOUCHER', voucherId);
        });

    },

    /**
     * Called when the selection in the order store changed
     * Will show/hide the components in the details view depending on the number of selected records
     * If one record was selected, the updateDetails() function will be called
     * @param record
     */
    onSelectionChange: function(sm, selections) {
        var me = this,
            detailView = me.getDetailView(),
            combo = detailView.voucherCombo,
            button = detailView.askReasonButton,
            info = detailView.infoLabel;

        if (selections.length <= 0) {
            info.setText('{s name=detailsNoOrderSelected}No order selected{/s}');
            button.hide();
            combo.hide();
            return;
        }
        if (selections.length > 1) {
            info.setText('{s name=detailsMoreThanOneOrderSelected}More than one order selected{/s}');
            button.hide();
            combo.hide();
            return;
        }

        me.updatePosition(selections[0]);
        me.updateDetails(selections[0]);
    },

    /**
     * Updates the position grid
     *
     * @param selected
     */
    updatePosition: function(selected) {
        var me = this,
            positionGrid = me.getOrderPositionGrid(),
            positionStore,
            record = null;

        if (Ext.isArray(selected)) {
            record = selected[selected.length-1];
        } else {
            record = selected;
        }

        if (record instanceof Ext.data.Model && record.getPositions() instanceof Ext.data.Store) {
            positionStore = record.getPositions();
        }

        positionGrid.reconfigure(positionStore);
    },

    /**
     * Updates the details view and hides/shows components depending on current selected order
     * @param record
     */
    updateDetails: function(record) {
        var me = this,
            store = me.subApplication.canceledOrderVoucher,
            detailView = me.getDetailView(),
            comment = record.get('comment'),
            combo = detailView.voucherCombo,
            button = detailView.askReasonButton,
            info = detailView.infoLabel;

        if(comment == "") {
            info.setText("{s name=yourOptions}You can ask your customer for a reason or send him a voucher{/s}");
            combo.show();
            button.show();
        }else if(comment == "Frage gesendet") {
            info.setText("{s name=reasonMailAlreadySent}A 'Ask for reason' mail was already sent to this customer{/s}");
            button.hide();
            combo.show();
        }else {
            button.hide();
            combo.hide();
            info.setText("{s name=voucherAlreadySent}A voucher was already sent to this customer{/s}");
        }
        Ext.apply(store.getProxy().extraParams, {
            id: record.get('id')
        });
        store.load();
    },

    /**
     * Callback function for openArticle. Will open the Article subApplication.
     *
     * @param record
     * @return
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
     * Send a mail to a user who canceled his order. Called by onAskForReason and onSendVoucher
     * todo: Right now this only works for one user at time. Being able to send a mail to multiple user would be nice.
     * @param combo
     * @param record
     * @return void
     */
    contactUser: function(template, voucherId) {
        var me        = this,
            orderGrid = me.getOrderGrid(),
            selectionModel = orderGrid.getSelectionModel(),
            selectedOrderRecords = selectionModel.getSelection(),
            detailView = me.getDetailView();

        if (selectedOrderRecords.length <= 0) {
            return;
        }
        if (selectedOrderRecords.length > 1) {
            Shopware.Notification.createGrowlMessage('{s name=noMultipleContactsPossibleTitle}Not possible{/s}', '{s name=noMultipleContactsPossibleTitleMessage}Currently it is not possible to contact multiple users at the same time.{/s}');
            return;
        }

        selectedOrderRecords = selectedOrderRecords[0];

        if(!selectedOrderRecords.getCustomer() || !selectedOrderRecords.getCustomer().first()) {
            return;
        }

        var customer = selectedOrderRecords.getCustomer().first(),
            mail = customer.get('email'),
            customerId = selectedOrderRecords.get('customerId'),
            orderId = selectedOrderRecords.get('id');

        // do the actual ajax query to send the mail
        Ext.Ajax.request({
            url: '{url controller=CanceledOrder action="sendCanceledQuestionMail"}',
            method: 'POST',
            params: {
                mail: mail,
                voucherId: voucherId,
                customerId: customerId,
                orderId: orderId,
                template: template
            },
            success: function(response) {
                var status = Ext.decode(response.responseText);
                if (status.success) {
                    Shopware.Notification.createGrowlMessage('{s name=mailSentTitle}Mail was sent{/s}', '{s name=mailSentMessage}You sent a mail to the customer{/s}');
                    me.subApplication.canceledOrderStore.reload();
                    detailView.voucherCombo.setValue('{s name=selectVoucher}Select Voucher{/s}');
                } else {
                    Shopware.Notification.createGrowlMessage('{s name=mailNotSent}Mail was not sent{/s}', status.message);
                    detailView.voucherCombo.setValue('{s name=selectVoucher}Select Voucher{/s}');
                }
            }
        });
    },

    /**
     * Callback function for convertOrder. Will convert a given order into a regular
     * order and ask the user if he wants to show the new order in the order window
     * @param record
     */
    onConvertOrder: function(record) {
        var me = this;

        //Confirm message to ask the user if he is sure to convert the order
        Ext.MessageBox.confirm(me.snippets.convertOrder.title, me.snippets.convertOrder.message, function (response) {
            if ( response !== 'yes' ) {
                return;
            }

            //Confirm message to refresh the stock of for the articles in the converted order, if "no" the order will be created
            //but the stock will not be refreshed
            Ext.MessageBox.confirm(me.snippets.convertOrder.refreshInStockTitle, me.snippets.convertOrder.refreshInStockMessage, function (responseInStock) {
                if ( responseInStock === 'yes' ) {
                    me.convertOrderRequest(record, true);
                } else {
                    me.convertOrderRequest(record, false);
                }

            });
        });
    },

    /**
     * sends the request to convert the order
     *
     * @param record
     * @param shouldRefreshInStock
     */
    convertOrderRequest: function(record, shouldRefreshInStock) {
        var me = this,
            orderGrid = me.getOrderGrid(),
            orderStore = orderGrid.getStore();

        // do the actual request to convert the order
        Ext.Ajax.request({
            url: '{url controller=CanceledOrder action="convertOrder"}',
            method: 'POST',
            params: {
                orderId: record.get('id'),
                refreshInStock: shouldRefreshInStock ? 1 : 0
            },
            success: function(response) {
                var status = Ext.decode(response.responseText);
                if (status.success) {
                    if (shouldRefreshInStock) {
                        Shopware.Notification.createGrowlMessage(me.snippets.convertOrder.successTitle, me.snippets.convertOrder.withInStockSuccessMessage);
                    } else {
                        Shopware.Notification.createGrowlMessage(me.snippets.convertOrder.successTitle, me.snippets.convertOrder.successMessage);
                    }

                    orderStore.reload();
                    Ext.MessageBox.confirm(me.snippets.convertOrder.successTitle, me.snippets.convertOrder.showOrderMessage, function (response) {
                        if ( response !== 'yes' ) {
                            return;
                        }
                        me.onOpenOrder(record);
                    });
                } else {
                    Shopware.Notification.createGrowlMessage('{s name=convertError}Order was not converted{/s}', status.message);
                }
            }
        });
    },

    /**
     * Callback function for deleteOrder. Will ask for conformation.
     *
     * @param record
     * @return
     */
    onDeleteOrder: function(orders) {
        var me = this,
            orderGrid = me.getOrderGrid(),
            store = orderGrid.getStore();

        if(orders.length === 0) {
            return;
        }

        Ext.MessageBox.confirm(me.snippets.deleteOrder.title, me.snippets.deleteOrder.message, function (response) {
            if ( response !== 'yes' ) {
                return;
            }
            store.remove(orders);
            store.save();
        });
    },

    /**
     * Callback function for openOrder. Will open the articles subApplication
     *
     * @param record
     * @return
     */
    onOpenOrder: function(record) {
        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Order',
            params: {
                orderId:record.get('id')
            }
        });
    },


    /**
     * Little helper functions that returns the current panel of a tab.
     * @return tab.Panel
     */
    getCurrentTab: function() {
        var me = this,
            tabPanel = me.subApplication.mainWindow.tabPanel,
            active = tabPanel.getActiveTab(),
            title = active.internalTitle;

        switch(title) {
            case 'baskets':
                var tab = active.tabPanel.getActiveTab();
                return tab ;
                break;
            default:
                return active ;
                break;
        }
    },

    /**
     * Called when the filter button is clicked in order to filter the list by date
     *
     * @param [Date] fromDate
     * @param [Date] toDate
     */
    onFilter: function (fromDate, toDate) {
        var me = this,
            active = this.getCurrentTab(),
            title = active.internalTitle,
            store;

        switch(title) {
            case 'orders':
                store = me.subApplication.canceledOrderStore;
                break;
            case 'statistics':
                store = me.subApplication.canceledOrderStatistic;
                break;
            case 'overview':
                store = me.subApplication.canceledOrderBasket;
                break;
            case 'articles':
                store = me.subApplication.canceledOrderArticles;
                break;
            case 'viewports':
                store = me.subApplication.canceledOrderViewports;
                break;
            default:
                return;
        }

        Ext.apply(store.getProxy().extraParams, {
            fromDate: fromDate,
            toDate: toDate
        });
        store.load();

    },

    /**
     * Callback function for search events
     * As we use a default toolbar, search events from those toolbars are handled here
     *
     * @param field
     * @return
     */
    onSearch: function(field){
        if(!field) {
            return;
        }

        var me = this,
            searchString = Ext.String.trim(field.getValue()),
            tabPanel = me.subApplication.mainWindow.tabPanel,
            active = me.getCurrentTab(),
            title = active.internalTitle;

            switch(title) {
                case 'orders':
                    var store = me.subApplication.canceledOrderStore;
                    break;
                case 'statistics':
                    var store = me.subApplication.canceledOrderStatistic;
                    break;
                case 'overview':
                    var store = me.subApplication.canceledOrderBasket;
                    break;
                case 'articles':
                    var store = me.subApplication.canceledOrderArticles;
                    break;
                case 'viewports':
                    var store = me.subApplication.canceledOrderViewports;
                    break;
                case 'default':
                    return;
            }

            //scroll the store to first page
            store.currentPage = 1;

            //If the search-value is empty, reset the filter
            if ( searchString.length === 0 ) {
                store.clearFilter();
            } else {
                //This won't reload the store
                store.filters.clear();
                //Loads the store with a special filter
                store.filter('filter', searchString);
            }
    }

});
//{/block}
