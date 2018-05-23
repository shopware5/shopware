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

//{namespace name=backend/form/controller/fields}

/**
 * todo@all: Documentation
 */
//{block name="backend/form/controller/fields"}
Ext.define('Shopware.apps.Form.controller.Fields', {

    /**
     * Extend from the standard ExtJS 4 controller
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
        { ref: 'fieldgrid', selector: 'form-main-fieldgrid' }
    ],

    /**
     * Creates the necessary event listener for this
     * specific controller
     *
     * @return void
     */
    init: function() {
        var me = this;

        me.control({
            'form-main-fieldgrid dataview': {
                drop: me.onDropField
            },
            'form-main-fieldgrid': {
                beforeedit: me.onBeforeEditField,
                edit: me.onAfterEditField,
                canceledit: me.onCancelEditField,
                validateedit: me.onValidateEditField

            },
            'form-main-fieldgrid actioncolumn': {
                /*{if {acl_is_allowed privilege=createupdate}}*/
                render: function (view) {
                    view.scope = this;
                    view.handler = this.onDeleteSingleField;
                }
                /*{/if}*/
            },

            'form-main-fieldgrid button[action=add]': {
                click: me.onAddField
            }
        });

        me.callParent(arguments);
    },

    /**
     * Event listener which deletes a single field based on the passed
     * grid (e.g. the grid store) and the row index
     *
     * @event render
     * @param { Ext.grid.View } grid - The grid on which the event has been fired
     * @param { integer } rowIndex - On which row position has been clicked
     * @param { integer } colIndex - On which coulmn position has been clicked
     * @param { object } item - The item that has been clicked
     * @return void
     */
    onDeleteSingleField: function(grid, rowIndex, colIndex, item) {
        var store  = grid.getStore(),
            record = store.getAt(rowIndex);

        var message = Ext.String.format('{s name=dialog_delete_field_message}Are you sure you want to delete the selected field ([0])?{/s}', record.get('name'));

        Ext.MessageBox.confirm('{s name=dialog_delete_field_title}Delete field{/s}', message, function (response) {
            if (response !== 'yes') {
                return false;
            }

            grid.setLoading(true);
            record.destroy({
                callback: function() {
                    store.load();
                    grid.setLoading(false);
                }
            });
        });
    },

    /**
     * Function to add a new field
     *
     * @event click
     * @return void
     */
    onAddField: function() {
        var me = this,
            grid = me.getFieldgrid(),
            editor = grid.editor,
            count = grid.store.count() + 1,
            newField = Ext.create('Shopware.apps.Form.model.Field', {
                position: count
            });

        grid.store.add(newField);
        editor.startEdit(newField, 1);
    },

    /**
     * Saves current positions in the grid to the backend
     *
     * @event drop
     * @param { HTMLElement } node          The GridView node if any over which the mouse was positioned.
     * @param { Object } data               The data object gathered at mousedown time
     * @param { Ext.data.Model } overModel
     * @param { string } dropPosition       "before" or "after" depending on whether the mouse is above or below the midline of the node.
     * @return void
     */
    onDropField: function (node, data, overModel, dropPosition) {
        var store = this.getStore('Field'),
            orderedItems = [],
            index = 0;

        store.each(function(item) {
            orderedItems[index++] = item.data.id;
        });

        // Send current positions to backend
        Ext.Ajax.request({
            url: '{url controller="form" action="changeFieldPosition"}',
            method: 'POST',
            params: {
                data : JSON.stringify(orderedItems)
            }
        });
    },

    /**
     * Fired after a row is edited and passes validation. This event is fired
     * after the store's update event is fired with this edit.
     *
     * @event edit
     * @param { Ext.grid.plugin.Editing } editor
     * @param { Object } event An edit event with the following properties:
     *                 grid - The grid
     *                 record - The record that was edited
     *                 field - The field name that was edited
     *                 value - The value being set
     *                 row - The grid table row
     *                 column - The grid Column defining the column that was edited.
     *                 rowIdx - The row index that was edited
     *                 colIdx - The column index that was edited
     *                 originalValue - The original value for the field, before the edit (only when using CellEditing)
     *                 originalValues - The original values for the field, before the edit (only when using RowEditing)
     *                 newValues - The new values being set (only when using RowEditing)
     *                 view - The grid view (only when using RowEditing)
     *                 store - The grid store (only when using RowEditing)
     * @return void
     */
    onAfterEditField: function(editor, event) {
        var record = event.record,
            view   = editor.grid.getView();

        record.save();

        // enable add button
        view.ownerCt.down('button[action=add]').enable();

        // enable drag&drop when editing is over
        view.getPlugin('my-gridviewdragdrop').enable();
    },

    /**
     * Fires when the user has started editing a row but then cancelled the edit
     *
     * @event canceledit Fires when the user started editing but then cancelled the edit.
     * @param { Ext.grid.plugin.Editing } editor
     * @param { Object } event An edit event with the following properties:
     *                 grid - The grid
     *                 record - The record that was edited
     *                 field - The field name that was edited
     *                 value - The value being set
     *                 row - The grid table row
     *                 column - The grid Column defining the column that was edited.
     *                 rowIdx - The row index that was edited
     *                 colIdx - The column index that was edited
     *                 view - The grid view
     *                 store - The grid store
     * @return void
     */
    onCancelEditField: function(editor, event) {
        var grid   = editor.grid,
            record = event.record,
            store  = grid.getStore(),
            view   = grid.getView();

        if (record.phantom) {
            store.remove(record);
        }

        // enable add button
        view.ownerCt.down('button[action=add]').enable();

        // enable drag&drop when editing is canceled
        view.getPlugin('my-gridviewdragdrop').enable();
    },

    /**
     * Disables the add button and the drag and drop handler
     *
     * @event beforeedit
     * @param { Ext.grid.plugin.Editing } editor
     * @return void
     */
    onBeforeEditField: function(editor) {
        /*{if !{acl_is_allowed privilege=createupdate}}*/
        return false;
        /*{/if}*/

        var view = editor.grid.getView();

        // disable add button
        view.ownerCt.down('button[action=add]').disable();

        // disable drag&drop when row editing is started
        view.getPlugin('my-gridviewdragdrop').disable();
    },


    /**
     * Validates the record
     *
     * @event validateedit
     * @param { Ext.grid.plugin.Editing } editor
     * @param { Object } event An edit event
     *
     * @return bool
     */
    onValidateEditField: function(editor, event) {
        var record = event.record,
            store = event.store,
            newName = event.newValues.name,
            isValid = true;

        store.each(function(item) {
            if (item.internalId === record.internalId) {
                return true;
            }

            if ((newName === item.get('name'))) {
                record.markDirty();
                isValid = false;
            }
        });

        return isValid;
    }
});
//{/block}
