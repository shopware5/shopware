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
 * @subpackage List
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Article detail page - Category
 * The category list component contains the grid panel for the category listing which display all already assigned categories.
 * Each row have an action column to remove the category.
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/category/list"}
Ext.define('Shopware.apps.Article.view.category.List', {
    /**
     * Define that the category list is an extension of the Ext.grid.Panel
     * @string
     */
    extend:'Ext.grid.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.article-category-list',

    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'category-list',

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        title: '{s name=category/list/title}Assigned categories{/s}',
        name: '{s name=category/list/name_column}Category name{/s}',
        delete: '{s name=category/list/delete_tooltip}Remove entry{/s}',
        toolbar: {
            delete: '{s name=category/list/toolbar/delete_button}Remove all selected entries{/s}',
            search: '{s name=search}Search...{/s}'
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

        me.store = me.article.getCategory();
        me.title = me.snippets.title;
        me.columns = me.getColumns();
        me.toolbar = me.createToolbar();
        me.dockedItems = Ext.clone(me.dockedItems);
        me.dockedItems = [ me.toolbar, me.pagingbar ];
        me.selModel = me.getGridSelModel();

        me.registerEvents();
        me.addCls(Ext.baseCSSPrefix + 'free-standing-grid');
        me.callParent(arguments);
    },

    /**
     * Registers additional component events.
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the delete button in the
             * grid toolbar to remove all selected entries
             *
             * @event
             * @param [array] records - The checked grid records
             */
            'removeCategories'
        );
    },

    /**
     * Creates the grid selection model for checkboxes
     *
     * @return [Ext.selection.CheckboxModel] grid selection model
     */
    getGridSelModel:function () {
        var me = this;

        return Ext.create('Ext.selection.CheckboxModel', {
            listeners:{
                // Unlocks the save button if the user has checked at least one checkbox
                selectionchange:function (sm, selections) {
                    if (me.deleteButton === null) {
                        return;
                    }
                    me.deleteButton.setDisabled(selections.length === 0);
                }
            }
        });
    },

    /**
     * Creates the toolbar for the category list.
     * @return Ext.toolbar.Toolbar
     */
    createToolbar: function() {
        var me = this;

        me.deleteButton = Ext.create('Ext.button.Button', {
            iconCls:'sprite-minus-circle-frame',
            text:me.snippets.toolbar.delete,
            disabled:true,
            handler: function() {
                var records = me.selModel.getSelection();
                me.fireEvent('removeCategories', records);
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            dock:'top',
            items:[
                me.deleteButton
            ]
        });
    },

    /**
     * Creates the columns for the grid panel.
     * @return array
     */
    getColumns: function() {
       var me = this;

        return [
            {
                header: me.snippets.name,
                dataIndex: 'name',
                flex: 1
            }, {
                xtype: 'actioncolumn',
                width: 25,
                items: [
                    {
                        iconCls: 'sprite-minus-circle-frame',
                        action: 'delete',
                        tooltip: me.snippets.delete,
                        handler: function (view, rowIndex, colIndex, item, opts, record) {
                            var records = [ record ];

                            me.fireEvent('removeCategories', records);
                        }
                    }
                ]
            }
        ];
    }
});
//{/block}
