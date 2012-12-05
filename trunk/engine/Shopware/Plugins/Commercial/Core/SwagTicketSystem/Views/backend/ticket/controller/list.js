/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @package    Ticket
 * @subpackage Controller
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stephan Pohl
 * @author     $Author$
 */
//{namespace name=backend/ticket/main}
//{block name="backend/ticket/controller/list"}
Ext.define('Shopware.apps.Ticket.controller.List', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend:'Ext.app.Controller',

    /**
     * Array of configs to build up references to views on page
     * @array
     */
    refs: [
        { ref: 'overviewGrid', selector: 'ticket-list-overview' },
        { ref: 'ticketInfo', selector: 'ticket-list-ticket-info' },
        { ref: 'newWindow', selector: 'ticket-ticket-new-window' },
        { ref: 'editWindow', selector: 'ticket-ticket-edit-window' }
    ],

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
            'ticket-list-overview': {
                'addTicket': me.onAddTicket,
                'deleteTickets': me.onDeleteTickets,
                'deleteTicket': me.onDeleteTicket,
                'changeStatus': me.onChangeStatus,
                'changeEmployee': me.onChangeEmployee,
                'searchTicket': me.onSearch,
                'selectionChange': me.onSelectionChange,
                'openCustomer': me.onOpenCustomer,
                'editTicket': me.onEditTicket,
                'edit': me.onEdit
            },
            'ticket-ticket-new-window': {
                'createTicket': me.onCreateTicket,
                'createTicketUnregistered': me.onCreateTicketUnregistered
            },
            'ticket-ticket-edit-window': {
                answerTicket: me.onAnswerTicket
            }
        });
    },

    /**
     * Event listener method which will be fired when the user
     * clicks on the "answer"-button.
     *
     * Sends the submitted values to the server side.
     *
     * @public
     * @event click
     * @return [boolean]
     */
    onAnswerTicket: function() {
        var me = this,
            win = me.getEditWindow(),
            formPanel = win.formPanel,
            form = formPanel.getForm(),
            values = formPanel.getValues();

        if(!form.isValid()) {
            Shopware.Notification.createGrowlMessage('{s name=window_title}Ticket system{/s}', '{s name=error/answer_ticket_fill_fields}Please fill out all required fields (marked red) to send your ticket answer.{/s}');
            return false;
        }

        Ext.Ajax.request({
            url: '{url action=answerTicket}',
            params: values,
            success: function(r, opts) {
                var response = Ext.decode(r.responseText);
                if(!response.success) {
                    Shopware.Notification.createGrowlMessage('An error occurred', response.errorMsg, 'Ticket-System');
                }

                me.subApplication.overviewStore.load();
                win.destroy();

            }
        })
    },

    /**
     * Event listener method which will be triggered when
     * the user presses the "create new ticket"-button.
     *
     * The method opens the "add ticket" window.
     *
     * @public
     * @event click
     * @param [object] btn - Ext.button.Button
     * @param [object] view - Shopware.apps.Ticket.view.list.Overview
     * @return void
     */
    onAddTicket: function(btn, view) {
        var me = this;

        me.subApplication.customerStore = me.subApplication.getStore('Customer');
        me.getView('ticket.NewWindow').create({
            formsStore: me.subApplication.formsStore,
            customerStore: me.subApplication.customerStore
        });
    },

    /**
     * Event listener method which will be fired when the user
     * clicks on the edit-icon (pencil) in the overview grid.
     *
     * Prepares the history store and creates the edit window.
     *
     * @public
     * @event click
     * @param [object] view - Ext.grid.Panel
     * @param [object] record - Shopware.apps.Ticket.model.List
     * @return [boolean] - If falsy the selected record could not be found, otherwise null|undefined
     */
    onEditTicket: function(view, record) {
        var me = this, historyStore, defaultSubmission;

        if(!record) {
            Shopware.Notification.createGrowlMessage('{s name=window_title}Ticket system{/s}', '{s name=error/ticket_not_found}The selected ticket could not be loaded successfully.{/s}');
            return false;
        }

        // Create a history store and add the ticket id to it
        historyStore = me.subApplication.getStore('History');
        historyStore.getProxy().extraParams = { id: ~~(1 * record.get('id')) };

        var submissionDetailStore = me.subApplication.submissionDetailStore;
        submissionDetailStore.getProxy().extraParams.onlyDefaultSubmission = true;

        submissionDetailStore.load({
            scope: this,
            callback: function(records, operation, success) {
                defaultSubmission = records[0];
                // Open the edit window
                me.getView('ticket.EditWindow').create({
                    record: record,
                    defaultSubmission: defaultSubmission,
                    localeStore: me.subApplication.localeStore,
                    submissionStore: me.subApplication.submissionStore,
                    submissionDetailStore: me.subApplication.submissionDetailStore,
                    statusStore: me.subApplication.statusStore,
                    historyStore: historyStore.load()
                });
            }
        });
    },

    /**
     * Event listener method which will be triggered when
     * the user presses the "delete marked"-button.
     *
     * The method deletes the selected tickets.
     *
     * @public
     * @event click
     * @param [object] btn - Ext.button.Button
     * @param [object] view - Shopware.apps.Ticket.view.list.Overview
     * @return void
     */
    onDeleteTickets: function(btn, view) {
        var me = this,
            selModel = view.selModel,
            store = view.store,
            selected = selModel.getSelection();

        view.setLoading(true);
        Ext.each(selected, function(item) {
            item.destroy();
        });

        store.load({
            callback: function() {
                view.setLoading(false);
            }
        });
    },

    /**
     * Event listener method which will be triggered when the user
     * clicks on the "delete" action column in the overview grid.
     *
     * The method deletes the record which is associated with the
     * clicked action column.
     *
     * @public
     * @event click
     * @param [object] view - Shopware.apps.Ticket.view.list.Overview
     * @param [object] record - Shopware.apps.Ticket.model.List
     */
    onDeleteTicket: function(view, record) {
        var me = this, store = me.subApplication.overviewStore;
        record.destroy();
        store.load()
    },

    /**
     * Event listener method which will be trigged when
     * the user changes the active option in the
     * "status"-filter.
     *
     * Filters the associated store.
     *
     * @public
     * @event change
     * @param [object] field - Ext.form.field.Combobox
     * @param [string] newValue - the changed value
     * @param [string] oldValue - the old value before the user changes the active option.
     * @param [object] view - Shopware.apps.Ticket.view.list.Overview
     * @return void
     */
    onChangeStatus: function(field, newValue, oldValue, view) {
        var me = this,
            store = view.store,
            proxy = store.getProxy();

        view.setLoading(true);
        if(newValue === 0) {
            delete proxy.extraParams.statusId;
        } else {
            store.getProxy().extraParams.statusId = ~~(1 * newValue);
        }

        store.load({
            callback: function() {
                view.setLoading(false);
            }
        });
    },

    /**
     * Event listener method which will be trigged when
     * the user changes the active option in the
     * "employee"-filter.
     *
     * Filters the associated store.
     *
     * @public
     * @event change
     * @param [object] field - Ext.form.field.Combobox
     * @param [string] newValue - the changed value
     * @param [string] oldValue - the old value before the user changes the active option.
     * @param [object] view - Shopware.apps.Ticket.view.list.Overview
     * @return void
     */
    onChangeEmployee: function(field, newValue, oldValue, view) {
        var me = this,
            store = view.store,
            proxy = store.getProxy();

        view.setLoading(true);
        if(newValue === 0) {
            delete proxy.extraParams.userId;
        } else {
            store.getProxy().extraParams.userId = ~~(1 * newValue);
        }

        store.load({
            callback: function() {
                view.setLoading(false);
            }
        });
    },

    /**
     * Event listener method which will be trigged when
     * the user changes the value of the search field.
     *
     * Filters associcated store.
     *
     * @public
     * @event change
     * @param [object] field - Ext.form.field.Text
     * @param [string] newValue - the changed value
     * @return void
     */
    onSearch: function(field, newValue) {
        var me = this, store = me.subApplication.overviewStore

        store.filters.clear();
        store.filter({ property: 'free', value: newValue });
    },

    /**
     * Event listener method which will be trigged when
     * the user changes the selection.
     *
     * The method simply locks / unlocks the delete button
     * based on the selection of the user and fills
     * the panel under the overview list.
     *
     * @public
     * @event selectionchange
     * @paran [array] selection - Array of records which are included in the user selection
     * @return void
     */
    onSelectionChange: function(selection) {
        var me = this,
            grid = me.getOverviewGrid(),
            info = me.getTicketInfo(),
            btn = grid.deleteButton;

        btn.setDisabled(!selection.length);

        if(!selection.length) {
            return false;
        }
        var record = selection[0];
        info.dataView.update(record.data);
    },

    /**
     * Event listener method which will be triggered when
     * the user clicks the "user"-iocn in the grid.
     *
     * Opens the customer module with the selected customer.
     *
     * @param [object] view - Shopware.apps.Ticket.view.list.Overview
     * @param [object] record - Shopware.apps.Ticket.model.List
     * @return void
     */
    onOpenCustomer: function(view, record) {
        var id = ~~(1 * record.get('userId'));

        Shopware.app.Application.addSubApplication({
           name: 'Shopware.apps.Customer',
           action: 'detail',
           params: {
               customerId: id
           }
        });
    },

    /**
     * Event listener method which will be fired when the
     * user edits a cell with the cell editor.
     *
     * The method saves the record.
     *
     * @param [object] editor - Ext.grid.plugin.CellEditing
     * @param [object] event - Ext.EventImplObj
     */
    onEdit: function(editor, event) {
        event.record.save();
    },

    /**
     * Event listener method which triggers when the
     * user clicks on the "create ticket" button.
     *
     * The method calls a backend action with redirects
     * the user to the terminated form with the specific
     * customer.
     *
     * @public
     * @event click
     * @return [boolean]
     */
    onCreateTicket: function() {
        var me = this,
            newWin = me.getNewWindow(),
            formPnl = newWin.formPanel,
            form = formPnl.getForm(),
            values = form.getValues();

        if(!form.isValid() || !values.customerId) {
            Shopware.Notification.createGrowlMessage('{s name=window_title}Ticket system{/s}', '{s name=error/new_window_fill_all_fields}Please fill out all required fields (marked red) to create a new ticket.{/s}');
            return false;
        }

        window.open('{url controller=Ticket action=redirectToForm}?formId=' + values.formId + '&customerId=' + values.customerId);
    },

    /**
     * Event listener method which triggers when the
     * user clicks on the "create ticket without customer account" button.
     *
     * The method calls a backend action with redirects
     * the user to the terminated form.
     *
     * @public
     * @event click
     * @return [boolean]
     */
    onCreateTicketUnregistered: function() {
        var me = this,
            newWin = me.getNewWindow(),
            formPnl = newWin.formPanel,
            form = formPnl.getForm(),
            values = form.getValues();

        if(!form.isValid()) {
            Shopware.Notification.createGrowlMessage('{s name=window_title}Ticket system{/s}', '{s name=error/new_window_fill_all_fields}Please fill out all required fields (marked red) to create a new ticket.{/s}');
            return false;
        }
        window.open('{url controller=Ticket action=redirectToForm}?formId=' + values.formId);
    }
});
//{/block}
