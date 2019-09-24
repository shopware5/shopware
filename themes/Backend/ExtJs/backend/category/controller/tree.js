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
 * @package    Category
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

/* {namespace name=backend/category/main} */

/**
 * Shopware Controller - category tree manager controller
 *
 * The category management controller handles the initialisation of the category tree.
 */
//{block name="backend/category/controller/tree"}
Ext.define('Shopware.apps.Category.controller.Tree', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Ext.app.Controller',

     /**
     * Define references for the different parts of our application. The
     * references are parsed by ExtJS and Getter methods are automatically created.
     *
     * @array
     */
    refs: [
        { ref: 'mainWindow', selector: 'category-main-window' },
        { ref: 'categoryTree', selector: 'category-category-tree' },
        { ref: 'deleteButton', selector: 'category-category-tree button[action=deleteCategory]' },
        { ref: 'duplicateButton', selector: 'category-category-tree button[action=duplicateCategory]' },
        { ref: 'saveCategoryButton', selector: 'button[action=saveDetail]' },
        { ref: 'settingsForm', selector: 'category-category-tabs-settings' },
        { ref: 'articleMappingForm', selector: 'category-category-tabs-article_mapping' }
     ],

    /**
     * Translations
     * @Object
     */
    snippets : {
        confirmMoveCategory : '{s name=tree/move_confirmation}Are you sure you want to move this category?{/s}',
        moveCategorySuccess : '{s name=tree/move_success}Category has been moved.{/s}',
        moveCategoryFailure : '{s name=tree/move_failure}Category could not be moved.{/s}',
        confirmDeleteCategoryTitle   : '{s name=tree/delete_confirmation_title}Are you sure you want to delete the category?{/s}',
        confirmDeleteCategory : '{s name=tree/delete_confirmation}Are you sure you want to delete category: [0] and all its subcategories?{/s}',
        confirmDeleteCategoryHeadline: '{s name=tree/delete_confirmation_headline}Delete this Category?{/s}',
        deleteSingleItemSuccess : '{s name=tree/delete_success}Category has been deleted.{/s}',
        deleteSingleItemFailure : '{s name=tree/delete_failure}Category could not be deleted.{/s}',
        duplicateItemSuccess : '{s name=tree/duplicate_success}Category has been duplicated.{/s}',
        duplicateItemFailure : '{s name=tree/duplicate_failure}Category could not be duplicated.{/s}',
        onSaveChangesSuccess    : '{s name=settings/save_success}Changes have been saved successfully.{/s}',
        onSaveChangesError      : '{s name=settings/save_error}An error has occurred while saving the changes.{/s}',
        emptySubcategoryField   : '{s name=tree/empty_subcategory}Required field.{/s}',
        subCategoryNameRequired : '{s name=tree/sub_category_name_required}A name is required in order to create a sub category.{/s}',
        growlMessage            : '{s name=window/main_title}{/s}'
    },

    productMappingRendered: false,

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the sub-application
     *
     * @return void
     */
    init: function() {
        var me = this;
        me.control({
            // Context Menu
            'category-category-tree':{
                'selectionchange'   : me.onSelectionChange,
                // event when ever a category is moved in the tree
                'itemmove'      : me.onCategoryMove,
                // event when ever the category tree store should be reloaded
                'reload'        : me.onReload,
                // delete event
                'deleteSubCategory' : function() { me._destroyOtherModuleInstances(me.onDeleteCategory, arguments) },
                // event when ever someone tries to add a new category into the category tree
                'addSubCategory'    : function() { me._destroyOtherModuleInstances(me.onOpenNameDialog, arguments) },
                // event when ever someone tries to duplicate a category from the category tree
                'duplicateSubCategory'    : function() { me._destroyOtherModuleInstances(me.onDuplicateCategory, arguments) },
                // event when ever someone tries to edit a category
                'itemclick'      : me.onItemClick,
                //
                'beforeDropCategory': function() { me._destroyOtherModuleInstances(me.onBeforeDrop, arguments) }
            },
             // Add Category from a dialog window, route event to the tree controller
            'category-category-tree button[action=addCategory]' : {
                'click' : function() { me._destroyOtherModuleInstances(me.onOpenNameDialog, arguments) }
            },
             // Add Category in settings tab
            'category-category-tabs-settings [action=addCategory]':{
                'click' : function() { me._destroyOtherModuleInstances(me.onAddCategory, arguments) }
            },
            'category-category-tree button[action=duplicateCategory]' : {
                'click' : function() { me._destroyOtherModuleInstances(me.onDuplicateCategory, arguments) }
            },
            // Add dialog box
            'category-category-tree button[action=deleteCategory]' : {
                'click' : function() { me._destroyOtherModuleInstances(me.onDeleteCategory, arguments) }
            },
            'duplication-settings-window': {
                'start-duplication': me.onStartDuplication
            }
        });
        // need to call parent
        me.callParent(arguments);
    },



    /**
     * Deletes one category tree node and its children
     *
     * @event deleteSubCategory
     * @return void
     */
    onDeleteCategory: function() {
        var me          = this,
            tree        = me.getCategoryTree(),
            selection   = tree.getSelectionModel( ).getSelection(),
            store = me.subApplication.getStore('Tree');

        var mainWindow = me.getMainWindow();
        mainWindow.setLoading(true);
        Ext.MessageBox.confirm(
            me.snippets.confirmDeleteCategoryHeadline,
            Ext.String.format(me.snippets.confirmDeleteCategory, selection[0].get('text')),
            function (response) {
                if (response !== 'yes') {
                    mainWindow.setLoading(false);
                    return false;
                }
                var record = selection[0],
                parentNode = record.parentNode;

                record.removeAll();
                record.childNodes = [];
                record.destroy({
                    callback: function(self, operation) {
                        mainWindow.setLoading(false);
                        var rawData = operation.records[0].proxy.reader.rawData
                        if (operation.success) {
                            Shopware.Notification.createGrowlMessage('',me.snippets.deleteSingleItemSuccess, me.snippets.growlMessage);
                            store.load({ node: parentNode });
                            me.disableForm();
                        } else {
                            if (rawData.message) {
                                Shopware.Notification.createGrowlMessage('',me.snippets.deleteSingleItemFailure + '<br>' + rawData.message, me.snippets.growlMessage);
                            } else {
                                Shopware.Notification.createGrowlMessage('',me.snippets.deleteSingleItemFailure, me.snippets.growlMessage);
                            }
                        }
                    }
                });

            });
    },

    /**
     * Duplicate selected category and children
     *
     * @return void
     */
    onDuplicateCategory: function() {
        var me          = this,
            tree        = me.getCategoryTree(),
            selection   = tree.getSelectionModel( ).getSelection(),
            record      = selection[0];

        me.getView('main.DuplicateSettings').create({
            treeRecord: record
        }).show();
    },

    /**
     * Displays a dialog box to get the name for the new created category.
     * The real adding is done on addCategory()
     *
     * @event addSubCategory
     * @return void
     */
    onOpenNameDialog:function () {
        var me = this;
        Ext.Msg.prompt('{s name=view/add_dialog_headline}Enter category name{/s}', '{s name=view/add_dialog_label}Category name{/s}', function (btn, text) {
            if (btn == 'ok') {
                if(text == "") {
                    //you can not save categories with empty names try it again
                    me.onOpenNameDialog();
                }
                else {
                    me.saveCategory(text, null);
                }
            }
        });
    },

    /**
     * Loads a record into the settings area and fires an 'recordloaded' event.
     *
     * @param view [Ext.tree.View]
     * @param record [Ext.data.Model]
     * @event editSettings
     * @return void
     */
    onItemClick : function (view, record) {
        var me = this,
            window          = me.getMainWindow(),
            mainForm        = window.formPanel,
            settingForm     = me.getSettingsForm(),
            defaultSettings = settingForm.defaultSettings,
            saveButton      = me.getSaveCategoryButton(),
            title           = settingForm.snippets.defaultSettingsTitle;

        //to always get the latest parentId on reloading or saving
        me.subApplication.treeStore.getProxy().extraParams = { node:record.get("id") };
        var detailStore = me.subApplication.getStore('Detail');
        detailStore.getProxy().extraParams = { node:record.get("id") };

        me.subApplication.availableProductsStore.getProxy().extraParams = { categoryId: record.get("id") };
        me.subApplication.assignedProductsStore.getProxy().extraParams = { categoryId: record.get("id") };

        detailStore.load({
            scope:this,
            callback:function (records) {
                var mainWindow = me.subApplication.mainWindow,
                    articleMappingContainer = mainWindow.articleMappingContainer,
                    categoryRestrictionContainer = mainWindow.categoryRestrictionContainer,
                    restrictionView;

                me.detailRecord = records[0];

                settingForm.attributeForm.loadAttribute(me.detailRecord.get('id'));

                //first reset the old data of this form
                settingForm.getForm().reset();
                // change fieldset header
                defaultSettings.setTitle(Ext.String.format(title, me.detailRecord.get('name'), me.detailRecord.get('id')));
                // load record into forms
                mainForm.loadRecord(me.detailRecord);

                var disableTab = !record.get('leaf');
                if (me.detailRecord.get('streamId')) {
                    disableTab = true;
                }

                // Just create the selection view once, if created just refresh the stores and the detail record.
                if(!me.productMappingRendered) {
                    me.selectorView = Ext.create('Shopware.apps.Category.view.category.tabs.ArticleMapping', {
                        availableProductsStore: me.subApplication.availableProductsStore,
                        assignedProductsStore: me.subApplication.assignedProductsStore,
                        record: me.detailRecord
                    });

                    me.updateTab(articleMappingContainer, me.selectorView, disableTab);
                    me.productMappingRendered = true;
                } else {
                    me.selectorView.availableProductsStore = me.subApplication.availableProductsStore;
                    me.selectorView.assignedProductsStore = me.subApplication.assignedProductsStore;
                    me.selectorView.record = me.detailRecord;
                    me.selectorView.fireEvent('storeschanged');
                    articleMappingContainer.setDisabled(disableTab);
                }

                restrictionView = Ext.create('Shopware.apps.Category.view.category.tabs.restriction', {
                    customerGroupsStore: me.subApplication.custeromGroupsStore,
                    record: me.detailRecord
                });

                me.updateTab(categoryRestrictionContainer, restrictionView, me.detailRecord.get('parentId') == 0);

                /*{if {acl_is_allowed privilege=update}}*/
                // enable save button
                saveButton.enable();
                /* {/if} */

                // fire event that a new record has been loaded.
                settingForm.fireEvent('recordloaded', me.detailRecord, record);
                settingForm.loadRecord(record);

                window.customListing.loadCategory(me.detailRecord);
                window.customSortingTab.loadCategory(me.detailRecord);
            }
        });
    },

    /**
     * Event listener before an tree item is drop to an node
     *
     * @event beforeDropCategory
     * @param [array] option The options object passed to Ext.util.Observable.addListener.
     */
    onBeforeDrop: function(options) {
        var dropHandlers = options[4],
            me = this;
        // we are processing the drop later asynchronously
        // so we just set the dropHandlers.wait property to true to delay the processing
        // instead of returning true/false from this handler
        dropHandlers.wait = true;
        Ext.MessageBox.confirm(
            me.snippets.confirmMoveCategory,
            me.snippets.confirmMoveCategory,
            function(button) {
                if (button == 'yes') {
                    dropHandlers.processDrop();
                } else {
                    dropHandlers.cancelDrop();
                }
             }
        );
    },

    /**
     * Event listener method which fires when the user
     * moves a category to a different place.
     *
     * Moves an category to a different position.
     *
     * @event itemmove
     * @return void
     * @param position
     * @param node
     * @param newParent
     * @param oldParent
     */
    onCategoryMove: function (node, oldParent, newParent, position) {
        var me = this;

        node.data.position = position;
        node.data.parentId = !newParent.isRoot() ? newParent.data.id : null;
        node.data.previousId = node.previousSibling ? node.previousSibling.data.id : null;

        var mainWindow = me.getMainWindow();
        mainWindow.setLoading(true);
        node.save({
            callback: function (self, operation) {
                mainWindow.setLoading(false);
                var rawData = self.proxy.reader.rawData;
                if (!rawData.success) {
                    Shopware.Notification.createGrowlMessage('', me.snippets.moveCategoryFailure + '<br>' + rawData.message, me.snippets.growlMessage);
                }

                Shopware.Notification.createGrowlMessage('', me.snippets.moveCategorySuccess, me.snippets.growlMessage);

                me.saveNewChildPositions(newParent);

                if(!Ext.isEmpty(operation.response)) {
                    var responseObject = Ext.decode(operation.response.responseText);

                    if (responseObject.needsRebuild) {
                        var batch = me.getView('main.MultiRequestTasks').create({
                            categoryId: node
                        }).show();
                        batch.run();
                    }
                }
            }
        });
    },

    _destroyOtherModuleInstances: function (cb, cbArgs) {
        var me = this, activeWindows = [], subAppId = me.subApplication.$subAppId;
        cbArgs = cbArgs || [];

        Ext.each(Shopware.app.Application.subApplications.items, function (subApp) {

            if (!subApp || !subApp.windowManager || subApp.$subAppId === subAppId || !subApp.windowManager.hasOwnProperty('zIndexStack')) {
                return;
            }
            Ext.each(subApp.windowManager.zIndexStack, function (item) {
                if (me.isItemInBlacklist(item)) {
                    return false;
                }

                if (typeof item !== 'undefined' && item.$className === 'Ext.window.Window' || item.$className === 'Enlight.app.Window' || item.$className === 'Ext.Window') {
                    activeWindows.push(item);
                }
                if (item.alternateClassName === 'Ext.window.Window' || item.alternateClassName === 'Enlight.app.Window' || item.alternateClassName === 'Ext.Window') {
                    activeWindows.push(item);
                }
            });
        });

        if (activeWindows && activeWindows.length) {
            Ext.each(activeWindows, function (win) {
                win.destroy();
            });

            if (Ext.isFunction(cb)) {
                cb.apply(me, cbArgs);
            }
        } else {
            if (Ext.isFunction(cb)) {
                cb.apply(me, cbArgs);
            }
        }
    },

    /**
     * Checks if the provided item should be destroyed in the category crud operations
     * @param item
     * @returns { boolean }
     */
    isItemInBlacklist: function(item) {
        var blacklist = ['widget.widget-sidebar-window'];
        var inBlackList = false;

        if (item && item.alias) {
            Ext.each(item.alias, function(alias) {
                if (blacklist.indexOf(alias) > -1) {
                    inBlackList = true;
                }
            });
        }

        return inBlackList;
    },

   /**
    * @param parent
    */
    saveNewChildPositions: function(parent) {
        var me = this,
            url = '{url controller=Category action=saveNewChildPositions}',
            childNodeIds = [];

        //save the new position for all child categories in the parent category
        parent.eachChild(function (node) {
            childNodeIds.push(node.getId());
        });

        Ext.Ajax.request({
            url: url,
            params: {
                ids: Ext.JSON.encode(childNodeIds)
            }
        });
    },

    /**
     * Toggles the delete button in the tree view based on whether there is a selection or not.
     *
     * @event selectionchange
     * @param tree [Shopware.apps.Category.view.main.CategoryTree]
     * @param selection [array]
     */
    onSelectionChange : function(tree, selection)
    {
        /* {if {acl_is_allowed privilege=delete}} */
        var me = this,
            deleteButton = me.getDeleteButton(),
            duplicateButton = me.getDuplicateButton(),
            selectedNode = selection[0];
        // do not delete the root node
        if(selection.length > 0 && ! selectedNode.isRoot() ) {
            deleteButton.enable();
            duplicateButton.enable();
        } else {
            deleteButton.disable(true);
            duplicateButton.disable(true);
        }
        /* {/if} */
    },

    /**
     *  Refreshes the tree and select the last selected node.
     *
     *  @event reload
     *  @return void
     */
    onReload : function() {
        var tree     = this.getCategoryTree(),
            store    = tree.getStore(),
            rootNode = tree.getRootNode();

        rootNode.removeAll(false);
        tree.setLoading(true);
        store.load({
            callback: function() {
                tree.setLoading(false);
            }
        });
    },

    /**
     * will be called when the add addCategory button in the settings tab is clicked
     *
     * @param button
     * @param event
     */
    onAddCategory : function(button, event) {
        var me = this,
            form = me.getSettingsForm().getForm(),
            values = form.getValues(),
            newCategoryNameField = form.findField('newCategoryName');

        if (form.isValid() && newCategoryNameField.value != "") {
            me.saveCategory(newCategoryNameField.value, values);
            newCategoryNameField.reset();
        }else{
            // SW-3377. Set allowBlank to false only temporarily as it is not required for the whole form.
            newCategoryNameField.allowBlank = false;
            newCategoryNameField.validate();
            Shopware.Notification.createGrowlMessage('', me.snippets.subCategoryNameRequired, me.snippets.growlMessage);
            newCategoryNameField.allowBlank = true;
        }
    },

    /**
     * saves a new Category to the category tree
     *
     * @param categoryName
     * @param attributeValues
     */
    saveCategory: function(categoryName, attributeValues){
        var me = this,
        selectedNode = me.getSelectedNode(),
        parentNode = selectedNode.parentNode || selectedNode;

        var newCategory = me.getModel('Tree').create({
            'parentId'  : selectedNode.getId(),
            'name'      : categoryName,
            'text'      : categoryName
        });

        if(parentNode.isLeaf) {
            //setting the node to an folder
            parentNode.data.leaf = false;
        }
        newCategory.save({
            callback:function (self, operation) {
                if (operation.success) {
                    Shopware.Notification.createGrowlMessage('', me.snippets.onSaveChangesSuccess, me.snippets.growlMessage);
                    me.reloadTree();
                } else {
                    var rawData = self.proxy.reader.rawData;
                    if (rawData.message) {
                        Shopware.Notification.createGrowlMessage('',me.snippets.onSaveChangesError + '<br>' +  rawData.message, me.snippets.growlMessage);
                    } else {
                        Shopware.Notification.createGrowlMessage('', me.snippets.onSaveChangesError, me.snippets.growlMessage);
                    }
                }
            }
        });
    },

    /**
     * reloads the tree and restores the selection
     */
    reloadTree: function() {
        var me = this,
            tree = me.getCategoryTree(),
            store = me.subApplication.treeStore,
            selectedNode = me.getSelectedNode(),
            parentNode = selectedNode.parentNode || selectedNode,
            options = {
                node : parentNode
            },
            sm = tree.getSelectionModel(),
            selectedId;

        if (sm.hasSelection()) {
            selectedId = sm.getSelection()[0].getId();
            Ext.apply(options, {
                callback : function () {
                    var node = this.store.getNodeById(this.idToSelect);

                    if (node) {
                        this.sm.select(node);
                        node.expand();
                    }
                },
                scope : {
                    store : store,
                    sm : sm,
                    idToSelect : selectedId
                }
            });
        }
        store.load(options);
    },


    /**
     * updates the given tab and add or remove it to the tabpanel
     *
     * @param tabContainer
     * @param view
     * @param disabled
     */
    updateTab: function(tabContainer, view, disabled) {

        tabContainer.setDisabled(disabled);
        tabContainer.removeAll(false);
        tabContainer.add(view);
    },

    /**
     * Disables the form which is disabled by default
     *
     * @return void
     */
    disableForm : function() {
        var me   = this,
            formPanel = me.getSettingsForm(),
            form = formPanel.getForm();

        formPanel.defaultSettings.disable();
        formPanel.createCategory.disable();
        formPanel.cmsSettings.disable();
        formPanel.metaInfo.disable();
        formPanel.attributes.disable();
        form.reset();
    },

    /**
     * helper method to return the current parentId
     *
     * @return int | parentId
     */
    getSelectedNode: function() {
        var me = this,
            tree = me.getCategoryTree(),
            treeStore = me.subApplication.treeStore,
            selection = tree.getSelectionModel().getSelection();
        // check if we have a selection otherwise we have to add the new category to the root node.
        if(selection.length > 0 && !isNaN(selection[0].get('id')) ) {
            return treeStore.getNodeById(selection[0].get('id'));
        } else {
            return treeStore.getRootNode();
        }
    },

    onStartDuplication: function(window, treeRecord) {
        var me = this,
            form = window.form,
            values = form.getValues(),
            store = me.getStore('Tree'),
            batch;

        if(!values.categoryId) {
            values.categoryId = NaN;
        }

        window.close();

        batch = me.getView('main.DuplicateTasks').create({
            categoryId: treeRecord.get('id'),
            parentId: values.categoryId,
            reassignArticleAssociations: values.reassignArticleAssociations,
            originalParentId: treeRecord.get('id'),
            callback: function() {
                store.load({ node: store.getById(values.categoryId) });
            }
        }).show();
        batch.run();
    }
});
//{/block}
