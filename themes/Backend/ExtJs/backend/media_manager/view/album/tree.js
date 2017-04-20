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
 * @package    MediaManager
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/media_manager/view/main}

/**
 * Shopware UI - Media Manager Album Tree
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 */
//{block name="backend/media_manager/view/album/tree"}
Ext.define('Shopware.apps.MediaManager.view.album.Tree', {
    extend: 'Ext.tree.Panel',
    alias: 'widget.mediamanager-album-tree',
    region: 'west',
    width: 230,
    rootVisible: false,
    singleExpand: true,

    /**
     * Indicates if the toolbars should be created
     * @boolean
     */
    createToolbars: true,

    // Plugin to reorder the albums
    viewConfig: {
        plugins: {
            ptype: 'treeviewdragdrop'
        }
    },

    snippets:{
        tree:{
            columns:{
                album: '{s name="tree/columns/album"}Album{/s}',
                files: '{s name="tree/columns/files"}Files{/s}',
                action: '{s name="tree/columns/action"}Action{/s}',
                editAlbumSettings: '{s name="tree/columns/editAlbumSettings"}Edit the album settings{/s}'
            },
            searchAlbum: '{s name="tree/searchAlbum"}Search album...{/s}',
            addAlbum: '{s name="tree/addAlbum"}Add album{/s}',
            deleteAlbum: '{s name="tree/deleteAlbum"}Delete album{/s}',
            createSubAlbum: '{s name="tree/createSubAlbum"}Create subalbum{/s}',
            settings: '{s name="tree/settings"}Settings{/s}',
            newAlbum: '{s name="tree/newAlbum"}Create new Album{/s}',
            refresh: '{s name="tree/refresh"}Refresh list{/s}',
            emptyTrash: '{s name="tree/emptyTrash"}Empty trash{/s}'
        }
    },

    /**
     * Initializes the component and sets the toolbars
     * and the neccessary event listener
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        // Set column model and selection model
        me.columns = me.createColumns();
        me.selModel = Ext.create('Ext.selection.RowModel', {
            allowDeselect: true,
            listeners: {
                scope: me,
                select: me.onUnlockDeleteBtn
            }
        });

        // Create toolbars
        if(me.createToolbars) {
            me.tbar = me.createSearchToolbar();
            me.bbar = me.createActionToolbar();
        }
        me.on({
            itemcontextmenu: me.onOpenItemContextMenu,
            containercontextmenu: me.onOpenContainerContextMenu,
            render: me.initializeTreeDropZone,
            scope: me
        });

        // Add events for the context menu's
        me.addEvents(
            'addSubAlbum',
            'deleteAlbum',
            'editSettings',
            'addAlbum',
            'reload',
            'refresh',
            'emptyTrash'
        );

        // Select the correct node if we're in the media selection
        me.store.on('load', function() {
            if (me.store.getProxy().extraParams && me.store.getProxy().extraParams.albumId) {
                var record = me.store.getById(
                    me.store.getProxy().extraParams.albumId
                );
                if (record) {
                    me.getSelectionModel().select(record);
                    me.fireEvent('itemclick', this, record);
                }
            }
        }, me, { single: true });

        me.callParent(arguments);
    },

    /**
     * Creates the column model for the TreePanel
     *
     * @return [array] columns - generated columns
     */
    createColumns: function() {
        var me = this;

        var columns = [{
            xtype: 'treecolumn',
            text: me.snippets.tree.columns.album,
            flex: 2,
            sortable: true,
            dataIndex: 'text'
        }, {
            xtype: 'templatecolumn',
            text: me.snippets.tree.columns.files,
            flex: 1,
            sortable: true,
            dataIndex: 'mediaCount',
            tpl: Ext.create('Ext.XTemplate', '{literal}{mediaCount}{/literal}')
        },
        /*{if {acl_is_allowed privilege=update}}*/
        {
            xtype: 'actioncolumn',
            width: 50,
            handler: function(view, rowIndex, colIndex, item, e) {
                var target = e.getTarget();
                var nodeId = target.parentNode.parentNode.parentNode.viewRecordId;
                var record = me.store.getNodeById(nodeId);

                if (record.getId() == -13) {
                    me.fireEvent('emptyTrash', me, view, record);
                } else {
                    me.fireEvent('editSettings', me, view, record);
                }
            },
            header: me.snippets.tree.columns.action,
            items: [{
                iconCls: 'sprite-gear--arrow',
                style: 'width: 16px; height:16px',
                qtip: me.snippets.tree.columns.editAlbumSettings
            }],
            renderer: function(value, meta, record) {
                if (record.getId() == -13) {
                    me.columns[2].items[0].iconCls = 'sprite-minus-circle-frame';
                } else {
                    me.columns[2].items[0].iconCls = 'sprite-gear--arrow';
                }
            }
        }
        /* {/if} */
        ];

        return columns;
    },

    /**
     * Creates the search toolbar and the search field to filter
     * the albums.
     *
     * @return [object] generated Ext.toolbar.Toolbar
     */
    createSearchToolbar: function() {
        var me = this;
        this.searchField = Ext.create('Ext.form.field.Text', {
            emptyText: me.snippets.tree.searchAlbum,
            cls: 'searchfield',
            enableKeyEvents: true,
            width: 196,
            checkChangeBuffer: 500,
            action: 'mediamanager-album-tree-search'
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            height: 35,
            items: [
                { xtype: 'tbspacer', width: 22 },
                this.searchField,
                { xtype: 'tbspacer', width: 4 }
            ]
        });
    },

    /**
     * Creates the action toolbar which includes the "add album"
     * and "delete album" buttons.
     *
     * @return [object] generated Ext.toolbar.Toolbar
     */
    createActionToolbar: function() {
        var me = this;
        /* {if {acl_is_allowed privilege=create}} */
        me.addBtn = Ext.create('Ext.button.Button', {
            text: me.snippets.tree.addAlbum,
            cls: 'small secondary',
            action: 'mediamanager-album-tree-add'
        });
        /* {/if} */

        /* {if {acl_is_allowed privilege=delete}} */
        me.deleteBtn = Ext.create('Ext.button.Button', {
            text: me.snippets.tree.deleteAlbum,
            cls: 'small secondary',
            action: 'mediamanager-album-tree-delete',
            disabled: true
        });
        /* {/if} */

        return Ext.create('Ext.toolbar.Toolbar', {
            items: [
        /* {if {acl_is_allowed privilege=create}} */
                '',
                me.addBtn,
        /* {/if} */
        /* {if {acl_is_allowed privilege=delete}} */
                '->',
                me.deleteBtn,
                ''
        /* {/if} */
            ]
        });
    },

    /**
     * Event listener method which fires when the user selects a
     * node in the Ext.tree.Panel.
     *
     * Unlocks the "delete album"-button in the action toolbar.
     *
     * @event select
     * @param [object] selModel - The Ext.selection.RowModel of the Ext.tree.Panel
     * @param [object] record - Associated Ext.data.Model for the clicked item
     * @return void
     */
    onUnlockDeleteBtn: function(selModel, record) {
        var sel = selModel.getSelection(),
            id = ~~(1 * record.get('id'));

        if(sel.length > 0 && id > 0 && record.data.leaf == true) {
            if(this.deleteBtn) {
                this.deleteBtn.setDisabled(false);
            }

            return true;
        }

        if(this.deleteBtn) {
            this.deleteBtn.setDisabled(true);
        }
    },

    /**
     * Event listener method which fires when the user performs a right click
     * on a node in the Ext.tree.Panel.
     *
     * Opens a context menu which features functions to create a new sub album,
     * to delete the selected album and to open the album settings.
     *
     * Fires the following events on the Ext.tree.Panel:
     * - addSubAlbum
     * - deleteAlbum
     * - editSettings
     *
     * @event itemcontextmenu
     * @param [object] view - HTML DOM Object of the Ext.tree.Panel
     * @param [object] record - Associated Ext.data.Model for the clicked node
     * @param [object] item HTML DOM Object of the clicked node
     * @param [integer] index - Index of the clicked node in the associated Ext.data.TreeStore
     * @param [object] event - The fired Ext.EventObject
     * @return
     */
    onOpenItemContextMenu: function(view, record, item, index, event) {
        event.preventDefault(true);

        var me = this,
            nodeId = ~~(1 * record.get('id')),
            disableStatus = (nodeId > 0 && record.data.leaf == true) ? false : true,
            isTrash = record.data.id == -13;

        var menuItems = [
            /* {if {acl_is_allowed privilege=create}} */
            {
                text: me.snippets.tree.createSubAlbum,
                iconCls: 'sprite-photo-album--plus',
                handler: function() {
                    me.fireEvent('addSubAlbum', me, view, record, item, index);
                }
            },
            /* {/if} */
            /* {if {acl_is_allowed privilege=delete}} */
            {
                text: me.snippets.tree.deleteAlbum,
                iconCls: 'sprite-photo-album--minus',
                disabled: disableStatus,
                handler: function() {
                    me.fireEvent('deleteAlbum', me, view, record, item, index);
                }
            },
            /* {/if} */
            /* {if {acl_is_allowed privilege=update}} */
            {
                text: me.snippets.tree.settings,
                iconCls: 'sprite-gear--arrow',
                handler: function() {
                    me.fireEvent('editSettings', me, view, record, item, index);
                }
            }
            /* {/if} */
        ];

        if(isTrash) {
            menuItems = [
                {
                    text: me.snippets.tree.emptyTrash,
                    iconCls: 'sprite-bin-metal-full',
                    handler: function() {
                        me.fireEvent('emptyTrash', me, view, record, item, index);
                    }
                }
            ];
        }

        var menu = Ext.create('Ext.menu.Menu', {
            items: menuItems
        });

        menu.showAt(event.getPageX(), event.getPageY());
    },

    /**
     * Event listener method which fires when the user performs a right click
     * on the Ext.tree.Panel.
     *
     * Opens a context menu which features functions to create a new album and
     * to reload the album list.
     *
     * Fires the following events on the Ext.tree.Panel:
     * - addAlbum
     * - reload
     *
     * @event containercontextmenu
     * @param [object] view - HTML DOM Object of the Ext.tree.Panel
     * @param [object] event - The fired Ext.EventObject
     * @return void
     */
    onOpenContainerContextMenu: function(view, event) {
        event.preventDefault(true);
        var me = this;

        var menu = Ext.create('Ext.menu.Menu', {
            items: [{
                text: me.snippets.tree.newAlbum,
                iconCls: 'sprite-photo-album--plus',
                handler: function() {
                    me.fireEvent('addAlbum', me, view);
                }
            }, {
                text: me.snippets.tree.refresh,
                iconCls: 'sprite-arrow-circle-315',
                handler: function() {
                    me.fireEvent('reload', me, view);
                }
            }]
        });
        menu.showAt(event.getPageX(), event.getPageY());
    },

    /**
     * Event listener method which fires when the associated
     * Ext.tree.Panel is rendered.
     *
     * Initializes the drop zone for the Ext.tree.Panel to
     * allow moving medias into different albums.
     *
     * @event render
     * @param [object] view - Rendered Ext.tree.Panel
     * @return void
     */
    initializeTreeDropZone: function(view) {
        var treeView = this.view,
            win = view.up('window'),
            mediaView = win.down('mediamanager-media-view');

        view.dropZone = Ext.create('Ext.dd.DropZone', view.getEl(), {
            ddGroup: 'media-tree-dd',
            getTargetFromEvent: function(event) {
                return event.getTarget(treeView.itemSelector);
            },

            onNodeDrop : function(target, dd, e, data) {
                var node = treeView.getRecord(target),
                    models = data.mediaModels,
                    store;

                // The event was fired from the list view
                if(!models) {
                    models = data.records;
                }
                store = models[0].store;

                Ext.each(models, function(model) {
                    model.set('newAlbumID', node.get('id'));
                });

                if (models.length > 1) {
                    view.fireEvent('startBatchMoveMedia', view, models);
                    return;
                }

                mediaView.setLoading(true);

                store.sync({
                    callback: function(){
                        mediaView.setLoading(false);
                        store.load();
                        view.fireEvent('reload');
                    }
                });
            }
        });
    }
});
//{/block}
