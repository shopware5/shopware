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
 * @package    Property
 * @subpackage Controller
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
        { ref: 'GroupGrid', selector: 'property-main-groupGrid' },
        { ref: 'optionGrid',        selector: 'property-main-optionGrid' },
        { ref: 'setGrid',        selector: 'property-main-setGrid' },
        { ref: 'setAssignGrid',        selector: 'property-main-setAssignGrid' }
    ],

    /**
     * Contains all snippets for the controller
     * @object
     */
    snippets: {
        // Delete option
        deleteOptionConfirmTitle:   '{s name=message/delete_option_confirm_title}Delete selected option{/s}',
        deleteOptionConfirmMessage: '{s name=message/delete_option_confirm_message}Are you sure you want to delete the selected option?{/s}',

        deleteOptionSuccessTitle: '{s name=message/delete_option_success_message}Successfully{/s}',
        deleteOptionSuccessMessage: '{s name=message/delete_option_success_title}Option has been removed{/s}',

        deleteOptionErrorTitle: '{s name=message/delete_option_error_title}Error{/s}',
        deleteOptionErrorMessage: '{s name=message/delete_option_error_message}An error has occurred.{/s}',

        // Delete group
        deleteGroupConfirmTitle:   '{s name=message/delete_group_confirm_title}Delete selected Group{/s}',
        deleteGroupConfirmMessage: '{s name=message/delete_group_confirm_message}Are you sure you want to delete the selected group?{/s}',

        deleteGroupSuccessTitle: '{s name=message/delete_group_success_message}Successfully{/s}',
        deleteGroupSuccessMessage: '{s name=message/delete_group_success_title}Group has been removed{/s}',

        deleteGroupErrorTitle: '{s name=message/delete_group_error_title}Error{/s}',
        deleteGroupErrorMessage: '{s name=message/delete_group_error_message}An error has occurred.{/s}',

        // Delete set
        deleteSetConfirmTitle:   '{s name=message/delete_group_confirm_title}Delete selected Group{/s}',
        deleteSetConfirmMessage: '{s name=message/delete_group_confirm_message}Are you sure you want to delete the selected group?{/s}',

        deleteSetSuccessTitle: '{s name=message/delete_group_success_message}Successfully{/s}',
        deleteSetSuccessMessage: '{s name=message/delete_group_success_title}Group has been removed{/s}',

        deleteSetErrorTitle: '{s name=message/delete_group_error_title}Error{/s}',
        deleteSetErrorMessage: '{s name=message/delete_group_error_message}An error has occurred.{/s}',


        // set assigned
        groupAlreadyAssigned: '{s name=message/group_already_assigned}The group was already assigned.{/s}',
        groupSuccessfulAssigned: '{s name=message/group_successful_assigned}Group successful assigned.{/s}',
        groupSuccessfulSorted: '{s name=message/group_successful_sorted}The group position has been successfully saved.{/s}',
        optionSuccessfulSorted: '{s name=message/option_successful_sorted}The option position has been successfully saved.{/s}',
        successfulRemovedAssignment: '{s name=message/group_assignment_successful_removed}The group has been successfully removed.{/s}',
        successfulSavedSet: '{s name=message/set_successful_saved}The set has been successfully saved.{/s}',

        successfulTitle: '{s name=message/successful_title}Successful{/s}',
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
            'property-main-groupGrid': {
                deleteGroup: me.onDeleteGroup,
                edit: me.onEditGroup,
                selectionchange: me.onGroupChange,
                'editGroup': me.openGroupWindow
            },

            'property-main-groupGrid textfield[action=searchGroups]': {
                change: me.onSearchGroups
            },
            'property-main-setGrid': {
                selectionchange: me.onSetChange,
                deleteSet: me.onDeleteSet,
                edit: me.onEditSet,
                'editSet': me.openSetWindow
            },
            'property-main-setGrid textfield[action=searchSets]': {
                change: me.onSearchSets
            },

            'property-main-setAssignGrid': {
                deleteAssignment: me.onRemoveSetAssignment,
                addAssignment: me.onAddGroupToSet
            },

            'property-main-setAssignGrid dataview': {
                drop: me.onDropSetAssignment,
                beforedrop: me.onBeforeDropSetAssignment,
            },

            'property-main-optionGrid': {
                deleteOption: me.onDeleteOption,
                edit: me.onEditOption,
                editOption: me.editOption
            },

            'property-main-optionGrid dataview': {
                drop: me.onDropOption
            }
        });

        me.subApplication.optionStore = me.subApplication.getStore('Option');
        me.subApplication.groupStore = me.subApplication.getStore('Group');
        me.subApplication.setStore = me.subApplication.getStore('Set');
        me.subApplication.setAssignStore = me.subApplication.getStore('SetAssign');

        me.mainWindow = me.getView('main.Window').create({
            optionStore: me.subApplication.optionStore,
            groupStore: me.subApplication.groupStore,
            setStore: me.subApplication.setStore,
            setAssignStore: me.subApplication.setAssignStore
        });

        me.mainWindow.show();

        me.callParent(arguments);
    },

    /**
     * Event listener function of the grid drag and drop plugin.
     * This event listener function checks if the dragged records
     * is already in the set assignment store.
     * If this is the case, the drop event is cancled.
     *
     * @param node
     * @param data
     * @returns { boolean }
     */
    onBeforeDropSetAssignment: function(node, data) {
        var me = this;
        var record = data.records[0];

        if (!record) {
            return false;
        }

        if (data.view.initialConfig.grid.xtype == 'property-main-setAssignGrid') {
            return true;
        }

        var inStore = me.subApplication.setAssignStore.getById(record.get('id'));

        return (inStore === null);
    },

    /**
     * Saves current positions in the grid to the backend
     *
     * @event drop
     * @return void
     */
    onDropSetAssignment: function (dragZone, element) {
        var me = this,
            assignStore = me.subApplication.setAssignStore,
            alreadyAssigned = false;

        if(element.records.length == 0){
            return;
        }
        var record = element.records[0],
            setId = assignStore.getProxy().extraParams.setId;

        if(element.view.ownerCt.alias == "widget.property-main-groupGrid") {

            assignStore.each(function(item) {
                var optionId = item.data.optionId;
                if(record.data.id == item.data.optionId) {
                    //record already assigned
                    Shopware.Notification.createGrowlMessage(me.snippets.deleteGroupErrorTitle,me.snippets.groupAlreadyAssigned, me.snippets.growlMessage);
                    me.subApplication.groupStore.load();
                    assignStore.load();
                    alreadyAssigned = true;
                }
            });
            if(!alreadyAssigned) {
                //save group assignment
                Ext.Ajax.request({
                    url: '{url controller="property" action="onAddAssignment"}',
                    params: {
                        setId: setId,
                        optionId: record.get('id')
                    },
                    success: function(response, opts) {
                        me.subApplication.groupStore.load();
                        assignStore.load();
                        Shopware.Notification.createGrowlMessage(me.snippets.successfulTitle,me.snippets.groupSuccessfulAssigned, me.snippets.growlMessage);
                    }
                });
            }
        }
        else {
            //save the sorting in the space of the grid
            me.saveAssignmentPosition(assignStore);
        }
    },


    /**
     * Internal helper function to save current postion of values
     */
    saveAssignmentPosition: function(store) {
        var me = this;
        if(store.getProxy().extraParams.length == 0) {
            return;
        }
        var orderedItems = [],
            index = 0,
            setId = store.getProxy().extraParams.setId;

        store.each(function(item) {
            orderedItems[index] = item.get('id');
            index +=1;
        });

        // Send current positions to backend
        Ext.Ajax.request({
            url: '{url controller="property" action="changeAssignmentPosition"}',
            method: 'POST',
            params: {
                setId: setId,
                data : Ext.encode(orderedItems)
            },
            success: function(response, opts) {
                Shopware.Notification.createGrowlMessage(me.snippets.successfulTitle,me.snippets.groupSuccessfulSorted, me.snippets.growlMessage);
            }
        });
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
    onDropOption: function (node, data, overModel, dropPosition) {
        var me = this;
            me.saveOptionPosition(true);
    },

    /**
     * Internal helper function to save current postion of values
     */
    saveOptionPosition: function(showSuccessMessage) {
        var me = this,
            store = me.subApplication.optionStore,
            orderedItems = [],
            index = 0;

        store.each(function(item) {
            orderedItems[index] = item.data.id;
            index +=1;
        });

        // Send current positions to backend
        Ext.Ajax.request({
            url: '{url controller="property" action="changeOptionPosition"}',
            method: 'POST',
            params: {
                data : Ext.encode(orderedItems)
            },
            success: function(response, opts) {
                if(showSuccessMessage) {
                    Shopware.Notification.createGrowlMessage(me.snippets.successfulTitle,me.snippets.optionSuccessfulSorted, me.snippets.growlMessage);
                }
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
    onAddGroupToSet: function(tree, group, option, child) {
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
     * @event deleteAssignment
     * @param [object] record
     * @param [object] grid
     */
    onRemoveSetAssignment: function(record, grid) {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller="property" action="removeOptionFromGroup"}',
            params: {
                groupId: record.get('groupId'),
                optionId: record.get('id')
            },
            success: function(response, opts) {
                Shopware.Notification.createGrowlMessage(me.snippets.successfulTitle,me.snippets.successfulRemovedAssignment, me.snippets.growlMessage);
                me.subApplication.setAssignStore.load();
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
    onEditSet: function(editor, event) {
        var me     = this,
            record = event.record,
            view   = editor.grid.getView();

        if (!record.dirty) {
            return;
        }

        record.save({
            callback: function() {
                Shopware.Notification.createGrowlMessage(me.snippets.successfulTitle,me.snippets.successfulSavedSet, me.snippets.growlMessage);
                me.subApplication.setStore.load();
                me.getSetGrid().addBtn.enable();

            }
        });
    },


    /**
     * Event will be fired when the selected option changes
     *
     * @event selectionchange
     * @param [object] Ext.selection.Model selModel
     * @param [object] Ext.data.Model[] selected
     * @return void
     */
    onSetChange: function(selModel, selected) {
        var me    = this,
            store = me.getStore('SetAssign'),
            grid  = me.getSetAssignGrid();

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

        store.getProxy().extraParams.setId = selected[0].get('id');
        grid.setLoading(true);
        store.load({
            'callback': function() {
                grid.setLoading(false);
                grid.enable();
            }
        });
    },

    /**
     * Event will be fired when the selected option changes
     *
     * @event selectionchange
     * @param [object] Ext.selection.Model selModel
     * @param [object] Ext.data.Model[] selected
     * @return void
     */
    onGroupChange: function(selModel, selected) {
        var me    = this,
            store = me.subApplication.optionStore,
            grid  = me.getOptionGrid();

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
    onDeleteSet: function(record, tree) {
        var me    = this;

        Ext.MessageBox.confirm(me.snippets.deleteSetConfirmTitle, me.snippets.deleteSetConfirmMessage, function (response) {
            if (response !== 'yes') {
                return false;
            }

            record.destroy({
                success: function() {
                    Shopware.Notification.createGrowlMessage(me.snippets.deleteSetSuccessTitle, me.snippets.deleteSetSuccessMessage, me.snippets.growlMessage);
                },
                failure: function() {
                    Shopware.Notification.createGrowlMessage(me.snippets.deleteSetErrorTitle, me.snippets.deleteSetErrorMessage, me.snippets.growlMessage);
                },
                callback: function() {
                    me.subApplication.setStore.load();
                }
            });
        });
    },

    /**
     * Event will be fired when the user clicks the delete icon in the
     * action column
     *
     * @event deleteGroup
     * @param [object] record
     * @param [object] grid - Associated Ext.view.Table
     * @return void
     */
    onDeleteGroup: function(record, grid) {
        var me    = this,
            store = grid.getStore();

        Ext.MessageBox.confirm(me.snippets.deleteGroupConfirmTitle, me.snippets.deleteGroupConfirmMessage, function (response) {
            if (response !== 'yes') {
                return false;
            }

            grid.setLoading(true);
            record.destroy({
                success: function() {
                    store.remove(record);
                    me.getGroupGrid().getSelectionModel().selectPrevious();
                    Shopware.Notification.createGrowlMessage(me.snippets.deleteGroupSuccessTitle, me.snippets.deleteGroupSuccessMessage, me.snippets.growlMessage);
                },
                failure: function() {
                    Shopware.Notification.createGrowlMessage(me.snippets.deleteGroupErrorTitle, me.snippets.deleteGroupErrorMessage, me.snippets.growlMessage);
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
     * @event deleteOption
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

            me.getOptionGrid().setLoading(true);
            record.destroy({
                success: function() {
                    store.remove(record);
                    Shopware.Notification.createGrowlMessage(me.snippets.deleteOptionSuccessTitle, me.snippets.deleteOptionSuccessMessage, me.snippets.growlMessage);
                },
                failure: function() {
                    Shopware.Notification.createGrowlMessage(me.snippets.deleteOptionErrorTitle, me.snippets.deleteOptionErrorMessage, me.snippets.growlMessage);
                },
                callback: function() {
                    me.getOptionGrid().setLoading(false);
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
    onEditOption: function(editor, event) {
        var me     = this,
            record = event.record,
            view   = editor.grid.getView(),
            optionStore = me.subApplication.optionStore,
            optionGrid = me.getOptionGrid();

        if (!record.dirty) {
            return;
        }

        optionGrid.setLoading(true);
        record.save({
            success: function() {
                me.saveOptionPosition();
            },
            failure: function() {
                optionStore.remove(record);
            },
            callback: function() {
                optionGrid.addBtn.enable();
                optionGrid.setLoading(false);
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
            optionGrid  = me.getOptionGrid(),
            groupGrid = me.getGroupGrid();

        if (!record.dirty) {
            return;
        }

        groupGrid.setLoading(true);
        record.save({
            callback: function(record) {
                groupGrid.addBtn.enable();
                groupGrid.setLoading(false);

                optionGrid.getStore().getProxy().setExtraParam('optionId', record.get('id'));
                groupGrid.getSelectionModel().select(record);
                optionGrid.enable();
            },
            failure: function() {
                if (record.phantom) {
                    event.store.remove(record);
                }
            }
        });
    },

    /**
     * Filters the grid with the passed search value to find the right item
     *
     * @param field
     * @param value
     * @return void
     */
    onSearchGroups:function (field, value) {
        var me = this,
            searchString = Ext.String.trim(value),
            store = me.subApplication.groupStore;
        store.filters.clear();
        store.currentPage = 1;
        store.filter('filter',searchString);
    },


    /**
     * Filters the grid with the passed search value to find the right item
     *
     * @param field
     * @param value
     * @return void
     */
    onSearchSets:function (field, value) {
        var me = this,
            searchString = Ext.String.trim(value),
            store = me.subApplication.setStore;
        store.filters.clear();
        store.currentPage = 1;
        store.filter('filter',searchString);
    },

    openGroupWindow: function (record) {
        var me = this;
        var listing = me.getGroupGrid();

        me.groupWindow = me.getView('detail.GroupWindow').create({
            record: record
        });
        me.groupWindow.show();
        me.groupWindow.on('record-saved', function () {
            listing.getStore().load();
        });
    },

    openSetWindow: function (record) {
        var me = this;
        var listing = me.getSetGrid();

        me.setWindow = me.getView('detail.SetWindow').create({
            record: record
        });
        me.setWindow.show();
        me.setWindow.on('record-saved', function () {
            listing.getStore().load();
        });
    },

    editOption: function(record) {
        var me = this;
        var listing = me.getOptionGrid();

        me.optionWindow = me.getView('detail.OptionWindow').create({
            record: record
        });
        me.optionWindow.show();
        me.optionWindow.on('record-saved', function () {
            listing.getStore().load();
        });
    }
});
//{/block}
