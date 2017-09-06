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
 * @package    Supplier
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

/*{namespace name=backend/supplier/controller/main}*/

/**
 * Shopware Controller - Supplier
 *
 * Backend - Management for Suppliers. Create | Modify | Delete and Logo Management.
 * Default supplier view. Extends a grid view.
 */
// {block name="backend/supplier/controller/main"}
Ext.define('Shopware.apps.Supplier.controller.Main', {
    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend : 'Ext.app.Controller',

    /**
     * ExtJS Shortcuts
     */
    refs : [
        /**
         * Addresses the main grid
         */
        { ref : 'grid', selector : 'supplier-main-list' },

        /**
         * Address the detail view in the main window
         */
        { ref : 'detailView', selector : 'supplier-main-detail' },

        /**
         * Image in the detailView
         */
        { ref : 'detailViewImage', selector : 'supplier-main-detail img' }
    ],

    /**
     * Contains all text messages for this controller
     * @object
     */
    messages: {
        deleteDialogMessage : '{s name=dialog_text}Are you sure you want to delete this supplier ([0])?{/s}',
        deleteDialogMessageMulti : '{s name=dialog_multi}Are you sure you want to delete these suppliers ([0])?{/s}',
        deleteDialogForbidden : '{s name=delete_forbidden}Suppliers with assigned articles can not be deleted.{/s}',
        noDescriptionFound :'{s name=details_no_description}No description saved{/s}',
        deleteDialogSuccess :'{s name=dialog_multi_success}Supplier has been deleted successfully.{/s}',
        saveDialogSuccess :'{s name=dialog_save_success}Supplier has been saved successfully.{/s}',
        deleteDialogFailure :'{s name=dialog_multi_error}Some suppliers could not be removed.{/s}',
        deleteDialogTitle : '{s name=delete_dialog_title}Delete selected supplier{/s}',
        growlMessage: '{s name=window_title}{/s}'
    },

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the sub-application
     *
     * beware: there is some controller logic in edit.js view.
     *          This is because of the special handling of the
     *          upload method.
     * @return void
     */
    init: function () {
        var me = this;

        this.control({
            // Listener for the Add a new Supplier Action
            'supplier-main-toolbar button[action=addSupplier]' : {
                'click' : function () {
                    /*{if {acl_is_allowed privilege=create}}*/
                    this.onCreateSupplier();
                    /*{/if} */
                }
            },
            'grid': {
                itemclick : me.onShowDetails,
                selectionchange : me.onSelectionChange
            },
            // Listener for the delete Logo button in the edit view
            'supplier-main-edit button[action=deleteLogo]' : {
                'click' : function (btn) {
                    /*{if {acl_is_allowed privilege=update}}*/
                    this.onDeleteLogo(btn);
                    /* {/if} */
                }
            },
            // Listener for saving the supplier info
            'supplier-main-create button[action=saveSupplier]' : {
                'click' : function(btn) {
                    /*{if {acl_is_allowed privilege=create}}*/
                    this.onSupplierSave(btn);
                    /* {/if} */

                }
            },
            // Listener for saving the modified supplier logo
            'supplier-main-edit button[action=saveSupplier]' : {
                'click' : function(btn) {
                    /*{if {acl_is_allowed privilege=update}}*/
                    this.onSupplierSave(btn);
                    /* {/if} */
                }
            },
            // Listener for the mass delete button in default view
            'supplier-main-toolbar button[action=deleteSupplier]' : {
                /*{if {acl_is_allowed privilege=delete}}*/
                'click' : this.onDeleteMultipleSuppliers
                /* {/if} */

            },
            // Listener for the default search
            'supplier-main-toolbar textfield[action=searchSupplier]' : {
                keyup : this.onSearch
            },

            // Listener for the action column in the grid
            'supplier-main-list actioncolumn': {
                render : function (view) {
                    view.scope = me;
                    view.handler = me.handleActionColumn;
                }
            }
        });

        me.mainWindow = me.getView('Main').create({
            supplierStore: me.getStore('Supplier')
        });

        /**
         * Keeps track of the current selection
         * @array
         */
        me.mainWindow.show();
    },

    /**
     * Toggles the delete button in the toolbar
     *
     * @param sm Ext.selection.Model
     * @param selection array of Ext.data.Model
     * @return void
     */
    onSelectionChange: function(sm, selection) {
        var me = this,
            deleteButton = me.mainWindow.down('button[action=deleteSupplier]'),
            allowDelete = true;

        // hide detail panel if there are more than one item selected
        if (selection.length > 1 ) {
            me.getDetailView().collapse(false);
        }

        // check for assigned articles
        Ext.each(selection, function(element) {
            if (element.get('articleCounter') !== 0) {
                // remove supplier from selection if there are still articles assigned to it
                sm.deselect(element);
            }
        });

        selection = sm.getSelection();

        /*{if !{acl_is_allowed privilege=delete}}*/
        allowDelete = false;
        /* {/if} */

        // show details and enable delete button
        if (selection.length > 0 ) {
            if(allowDelete) {
                deleteButton.setDisabled(false);
            }
        } else {
            deleteButton.setDisabled(true);
        }

    },

    /**
     * This method will be called if the user hits the save button either in the edit window or
     * in the add supplier window
     *
     * @param btn Ext.button.Button
     * @return void
     */
    onSupplierSave: function(btn) {
        var win     = btn.up('window'), // Get Window
            form    = win.down('form'), // Get the DOM Form used in that window
            formBasis = form.getForm(), // Extract the form from the DOM
            me      = this,             // copy the current scope to me, because the 'this' scope tends to change
            store   = me.getStore('Supplier'), // load the supplier store
            record  = form.getRecord(),   // retrieve the record
            detailViewData = me.getDetailView().dataView,   // Detail view
            detailView = me.getDetailView();                // Detail View manager

        if (!(record instanceof Ext.data.Model)){
            record = Ext.create('Shopware.apps.Supplier.model.Supplier');
        }

        formBasis.updateRecord(record);

        // Check if there the form is valid -> see model/supplier.js
        if (formBasis.isValid()) {
            record.save({
                callback: function() {
                    // save attributes
                    win.attributeForm.saveAttribute(record.getId());

                    Shopware.app.Application.fireEvent('supplier-save-successfully', me, record, form);

                    // reload the store
                    store.load();
                    // and close the window.
                    win.close();
                    detailViewData.update(record);
                    detailView.collapse(false);
                    Shopware.Msg.createGrowlMessage('',me.messages.saveDialogSuccess, me.messages.growlMessage);
                }
            });
        }
    },

    /**
     * Event listener method which fires when the user clicks on
     * a item in the grid.
     *
     * Shows detail information about the selected item.
     *
     * @event itemclick
     * @param [object] view - Ext.grid.Panel
     * @param [object] record - Associated Ext.data.Model from the clicked iten
     * @return void
     */
    onShowDetails: function(view, record, item, rowIndex, event, options) {
        var me = this,
            detailView = me.getDetailView().dataView,
            domEl = event.getTarget(),
            element = Ext.get(domEl);

        if(!element.hasCls('x-grid-row-checker')) {
            if (!record.get('description')) {
                record.data.description = me.messages.noDescriptionFound;
            }
            detailView.update(record.data);
        }
    },

    /**
     * Deletes the logo
     *
     * @param [object] btn - Object which received the click. E.g. a delete button
     * @return void
     */
    onDeleteLogo: function (btn) {
        var win         = btn.up('window'),
            form        = win.down('form'),
            me          = this,
            formBasis   = form.getForm(),
            store   = me.getStore('Supplier'),
            detailViewData = me.getDetailView().dataView,
            detailView = me.getDetailView(),
            record      = form.getRecord();

        // remove the data from the model
        record.set('image', '');
        form.remove('supplierLogoImg');
        form.loadRecord(record);
        if (formBasis.isValid()) {
            record.save({
                scope: me,
                callback: function() {
                    // reload the store
                    store.load( );
                    win.close();
                    // refresh and reload the detail view and then hide it
                    detailViewData.update(record);
                    detailView.collapse(false);
                }
            });
        }
    },

    /**
     * Handling the search in the grid
     * @event keypressed
     * @param [object] field - Input field in which the search string has been put
     */
    onSearch : function (field) {
        var me = this,
            store = me.getStore('Supplier');

        if (field.value.length === 0) {
            store.clearFilter();
            return false;
        }
        store.filters.clear();
        store.filter('name', field.value);

        return true;
    },

    /**
     * Helper method which handles all clicks of the action column
     *
     * @param [object] view - The view
     * @param [integer] rowIndex - On which row position has been clicked
     * @param [integer] colIndex - On which column position has been clicked
     * @param [object] item - The item that has been clicked
     * @return void
     */
    handleActionColumn : function (view, rowIndex, colIndex, item) {
        var me = this.scope;

        switch (item.action) {
            case 'edit':
                me.onEditSupplier(view, item, rowIndex);
                break;
            case 'delete':
                me.onDeleteSingleSupplier(view, rowIndex);
                break;
            default:
                break;
        }
    },

    /**
     * Opens the Ext.window.window which displays
     * the Ext.form.Panel to create a new supplier
     *
     * @event click
     * @return void
     */
    onCreateSupplier: function () {
        var me = this;

        /*{if {acl_is_allowed privilege=create}}*/
        me.getView('main.Create').create({
            mainStore: me.getStore('Supplier')
        }).show();
        /* {/if} */
    },

    /**
     * Opens the Ext.window.window which displays
     * the Ext.form.Panel to modify an existing supplier
     *
     * @event click
     * @param [object]  view - The view. Is needed to get the right store
     * @param [object]  item - The row which is affected
     * @param [integer] rowIndex - The row number
     * @return void
     */
    onEditSupplier : function (view, item, rowIndex) {
        /*{if {acl_is_allowed privilege=update}}*/
        var store = view.getStore(),
            me = this,
            record = store.getAt(rowIndex),
            newStore = Ext.create('Shopware.apps.Supplier.store.Supplier');

        newStore.load({
            id: record.getId(),
            callback: function(records, operation, success) {
                if (success) {
                    var newRecord = records[0];
                    me.getView('main.Edit').create({
                        record: newRecord,
                        mainStore: store
                    }).show();
                }
            }
        });
        /* {/if} */
    },

    /**
     * Event listener which deletes a single user based on the passed
     * grid (e.g. the grid store) and the row index
     *
     * @event click
     * @param [object] grid - The grid on which the event has been fired
     * @param [integer] rowIndex - Position of the event
     */
    onDeleteSingleSupplier: function (grid, rowIndex) {
        /*{if {acl_is_allowed privilege=delete}}*/
        var store = grid.getStore(),
            me = this,
            record = store.getAt(rowIndex);

        if (record.get('articleCounter') === 0) {
            // we do not just delete - we are polite and ask the user if he is sure.
            Ext.MessageBox.confirm(me.messages.deleteDialogTitle,
                 Ext.String.format(me.messages.deleteDialogMessage, record.get('name')),
                function (response) {
                    if (response !== 'yes') {
                        return false;
                    }
                record.destroy({
                    success : function () {
                        store.load();
                        Shopware.Msg.createGrowlMessage('',me.messages.deleteDialogSuccess, me.messages.growlMessage);
                    },
                    failure : function () {
                        Shopware.Msg.createGrowlMessage('', me.messages.deleteDialogFailure, me.messages.growlMessage);
                    }
                });
            });
        } else {
            Shopware.Msg.createGrowlMessage(
                me.messages.deleteDialogTitle,
                me.messages.deleteDialogForbidden,
                me.messages.growlMessage
            );
        }
        /* {/if} */
    },

    /**
     * Event listener method which deletes multiple suppliers
     * @event click
     * @param button Ext.button.Button
     * @return void
     */
    onDeleteMultipleSuppliers: function (button) {
        /* {if {acl_is_allowed privilege=delete}} */
        var me = this,
            grid = me.getGrid(),
            selection = grid.getSelectionModel().getSelection(),
            store = grid.getStore(),
            noOfElements = selection.length,
            listOfSupplierNames = "";

        // Collect the names of the object that are marked for deletion
        for (var i = 0; i <= noOfElements - 1; i++) {
            listOfSupplierNames += selection[i].get('name');
            if (i <= noOfElements - 2) {
                listOfSupplierNames += ', ';
            }
        }
        // Get the user to confirm the delete process
        Ext.MessageBox.confirm(me.messages.deleteDialogTitle,
            Ext.String.format(me.messages.deleteDialogMessageMulti, listOfSupplierNames),
            function (response) {
                var errorOccurred = false;
                if (response !== 'yes') {
                    return false;
                }

                Ext.each(selection, function (supplier) {
                    supplier.destroy({
                        success : function () {
                            store.remove(supplier);
                        },
                        failure : function () {
                            errorOccurred = true;
                        }
                    });
                });

                if (!errorOccurred) {
                    Shopware.Msg.createGrowlMessage('', me.messages.deleteDialogSuccess, me.messages.growlMessage);
                } else {
                    Shopware.Msg.createGrowlMessage('', me.messages.deleteDialogFailure, me.messages.growlMessage);
                }
                store.load();
            });
        /* {/if} */
    }
});
//{/block}
