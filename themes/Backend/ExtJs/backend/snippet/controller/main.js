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
 */

//{namespace name=backend/snippet/controller/main}

/**
 * todo@all: Documentation
 */
//{block name="backend/snippet/controller/main"}
Ext.define('Shopware.apps.Snippet.controller.Main', {

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
        { ref: 'navigationTree', selector: 'snippet-main-navigation' },
        { ref: 'snippetPanel', selector: 'snippet-main-snippetPanel' },
        { ref: 'expertButton', selector: 'snippet-main-window button[action=expert]' }
    ],

    /**
     * Contains all snippets for the controller
     * @object
     */
    snippets: {
        singleDeleteTitle: '{s name=message/delete_single_title}Delete selected snippet{/s}',
        singleDeleteMessage: '{s name=message/delete_single_message}Are you sure, you want to delete the selected snippet: {/s}',

        deleteNamespaceTitle: '{s name=message/delete_namespace_title}Delete selected namespace{/s}',
        deleteNamespaceMessage: '{s name=message/delete_namespace_message}Are you sure, you want to delete the selected Namespace?{/s}',

        deleteNamespaceSuccessTitle: '{s name=message/delete_namespace_success_message}Successfully{/s}',
        deleteNamespaceSuccessMessage: '{s name=message/delete_namespace_success_title}Namespace has been removed{/s}',

        deleteSuccessTitle: '{s name=message/delete_success_message}Successfully{/s}',
        deleteSuccessMessage: '{s name=message/delete_success_title}Snippet has been removed{/s}',

        deleteErrorTitle: '{s name=message/delete_error_title}Failure{/s}',
        deleteErrorMessage: '{s name=message/delete_error_message}During deleting an error has occurred.{/s}',

        saveSuccessTitle: '{s name=message/save_success_message}Successfully{/s}',
        saveSuccessMessage: '{s name=message/save_success_title}Snippets have been saved{/s}',

        saveErrorTitle: '{s name=message/save_error_title}Failure{/s}',
        saveErrorMessage: '{s name=message/save_error_message}During saving an error has occurred.{/s}',

        createSuccessTitle: '{s name=message/save_success_message}Successfully{/s}',
        createSuccessMessage: '{s name=message/save_success_title}Snippet has been created{/s}',

        createErrorTitle: '{s name=message/save_error_title}Failure{/s}',
        createErrorMessage: '{s name=message/save_error_message}During saving an error has occurred.{/s}',

        growlMessage: '{s name=title}Snippet{/s}'
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
            'snippet-main-navigation': {
                select:          me.onSelect,
                deleteNamespace: me.onDeleteNamespace
            },

            'snippet-main-grid': {
                edit:         me.onEdit,
                beforeedit:   me.onBeforeEdit,
                deleteSingle: me.onDeleteSingle,
                editSelectedSnippets: me.onOpenEditSnippetWindow,
                translateSnippet: me.onTranslate
            },

            'snippet-main-snippetPanel': {
                tabchange: me.onTabChange
            },

            'snippet-main-grid textfield[action=search]': {
                change: me.onSearch
            },

            'snippet-main-editForm button[action=save]': {
                click: me.onSaveForm
            },

            'snippet-main-translateForm button[action=save]': {
                click: me.onSaveTranslationForm
            },

            'snippet-main-window button[action=expert]': {
                toggle: me.onToggleExpert
            },

            'snippet-main-grid button[action=add-snippet]': {
                click: me.onOpenAddSnippetWindow
            },

            'snippet-main-window button[action=export]': {
                click: me.onOpenExportWindow
            },

            'snippet-main-grid button[action=filterEmpty]': {
                toggle: me.onToggleEmpty
            },

            'snippet-main-createForm button[action=save]': {
                click: me.onCreateRecord
            }
        });

        me.getStore('Snippet').proxy.extraParams.localeId = 1;
        me.getStore('Snippet').proxy.extraParams.shopId   = 1;
        me.getStore('Snippet').load();

        var localeStore = me.getStore('Shoplocale');
        if (me.subApplication.shopId) {
            localeStore.filter({ property: 'shopId', value: me.subApplication.shopId })
        }

        localeStore.load({
            callback: function(records) {
                if (me.subApplication.action && me.subApplication.action === 'detail') {
                    var snippet = Ext.create('Shopware.apps.Snippet.model.Snippet', me.subApplication.snippet);
                    me.onTranslate(snippet);
                } else {
                    Ext.Ajax.request({
                        url: '{url controller=UserConfig action=get}',
                        params: {
                            name: 'snippet_module'
                        },
                        callback: function (request, success, response) {
                            var config = Ext.JSON.decode(response.responseText);

                            if (!config || config.length <= 0) {
                                config = { extendedMode: false };
                            }

                            me.subApplication.userConfig = config;

                            me.mainWindow = me.getView('main.Window').create({
                                nSpaceStore:     me.getStore('NSpace'),
                                snippetStore:    me.getStore('Snippet'),
                                shoplocaleStore: localeStore
                            });
                            me.subApplication.setAppWindow(me.mainWindow);
                            me.mainWindow.show();

                            me.getExpertButton().toggle(config.extendedMode);
                        }
                    });
                }
            }
        });

        me.callParent(arguments);
    },

    /**
     * Accessor accessor for the current active grid
     *
     * @return [object]
     */
    getSnippetGrid: function() {
        var me        = this,
            activeTab = me.getSnippetPanel().getActiveTab();

        return activeTab;
    },

    /**
     * Event listener which deletes a namespace
     *
     * @param [Ext.data.Model] record
     * @return void
     */
    onDeleteNamespace: function(record) {
        var me  = this,
            tree = me.getNavigationTree(),
            selModel = tree.getSelectionModel();

        Ext.MessageBox.confirm(me.snippets.deleteNamespaceTitle, me.snippets.deleteNamespaceMessage, function (response) {
            if (response !== 'yes') {
                return false;
            }

            record.destroy({
                success: function() {
                    Shopware.Notification.createGrowlMessage(me.snippets.deleteNamespaceSuccessTitle, me.snippets.deleteNamespaceSuccessMessage, me.snippets.growlMessage);
                },
                callback: function() {
                    selModel.select(0, false);
                    me.reloadTree();
                }
            });
        });
    },

    /**
     * Event listener which deletes a single snippet
     *
     * @param [Ext.grid.View] grid - The grid on which the event has been fired
     * @param [integer] rowIndex - On which row position has been clicked
     * @return void
     */
    onDeleteSingle: function (grid, rowIndex) {
        var me      = this,
            store   = grid.getStore(),
            record  = store.getAt(rowIndex);

        Ext.MessageBox.confirm(me.snippets.singleDeleteTitle, me.snippets.singleDeleteMessage, function (response) {
            if (response !== 'yes') {
                return false;
            }

            me.getStore('Snippet').remove(record)

            me.getStore('Snippet').sync({
                success: function() {
                    Shopware.Notification.createGrowlMessage(me.snippets.deleteSuccessTitle, me.snippets.deleteSuccessMessage, me.snippets.growlMessage);
                },
                failure: function() {
                    Shopware.Notification.createGrowlMessage(me.snippets.deleteErrorTitle, me.snippets.deleteErrorMessage, me.snippets.growlMessage);
                },
                callback: function() {
                    me.reloadTree();
                    store.load();
                }
            });
        });
    },

    /**
     * Function to create a new record
     *
     * @event click
     * @param [object] btn Contains the clicked button
     * @return void
     */
    onCreateRecord: function(btn) {
        var me         = this,
            win        = btn.up('window'),
            formPanel  = win.down('form'),
            form       = formPanel.getForm(),
            record     = Ext.create('Shopware.apps.Snippet.model.Snippet');

        if (!form.isValid()) {
            return;
        }

        form.updateRecord(record);
        me.getStore('Snippet').add(record)

        me.getStore('Snippet').sync({
            success : function() {
                Shopware.Notification.createGrowlMessage(me.snippets.createSuccessTitle, me.snippets.createSuccessMessage, me.snippets.growlMessage);
            },
            failure: function() {
                Shopware.Notification.createGrowlMessage(me.snippets.createErrorTitle, me.snippets.createErrorMessage, me.snippets.growlMessage);
            },
            callback: function() {
                me.getStore('Snippet').load();
                me.reloadTree();
            }
        });

        win.destroy();
    },


    /**
     * Function to save a form
     *
     * @event click
     * @param [object] btn Contains the clicked button
     * @return void
     */
    onSaveForm: function(btn) {
        var me         = this,
            win        = btn.up('window'),
            formPanel  = win.down('form'),
            form       = formPanel.getForm(),
            values     = form.getValues(false, true),
            store      = me.getStore('Snippet'),
            record     = null,
            newRecords = false;

        if (!form.isValid()) {
            return;
        }

        Ext.iterate(values, function(internalId, value) {
            store.each(function(item) {
                // double equals instead of triple equals intended
                if (item.internalId == internalId) {
                    record = item;
                    record.set('value', value);

                    if (record.get('id') === null) {
                        // set phantom true to call create event instead of update
                        record.phantom = true;
                        newRecords = true;
                    }
                    return false;
                }
            });
        });

        // if the store contains new records disable batch mode
        store.getProxy().batchActions = !newRecords;

        store.sync({
            success : function() {
                Shopware.Notification.createGrowlMessage(me.snippets.saveSuccessTitle, me.snippets.saveSuccessMessage, me.snippets.growlMessage);
                me.getStore('Snippet').reload();
            },
            failure: function() {
                Shopware.Notification.createGrowlMessage(me.snippets.saveErrorTitle, me.snippets.saveErrorMessage, me.snippets.growlMessage);
            }
        });

        // some more cleanup to do?
        win.destroy();
    },


    /**
     * Function to save a form
     *
     * @event click
     * @param [object] btn Contains the clicked button
     * @return void
     */
    onSaveTranslationForm: function(btn) {
        var me         = this,
            win        = btn.up('window'),
            formPanel  = win.down('form'),
            form       = formPanel.getForm(),
            values     = form.getValues(false, true),
            store      = formPanel.snippetStore,
            record     = null,
            newRecords = false;

        if (!form.isValid()) {
            return;
        }

        Ext.iterate(values, function(internalId, value) {
            store.each(function(item) {
                // double equals instead of triple equals intended
                if (item.internalId == internalId) {

                    record = item;
                    record.set('value', value);

                    if (record.get('id') === null) {
                        // set phantom true to call create event instead of update
                        record.phantom = true;
                        newRecords = true;
                    }
                    return false;
                }
            });
        });

        // if the store contains new records disable batch mode
        store.getProxy().batchActions = !newRecords;

        store.sync({
            success : function() {
                Shopware.Notification.createGrowlMessage(me.snippets.saveSuccessTitle, me.snippets.saveSuccessMessage, me.snippets.growlMessage);
                me.getStore('Snippet').reload();
            },
            failure: function() {
                Shopware.Notification.createGrowlMessage(me.snippets.saveErrorTitle, me.snippets.saveErrorMessage, me.snippets.growlMessage);
            }
        });

        // some more cleanup to do?
        win.destroy();
    },

    /**
     * @event tabchange
     * @param [Ext.tab.Panel] tabPanel
     * @param [Ext.Component] newCard
     *
     * @return void
     */
    onTabChange: function(tabPanel, newCard, oldCard) {
        var me    = this,
            store = me.getStore('Snippet');

        store.proxy.extraParams.localeId    = newCard.shoplocale.get('localeId');
        store.proxy.extraParams.shopId      = newCard.shoplocale.get('shopId');
        store.proxy.extraParams.filterEmpty = null;
        store.currentPage = 1;
        store.filters.clear();

        newCard.setLoading(true);
        store.load({
            callback: function() {
                newCard.setLoading(false);
            }
        });
    },

    /**
     * Fires when the 'pressed' state of the "Expert"-button changes
     *
     * @event toggle
     * @param [Ext.button.Button] btn
     * @param [boolean] isPressed
     * @return void
     */
    onToggleExpert: function(btn, isPressed) {
        var me = this, config = me.subApplication.userConfig;

        config.extendedMode = isPressed;

        Ext.Ajax.request({
            url: '{url controller=UserConfig action=save}',
            params: {
                config: Ext.JSON.encode(config),
                name: 'snippet_module'
            }
        });

        me.getSnippetPanel().enableExpertMode(isPressed);
   },

    /**
     * Fires when the 'Add Snippet' button is clicked
     *
     * @event click
     * @param [Ext.button.Button] btn
     * @return void
     */
    onOpenAddSnippetWindow: function(btn) {
        var me               = this,
            shoplocale       = me.getSnippetGrid().shoplocale,
            defaultNamespace = '',
            lastSelected     = me.getNavigationTree().getSelectionModel().getLastSelected();

        if (lastSelected !== null && !lastSelected.isRoot()) {
            defaultNamespace = lastSelected.get('id');
        }

        me.getView('main.CreateForm').create({
            defaultLocaleId:  shoplocale.get('localeId'),
            defaultShopId:    shoplocale.get('shopId'),
            defaultNamespace: defaultNamespace
        }).show();
    },

    /**
     * Fires when the 'Edit selected snippets' button is clicked
     *
     * @event click
     * @param [array]
     * @return void
     */
    onOpenEditSnippetWindow: function(selectedSnippets) {
        var me = this;

        me.getView('main.EditForm').create({
            selectedSnippets: selectedSnippets
        }).show();
    },

    /**
     * Fires when the 'Translate snippets' button is clicked
     *
     * @event click
     * @param [object] record
     * @return void
     */
    onTranslate: function(snippet) {
        var me = this,
            snippetStore = Ext.create("Shopware.apps.Snippet.store.Snippet");

        snippetStore.proxy.extraParams = {};
        snippetStore.proxy.extraParams.name = snippet.get('name');
        snippetStore.proxy.extraParams.namespace = snippet.get('namespace');

        snippetStore.load({
            callback: function() {
                me.getView('main.TranslateWindow').create({
                    rootSnippet: snippet,
                    snippetStore: snippetStore,
                    shopLocaleStore: me.getStore('Shoplocale')
                }).show();
            }
        });
    },

    /**
     * Fires when the "Import/Export" button is clicked
     *
     * Opens a "Import/Export"-Window
     *
     * @event click
     * @return void
     */
    onOpenExportWindow: function() {
        var me    = this;

        me.getView('main.ImportExport').create().show();
    },

    /**
     * Fires when the 'pressed' state of the "Empty"-button changes
     *
     * @event toggle
     * @param [Ext.button.Button] btn
     * @param [boolean] isPressed
     * @return void
     */
    onToggleEmpty: function(btn, isPressed) {
        var me    = this,
            store = me.getStore('Snippet');

        //scroll the store to first page
        store.currentPage = 1;

        if (isPressed) {
            store.getProxy().extraParams.filterEmpty = true;

            store.filters.add('emptyFilter', new Ext.util.Filter({
                property: 'filterEmpty',
                value: true
            }));
        } else {
            store.filters.removeAtKey('emptyFilter');
        }

        store.filter();
    },

    /**
     * Event listener method which will be fired when the user
     * insert a value in the search field on the right hand of the module
     *
     * @event change
     * @param [object] field - Ext.form.field.Text
     * @param [string] value - inserted search value
     * @return void
     */
    onSearch: function(field, value) {
        var store        = this.getStore('Snippet'),
            searchString = Ext.String.trim(value);

        //scroll the store to first page
        store.currentPage = 1;

        if (searchString.length === 0 ) {
            store.filters.removeAtKey('searchFilter');
        } else {
            store.filters.add('searchFilter', new Ext.util.Filter({
                property: 'search',
                value: searchString
            }));
        }
        store.filter();
    },

    /**
     * @event select
     * @param [Ext.selection.RowModel] rowModel
     * @param [Ext.data.Model] record
     * @param [Number] index
     *
     * @return boolean
     */
    onSelect: function(rowModel, record, index) {
        var me    = this,
            store = me.getStore('Snippet'),
            grid  = me.getSnippetGrid();

        if (record.get('id') === 'root') {
            store.getProxy().extraParams.namespace = null;
        } else {
            store.getProxy().extraParams.namespace = record.get('id');
        }

        //scroll the store to first page
        store.currentPage = 1;

        grid.setLoading(true);
        store.load({
            callback: function() {
                grid.setLoading(false);
            }
        });
    },

    /**
     * Fires before editing is triggered.
     * Used to prevent editing if user has insufficient permissions
     *
     * @event beforeedit
     * @return void
     */
    onBeforeEdit: function() {
        /*{if !{acl_is_allowed privilege=update}}*/
        return false;
        /*{/if}*/
    },

    /**
     * Fired after a row is edited and passes validation. This event is fired
     * after the store's update event is fired with this edit.
     *
     * @event edit
     * @param [Ext.grid.plugin.Editing]
     * @param [object] An edit event with the following properties:
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
    onEdit: function(editor, event) {
        var me     = this,
            record = event.record,
            view   = editor.grid;

        if (!record.dirty) {
            return;
        }

        if (record.get('id') === null) {
            // set phantom true to call create event instead of update
            record.phantom = true;
        }

        view.setLoading(true);
        me.getStore('Snippet').sync({
            callback: function() {
                view.setLoading(false);

                // If the namespace of a record has changed, reload the tree
                // to reflect the changes in the navigtaion
                if (event.originalValues.namespace !== event.newValues.namespace) {
                    me.reloadTree();
                }
            }
        });
    },

    /**
     * Reloads the Navigation tree
     *
     * Reloads the store and expands the path to the previously selected node
     * and selects it without firing the select event
     *
     * @return void
     */
    reloadTree: function() {
        var me           = this,
            tree         = me.getNavigationTree(),
            store        = me.getStore('NSpace'),
            rootNode     = tree.getRootNode(),
            selModel     = tree.getSelectionModel(),
            lastSelected = selModel.getLastSelected();

        rootNode.removeAll(false);
        tree.setLoading(true);
        store.load({
            callback: function() {
                tree.setLoading(false);

                if (lastSelected !== null) {
                    lastSelected = store.getNodeById(lastSelected.get('id'));
                } else {
                    lastSelected = false;
                }

                if (lastSelected) {
                    lastSelected.bubble(function(node) {
                        node.expand();
                    });

                    // select node but do not fire event
                    selModel.select(lastSelected, false, true);
                }
            }
        });
    }
});
//{/block}
