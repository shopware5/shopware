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
// Auswahl &uuml;bernehmen
/**
 * Shopware UI - Media Manager Selection Window
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 */

//{block name="backend/media_manager/view/main/selection"}
Ext.define('Shopware.apps.MediaManager.view.main.Selection', {
    extend: 'Enlight.app.Window',
    title: '{s name="selectionWindowTitle"}Mediaselection{/s}',
    cls: Ext.baseCSSPrefix + 'media-manager-window ' + Ext.baseCSSPrefix + 'media-manager-selection',
    alias: 'widget.mediamanager-selection-window',
    border: false,
    autoShow: true,
    layout: 'border',
    height: 500,

    /**
     * Forces the window to be on front
     *
     * @boolean
     * @default false
     */
    forceToFront: false,

    /**
     * Collection of used snippets.
     *
     * @object
     */
    snippets:{
        album: '{s name="selection/album"}Album{/s}',
        searchText: '{s name="selection/search_text"}Search...{/s}',
        applySelection: '{s name="selection/apply_selection"}Apply Selection{/s}'
    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.mediaView = Ext.create('Shopware.apps.MediaManager.view.media.View', {
            mediaStore: me.mediaStore,
            validTypes: me.validTypes,
            createInfoPanel: false,
            createDeleteButton: false,
            createMediaQuantitySelection: false,
            selectionMode: me.selectionMode
        });

        me.items = [{
            xtype: 'mediamanager-album-tree',
            store: me.albumStore,
            width: 155,

            // We don't need toolbars here
            createToolbars: false,

            // Deactivate the drag and drop reordering of the albums
            viewConfig: {  },

            // Customize the column model
            createColumns: function() {
                return [{
                    xtype: 'treecolumn',
                    text: me.snippets.album,
                    flex: 2,
                    sortable: true,
                    dataIndex: 'text'
                }];
            }
        }, me.mediaView
        ];

        me.bbar = me.createFooterToolbar();
        me.callParent(arguments);
    },

    /**
     * Creates the footer toolbar which features the
     * search field and the "apply selection" button
     *
     * @return [object] generated Ext.toolbar.Toolbar
     */
    createFooterToolbar: function() {
        var me = this;

        var searchField = Ext.create('Ext.form.field.Text', {
            name: 'searchfield',
            cls: 'searchfield',
            emptyText: me.snippets.searchText,
            enableKeyEvents: true,
            checkChangeBuffer: 500,
            action: 'mediamanager-selection-window-searchfield'
        });

        var addBtn = Ext.create('Ext.button.Button', {
            text: me.snippets.applySelection,
            cls: 'primary',
            action: 'mediamanager-selection-window-apply-selection',
            handler: function(btn) {
                if (Ext.isFunction(me.selectionHandler)) {
                    // Set selectionModel based on current view layout
                    var selectionModel = me.mediaView.dataView.getSelectionModel();
                    if (me.mediaView.selectedLayout === 'table') {
                        selectionModel = me.mediaView.down('mediamanager-media-grid').getSelectionModel();
                    }
                    me.selectionHandler.call(
                        me.eventScope,
                        btn,
                        me,
                        selectionModel.getSelection()
                    );
                }
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            padding: '15 0',
            items: [
                { xtype: 'tbspacer', width: 26 },
                searchField,
                '->',
                addBtn,
                { xtype: 'tbspacer', width: 6 }
            ]
        })
    }
});
//{/block}
