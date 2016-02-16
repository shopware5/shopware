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
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

/* {namespace name=backend/category/main} */

/**
 * Shopware UI - Category management main window.
 *
 * This component contains a category tree. This tree is uses to manage
 * the hierarchical order of categories. Its provides methods to create, delete, rearrange categories.
 */
//{block name="backend/category/view/category/tree"}
Ext.define('Shopware.apps.Category.view.category.Tree', {
    /**
    * Parent Element Ext.tree.Panel
    * @string
    */
    extend: 'Ext.tree.Panel',
    /**
     * Register the alias for this class.
     * @string
     */
    alias : 'widget.category-category-tree',
    /**
     * True to make the panel collapsible and have an expand/collapse toggle
     * Tool added into the header tool button area.
     * False to keep the panel sized either statically, or by an owning layout manager, with no toggle Tool.
     *
     * @boolean
     */
    collapsible: false,

    region   : 'west',
    /**
     * The Store the tree should use as its data source.
     * @string
     */
    store: 'Tree',
    /**
     * False to hide the root node.
     * @boolean
     */
    rootVisible: true,
    /**
     * True to use Vista-style arrows in the tree.
     * @boolean
     */
    useArrows: false,
    /**
     * The width of this component in pixels.
     * @integer
     */
    width: 300,
    /**
     * Plugins and plugin configurations
     * @object
     */
    viewConfig: {
        plugins: {
            ptype: 'treeviewdragdrop'
        }
    },
    /**
     * Array containing docked items
     * @array
     */
    dockedItems: [ ],
    /**
     * Translations
     * @object
     */
    snippets : {
        // Grid headers
        columnCategoryHeader : '{s name=view/category_column_title}Catergories{/s}',
        columnActionHeader : '{s name=view/action_column_title}Action{/s}',
        columnArticleHeader : '{s name=view/articles_column_title}Articles{/s}',
        // Context menu
        contextAddSubCategory : '{s name=view/context_add_category}Add new{/s}',
        contextDuplicateSubCategory : '{s name=view/context_duplicate_category}Duplicate{/s}',
        contextDeleteSubCategory : '{s name=view/context_delete_category}Delete{/s}',
        contextReloadTree : '{s name=view/context_reload_tree}Reload{/s}',

        treeAdd : '{s name=view/tree_add}Add new{/s}',
        treeDuplicate : '{s name=view/tree_duplicate}Duplicate{/s}',
        treeDelete : '{s name=view/tree_delete}Delete{/s}'
    },
    /**
     * Name of the root node. We have to show the root node in order to move a subcategory under the root.
     * @string
     */
    rootNodeName : 'Shopware',

     /**
     * Initialize the controller and defines the necessary default configuration
     */
    initComponent : function() {
        var me = this;
        me.registerEvents();
        me.columns = me.createColumns();
        me.selModel = Ext.create('Ext.selection.RowModel', {
        });
        me.on({
            // Context menu on items
            itemcontextmenu: me.onOpenItemContextMenu,
            // Context menu on container
            containercontextmenu: me.onOpenContainerContextMenu,
            // scope
            scope: me
        });

        // rename root node to shopware.
        var rootNode = me.getStore().getRootNode();
        rootNode.data.text = me.rootNodeName;
        me.dockedItems = me.createMenu();
        me.callParent(arguments);
        // forward the beforedrop event to the controller
        // Used a different name to avoid event loops
        me.view.on('beforedrop', function() {
            me.fireEvent('beforeDropCategory', arguments);
        });
    },

    /**
     * Event listener method which fires when the user performs a right click
     * on a node in the Ext.tree.Panel.
     *
     * Opens a context menu which features functions to create a new sub category,
     * to delete the selected category including all children.
     *
     * Fires the following events on the Ext.tree.Panel:
     * - addSubCategory
     * - deleteSubCategory
     *
     * @event itemcontextmenu
     * @param [object] view - HTML DOM Object of the Ext.tree.Panel
     * @param [object] record - Associated Ext.data.Model for the clicked node
     * @param [object] item HTML DOM Object of the clicked node
     * @param [integer] index - Index of the clicked node in the associated Ext.data.TreeStore
     * @param [object] event - The fired Ext.EventObject
     * @return void
     */
    onOpenItemContextMenu : function(view, record, item, index, event) {
        event.preventDefault(true);
        var me = this,
            nodeId = ~~(1 * record.get('id')),
            disableStatus = (nodeId > 0 ) ? false : true,
            menuElements = [];
        /*{if {acl_is_allowed privilege=create}}*/
        menuElements.push({
                text: me.snippets.contextAddSubCategory,
                iconCls: 'sprite-plus-circle',
                handler: function() {
                    me.fireEvent('addSubCategory', record, item, index);
                }
        });
        /* {/if} */
        /*{if {acl_is_allowed privilege=create}}*/
        menuElements.push({
            text: me.snippets.contextDuplicateSubCategory,
            iconCls: 'sprite-document-copy',
            disabled: disableStatus,
            handler: function() {
                me.fireEvent('duplicateSubCategory', record, item, index);
            }
        });
        /* {/if} */
        /* {if {acl_is_allowed privilege=delete}} */
        menuElements.push({
                text: me.snippets.contextDeleteSubCategory,
                iconCls: 'sprite-minus-circle',
                disabled: disableStatus,
                handler: function() {
                    me.fireEvent('deleteSubCategory', me, view, record, item, index);
                }
            });
        /* {/if} */
        var menu = Ext.create('Ext.menu.Menu', {
            items: menuElements
        });
        menu.showAt(event.getPageX(), event.getPageY());
    },
     /**
     * Event listener method which fires when the user performs a right click
     * on the Ext.tree.Panel.
     *
     * Opens a context menu which features functions to create a category and
     * to reload the category list.
     *
     * Fires the following events on the Ext.tree.Panel:
     * - addSubCategory
     * - reload
     *
     * @event containercontextmenu
     * @param [object] view - HTML DOM Object of the Ext.tree.Panel
     * @param [object] event - The fired Ext.EventObject
     * @return void
     */
     onOpenContainerContextMenu : function(view, event) {
         event.preventDefault(true);
         var me = this,
             menuElements = [];
         /* {if {acl_is_allowed privilege=create}} */
         menuElements.push({
             text:me.snippets.contextAddSubCategory,
             iconCls: 'sprite-plus-circle',
             handler:function () {
                 me.fireEvent('addSubCategory');
             }
         });
         /* {/if} */
         menuElements.push({
             text:me.snippets.contextReloadTree,
             iconCls:'sprite-arrow-circle-315',
             handler:function () {
                 me.fireEvent('reload', me, view);
             }
         });

         var menu = Ext.create('Ext.menu.Menu', {
             items:menuElements
         });
         menu.showAt(event.getPageX(), event.getPageY());
    },

    /**
     * Builds and returns the footer menu
     *
     * @return [array]
     */
    createMenu : function()
    {
        var me   = this,
            menu = [];
        /* {if {acl_is_allowed privilege=create}} */
        menu.push({
            text   : me.snippets.treeAdd,
            iconCls: 'sprite-plus-circle',
            action : 'addCategory',
            cls    : 'addBtn small secondary'
        });
        menu.push( '->');
        /* {/if} */
        /* {if {acl_is_allowed privilege=create}} */
        menu.push({
            text     : me.snippets.treeDuplicate,
            iconCls  : 'sprite-document-copy',
            action   : 'duplicateCategory',
            disabled : true,
            cls      : 'small secondary'
        });
        /* {/if} */
        /* {if {acl_is_allowed privilege=delete}} */
        menu.push({
            text     : me.snippets.treeDelete,
            iconCls  : 'sprite-minus-circle',
            action   : 'deleteCategory',
            disabled : true,
            cls      : 'deleteBtn small secondary'
        });
        /* {/if} */

        menu.push({
            xtype: 'tbspacer',
            width: 5
        });
        return [{
            xtype:'toolbar',
            dock:'bottom',
            items:menu,
            cls: Ext.baseCSSPrefix + 'tree-toolbar'
        }]
    },
    /**
     * Creates the column model for the TreePanel
     *
     * @return [array] columns - generated columns
     */
    createColumns : function() {
        var me = this,
            columns = [{
                xtype: 'treecolumn',
                text: me.snippets.columnCategoryHeader,
                sortable: false,
                flex:1,
                renderer: me.categoryFolderRenderer,
                dataIndex: 'text'
            }];

        return columns;
    },
     /**
     * Defines additional events which will be
     * fired from the component
     *
     * @return void
     */
    registerEvents : function() {
        this.addEvents(

            /**
             * Event will be fired when the user clicks the edit icon in the
             * action column
             *
             * This event can easily be captured in the controller
             * eg.
             * <code>
             * this.control({ 'editSettings' : function(){
             *     console.log('the editSettings button has been pressed.');
             * }
             * </code>
             *
             * @event editSettings
             * @param [object] View - Associated Ext.view.Table
             * @param [integer] rowIndex - Row index
             * @param [integer] colIndex - Column index
             * @param [object] item - Associated HTML DOM node
             */
            'editSettings',
            /**
             * Event will be fired when the user clicks the add icon in the
             * action column or in the footer menu.
             *
             * This event can easily be captured in the controller
             * eg.
             * <code>
             * this.control({ 'addSubCategory' : function(){
             *     console.log('the addSubCategory button has been pressed.');
             * }
             * </code>
             *
             * @event addSubCategory
             * @param [object] View - Associated Ext.view.Table
             * @param [integer] rowIndex - Row index
             * @param [integer] colIndex - Column index
             * @param [object] item - Associated HTML DOM node
             */
            'addSubCategory',
            /**
             * Event will be fired when the user clicks the delete icon in the
             * action column or in the footer menu.
             *
             * This event can easily be captured in the controller
             * eg.
             * <code>
             * this.control({ 'deleteSubCategory' : function(){
             *     console.log('the delete button has been pressed.');
             * }
             * </code>
             *
             * @event deleteSubCategory
             * @param [object] View - Associated Ext.view.Table
             * @param [integer] rowIndex - Row index
             * @param [integer] colIndex - Column index
             * @param [object] item - Associated HTML DOM node
             */
            'deleteSubCategory',
            /**
             * Event will be fired when the user clicks the reload icon in the
             * context menu of the category tree.
             *
             * This event can easily be captured in the controller
             * eg.
             * <code>
             * this.control({ 'reload' : function(){
             *     console.log('the reload button has been pressed.');
             * }
             * </code>
             *
             * @event reloadTree
             * @param [object] View - Associated Ext.view.Table
             * @param [integer] rowIndex - Row index
             * @param [integer] colIndex - Column index
             * @param [object] item - Associated HTML DOM node
             */
            'reload',
            /**
             * Event will be fired when the user want to drop a tree node
             *
             * @event
             * @param [array] node The TreeView node if any over which the mouse was positioned.
             * @param [array] data The data object gathered at mousedown time by the cooperating DragZone's getDragData method it contains the following properties:
             * @param [array] overModel The Model over which the drop gesture took place.
             * @param [array] dropPosition "before", "after" or "append" depending on whether the mouse is above or below the midline of the node, or the node is a branch node which accepts new child nodes.
             * @param [array] dropHandler An object containing methods to complete/cancel the data transfer operation and either move or copy Model instances from the source View's Store to the destination View's Store.
             * @param [array] option The options object passed to Ext.util.Observable.addListener.
             *
             */
            'beforeDropCategory'

        );
        return true;
    },

    /**
     * category folder renderer
     *
     * @param value
     * @param record
     * @param metaData
     */
    categoryFolderRenderer: function (value, metaData, record) {
        if(!record.data.active && !record.data.root) {
            metaData.tdAttr = 'style="opacity:0.4"';
        }
        return value;
    }
});
//{/block}
