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
 * @package    Property
 * @subpackage Controller
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/property/view/main}

/**
 * todo@all: Documentation
 */
//{block name="backend/property/controller/main"}
Ext.define('Shopware.apps.Property.controller.Main', {

    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend: 'Ext.app.Controller',

    /**
     * Class property which holds the main application if it is created
     *
     * @default null
     * @object
     */
    mainWindow: null,

    /**
     * Define references for the different parts of our application. The
     * references are parsed by ExtJS and Getter methods are automatically created.
     *
     * @array
     */
    refs: [
        { ref: 'groupTree',        selector: 'property-main-groupTree' },
        { ref: 'filterOptionGrid', selector: 'property-main-filterOptionGrid' },
        { ref: 'valueGrid',        selector: 'property-main-valueGrid' }
    ],

    /**
     * Contains all snippets for the controller
     * @object
     */
    snippets: {
        // Delete Value
        deleteValueConfirmTitle:   '{s name=message/delete_value_confirm_title}Delete selected value{/s}',
        deleteValueConfirmMessage: '{s name=message/delete_value_confirm_message}Are you sure you want to delete the selected value?{/s}',

        deleteValueSuccessTitle: '{s name=message/delete_value_success_message}Successfully{/s}',
        deleteValueSuccessMessage: '{s name=message/delete_value_success_title}Value has been removed{/s}',

        deleteValueErrorTitle: '{s name=message/delete_value_error_title}Error{/s}',
        deleteValueErrorMessage: '{s name=message/delete_value_error_message}An error has occurred.{/s}',

        // Delete Option
        deleteOptionConfirmTitle:   '{s name=message/delete_option_confirm_title}Delete selected Option{/s}',
        deleteOptionConfirmMessage: '{s name=message/delete_option_confirm_message}Are you sure you want to delete the selected option?{/s}',

        deleteOptionSuccessTitle: '{s name=message/delete_option_success_message}Successfully{/s}',
        deleteOptionSuccessMessage: '{s name=message/delete_option_success_title}Option has been removed{/s}',

        deleteOptionErrorTitle: '{s name=message/delete_option_error_title}Error{/s}',
        deleteOptionErrorMessage: '{s name=message/delete_option_error_message}An error has occurred.{/s}',

        // Delete Group
        deleteGroupConfirmTitle:   '{s name=message/delete_group_confirm_title}Delete selected Group{/s}',
        deleteGroupConfirmMessage: '{s name=message/delete_group_confirm_message}Are you sure you want to delete the selected group?{/s}',

        deleteGroupSuccessTitle: '{s name=message/delete_group_success_message}Successfully{/s}',
        deleteGroupSuccessMessage: '{s name=message/delete_group_success_title}Group has been removed{/s}',

        deleteGroupErrorTitle: '{s name=message/delete_group_error_title}Error{/s}',
        deleteGroupErrorMessage: '{s name=message/delete_group_error_message}An error has occurred.{/s}',

		growlMessage: '{s name=title}{/s}'
    },

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     *
     * @return void
     */
    init: function() {
        var me = this;

        me.control({
            'property-main-groupTree': {
                deleteGroup:           me.onDeleteGroup,
                removeOptionFromGroup: me.onRemoveOptionFromGroup,
                addOptionToGroup:      me.onAddOptionToGroup,
                edit:                  me.onEditGroup
            },

            'property-main-groupTree dataview': {
                drop: me.onDropGroupOption
            },

            'property-main-filterOptionGrid': {
                deleteOption:    me.onDeleteOption,
                edit:            me.onEditOption,
                selectionchange: me.onOptionChange
            },

            'property-main-valueGrid': {
                deleteValue: me.onDeleteValue,
                edit:        me.onEditValue
            },

            'property-main-valueGrid dataview': {
                drop: me.onDropValue
            }
        });

        me.mainWindow = me.getView('main.Window').create({
            valueStore:        me.getStore('Value'),
            filterOptionStore: me.getStore('FilterOption'),
            groupStore:        me.getStore('Group')
        });

        me.mainWindow.show();

        me.callParent(arguments);
    },

    /**
     * Saves current positions in the grid to the backend
     *
     * @event drop
     * @param [HTMLElement ] The GridView node if any over which the mouse was positioned.
     * @param [Object] The data object gathered at mousedown time
     * @param [Ext.data.Model]
     * @param [String] "before" or "after" depending on whether the mouse is above or below the midline of the node.
     * @return void
     */
    onDropGroupOption: function (node, data, overModel, dropPosition) {
        var me    = this,
            group = data.records[0].parentNode;

        return me.saveGroupPosition(group);
    },

    /**
     * Internal helper function to save current postion of values
     */
    saveGroupPosition: function(group) {
        var me = this,
            orderedItems = [],
            groupId = group.get('id');

        group.eachChild(function(item) {
            // Option is constructed like this groupId + "_" + optionId eG. "4_3"
            var optionId = item.get('id').split("_")[1];

            orderedItems.push({
                position: item.get('position'),
                groupId: groupId,
                optionId: optionId
            });
        });

        // Send current positions to backend
        Ext.Ajax.request({
            url: '{url controller="property" action="changeGroupPosition"}',
            method: 'POST',
            params: {
                data : Ext.encode(orderedItems)
            }
        });
    },

    /**
     * Saves current positions in the grid to the backend
     *
     * @event drop
     * @param [HTMLElement ] The GridView node if any over which the mouse was positioned.
     * @param [Object] The data object gathered at mousedown time
     * @param [Ext.data.Model]
     * @param [String] "before" or "after" depending on whether the mouse is above or below the midline of the node.
     * @return void
     */
    onDropValue: function (node, data, overModel, dropPosition) {
        var me = this;
            me.saveValuePostion();
    },

    /**
     * Internal helper function to save current postion of values
     */
    saveValuePostion: function() {
        var store = this.getStore('Value'),
            orderedItems = [],
            index = 0;

        store.each(function(item) {
            orderedItems[index] = item.data.id;
            index +=1;
        });

        // Send current positions to backend
        Ext.Ajax.request({
            url: '{url controller="property" action="changeValuePosition"}',
            method: 'POST',
            params: {
                data : Ext.encode(orderedItems)
            }
        });
    },

    /**
     * @event addOptionToGroup
     * @param [object] tree -  Associated  Ext.tree.ViewView
     * @param [object] group -  Shopware.apps.Property.model.Group
     * @param [object] option -  Associated  Ext.tree.ViewView
     * @param [object] child - Shopware.apps.Property.model.Group
     */
    onAddOptionToGroup: function(tree, group, option, child) {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller="property" action="onAddOptionToGroup"}',
            params: {
                groupId: group.get('id'),
                optionId: option.get('id')
            },
            success: function(response, opts) {
                me.saveGroupPosition(group);
            }
        });


    },

    /**
     * Event will be fired when the user clicks the delete icon in the
     * toolbar
     *
     * @event removeOptionFromGroup
     * @param [object] node - Ext.data.NodeInterface
     * @param [object] tree - Associated  Ext.tree.ViewView
     */
    onRemoveOptionFromGroup: function(node, tree) {
        var me = this,
            group = node.parentNode,
            groupId = group.get('id');

        // Option is constructed like this groupId + "_" + optionId eG. "4_3"
        var optionId = node.get('id').split("_")[1];

        Ext.Ajax.request({
            url: '{url controller="property" action="removeOptionFromGroup"}',
            params: {
                groupId: groupId,
                optionId: optionId
            },
            success: function(response, opts) {
                node.remove();
                me.saveGroupPosition(group);
            }
        });
    },

    /**
     * Fired after a row is edited and passes validation. This event is fired
     * after the store's update event is fired with this edit.
     *
     * @event edit
     * @param [Ext.grid.plugin.Editing]
     * @param [object] An edit event
     *
     * @return void
     */
    onEditGroup: function(editor, event) {
        var me     = this,
            record = event.record,
            view   = editor.grid.getView();

        if (!record.dirty) {
            return;
        }

        if (record.get('isOption')) {
            // Option is constructed like this groupId + "_" + optionId eG. "4_3"
            var optionId = record.get('id').split("_")[1];
            var groupId = record.get('id').split("_")[0];
            var orderedItems = [];

            orderedItems.push({
                position: record.get('position'),
                groupId: groupId,
                optionId: optionId
            });

            // Send current positions to backend
            Ext.Ajax.request({
                url: '{url controller="property" action="changeGroupPosition"}',
                method: 'POST',
                params: {
                    data : Ext.encode(orderedItems)
                }
            });
            record.dirty = false;
            return;
        }

        view.setLoading(true);
        record.save({
            callback: function() {
                me.getGroupTree().addBtn.enable();
                view.setLoading(false);
            }
        });
        me.saveGroupPosition(record);
    },

    /**
     * Event will be fired when the selected option changes
     *
     * @event selectionchange
     * @param [object] Ext.selection.Model selModel
     * @param [object] Ext.data.Model[] selected
     * @return void
     */
    onOptionChange: function(selModel, selected) {
        var me    = this,
            store = me.getStore('Value'),
            grid  = me.getValueGrid();

        if (selected.length === 0) {
            grid.store.removeAll();
            grid.disable();
            return;
        }

        if (selected[0].phantom) {
            grid.store.removeAll();
            grid.disable();
            return;
        }

        store.getProxy().extraParams.optionId = selected[0].get('id');

        grid.setLoading(true);
        store.load({
            'callback': function() {
                grid.setLoading(false);
                grid.enable();
            }
        });
    },



    /**
     * Event will be fired when the user clicks the delete icon in theconfig
     * action column
     *
     * @event deleteGroup
     * @param [object] record
     * @param [object] tree - Associated Ext.tree.View
     * @return void
     */
    onDeleteGroup: function(record, tree) {
        var me    = this;

        Ext.MessageBox.confirm(me.snippets.deleteGroupConfirmTitle, me.snippets.deleteGroupConfirmMessage, function (response) {
            if (response !== 'yes') {
                return false;
            }

            record.removeAll();

            tree.setLoading(true);
            record.destroy({
                success: function() {
                    Shopware.Notification.createGrowlMessage(me.snippets.deleteGroupSuccessTitle, me.snippets.deleteGroupSuccessMessage, me.snippets.growlMessage);
                },
                failure: function() {
                    Shopware.Notification.createGrowlMessage(me.snippets.deleteGroupErrorTitle, me.snippets.deleteGroupErrorMessage, me.snippets.growlMessage);
                },
                callback: function() {
                    tree.setLoading(false);
                }
            });
        });
    },

    /**
     * Event will be fired when the user clicks the delete icon in the
     * action column
     *
     * @event deleteValue
     * @param [object] record
     * @param [object] grid - Associated Ext.view.Table
     * @return void
     */
    onDeleteOption: function(record, grid) {
        var me    = this,
            store = grid.getStore();

        Ext.MessageBox.confirm(me.snippets.deleteOptionConfirmTitle, me.snippets.deleteOptionConfirmMessage, function (response) {
            if (response !== 'yes') {
                return false;
            }

            grid.setLoading(true);
            record.destroy({
                success: function() {
                    store.remove(record);
                    me.getFilterOptionGrid().getSelectionModel().selectPrevious();
                    Shopware.Notification.createGrowlMessage(me.snippets.deleteOptionSuccessTitle, me.snippets.deleteOptionSuccessMessage, me.snippets.growlMessage);
                },
                failure: function() {
                    Shopware.Notification.createGrowlMessage(me.snippets.deleteOptionErrorTitle, me.snippets.deleteOptionErrorMessage, me.snippets.growlMessage);
                },
                callback: function() {
                    grid.setLoading(false);
                }
            });
        });
    },

    /**
     * Event will be fired when the user clicks the delete icon in the
     * action column
     *
     * @event deleteValue
     * @param [object] record
     * @param [object] grid - Associated Ext.view.Table
     * @return void
     */
    onDeleteValue: function(record, grid) {
        var me    = this,
            store = grid.getStore();

        Ext.MessageBox.confirm(me.snippets.deleteValueConfirmTitle, me.snippets.deleteValueConfirmMessage, function (response) {
            if (response !== 'yes') {
                return false;
            }

            me.getValueGrid().setLoading(true);
            record.destroy({
                success: function() {
                    store.remove(record);
                    Shopware.Notification.createGrowlMessage(me.snippets.deleteValueSuccessTitle, me.snippets.deleteValueSuccessMessage, me.snippets.growlMessage);
                },
                failure: function() {
                    Shopware.Notification.createGrowlMessage(me.snippets.deleteValueErrorTitle, me.snippets.deleteValueErrorMessage, me.snippets.growlMessage);
                },
                callback: function() {
                    me.getValueGrid().setLoading(false);
                }
            });
        });
    },

    /**
     * Fired after a row is edited and passes validation. This event is fired
     * after the store's update event is fired with this edit.
     *
     * @event edit
     * @param [Ext.grid.plugin.Editing]
     * @param [object] An edit event
     *
     * @return void
     */
    onEditValue: function(editor, event) {
        var me     = this,
            record = event.record,
            view   = editor.grid.getView(),
            valueStore = me.getStore('Value');

        if (!record.dirty) {
            return;
        }

        me.getValueGrid().setLoading(true);
        record.save({
            success: function() {
                me.saveValuePostion();
            },
            failure: function() {
                valueStore.remove(record);
            },
            callback: function() {
                me.getValueGrid().addBtn.enable();
                me.getValueGrid().setLoading(false);
            }
        });
    },


    /**
     * Fired after a row is edited and passes validation. This event is fired
     * after the store's update event is fired with this edit.
     *
     * @event edit
     * @param [Ext.grid.plugin.Editing]
     * @param [object] An edit event
     *
     * @return void
     */
    onEditOption: function(editor, event) {
        var me     = this,
            record = event.record,
            valueStore = me.getStore('Value'),
            valueGrid  = me.getValueGrid();

        if (!record.dirty) {
            return;
        }

        me.getFilterOptionGrid().setLoading(true);
        record.save({
            callback: function(record) {
                me.getFilterOptionGrid().addBtn.enable();
                me.getFilterOptionGrid().setLoading(false);

                valueStore.getProxy().extraParams.optionId = record.get('id');
                me.getFilterOptionGrid().getSelectionModel().select(record);
                valueGrid.enable();
            },
            failure: function() {
                if (record.phantom) {
                    event.store.remove(record);
                }
            }
        });
    }
});
//{/block}
