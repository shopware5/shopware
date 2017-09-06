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
 * @package    Shipping
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

/*{namespace name=backend/shipping/controller/main}*/

/**
 * Shopware Controller - Shipping
 *
 * todo@all: Documentation
 */
//{block name="backend/shipping/controller/main"}
Ext.define('Shopware.apps.Shipping.controller.Main', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Enlight.app.Controller',

    /**
     * Some references to get a better grip of the single elements
     * @object
     */
    refs   : [
        { ref : 'grid', selector : 'shipping-list' },
        { ref : 'mainWindow', selector : 'dispatchGrid' }
    ],

    /**
     * Keeps the current config for the costs matrix
     * @null
     */
    currentConfig : null,

    /**
     * Holds the main window
     * @null
     */
    mainWindow : null,

     /**
     * Contains all text messages for this controller
     * @object
     */
    messages: {
        deleteDialogMessageMulti : '{s name=delete_dialog_multi}Are you sure you want to delete those dispatchs ([0]){/s}?',
        deleteDialogMessageSingle : '{s name=delete_dialog_single}Are you sure you want to delete this dispatchs ([0]){/s}?',
        deleteDialogSuccess :'{s name=dialog_multi_success}Dispatches successfully deleted.{/s}',
        saveDialogSuccess :'{s name=dialog_save_success}Dispatch successfully saved.{/s}',
        deleteDialogFailure :'{s name=dialog_multi_error}Some dispatches couldn\'t removed.{/s}',
        deleteDialogTitle : '{s name=delete_dialog_title}Delete selected dispatch{/s}'
    },

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the sub-application
     *
     * @return void
     */
    init: function () {
        var me = this;

        me.control({
            // Handling the action column in the main view
            'shipping-list actioncolumn' : {
                render : function (view) {
                    view.scope = this;
                    view.handler = this.handleActionColumn;
                }
            },

            'shipping-list button[action=addShipping]' :  {
                'click' : me.onAddShipping
            },

            'shipping-list button[action=deleteShipping]' :  {
                'click' : me.onDeleteShipping
            },

            'shipping-list' : {
                'selectionchange' : me.onSelectionChange
            }
        });

        me.customerGroupStore = me.getStore('Shopware.apps.Base.store.CustomerGroup').load();

        me.subApplication.paymentStore = me.subApplication.getStore('Payment');
        me.subApplication.countryStore = me.subApplication.getStore('Country');

        me.shopStore = me.getStore('Shopware.apps.Base.store.Shop').load({
            callback: function() {
                me.mainWindow = me.getView('Main').create({
                    customerGroupStore: me.customerGroupStore,
                    shopStore:          me.shopStore,
                    dispatchStore:      me.getStore('Dispatch').load()
                });
                me.subApplication.setAppWindow(me.mainWindow);
                me.mainWindow.show();
            }
        });
        me.callParent(arguments);
    },

    /**
     * Event listener which deletes a single dispatch entry based on the passed
     * grid (e.g. the grid store) and the row index
     *
     * @param [object] grid - The grid on which the event has been fired
     * @param [integer] rowIndex - Position of the event
     * @return void
     */
    onDeleteSingleShipping:function (grid, rowIndex) {
        var me = this,
            store = grid.getStore(),
            record = store.getAt(rowIndex);

        Ext.MessageBox.confirm(
            me.messages.deleteDialogTitle,
            Ext.String.format(me.messages.deleteDialogMessageSingle, record.get('name')), function (response) {
            if (response !== 'yes') {
                return false;
            }
            store.remove(record);
            store.sync({
                callback: function() {
                    Shopware.Msg.createGrowlMessage('', me.messages.deleteDialogSuccess, '{s name=title}{/s}');
                    Ext.Error.handle = function() {
                        Shopware.Msg.createGrowlMessage('', me.messages.deleteDialogFailure + e.message, '{s name=title}{/s}');
                    }
                    store.load();
                }
            });
        });

    },

    /**
     * Listen to the delete event
     *
     * @param button
     * @param event
     */
    onDeleteShipping : function(button, event) {
        var me                  = this,
            grid                = me.getGrid(),
            selection           = grid.getSelectionModel().getSelection(),
            store               = me.getStore('Dispatch'),
            listOfDispatchNames = [],
            noOfElements        = selection.length;

        for (var i = 0; i <= noOfElements - 1; i++) {
            listOfDispatchNames += selection[i].get('name');
            if (i <= noOfElements - 2) {
                listOfDispatchNames += ', ';
            }
        }
        Ext.MessageBox.confirm(me.messages.deleteDialogTitle,
            Ext.String.format(me.messages.deleteDialogMessageMulti, listOfDispatchNames),
            function (response) {
                if ('yes' !== response) {
                    return false;
                }
                if (selection.length > 0) {
                    store.remove(selection);
                    store.sync({
                        callback: function() {
                            Shopware.Msg.createGrowlMessage('', me.messages.deleteDialogSuccess, '{s name=title}{/s}');
                            store.load();
                        },
                        failure: function() {
                            Shopware.Msg.createGrowlMessage('', me.messages.deleteDialogFailure + e.message, '{s name=title}{/s}');
                        }
                    });
                }
        });
    },

    /**
     * Toggles the delete button in the toolbar
     *
     * @param sm Ext.selection.Model
     * @param selection array of Ext.data.Model
     * @return void
     */
    onSelectionChange : function(sm, selection) {
        var me = this,
            deleteButton = me.getMainWindow().down('button[action=deleteShipping]'),
            allowDelete = true;

        // check for assigned articles
        /*{if !{acl_is_allowed privilege=delete}}*/
        allowDelete = false;
        /* {/if} */

        // show details and enable delete button
        if (selection.length > 0) {
            if (allowDelete) {
                deleteButton.setDisabled(false);
            }
        } else {
            deleteButton.setDisabled(true);
        }
    },
    /**
     * Is called if a new dispatch should be created
     *
     * @return void
     */
    onAddShipping : function() {
        var me = this,
            costsmatrix = this.getStore('Costsmatrix'),
            // create a empty dispatch model
            record = me.getModel('Dispatch').create(),
            // create an inital empty costs matrix entry
            emptyCostsMatrix = me.getModel('Costsmatrix').create();

        // Make shure that an empty dispatch id will be send, so that a new data entry will be generated.
        costsmatrix.getProxy().extraParams = {
            dispatchId : ''
        };
        costsmatrix.removeAll();
        costsmatrix.add(emptyCostsMatrix);
        me.createEditForm(record, costsmatrix).show();
    },

    /**
     * Creates the edit form for the dispatch manager
     *
     * @param record
     * @param costsmatrix
     * @param createMode - boolean
     * @return Shopware.apps.Shipping.view.edit.Panel
     */
    createEditForm : function(record, costsmatrix) {
        var me = this;

        // Using Ext.create() here to force new instances of Stores (sw-3780)
        return  me.getView('Shopware.apps.Shipping.view.edit.Panel').create({
            editRecord              : record,
            // store for costs matrix tab
            costMatrixStore         : costsmatrix,
            // store for payments tab
            availablePayments       : Ext.create('Shopware.apps.Shipping.store.Payment').load(),
            // store for county tab
            availableCountries      : Ext.create('Shopware.apps.Shipping.store.Country').load(),
            // store for the category tree
            availableCategoriesTree : Ext.create('Shopware.apps.Shipping.store.CategoryTree').load(),
            // store for holidays
            availableHolidays       : Ext.create('Shopware.apps.Shipping.store.Holiday').load(),
            // current dispatch id
            dispatchId              : record.get('id'),
            // store for the dispatch
            mainStore               : Ext.create('Shopware.apps.Shipping.store.Dispatch').load()
        });
    },

     /**
     * Helper method which handles all clicks of the action column in the main view
     *
     * @param [object] view - The view
     * @param [integer] rowIndex - On which row position has been clicked
     * @param [integer] colIndex - On which coulmn position has been clicked
     * @param [object] item - The item that has been clicked
     * @return void
     */
    handleActionColumn : function (view, rowIndex, colIndex, item) {
        var me = this.scope;

        switch (item.iconCls) {
            case 'sprite-pencil':
                me.onEditShippingCosts(view, item, rowIndex);
                break;
            case 'sprite-minus-circle-frame':
                me.onDeleteSingleShipping(view, rowIndex);
            break;
            case 'sprite-blue-document-copy':
                me.onCloneShippingCosts(view, item, rowIndex);
                break;
            default:
                break;
        }
    },

    /**
     * Opens the Ext.window.window which displays
     * the Ext.form.Panel to modify existing shipping / dispatch costs
     *
     * @param [object]  view - The view. Is needed to get the right store
     * @param [object]  item - The row which is affected
     * @param [integer] rowIndex - The row number
     */
    onEditShippingCosts : function (view, item, rowIndex) {
        var store       = view.getStore(),
            me          = this,
            costsmatrix = Ext.create('Shopware.apps.Shipping.store.Costsmatrix'),
            record      = store.load().getAt(rowIndex);

        record.data.clone = false;

        // load the right data set based on the dispatch ID
        costsmatrix.getProxy().extraParams = {
            dispatchId: record.get('id')
        };
        // needs to be loaded here to have the data ready on view
        costsmatrix.load();

        me.getGrid().setLoading(true);

        // load full entity from api and create detail window
        me.getModel('Dispatch').load(record.get('id'), {
            callback: function (record) {
                // supply data to the main view
                me.createEditForm(record, costsmatrix).show();
                me.getGrid().setLoading(false);
            }
        });
    },

    /**
     * Opens the Ext.window.window which displays
     * the Ext.form.Panel to modify existing shipping / dispatch costs
     *
     * @param [object]  view - The view. Is needed to get the right store
     * @param [object]  item - The row which is affected
     * @param [integer] rowIndex - The row number
     */
    onCloneShippingCosts : function (view, item, rowIndex) {
        var store       = view.getStore(),
            me          = this,
            costsmatrix = this.getStore('Costsmatrix'),
            record      = store.getAt(rowIndex),
            emptyCostsMatrix = me.getModel('Costsmatrix').create();

        record.data.clone = true;
        costsmatrix.removeAll();

        // also clone the actual shipping costs SW-2263
        costsmatrix.getProxy().extraParams = {
            dispatchId : record.get('id')
        };

        costsmatrix.load({
            scope: me,
            callback: function(records, operation, success) {
                var newRecords  = new Array();
                Ext.each(records, function(record) {
                    var newRecord = record.copy();
                    Ext.data.Model.id(newRecord);
                    newRecord.setId(null);
                    newRecords.push(newRecord);

                });
                costsmatrix.removeAll();
                costsmatrix.add(newRecords);
            }
        });

        // save costsmatrix for further reference
//        me.costsmatrix = costsmatrix;

        me.getGrid().setLoading(true);

        // load full entity from api and create detail window
        me.getModel('Dispatch').load(record.get('id'), {
            callback: function (record) {
                record.data.clone = true;

                // supply data to the main view
                me.createEditForm(record, costsmatrix).show();
                me.getGrid().setLoading(false);
            }
        });
    },

    /**
     * The costs matrix behaves differently based
     * on the chosen calculation in the default form
     *
     * todo@all Duplicates getCalculationConfig in the backend controller
     *
     * Returns an object containing following attributes
     * - decimalPrecision
     * - minChange
     * - startValue
     *
     * @param calculationType integer
     * @return Object
     */
    getConfig: function(calculationType) {
        switch (calculationType)
        {
            case 1:
            return {
                decimalPrecision : 2,
                minChange : 0.01,
                startValue : 0
            };
            break;
            case 2:
            case 3:
                return {
                    decimalPrecision : 0,
                    minChange : 1,
                    startValue : 1
                };
                break;
            case 0:
            default:
                return {
                    decimalPrecision : 3,
                    minChange : 0.001,
                    startValue : 0
                };
                break;
        }
    }
});
//{/block}
