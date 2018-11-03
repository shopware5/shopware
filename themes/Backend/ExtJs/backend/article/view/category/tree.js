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
 * @package    Article
 * @subpackage Tree
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Article detail page - Category
 * The tree component contains a tree panel element which list all defined categories of the shop.
 * The tree nodes have an action column, if the category is a leaf category (no child categories)
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/category/tree"}
Ext.define('Shopware.apps.Article.view.category.Tree', {
    /**
     * Define that the billing field set is an extension of the Ext.form.FieldSet
     * @string
     */
    extend:'Ext.tree.Panel',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.article-category-tree',
    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'category-tree',
    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        title: '{s name=category/tree/title}Category{/s}',
        tooltip: '{s name=category/tree/tooltip}Add category{/s}'
    },

    /**
     * Enables multiple selection of the tree nodes
     * @boolean
     */
    multiSelect: true,

    /**
     * Hides the root node.
     * @boolean
     */
    rootVisible: false,
    /**
     * Sets the width of the tree panel
     * @integer
     */
    width: 250,

    /**
     * Displays the split button.
     * @boolean
     */
    split: true,

    padding: '10 0 10 10',

    /**
     * Configuration for the tree view.
     * @object
     */
    viewConfig: {
        plugins: {
            ptype: 'treeviewdragdrop',
            ddGroup: 'Category',
            dragText : '{s name=category/tree/drag_text}[0] selected node [1]{/s}',
            enableDrop: true,
            copy: true
        }
    },

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @return void
     */
    initComponent:function () {
        var me = this;
        me.title = me.snippets.title;
        me.columns = me.getColumns();
        me.registerEvents();
        me.callParent(arguments);
    },

    /**
     * Registers additional component events.
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * Event will be fired when the user want to add a category and used
             * the tree action column. Event will be handled in the category controller.
             *
             * @event
             * @param [array] record - The node record
             */
            'addCategory'
        );
    },

    /**
     * Creates the columns for the tree panel.
     * @return array
     */
    getColumns: function() {
       var me = this;

        return [
            {
                xtype: 'treecolumn',
                text: '&nbsp;',
                flex: 2,
                sortable: true,
                dataIndex: 'name'
            },{
                xtype: 'actioncolumn',
                width: 30,
                items: [{
                    iconCls: 'sprite-plus-circle-frame',
                    tooltip: me.snippets.tooltip,
                    /**
                     * Handler for the action column
                     * @param view
                     * @param rowIndex
                     * @param colIndex
                     * @param item
                     */
                    handler: function (view, rowIndex, colIndex, item, opts,  record) {
                        var records = [ record ];
                        me.fireEvent('addCategory', records);
                    },
                    /**
                     * If the item has no leaf flag, hide the add button
                     * @param value
                     * @param metadata
                     * @param record
                     * @return string
                     */
                    getClass: function(value, metadata, record) {
                        if (!record.isLeaf())  {
                            return 'x-hidden';
                        }
                    }
                }]
            }
        ];
    }

});
//{/block}
