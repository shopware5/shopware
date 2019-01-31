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
 * Shopware Controller - Order backend module
 *
 * The order module detail controller handles all action around the detail page.
 * It handles also the click on the pencil action column which opens the detail page.
 */
//{block name="backend/order/controller/detail"}
Ext.define('Shopware.apps.Order.controller.Detail', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend:'Ext.app.Controller',

    refs: [
        { ref: 'orderList', selector: 'order-list' },
        { ref: 'positionGrid', selector: 'order-detail-window order-position-panel' },
        { ref: 'detailWindow', selector: 'order-detail-window' }
    ],

    snippets: {
        successTitle:'{s name=message/save/success_title}Successful{/s}',
        failureTitle:'{s name=message/save/error_title}Error{/s}',
        warningTitle:'{s name=message/save/warning_title}Warning{/s}',
        internalComment: {
            successMessage: '{s name=message/internal_comment/success}Internal comment has been saved successfully for order [0]{/s}',
            failureMessage: '{s name=message/internal_comment/failure}An error has occurred while saving the internal comment for order [0].{/s}'
        },
        externalComment: {
            successMessage: '{s name=message/external_comment/success}External comment has been saved successfully for order [0]{/s}',
            failureMessage: '{s name=message/external_comment/failure}An error has occurred while saving the external comment for order [0].{/s}'
        },
        overview: {
            successMessage: '{s name=message/overview/success}Order [0] has been saved successfully{/s}',
            failureMessage: '{s name=message/overview/failure}An error has occurred while saving order [0].{/s}'
        },
        details: {
            successMessage: '{s name=message/details/success}The order addresses and payment method have been saved successfully for order [0]{/s}',
            failureMessage: '{s name=message/details/failure}An error has occurred while saving the order details for order [0].{/s}'
        },
        positions: {
            successMessage: '{s name=message/positions/success}The order position has been saved successfully{/s}',
            failureMessage: '{s name=message/positions/failure}An error has occurred while saving the order positions.{/s}',
            nonEditable: '{s name=message/positions/nonEditable}This article is not editable{/s}'
        },
        documents: {
            successMessage: '{s name=message/documents/success}Document has been created successfully{/s}',
            failureMessage: '{s name=message/documents/failure}An error has occurred while creating the order document.{/s}'
        },
        delete: {
            title: '{s name=message/delete/title}Delete selected positions{/s}',
            message: '{s name=message/delete/message}There have been marked [0] positions. Are you sure you want to delete all selected positions?{/s}',
            successMessage: '{s name=message/delete/success}The order position(s) has been removed successfully{/s}',
            failureMessage: '{s name=message/delete/failure}An error has occurred while saving the order position(s).{/s}'
        },
        convertOrder: {
            title: '{s name=convertOrder/title}Convert order?{/s}',
            message: '{s name=convertOrder/message}Do you want to convert this order to a regular order?{/s}',
            successTitle: '{s name=convertOrderSuccess/tile}Order converted{/s}'
        },
        overwriteOrder: {
            title: '{s name=overwriteOrder/title}Overwrite most recent changes{/s}',
            message: '{s name=overwriteOrder/message}The order has been changed by another user in the meantime. To prevent overwriting these changes, saving the order was aborted. To show these changes, please close the order and re-open it.<br /><br /><b>Do you want to overwrite the latest changes?</b>{/s}',
        },
        overwriteDocument: {
            title: '{s name=document/overwrite/confirmation/title}{/s}',
            message: '{s name=document/overwrite/confirmation/message}{/s}',
        },
        growlMessage: '{s name=growlMessage}Order{/s}'
    },

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     *
     * @return void
     */
    init:function () {
        var me = this;

        me.control({
            'order-list-main-window order-list': {
                showDetail: me.onShowDetail
            },
            'order-detail-window order-communication-panel': {
                saveInternalComment: me.onSaveInternalComment,
                saveExternalComment: me.onSaveExternalComment,
                updateForms: me.onUpdateDetailPage
            },
            'order-detail-window order-overview-panel': {
                saveOverview: me.onSaveOverview,
                updateForms: me.onUpdateDetailPage,
                convertOrder: me.onConvertOrder,
                openCustomer: me.onOpenCustomer
            },
            'order-billing-field-set': {
                countryChanged: me.onCountryChanged
            },

            'order-shipping-field-set': {
                countryChanged: me.onCountryChanged
            },
            'order-detail-window order-detail-panel': {
                saveDetails: me.onSaveDetails,
                updateForms: me.onUpdateDetailPage
            },
            'order-detail-window order-detail-panel order-debit-field-set': {
                changePayment:me.onChangePayment
            },
            'order-detail-window order-detail-panel order-dispatch-field-set': {
                changeDispatch: me.onChangeDispatch
            },
            'order-detail-window order-configuration-panel': {
                resetConfiguration: me.onResetConfiguration,
                createDocument: me.onCreateDocument,
                documentPreview: me.onDocumentPreview
            },
            'order-detail-window order-position-panel': {
                beforeEdit: me.onBeforeEdit,
                savePosition: me.onSavePosition,
                cancelEdit: me.onCancelEdit,
                articleNumberSelect: me.onArticleSelect,
                articleNameSelect: me.onArticleSelect,
                addPosition: me.onAddPosition,
                deleteMultiplePositions: me.onDeleteMultiplePositions,
                updateForms: me.onUpdateDetailPage
            },
            'order-detail-window tabpanel[name=main-tab]': {
                beforetabchange: me.onTabChange
            }
        });
        me.callParent(arguments);
    },

    /**
     * Event listener function,  needed for convertOrder event.
     * Will convert the current order after user's confirmation
     * @param record
     */
    onConvertOrder: function(record) {
        var me = this,
            window = me.getDetailWindow();

        Ext.MessageBox.confirm(me.snippets.convertOrder.title, me.snippets.convertOrder.message, function (response) {
            if ( response !== 'yes' ) {
                return;
            }

            // do the actual request to convert he order
            Ext.Ajax.request({
                url: '{url controller=CanceledOrder action="convertOrder"}',
                method: 'POST',
                params: {
                    orderId: record.get('id')
                },
                success: function(response) {
                    var status = Ext.decode(response.responseText);
                    if (status.success) {
                        Shopware.Notification.createGrowlMessage(me.snippets.convertOrder.successTitle);
                        Shopware.app.Application.addSubApplication({
                            name: 'Shopware.apps.Order',
                            params: {
                                orderId:record.get('id')
                            }
                        });
                        window.destroy();
                    } else {
                        Shopware.Notification.createGrowlMessage('{s name=convertError}Order was not converted{/s}', status.message);
                    }
                }
            });

        });
    },

    /**
     * Event listener function, fired when the user want to change the tab in the order detail window
     */
    onTabChange: function(panel, newTab, oldTab) {
        this.cancelPositionEdit();
    },

    /**
     * Internal helper function to cancel the open editing of the position grid.
     */
    cancelPositionEdit: function() {
        var me = this,
            positionGrid = me.getPositionGrid();

        if (positionGrid && positionGrid.rowEditor) {
            positionGrid.rowEditor.cancelEdit();
        }
    },

    /**
     * Updates the detail page after the record has been saved.
     * @param order
     * @param window
     */
    onUpdateDetailPage: function(order, window) {
        var me = this,
            overview = window.down('order-overview-panel'),
            communication = window.down('order-communication-panel'),
            history = window.down('order-history-list'),
            detail = window.down('order-detail-panel');

        overview.record = order;
        communication.record = order;
        detail.record = order;
        history.getStore().load();

        overview.detailsForm.loadRecord(order);
        overview.editForm.loadRecord(order);
        detail.loadRecord(order);
        communication.loadRecord(order);
    },

    /**
     * Event will be fired when the user start the editing of the order position grid
     *
     * @param [Ext.grid.plugin.Editing] - The row editor
     * @param [object]  - An edit event with the following properties:
     *   grid - The grid this editor is on
     *   view - The grid view
     *   store - The grid store
     *   record - The record being edited
     *   row - The grid table row
     *   column - The grid Column defining the column that initiated the edit
     *   rowIdx - The row index that is being edited
     *   colIdx - The column index that initiated the edit
     *   cancel - Set this to true to cancel the edit or return false from your handler.
     */
    onBeforeEdit: function(editor, e) {
        var me = this,
            columns = editor.editor.items.items,
            articleId = e.record.get('articleId');

            columns[1].setValue(e.record.get('articleNumber'));
            columns[2].setValue(e.record.get('articleName'));

    },

    /**
     * Event will be fired when the user clicks the update button of the row editor.
     *
     * @param [Ext.grid.plugin.Editing] - The row editor
     * @param [object]  - An edit event with the following properties:
     *   grid - The grid this editor is on
     *   view - The grid view
     *   store - The grid store
     *   record - The record being edited
     *   row - The grid table row
     *   column - The grid Column defining the column that initiated the edit
     *   rowIdx - The row index that is being edited
     *   colIdx - The column index that initiated the edit
     *   cancel - Set this to true to cancel the edit or return false from your handler.
     */
    onSavePosition: function(editor, e, order, options) {
        var me = this;

        //to convert the float value. Without this the insert value "10,55" would be converted to "1055,00"
        e.record.set('price', e.newValues.price);

        //the article suggest search is not a form field so we have to set the value manually
        e.record.set('articleName', e.newValues.articleName);
        e.record.set('articleNumber', e.newValues.articleNumber);

        //calculate the new total amount.
        if (Ext.isNumeric(e.newValues.price) && Ext.isNumeric(e.newValues.quantity)) {
            e.record.set('total', e.newValues.price * e.newValues.quantity);
            e.newValues.total = e.newValues.price * e.newValues.quantity;
        }

        e.record.save({
            params: {
                changed: order.get('changed') ? order.get('changed').toISOString() : null,
            },
            callback:function (data, operation) {
                var records = operation.getRecords(),
                    record = records[0],
                    rawData = record.getProxy().getReader().rawData;

                if ( operation.success === true ) {
                    Shopware.Notification.createGrowlMessage(me.snippets.successTitle, me.snippets.positions.successMessage, me.snippets.growlMessage);
                    order.set('invoiceAmount', rawData.invoiceAmount);
                    order.set('changed', rawData.changed);
                    if (options !== Ext.undefined && Ext.isFunction(options.callback)) {
                        options.callback(order);
                    }
                } else {
                    Shopware.Notification.createGrowlMessage(me.snippets.failureTitle, me.snippets.positions.failureMessage + '<br> ' + rawData.message, me.snippets.growlMessage);

                    if (rawData.overwriteAble) {
                        Ext.MessageBox.confirm(me.snippets.overwriteOrder.title, me.snippets.overwriteOrder.message, function (response) {
                            if (response === 'yes') {
                                order.set('changed', rawData.data.changed);
                                me.onSavePosition(editor, e, order, options);
                            } else {
                                e.store.rejectChanges();
                            }
                        });
                    }
                }
            }
        });
    },

    /**
     * Event listener method which is fired when the user cancel the row editing in the position grid
     * on the detail page. If the edited record is a new position, the position will be removed.
     *
     * @param grid
     * @param eOpts
     */
    onCancelEdit: function(grid, eOpts) {
        var record = eOpts.record,
            store = eOpts.store;

        if (!(record instanceof Ext.data.Model) || !(store instanceof Ext.data.Store)) {
            return;
        }
        if (record.get('id') === 0) {
            store.remove(record);
        }
    },

    /**
     * Event will be fired when the user search for an article number in the row editor
     * and selects an article in the drop down menu.
     *
     * @param [object] editor - Ext.grid.plugin.RowEditing
     * @param [string] value - Value of the Ext.form.field.Trigger
     * @param [object] record - Selected record
     */
    onArticleSelect: function(editor, value, record) {
        var columns = editor.editor.items.items,
            updateButton = editor.editor.floatingButtons.items.items[0];

        updateButton.setDisabled(false);
        columns[1].setValue(record.get('number'));
        columns[2].setValue(record.get('name'));

        // Update articleId for row
        editor.context.record.set('articleId', record.get('articleId'));
        editor.context.record.set('articleDetailID', record.get('id'));
    },


    /**
     * Event will be fired when the user clicks the add button to add an order position.
     *
     * @param [Ext.data.Model] record - The record of the detail page
     * @param [Ext.grid.Panel] grid - The order position grid of the detail page
     * @param [Ext.grid.plugin.RowEditing] editor - The row editor of the grid panel
     */
    onAddPosition: function(record, grid, editor) {
        var me = this;

        editor.cancelEdit();
        var position = Ext.create('Shopware.apps.Order.model.Position', {
            orderId: record.get('id'),
            quantity: 1,
            taxId: me.subApplication.getStore('Tax').getAt(0).get('id'),
            statusId: 0
        });

        grid.getStore().add(position);
        editor.startEdit(position, 0);
    },

    /**
     * Event will be fired when the user clicks the remove button to remove all selected order positions.
     *
     * @param [Ext.data.Model] order - The order of the detail page
     * @param [Ext.grid.Panel] grid - The order position grid of the detail page
     */
    onDeleteMultiplePositions: function(order, grid, options) {
        var me = this, orderId,
            store = grid.getStore(),
            selectionModel = grid.getSelectionModel(),
            positions = selectionModel.getSelection(),
            message =  Ext.String.format(me.snippets.delete.message, positions.length);

        if (positions.length === 0) {
            return;
        } else {
            orderId = positions[0].get('orderId');
        }

        // we do not just delete - we are polite and ask the user if he is sure.
        Ext.MessageBox.confirm(me.snippets.delete.title, message, function (response) {
            if ( response !== 'yes' ) {
                return;
            }
            store.remove(positions);
            store.getProxy().extraParams = {
                orderID: orderId,
                changed: order.get('changed') ? order.get('changed').toISOString() : null,
            };
            store.sync({
                callback:function (batch, operation) {
                    var rawData = batch.proxy.getReader().rawData;

                    if ( rawData.success === true ) {
                        Shopware.Notification.createGrowlMessage(me.snippets.successTitle, me.snippets.delete.successMessage, me.snippets.growlMessage);

                        order.set('invoiceAmount', rawData.data.invoiceAmount);
                        order.set('changed', rawData.data.changed);
                        if (options !== Ext.undefined && Ext.isFunction(options.callback)) {
                            options.callback(order);
                        }

                    } else {
                        Shopware.Notification.createGrowlMessage(me.snippets.failureTitle, me.snippets.delete.failureMessage + '<br> ' + rawData.message, me.snippets.growlMessage)

                        if (rawData.overwriteAble) {
                            Ext.MessageBox.confirm(me.snippets.overwriteOrder.title, me.snippets.overwriteOrder.message, function (response) {
                                if (response === 'yes') {
                                    order.set('changed', rawData.data.changed);
                                    store.rejectChanges();

                                    grid.getView().select(positions);

                                    me.onDeleteMultiplePositions(order, grid, options);
                                } else {
                                    store.rejectChanges();
                                }
                            });
                        } else {
                            store.rejectChanges();
                        }
                    }
                }
            });
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
    onChangePayment:function (value, container) {
        if ( value !== 2 ) {
            if (container.getEl()) {
                container.getEl().fadeOut({
                    opacity:0,
                    easing:'easeOut',
                    duration:500,
                    callback:function () {
                        container.hide();
                    }
                });
            } else {
                container.hide();
            }
        } else {
            container.show();
            if (container.getEl()) {
                container.getEl().fadeIn({
                    opacity:1,
                    easing:'easeOut',
                    duration:500
                });
            }
        }
    },

    /**
     * Updates the dispatch method of the edited order to correspond to the selection
     * made in the 'dispatch' combobox.
     *
     * @param Shopware.apps.Order.view.detail.Dispatch panel
     * @param int newValue
     */
    onChangeDispatch: function(panel, newValue) {
        var orderDispatch = panel.record.getDispatch(),
            newDispatch = panel.dispatchesStore.getById(newValue);

        orderDispatch.removeAll();
        orderDispatch.add(newDispatch);
    },

    /**
     * Called when the user changes the country combobox in the shipping or billing form
     *
     * @param countryCombo
     * @param newValue
     * @param countryStateCombo
     * @param record
     */
    onCountryChanged: function(countryCombo, newValue, countryStateCombo, record) {
        var store = countryStateCombo.store,
            oldStateId = record.get('stateId');
        if (newValue === null) {
            countryStateCombo.setValue(null);
            countryStateCombo.hide();
            return;
        }
        store.getProxy().extraParams = {
            countryId: newValue
        };
        countryStateCombo.show();
        store.load({
            callback: function() {
                var record = store.getById(oldStateId);
                if (store.getCount() === 0) {
                    countryStateCombo.setValue(null);
                    countryStateCombo.hide();
                    return true;
                }
                if (record instanceof Ext.data.Model) {
                    countryStateCombo.setValue(record.get('id'));
                } else {
                    countryStateCombo.setValue(null);
                }
                countryStateCombo.show();
            }
        });
    },

    /**
     * Event listener method which is fired when the user clicks the preview button
     * on the detail page in the document tab panel.
     *
     * @param [Ext.data.Model] order - The order record of the detail page
     * @param [Ext.data.Model] config - The configuration record
     * @param [Ext.container.Container] panel - The form panel
     */
    onDocumentPreview: function(order, config, panel) {
        window.open('{url action="createDocument"}' + '' +
                '?orderId=' + order.get('id') +
                '&preview=1'+ '' +
                '&taxFree=' + config.get('taxFree') +
                '&temp=1' +
                '&documentType=' + config.get('documentType') )
    },

    /**
     * Event listener method which is fired when the user clicks
     * the "create document" button on the detail page in the document tab.
     *
     * @param [Ext.data.Model]          The record of the detail page (Shopware.apps.Order.model.Order)
     * @param [Ext.data.Model]          The configuration record of the document form (Shopware.apps.Order.model.Configuration)
     * @param [Ext.container.Container] The panel
     */
    onCreateDocument: function(order, config, panel) {
        var me = this,
            documentAlreadyCreated = false;

        panel.setLoading(true);

        order.getReceiptStore.each(function (record) {
            if (record.get('typeId') === config.get('documentType')) {
                documentAlreadyCreated = true;
            }
        });

        if (documentAlreadyCreated) {
            Ext.MessageBox.confirm(
                me.snippets.overwriteDocument.title,
                me.snippets.overwriteDocument.message,
                function (clickedButton) {
                    if (clickedButton === 'no' || clickedButton === 'cancel') {
                        panel.setLoading(false);
                        return;
                    }
                    me.createDocument(order, config, panel);
                }
            );
        } else {
            me.createDocument(order, config, panel);
        }
    },

    /**
     * @param [Ext.data.Model]          The record of the detail page (Shopware.apps.Order.model.Order)
     * @param [Ext.data.Model]          The configuration record of the document form (Shopware.apps.Order.model.Configuration)
     * @param [Ext.container.Container] The panel
     */
    createDocument: function(order, config, panel) {
        var me = this,
            store = Ext.create('Shopware.apps.Order.store.Configuration');

        config.set('orderId', order.get('id'));
        store.add(config);
        store.sync({
            callback: function(batch, operation) {
                var rawData = batch.proxy.getReader().rawData;

                panel.setLoading(false);

                if ( rawData.success === true ) {
                    var data = rawData.data[0];
                    order.set(data);

                    Shopware.Notification.createGrowlMessage(me.snippets.successTitle, me.snippets.documents.successMessage, me.snippets.growlMessage);

                    var documentStore = order['getReceiptStore'],
                        documents = order.get('documents');

                    if (!documentStore) {
                        return;
                    }

                    documentStore.removeAll();
                    Ext.each(documents, function(modelData){
                        var model = Ext.create('Shopware.apps.Order.model.Receipt', modelData),
                            typeModel = Ext.create('Shopware.apps.Base.model.DocType', modelData.type);

                        var typeStore = model.getDocType();
                        typeStore.add(typeModel);
                        model['getDocTypeStore'] = typeStore;
                        documentStore.add(model);
                    });
                } else {
                    Shopware.Notification.createGrowlMessage(me.snippets.failureTitle, me.snippets.documents.failureMessage + '<br> ' + rawData.message, me.snippets.growlMessage);
                }
            }
        });
    },

    /**
     * Event listener method which is fired when the user clicks the reset button
     * in the document tab panel on the detail page to reset the document configuration.
     * @return void
     */
    onResetConfiguration: function(form, record) {
        /**
         * Usually called by the Ext.data.Store to which this model instance has been joined.
         * Rejects all changes made to the model instance since either creation, or the last commit operation.
         * Modified fields are reverted to their original values.
         */
        record.reject();
        form.loadRecord(record);
    },

    /**
     * Event will be fired when the user clicks on one of the three buttons in the customer
     * information panels.
     *
     * @param [Ext.data.Model] record - The record of the detail page
     */
    onOpenCustomer: function(record) {
        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Customer',
            action: 'detail',
            params: {
                customerId: record.get('customerId')
            }
        });
    },

    /**
     * Event listener method which is fired when the user wants to save the internal comment
     * which can be edit in the communication tab panel on the detail page.
     * @return void
     */
    onSaveInternalComment: function(record, panel, options) {
        var me = this;

        me.saveRecord(record, me.snippets.internalComment.successMessage, me.snippets.internalComment.failureMessage, options);
    },

    /**
     * Event listener method which is fired when the user edits an order over the detail
     * page and clicks the save button on the overview panel.
     *
     * @param record
     */
    onSaveOverview: function(record, options) {
        var me = this;

        me.saveRecord(record, me.snippets.overview.successMessage, me.snippets.overview.failureMessage, options);
    },

    /**
     * Event listener method which is fired when the user edits an order over the detail
     * page and clicks the save button on the details panel.
     *
     * @param record
     */
    onSaveDetails: function(record, options) {
        var me = this;

        me.saveRecord(record, me.snippets.details.successMessage, me.snippets.details.failureMessage, options);
    },

    /**
     * Event listener method which is fired when the user wants to save the external comment
     * which can be edit in the communication tab panel on the detail page.
     * @return void
     */
    onSaveExternalComment: function(record, panel, options) {
        var me = this;

        me.saveRecord(record, me.snippets.externalComment.successMessage, me.snippets.externalComment.failureMessage, options);

    },

    /**
     * Internal helper function to save the record and display a succes message or error message
     * @param record
     * @param title
     * @param message
     */
    saveRecord: function(order, successMessage, errorMessage, options) {
        var me = this;

        successMessage = Ext.String.format(successMessage, order.get('number'));
        errorMessage = Ext.String.format(errorMessage, order.get('number'));

        order.save({
            callback:function (data, operation) {
                var records = operation.getRecords(),
                    record = records[0],
                    rawData = record.getProxy().getReader().rawData;

                if ( operation.success === true ) {
                    Shopware.Notification.createGrowlMessage(me.snippets.successTitle, successMessage, me.snippets.growlMessage);
                    if (rawData && rawData.warning) {
                        Shopware.Notification.createGrowlMessage(me.snippets.warningTitle, rawData.warning, me.snippets.growlMessage);
                    }

                    order.set('invoiceAmount', rawData.data.invoiceAmount);

                    //Check if a status mail content created and create a model with the returned data and open the mail window.
                    if (rawData && rawData.data && rawData.data.mail && rawData.data.mail.content) {
                        var mail = Ext.create('Shopware.apps.Order.model.Mail', rawData.data.mail);
                        me.showOrderMail(mail, record)
                    }

                    if (options !== Ext.undefined && Ext.isFunction(options.callback)) {
                        options.callback(order);
                    }
                } else {
                    Shopware.Notification.createGrowlMessage(me.snippets.failureTitle, errorMessage + '<br> ' + operation.getError(), me.snippets.growlMessage)

                    if (rawData.overwriteAble) {
                        Ext.MessageBox.confirm(me.snippets.overwriteOrder.title, me.snippets.overwriteOrder.message, function (response) {
                            if (response === 'yes') {
                                order.set('changed', rawData.data.changed);
                                me.saveRecord(order, successMessage, errorMessage, options);
                            }
                        });
                    }
                }
                // reload the order list
                me.subApplication.getStore('Order').load();
            }
        });
    },

    /**
     * Opens the status mail window
     *
     * @param mail
     * @param record
     */
    showOrderMail: function(mail, record) {
        var me = this,
            documentTypeStore = Ext.create('Shopware.apps.Order.store.DocType');

        // open the status mail window
        documentTypeStore.load({
            callback: function () {
                me.mainWindow = me.getView('mail.Window').create({
                    attached: [
                        record.get('id')
                    ],
                    record: record,
                    order: record,
                    listStore: me.subApplication.getStore('Order'),
                    documentTypeStore: documentTypeStore,
                    mail: mail
                }).show();
            }
        });
    },


    /**
     * Event listener method which fired when the user clicks the pencil button
     * in the order list to show the order detail page.
     * @param record
     */
    onShowDetail: function(record) {
        var me = this;
        var mainController = me.subApplication.getController('Main');
        mainController.showOrder(record);
    }
});
//{/block}
