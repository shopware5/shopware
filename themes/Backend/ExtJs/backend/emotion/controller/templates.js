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
 * @package    Emotion
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/emotion/templates/main}

/**
 * Shopware UI - Emotion Main Controller
 *
 * This file contains the business logic for the Emotion module.
 */
//{block name="backend/emotion/controller/templates"}
Ext.define('Shopware.apps.Emotion.controller.Templates', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Ext.app.Controller',

    /**
     * References to components
     * @Array
     */
    refs: [
        { ref: 'list', selector: 'emotion-templates-list' },
        { ref: 'toolbar', selector: 'emotion-templates-toolbar' },
        { ref: 'settings', selector: 'emotion-view-templates-settings' }
    ],

    snippets: {
            title: '{s name=global/title}Shopping worlds{/s}',
            copie: '{s name=global/copie}Copie{/s}',
            edited: '{s name=global/edited}The template [0] was successfully edited.{/s}',
            duplicated: '{s name=global/duplicated}The template [0] was successfully duplicated.{/s}',
            removed: '{s name=global/removed}The template [0] was successfully removed.{/s}',
            marked_removed: '{s name=global/marked_removed}The selected template are successfully removed.{/s}',
            confirm: {
                remove: '{s name=global/confirm/remove}Are you sure you want to remove the template [0]?{/s}',
                marked_remove: '{s name=global/confirm/marked_remove}Are you sure you want to remove the selected template(s)?{/s}'
            },
            alert: {
                default_remove: '{s name=global/alert}Default templates could not be removed.{/s}'
            }
        },

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the subapplication
     *
     * @return void
     */
    init: function() {
        var me = this;

        me.control({
            'emotion-templates-list': {
                'edit': me.onInlineEdit,
                'selectionChange': me.onSelectionChange,
                'editEntry': me.onEdit,
                'duplicate': me.onDuplicate,
                'remove': me.onRemove
            },
            'emotion-templates-toolbar': {
                'searchGrids': me.onSearch
            },
            'emotion-templates-toolbar button[action=emotion-templates-new-template]': {
                click: me.onCreate
            },
            'emotion-templates-toolbar button[action=emotion-templates-delete-marked-templates]': {
                click: me.onMultipleRemove
            },
            'emotion-view-templates-settings button[action=emotion-save-grid]': {
                click: me.onSave
            }
        });
    },

    /**
     * Event listener method which will be triggered when the user wants to
     * create a new grid.
     *
     * @returns { Void }
     */
    onCreate: function() {
        var me = this;

        me.getView('templates.Settings').create();
    },

    /**
     * Event handler method which will be triggered when the user
     * clicks on the pencil icon in the list.
     *
     * The method opens the `settings` window to update the values.
     *
     * @param { Shopware.apps.Emotion.view.templates.List } grid
     * @param { Shopware.apps.Emotion.model.Template } rec
     * @returns { Void }
     */
    onEdit: function(grid, rec) {
        var me = this;

        me.getView('templates.Settings').create({
            record: rec
        });
    },

    /**
     * Event handler which will be triggered when the user updates an record
     * using the row editor plugin for the list.
     *
     * @param { Object } editor
     * @param { Object } values
     * @returns { Boolean }
     */
    onInlineEdit: function(editor, values) {
        var me = this,
            grid = editor.grid,
            record = values.record;

        if(!record) {
            return false;
        }
        grid.setLoading(true);
        record.save({
            callback: function() {
                grid.setLoading(false);
                Shopware.Notification.createGrowlMessage(me.snippets.title, Ext.String.format(me.snippets.edited, record.get('name')));
            }
        });
    },

    /**
     * Event listener method which will be triggered when the user
     * clicks on the duplicate icon in the list.
     *
     * The method sends an AJAX requests to the server side and transforms
     * the received JSON object to a record.
     *
     * @param { Shopware.apps.Emotion.view.grids.List } grid
     * @param { Shopware.apps.Emotion.model.Grid } rec
     * @returns { Boolean }
     */
    onDuplicate: function(grid, rec) {
        var me = this;

        if(!rec) {
            return false;
        }

        grid.setLoading(true);
        Ext.Ajax.request({
            url: '{url controller=Emotion action=duplicateTemplate}',
            params: { id: rec.get('id') },
            success: function(response) {
                var values = Ext.JSON.decode(response.responseText),
                    duplicateRecord;

                values.data.name += ' ' + me.snippets.copie;
                duplicateRecord = Ext.create('Shopware.apps.Emotion.model.Template', values.data);
                grid.getStore().add(duplicateRecord);
                duplicateRecord.save();
                grid.setLoading(false);
                Shopware.Notification.createGrowlMessage(me.snippets.title, Ext.String.format(me.snippets.duplicated, rec.get('name')));
            }
        });
    },

    /**
     * Event listener method which will be triggered when the user
     * clicks the delete icon in the list.
     *
     * @param { Shopware.apps.Emotion.view.grids.List } grid
     * @param { Shopware.apps.Emotion.model.Grid } rec
     * @returns { Void|Boolean }
     */
    onRemove: function(grid, rec) {
        var me = this,
            store = grid.getStore();

        if(rec.data.id < 2) {
            Ext.Msg.alert(me.snippets.title, me.snippets.alert.default_remove);
            return false;
        }

        Ext.Msg.confirm(me.snippets.title, Ext.String.format(me.snippets.confirm.remove, rec.get('name')), function(btn) {
            if(btn !== 'yes') {
                return false;
            }

            store.remove(rec);
                grid.setLoading(true);
                rec.destroy({
                    callback: function() {
                        Shopware.Notification.createGrowlMessage(me.snippets.title, Ext.String.format(me.snippets.removed, rec.get('name')));
                        grid.setLoading(false);
                    }
                });
        });
    },

    /**
     * Event listener method which will be triggered when the user
     * clicks the `remove selected grids` button.
     *
     * The method loops through the selection and destroyies the records.
     *
     * @returns { Void }
     */
    onMultipleRemove: function() {
        var me = this,
            grid = me.getList(),
            selModel = grid.getSelectionModel(),
            selected = selModel.getSelection();

        Ext.Msg.confirm(me.snippets.title, me.snippets.confirm.marked_remove, function(btn) {
            if(btn !== 'yes') {
                return false;
            }

            Ext.each(selected, function(item) {
                if(item.data.id > 1) {
                    item.destroy();
                }
            });

            grid.getStore().load({
                callback: function() {
                    Shopware.Notification.createGrowlMessage(me.snippets.title, me.snippets.marked_removed);
                }
            });
        });
    },

    /**
     * Event listener method which will be triggered when the user wants to
     * create a new grid.
     *
     * @returns { Void }
     */
    onCreate: function() {
        var me = this;

        me.getView('templates.Settings').create();
    },

    /**
     * Event listener method which will be triggered when the user clicks on the save
     * button in the `settings` window.
     *
     * @returns { Boolean }
     */
    onSave: function(btn) {
        var me = this,
            win = me.getSettings(),
            form = win.formPanel,
            rec = form.getRecord(),
            newRec = false;

        btn.setDisabled(true);
        if(!form.getForm().isValid()) {
            btn.setDisabled(false);
            return false;
        }

        if(rec) {
            form.getForm().updateRecord(rec);
        } else {
            rec = Ext.create('Shopware.apps.Emotion.model.Template', form.getForm().getValues());
            newRec = true;
        }

        rec.save({
            callback: function() {
                if(newRec) {
                    me.getList().getStore().add(rec);
                }
                Shopware.Notification.createGrowlMessage(me.snippets.title, Ext.String.format(me.snippets.edited, rec.get('name')));
                win.destroy();
            }
        });
    },

    /**
     * Event listener method which will be triggered when the user selects
     * one or more entries in the list.
     *
     * The method just unlocks the `delete`-button.
     *
     * @param { Array } selection - Array of the selected records
     * @returns { Void }
     */
    onSelectionChange: function(selection) {
        var me = this,
            toolbar = me.getToolbar(),
            btn = toolbar.deleteBtn,
            defaultSelected;

        Ext.each(selection, function(item) {
            if(item.data.id < 2) {
                defaultSelected = true;
                return false;
            }
        });

        btn.setDisabled(!(selection.length && !defaultSelected));
    },

    /**
     * Event listener method which will be (buffered) triggered
     * when the user inserts a search term.
     *
     * The method uses a custom `filterBy`-method to search for the
     * incoming value.
     *
     * @param { String } value - Search term
     * @returns { Void }
     */
    onSearch: function(value) {
        var me = this,
            grid = me.getList(),
            store = grid.getStore();

        if(!value.length) {
            store.clearFilter();
        } else {
            store.clearFilter(true);
            value = value.toLowerCase();
            store.filterBy(function(rec) {
                var name = rec.get('name').toLowerCase();
                return name.indexOf(value) !== -1;
            });
        }
    }
});
//{/block}
