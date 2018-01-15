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
 * @package    Form
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/form/controller/main}

/**
 * todo@all: Documentation
 */
//{block name="backend/form/controller/main"}
Ext.define('Shopware.apps.Form.controller.Main', {

    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend: 'Ext.app.Controller',

    /**
     * Define references for the different parts of our application. The
     * references are parsed by ExtJS and Getter methods are automatically created.
     *
     * Example: { ref : 'grid', selector : 'grid' } transforms to this.getGrid();
     *          { ref : 'addBtn', selector : 'button[action=add]' } transforms to this.getAddBtn()
     *
     * @array
     */
    refs: [
        { ref: 'formgrid', selector: 'form-main-formgrid' }
    ],

    /**
     * Class property which holds the main application if it is created
     *
     * @default null
     * @object
     */
    mainWindow: null,

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     *
     * @return void
     */
    init: function() {
        var me = this;

        me.control({
            'form-main-editwindow': {
                beforeclose: me.onBeforeClose
            },

            'form-main-formpanel button[action=save]': {
                click: me.onSaveForm
            },

            'form-main-formgrid button[action=add]': {
                click: me.onOpenAddWindow
            },

            'form-main-formgrid button[action=delete]': {
                click: me.onDeleteMultipleForms
            },

            'form-main-formgrid textfield[action=searchForms]' : {
                change: me.onSearch
            },

            'form-main-formgrid actioncolumn': {
                render: function (view) {
                    view.scope = this;
                    view.handler = this.handleActionColumn;
                }
            }
        });

        me.mainWindow = me.getView('main.Mainwindow').create({
            formStore: me.getStore('Form')
        }).show();

        me.callParent(arguments);
    },

    /**
     * Helper method which handles all clicks of the action column
     *
     * @event render
     * @param { Ext.grid.View } grid - The grid on which the event has been fired
     * @param { integer } rowIndex - On which row position has been clicked
     * @param { integer } colIndex - On which coulmn position has been clicked
     * @param { Object } item - The item that has been clicked
     * @return void
     */
    handleActionColumn: function (grid, rowIndex, colIndex, item) {
        var me = this.scope;

        switch (item.action) {
            case 'edit':
                me.onOpenEditWindow(grid, rowIndex, colIndex, item);
                break;
            case 'delete':
                me.onDeleteSingleForm(grid, rowIndex, colIndex, item);
                break;
            case 'copy':
                me.onCopyForm(grid, rowIndex, colIndex, item);
                break;
            default:
                break;
        }
    },


    /**
     * @event beforeclose
     * @return void
     */
    onBeforeClose: function() {
        this.getStore('Form').load();
    },

    /**
     * Event listener which copies a single Form based on the passed
     * grid (e.g. the grid store) and the row index
     *
     * @param { Ext.grid.View } grid - The grid on which the event has been fired
     * @param { integer } rowIndex - On which row position has been clicked
     * @param { integer } colIndex - On which coulmn position has been clicked
     * @param { Object } item - The item that has been clicked
     * @return void
     */
    onCopyForm: function(grid, rowIndex, colIndex, item) {
        var store  = grid.getStore(),
            record = store.getAt(rowIndex);

        var message = Ext.String.format('{s name=dialog_copy_form_message}Are you sure you want to duplicate the selected form ([0])?{/s}', record.get('name'));

        Ext.MessageBox.confirm('{s name=dialog_copy_form_title}Duplicate form{/s}', message, function (response) {
            if (response !== 'yes') {
                return false;
            }

            record.copy(function() {
                store.load();
            });
        });
    },

    /**
     * Event listener which deletes a single Form based on the passed
     * grid (e.g. the grid store) and the row index
     *
     * @param { Ext.grid.View } grid - The grid on which the event has been fired
     * @param { integer } rowIndex - On which row position has been clicked
     * @param { integer } colIndex - On which coulmn position has been clicked
     * @param { Object } item - The item that has been clicked
     * @return void
     */
    onDeleteSingleForm: function (grid, rowIndex, colIndex, item) {
        var store  = grid.getStore(),
            record = store.getAt(rowIndex);

        var message = Ext.String.format('{s name=dialog_delete_form_message}Are you sure you want to delete the selected form ([0])?{/s}', record.get('name'));

        Ext.MessageBox.confirm('{s name=dialog_delete_form_title}Delete form{/s}', message, function (response) {
            if (response !== 'yes') {
                return false;
            }

            record.destroy({
                callback: function() {
                    store.load();
                }
            });

        });
    },

    /**
     * Event listener method which will be fired when the user
     * insert a value in the search field on the right hand of the module,
     * to search forms by their name.
     *
     * @event change
     * @param { Object } field - Ext.form.field.Text
     * @param { string } value - inserted search value
     * @return void
     */
    onSearch: function(field, value) {
        var store = this.getStore('Form'),
            searchString = Ext.String.trim(value);

        //scroll the store to first page
        store.currentPage = 1;

        //If the search-value is empty, reset the filter
        if ( searchString.length === 0 ) {
            store.clearFilter();
        } else {
            //This won't reload the store
            store.filters.clear();
            //Loads the store with a special filter
            store.filter('name', "%" + searchString + "%");
        }
    },

    /**
     * Opens the Ext.window.window to modify an existing form
     *
     * @param { Ext.grid.View } grid - The grid on which the event has been fired
     * @param { integer } rowIndex - On which row position has been clicked
     * @param { integer } colIndex - On which coulmn position has been clicked
     * @param { Object } item - The item that has been clicked
     */
    onOpenEditWindow: function (grid, rowIndex, colIndex, item) {
        var me = this,
            store = grid.getStore(),
            newStore = Ext.create('Shopware.apps.Form.store.Form'),
            record = store.getAt(rowIndex),
            fieldgridStore = me.getStore('Field');

        newStore.load({
            id: record.getId(),
            scope: this,
            callback: function(records, operation, success) {
                if (success) {
                    var newRecord = records[0];

                    var view = me.getView('main.Editwindow').create({
                        formRecord: newRecord,
                        fieldStore: this.getStore('Field'),
                        shopStore: this.getStore('Shop').load()
                    });

                    view.down('form-main-fieldgrid').setDisabled(false);

                    fieldgridStore.getProxy().extraParams.formId = newRecord.data.id;
                    fieldgridStore.load();

                    view.show();
                }
            }
        });
    },


    /**
     * Opens the Ext.window.window to add a new form
     *
     * @event click
     * @return void
     */
    onOpenAddWindow: function() {
        var shopStore = this.getStore('Shop');
        shopStore.load();

        this.getView('main.Editwindow').create({
            fieldStore: this.getStore('Field'),
            shopStore: this.getStore('Shop').load()
        }).show();
    },

    /**
     * Event listener method which fires when the user
     * clicks the delete button in the top toolbar
     *
     * Deletes the currently selected forms.
     *
     * @event click
     * @return void
     */
    onDeleteMultipleForms: function() {
        var grid = this.getFormgrid(),
            sm = grid.getSelectionModel(),
            selected = sm.selected.items,
            store = grid.getStore();

        var message = Ext.String.format('{s name=dialog_delete_forms_message}Are you sure you want to delete the selected forms?{/s}');

        Ext.MessageBox.confirm('{s name=dialog_delete_forms_title}Delete forms{/s}', message, function (response) {
            if (response !== 'yes') {
                return false;
            }

            grid.setLoading(true);

            Ext.each(selected, function(record) {
                record.destroy();
            });

            store.load({
                callback: function() {
                    grid.setLoading(false);
                }
            });
        });
    },

    /**
     * Function to save a form
     *
     * @event click
     * @param { Object } btn Contains the clicked button
     * @return void
     */
    onSaveForm: function(btn) {
        var me         = this,
            win        = btn.up('window'),
            formPanel  = win.down('form'),
            form       = formPanel.getForm(),
            record     = form.getRecord(),
            fieldStore = me.getStore('Field');

        if (!form.isValid()) {
            return;
        }

        if (record === undefined) {
            record = Ext.create('Shopware.apps.Form.model.Form');
        }

        form.updateRecord(record);

        formPanel.setLoading(true);
        record.save({
            callback: function() {
                formPanel.setLoading(false);
                formPanel.loadRecord(record);
                // set extra params
                win.down('form-main-fieldgrid').setDisabled(false);

                formPanel.attributeForm.saveAttribute(record.data.id);
                fieldStore.getProxy().extraParams.formId = record.data.id;
                fieldStore.load();
            }
        });
    }
});
//{/block}
